$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'deploy-current-fixes.ps1') -LoadFunctionsOnly

if ($taskServer.WebBase -ne '/htdocs/406665027.site.yru.ac.th') {
    throw 'The web root does not match the approved website.'
}

$suspectMigrationPath = 'database/migrations/2026_10_09_000001_allow_suspect_details_in_informant_reports.php'
$suspectMigrationBytes = [IO.File]::ReadAllBytes((Join-Path $taskRoot $suspectMigrationPath))
$suspectMigrationHash = (Get-TaskHash $suspectMigrationBytes).ToLowerInvariant()
$suspectPreviousBytes = Get-TaskRemoteBytes $suspectMigrationPath $true
if ($null -ne $suspectPreviousBytes -and (Get-TaskHash $suspectPreviousBytes) -ne (Get-TaskHash $suspectMigrationBytes)) {
    $suspectBackupPath = Join-Path $PSScriptRoot ('deploy-backups/' + [DateTime]::UtcNow.ToString('yyyyMMdd-HHmmss') + '/' + $suspectMigrationPath)
    [IO.Directory]::CreateDirectory((Split-Path $suspectBackupPath -Parent)) | Out-Null
    [IO.File]::WriteAllBytes($suspectBackupPath, $suspectPreviousBytes)
}
Send-TaskRemoteBytes $suspectMigrationPath $suspectMigrationBytes
if ((Get-TaskHash (Get-TaskRemoteBytes $suspectMigrationPath)) -ne (Get-TaskHash $suspectMigrationBytes)) {
    throw 'Migration upload verification failed.'
}
Write-Output 'Targeted migration uploaded and SHA256 verified.'

$suspectRandom = [Security.Cryptography.RandomNumberGenerator]::Create()
$suspectTokenBytes = [byte[]]::new(32)
try { $suspectRandom.GetBytes($suspectTokenBytes) } finally { $suspectRandom.Dispose() }
$suspectToken = [BitConverter]::ToString($suspectTokenBytes).Replace('-', '')
$suspectTokenHash = (Get-TaskHash ([Text.Encoding]::UTF8.GetBytes($suspectToken))).ToLowerInvariant()
$suspectExpiresAt = ([DateTimeOffset]::UtcNow.ToUnixTimeSeconds() + 600).ToString([Globalization.CultureInfo]::InvariantCulture)
$suspectRunnerName = 'codex-informant-' + [Guid]::NewGuid().ToString('N') + '.php'
$suspectTemplate = [IO.File]::ReadAllText((Join-Path $PSScriptRoot 'informant-suspect-migration-runner.php'))
$suspectRunner = $suspectTemplate.Replace('__TOKEN_HASH__', $suspectTokenHash).Replace('__MIGRATION_HASH__', $suspectMigrationHash).Replace('__EXPIRES_AT__', $suspectExpiresAt)
$suspectRunnerBytes = [Text.Encoding]::UTF8.GetBytes($suspectRunner)
$suspectRunnerAttempted = $false

try {
    $suspectRunnerAttempted = $true
    Send-TaskRemoteBytes $suspectRunnerName $suspectRunnerBytes $taskServer.WebBase
    if ((Get-TaskHash (Get-TaskRemoteBytes $suspectRunnerName $false $taskServer.WebBase)) -ne (Get-TaskHash $suspectRunnerBytes)) {
        throw 'Temporary runner upload verification failed.'
    }
    $suspectRunnerUrl = 'https://406665027.site.yru.ac.th/' + $suspectRunnerName
    try {
        Invoke-WebRequest -Uri $suspectRunnerUrl -Method Get -UseBasicParsing -TimeoutSec 30 | Out-Null
        throw 'Temporary runner unexpectedly allowed unauthenticated access.'
    } catch {
        if ($null -eq $_.Exception.Response -or [int]$_.Exception.Response.StatusCode -ne 404) {
            throw 'Temporary runner authorization check failed.'
        }
    }
    Write-Output 'Temporary runner rejects unauthenticated requests.'
    $suspectResponse = Invoke-WebRequest -Uri $suspectRunnerUrl -Method Post -Headers @{ 'X-Codex-Migration-Token' = $suspectToken } -UseBasicParsing -TimeoutSec 120
    $suspectResult = $suspectResponse.Content | ConvertFrom-Json
    if (!$suspectResult.ok -or !$suspectResult.probe_rows_rolled_back) { throw 'Migration verification did not complete.' }
    Write-Output ($suspectResult | ConvertTo-Json -Compress)
} finally {
    if ($suspectRunnerAttempted) {
        $suspectDeleteRequest = New-TaskFtpRequest $suspectRunnerName ([Net.WebRequestMethods+Ftp]::DeleteFile) $taskServer.WebBase
        $suspectDeleteResponse = $suspectDeleteRequest.GetResponse()
        $suspectDeleteResponse.Close()
        if ($null -ne (Get-TaskRemoteBytes $suspectRunnerName $true $taskServer.WebBase)) { throw 'Temporary runner removal verification failed.' }
        Write-Output 'Temporary runner removed and absence verified.'
    }
}
