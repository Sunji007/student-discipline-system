$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path $PSScriptRoot -Parent
$taskTokens = $null
$taskParseErrors = $null
$taskAst = [System.Management.Automation.Language.Parser]::ParseFile((Join-Path $taskRoot 'ftp_sync.ps1'), [ref]$taskTokens, [ref]$taskParseErrors)
if ($taskParseErrors.Count -gt 0) { throw 'Deployment configuration could not be parsed.' }
$taskAssignment = $taskAst.Find({ param($node) $node -is [System.Management.Automation.Language.AssignmentStatementAst] -and $node.Left.VariablePath.UserPath -eq 'servers' }, $true)
$taskServerTables = @($taskAssignment.FindAll({ param($node) $node -is [System.Management.Automation.Language.HashtableAst] }, $true))
$taskServer = $null
foreach ($taskTable in $taskServerTables) {
    $taskCandidate = @{}
    foreach ($taskPair in $taskTable.KeyValuePairs) {
        $taskKey = $taskPair.Item1.SafeGetValue()
        $taskValueExpression = $taskPair.Item2.PipelineElements[0].Expression
        if ($taskValueExpression -isnot [System.Management.Automation.Language.StringConstantExpressionAst]) {
            throw 'Deployment configuration must contain literal strings.'
        }
        $taskCandidate[$taskKey] = $taskValueExpression.Value
    }
    if ($taskCandidate.Name -eq 'site.yru.ac.th') { $taskServer = $taskCandidate }
}
if ($null -eq $taskServer -or $taskServer.Host -ne 'ftp://host.site.yru.ac.th' -or $taskServer.RemoteBase -ne '/private/student-discipline-system') {
    throw 'The deployment destination does not match the approved website.'
}

$taskCredentials = [System.Net.NetworkCredential]::new($taskServer.User, $taskServer.Pass)

function New-TaskFtpRequest([string]$relativePath, [string]$method) {
    $taskUri = [Uri]($taskServer.Host + $taskServer.RemoteBase + '/' + $relativePath)
    $taskRequest = [System.Net.FtpWebRequest]::Create($taskUri)
    $taskRequest.Method = $method
    $taskRequest.Credentials = $taskCredentials
    $taskRequest.UseBinary = $true
    $taskRequest.UsePassive = $true
    $taskRequest.KeepAlive = $false
    $taskRequest.Timeout = 20000
    $taskRequest.ReadWriteTimeout = 20000
    return $taskRequest
}

function Get-TaskRemoteBytes([string]$relativePath, [bool]$allowMissing = $false) {
    $taskRequest = New-TaskFtpRequest $relativePath ([System.Net.WebRequestMethods+Ftp]::DownloadFile)
    $taskResponse = $null
    $taskBuffer = [System.IO.MemoryStream]::new()
    try {
        $taskResponse = $taskRequest.GetResponse()
        $taskResponse.GetResponseStream().CopyTo($taskBuffer)
        return ,$taskBuffer.ToArray()
    } catch {
        $taskFailure = $_.Exception
        while ($null -ne $taskFailure.InnerException) { $taskFailure = $taskFailure.InnerException }
        if ($taskFailure -is [System.Net.WebException] -and $null -ne $taskFailure.Response) {
            $taskMissing = $taskFailure.Response.StatusCode -eq [System.Net.FtpStatusCode]::ActionNotTakenFileUnavailable
            $taskFailure.Response.Close()
            if ($allowMissing -and $taskMissing) { return $null }
        }
        throw "FTP download failed for $relativePath."
    } finally {
        if ($null -ne $taskResponse) { $taskResponse.Close() }
        $taskBuffer.Dispose()
    }
}

function Send-TaskRemoteBytes([string]$relativePath, [byte[]]$bytes) {
    $taskRequest = New-TaskFtpRequest $relativePath ([System.Net.WebRequestMethods+Ftp]::UploadFile)
    $taskRequest.ContentLength = $bytes.Length
    $taskStream = $null
    $taskResponse = $null
    try {
        $taskStream = $taskRequest.GetRequestStream()
        $taskStream.Write($bytes, 0, $bytes.Length)
        $taskStream.Close()
        $taskStream = $null
        $taskResponse = $taskRequest.GetResponse()
    } catch {
        throw "FTP upload failed for $relativePath."
    } finally {
        if ($null -ne $taskStream) { $taskStream.Close() }
        if ($null -ne $taskResponse) { $taskResponse.Close() }
    }
}

