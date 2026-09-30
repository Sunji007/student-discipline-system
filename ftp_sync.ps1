$localBase = "c:\xampp1\htdocs\student-discipline-system"
if (-not (Test-Path $localBase)) {
    $localBase = "c:\xampp\htdocs\student-discipline-system"
}

$servers = @(
    @{
        Name       = "student.yru.ac.th"
        Host       = "ftp://ftp.student.yru.ac.th"
        User       = "S406665027"
        Pass       = "1969900475054"
        RemoteBase = "/web/406665027.student.yru.ac.th/private/student-discipline-system"
        WebBase    = "/web/406665027.student.yru.ac.th/public_html"
        StateFile  = ".deploy_state_student.json"
    },
    @{
        Name       = "site.yru.ac.th"
        Host       = "ftp://host.site.yru.ac.th"
        User       = "s406665027"
        Pass       = "Sunlun2548"
        RemoteBase = "/private/student-discipline-system"
        WebBase    = "/htdocs/406665027.site.yru.ac.th"
        StateFile  = ".deploy_state.json"
    }
)

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
    "^public/storage",
    "^public\\storage",
    "storage/logs",
    "storage\\logs",
    "storage/framework",
    "storage\\framework",
    "bootstrap/cache",
    "bootstrap\\cache",
    "^\.deploy_state",
    "^temp_",
    "\.ps1$",
    "\.html$",
    "\.md$",
    "\.docx$",
    "\.pdf$",
    "\.xml$",
    "\.dockerignore$",
    "\.editorconfig$",
    "\.gitattributes$",
    "\.gitignore$",
    "Dockerfile$",
    "package.*\.json$",
    "vite\.config\.js$"
)

function Upload-FtpFile($server, $remotePath, $localFilePath) {
    $ftpHost = $server.Host
    $ftpUser = $server.User
    $ftpPass = $server.Pass

    $parts = $remotePath.Substring($ftpHost.Length).Trim('/').Split('/')
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

    $uri = New-Object System.Uri($remotePath)
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

Write-Host "Found $($files.Count) syncable files." -ForegroundColor Cyan

foreach ($server in $servers) {
    Write-Host "`n--- Syncing to $($server.Name) ($($server.Host)) ---" -ForegroundColor Cyan
    $stateFilePath = Join-Path $localBase $server.StateFile
    $state = @{}
    if (Test-Path $stateFilePath) {
        try {
            $jsonObj = Get-Content $stateFilePath -Raw | ConvertFrom-Json
            if ($jsonObj) {
                foreach ($prop in $jsonObj.PSObject.Properties) {
                    $state[$prop.Name] = [string]$prop.Value
                }
            }
        } catch {
            $state = @{}
        }
    }

    $uploadedCount = 0

    foreach ($file in $files) {
        $relativePath = $file.FullName.Substring($localBase.Length + 1).Replace('\', '/')
        $lastWriteTime = $file.LastWriteTime.ToString("o")

        # Check if file has been modified since last sync for this server
        if ($state.ContainsKey($relativePath) -and $state[$relativePath] -eq $lastWriteTime) {
            continue
        }

        Write-Host "Syncing to $($server.Name): $relativePath..." -ForegroundColor Yellow

        try {
            Upload-FtpFile $server "$($server.Host)$($server.RemoteBase)/$relativePath" $file.FullName

            # Also sync public assets into web document root
            if ($relativePath.StartsWith("public/") -and $relativePath -ne "public/index.php") {
                $webRel = $relativePath.Substring(7)
                Upload-FtpFile $server "$($server.Host)$($server.WebBase)/$webRel" $file.FullName
            }

            # For site.yru.ac.th (where symlink is disabled in nginx/PHP), also sync storage/app/public into WebBase/storage
            if ($server.Name -eq "site.yru.ac.th" -and $relativePath.StartsWith("storage/app/public/")) {
                $storageRel = "storage/" + $relativePath.Substring(19)
                Upload-FtpFile $server "$($server.Host)$($server.WebBase)/$storageRel" $file.FullName
            }

            $state[$relativePath] = $lastWriteTime
            $uploadedCount++
        } catch {
            Write-Host "Failed to sync $relativePath to $($server.Name). Error: $_" -ForegroundColor Red
        }
    }

    $state | ConvertTo-Json | Set-Content $stateFilePath
    Write-Host "Completed sync for $($server.Name)! $uploadedCount files uploaded." -ForegroundColor Green
}

# Auto-clear view cache on student server
try {
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12
    $null = Invoke-WebRequest -Uri "https://406665027.student.yru.ac.th/clear-cache.php" -UseBasicParsing -TimeoutSec 10
    Write-Host "View & app cache cleared on 406665027.student.yru.ac.th!" -ForegroundColor Green
} catch {
    # Ignore if timeout
}

Write-Host "`nAll servers are synchronized!" -ForegroundColor Green
