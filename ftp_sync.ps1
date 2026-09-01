$ftpHost   = "ftp://ftp.student.yru.ac.th"
$ftpUser   = "S406665027"
$ftpPass   = "1969900475054"
$remoteBase = "/web/406665027.student.yru.ac.th/private/student-discipline-system"
$localBase  = "c:\xampp1\htdocs\student-discipline-system"

$stateFile = Join-Path $localBase ".deploy_state.json"
$state = @{}
if (Test-Path $stateFile) {
    try {
        $state = Get-Content $stateFile -Raw | ConvertFrom-Json -AsHashtable
    } catch {
        $state = @{}
    }
}

# Exclude patterns (regular expressions)
$excludePatterns = @(
    "^\.git",
    "node_modules",
    "vendor",
    "^\.env",
    "^docs",
    "storage/logs",
    "storage/framework",
    "bootstrap/cache",
    "^\.deploy_state\.json",
    "^temp_",
    "\.html$"
)

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

    # Ensure remote directory structure exists
    $parts = $relativePath.Split('/')
    if ($parts.Length -gt 1) {
        $currentRemotePath = "$ftpHost$remoteBase"
        for ($i = 0; $i -lt $parts.Length - 1; $i++) {
            $currentRemotePath += "/" + $parts[$i]
            try {
                $dirUri = New-Object System.Uri($currentRemotePath)
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

    # Upload file
    $remotePath = "$ftpHost$remoteBase/$relativePath"
    try {
        $uri = New-Object System.Uri($remotePath)
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        $request.Timeout = 30000

        $fileContent = [System.IO.File]::ReadAllBytes($file.FullName)
        $request.ContentLength = $fileContent.Length

        $stream = $request.GetRequestStream()
        $stream.Write($fileContent, 0, $fileContent.Length)
        $stream.Close()

        $response = $request.GetResponse()
        $response.Close()

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
