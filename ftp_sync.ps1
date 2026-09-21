$ftpHost   = "ftp://host.site.yru.ac.th"
$ftpUser   = "s406665027"
$ftpPass   = "Sunlun2548"
$remoteBase = "/private/student-discipline-system"
$webBase    = "/htdocs/406665027.site.yru.ac.th"
$localBase  = "c:\xampp1\htdocs\student-discipline-system"
if (-not (Test-Path $localBase)) {
    $localBase = "c:\xampp\htdocs\student-discipline-system"
}

$stateFile = Join-Path $localBase ".deploy_state.json"
$state = @{}
if (Test-Path $stateFile) {
    try {
        $jsonObj = Get-Content $stateFile -Raw | ConvertFrom-Json
        if ($jsonObj) {
            foreach ($prop in $jsonObj.PSObject.Properties) {
                $state[$prop.Name] = [string]$prop.Value
            }
        }
    } catch {
        $state = @{}
    }
}

# Exclude patterns (regular expressions)
# Only core Laravel production runtime files will be uploaded
$excludePatterns = @(
    "^tools(/|\\|$)",
    "^docs(/|\\|$)",
    "^tests(/|\\|$)",
    "^\.agents(/|\\|$)",
    "^\.cursor(/|\\|$)",
    "^\.vscode(/|\\|$)",
    "^\.git(/|\\|$)",
    "node_modules",
    "vendor",
    "^\.env",
    "storage/logs",
    "storage\\logs",
    "storage/framework",
    "storage\\framework",
    "bootstrap/cache",
    "bootstrap\\cache",
    "^\.deploy_state\.json",
    "^temp_",
    "\.ps1$",
    "\.html$",
    "\.md$",
    "\.xml$",
    "\.dockerignore$",
    "\.editorconfig$",
    "\.gitattributes$",
    "\.gitignore$",
    "Dockerfile$",
    "package.*\.json$",
    "vite\.config\.js$"
)

function Upload-FtpFile($ftpFullPath, $localFilePath) {
    $parts = $ftpFullPath.Substring($ftpHost.Length).Trim('/').Split('/')
    if ($parts.Length -gt 1) {
        $cur = $ftpHost
        for ($i = 0; $i -lt $parts.Length - 1; $i++) {
            $cur += "/" + $parts[$i]
            try {
                $dirUri = New-Object System.Uri($cur)
                $dirReq = [System.Net.FtpWebRequest]::Create($dirUri)
                $dirReq.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                $dirReq.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                $dirReq.UsePassive = $true
                $response = $dirReq.GetResponse()
                $response.Close()
            } catch {
                # Directory probably already exists, ignore error
            }
        }
    }

    $uri = New-Object System.Uri($ftpFullPath)
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $request.UseBinary = $true
    $request.UsePassive = $true
    $request.KeepAlive = $false
    $request.Timeout = 30000

    $fileContent = [System.IO.File]::ReadAllBytes($localFilePath)
    $request.ContentLength = $fileContent.Length

    $stream = $request.GetRequestStream()
    $stream.Write($fileContent, 0, $fileContent.Length)
    $stream.Close()

    $response = $request.GetResponse()
    $response.Close()
}

Write-Host "Scanning local files in $localBase..." -ForegroundColor Cyan

# Recursively get all files
$files = Get-ChildItem -Path $localBase -Recurse -File | Where-Object {
    $relativePath = $_.FullName.Substring($localBase.Length + 1).Replace('\', '/')
    $exclude = $false
    foreach ($pattern in $excludePatterns) {
        if ($relativePath -match $pattern) {
            $exclude = $true
            break
        }
    }
    !$exclude
}

Write-Host "Found $($files.Count) syncable files. Checking for modifications..." -ForegroundColor Cyan

$uploadedCount = 0

foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($localBase.Length + 1).Replace('\', '/')
    $lastWriteTime = $file.LastWriteTime.ToString("o")
    
    # Check if file has been modified since last sync
    if ($state.ContainsKey($relativePath) -and $state[$relativePath] -eq $lastWriteTime) {
        continue
    }

    Write-Host "Syncing: $relativePath..." -ForegroundColor Yellow

    try {
        Upload-FtpFile "$ftpHost$remoteBase/$relativePath" $file.FullName

        # Also sync public assets into web document root
        if ($relativePath.StartsWith("public/") -and $relativePath -ne "public/index.php") {
            $webRel = $relativePath.Substring(7)
            Upload-FtpFile "$ftpHost$webBase/$webRel" $file.FullName
        }

        # Update state
        $state[$relativePath] = $lastWriteTime
        $uploadedCount++
    } catch {
        Write-Host "Failed to sync $relativePath. Error: $_" -ForegroundColor Red
    }
}

# Save state
$state | ConvertTo-Json | Set-Content $stateFile

Write-Host "Sync completed! $uploadedCount files uploaded." -ForegroundColor Green