function Get-TaskHash([byte[]]$bytes) {
    $taskHasher = [System.Security.Cryptography.SHA256]::Create()
    try { return [BitConverter]::ToString($taskHasher.ComputeHash($bytes)).Replace('-', '') }
    finally { $taskHasher.Dispose() }
}

# Deploy parent credential generation and the login query fix before its form.
$taskPaths = @(
    'app/Http/Controllers/Admin/UserController.php',
    'app/Http/Controllers/Auth/LoginController.php',
    'resources/views/admin/users/create.blade.php'
)
$taskNewPaths = @()
$taskBackupName = [DateTime]::UtcNow.ToString('yyyyMMdd-HHmmss', [Globalization.CultureInfo]::InvariantCulture)
$taskBackupRoot = Join-Path $PSScriptRoot ('deploy-backups/' + $taskBackupName)
$taskChanges = [System.Collections.Generic.List[object]]::new()

foreach ($taskPath in $taskPaths) {
    $taskLocalPath = Join-Path $taskRoot $taskPath
    $taskLocalBytes = [System.IO.File]::ReadAllBytes($taskLocalPath)
    $taskRemoteBytes = Get-TaskRemoteBytes $taskPath ($taskNewPaths -contains $taskPath)
    if ($null -eq $taskRemoteBytes) {
        $taskChanges.Add(@{ Path = $taskPath; Local = $taskLocalBytes; Original = $null })
        Write-Output "New file: $taskPath"
        continue
    }
    if ((Get-TaskHash $taskLocalBytes) -eq (Get-TaskHash $taskRemoteBytes)) {
        Write-Output "Already current: $taskPath"
        continue
    }
    $taskBackupPath = Join-Path $taskBackupRoot $taskPath
    [System.IO.Directory]::CreateDirectory((Split-Path $taskBackupPath -Parent)) | Out-Null
    [System.IO.File]::WriteAllBytes($taskBackupPath, $taskRemoteBytes)
    $taskChanges.Add(@{ Path = $taskPath; Local = $taskLocalBytes; Original = $taskRemoteBytes })
    Write-Output "Backed up: $taskPath"
}

$taskAttempted = [System.Collections.Generic.List[object]]::new()
try {
    foreach ($taskChange in $taskChanges) {
        $taskAttempted.Add($taskChange)
        Send-TaskRemoteBytes $taskChange.Path $taskChange.Local
        $taskReadBack = Get-TaskRemoteBytes $taskChange.Path
        if ((Get-TaskHash $taskReadBack) -ne (Get-TaskHash $taskChange.Local)) {
            throw "Verification failed for $($taskChange.Path)."
        }
        Write-Output "Uploaded and SHA256 verified: $($taskChange.Path)"
    }
} catch {
    Write-Output 'Deployment failed. Restoring attempted files from backup.'
    for ($taskIndex = $taskAttempted.Count - 1; $taskIndex -ge 0; $taskIndex--) {
        $taskRestore = $taskAttempted[$taskIndex]
        try {
            if ($null -eq $taskRestore.Original) {
                $taskDeleteRequest = New-TaskFtpRequest $taskRestore.Path ([System.Net.WebRequestMethods+Ftp]::DeleteFile)
                $taskDeleteResponse = $taskDeleteRequest.GetResponse()
                $taskDeleteResponse.Close()
                if ($null -ne (Get-TaskRemoteBytes $taskRestore.Path $true)) { throw 'New file removal verification failed.' }
                Write-Output "Removed newly uploaded file: $($taskRestore.Path)"
                continue
            }
            Send-TaskRemoteBytes $taskRestore.Path $taskRestore.Original
            if ((Get-TaskHash (Get-TaskRemoteBytes $taskRestore.Path)) -ne (Get-TaskHash $taskRestore.Original)) {
                throw 'Restore verification failed.'
            }
            Write-Output "Restored: $($taskRestore.Path)"
        } catch {
            Write-Output "RESTORE FAILED: $($taskRestore.Path). Backup is available locally."
        }
    }
    throw 'Deployment did not complete. Review the restoration results above.'
}

Write-Output "Deployment completed: $($taskChanges.Count) files uploaded; $($taskPaths.Count) target files verified."
if ($taskChanges.Count -gt 0) { Write-Output "Backup directory: $taskBackupRoot" }
