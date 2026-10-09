$ErrorActionPreference = 'Stop'
$evidenceTargets = @('app/Http/Controllers/Discipline/InformantReportController.php', 'routes/web.php', 'resources/views/discipline/informant-reports/show.blade.php')
& (Join-Path $PSScriptRoot 'deploy-current-fixes.ps1') -Paths $evidenceTargets
. (Join-Path $PSScriptRoot 'deploy-current-fixes.ps1') -LoadFunctionsOnly
if ($taskServer.WebBase -ne '/htdocs/406665027.site.yru.ac.th') { throw 'Unexpected website root.' }

$evidenceRandom = [Security.Cryptography.RandomNumberGenerator]::Create()
$evidenceTokenBytes = [byte[]]::new(32)
try { $evidenceRandom.GetBytes($evidenceTokenBytes) } finally { $evidenceRandom.Dispose() }
$evidenceToken = [BitConverter]::ToString($evidenceTokenBytes).Replace('-', '')
$evidenceTokenHash = (Get-TaskHash ([Text.Encoding]::UTF8.GetBytes($evidenceToken))).ToLowerInvariant()
$evidenceExpiry = ([DateTimeOffset]::UtcNow.ToUnixTimeSeconds() + 600).ToString([Globalization.CultureInfo]::InvariantCulture)
$evidenceRunnerName = 'codex-evidence-' + [Guid]::NewGuid().ToString('N') + '.php'
$evidenceTemplate = [IO.File]::ReadAllText((Join-Path $PSScriptRoot 'informant-evidence-verification-runner.php'))
$evidenceRunner = $evidenceTemplate.Replace('__TOKEN_HASH__', $evidenceTokenHash).Replace('__EXPIRES_AT__', $evidenceExpiry)
$evidenceRunnerBytes = [Text.Encoding]::UTF8.GetBytes($evidenceRunner)
$evidenceAttempted = $false
try {
    $evidenceAttempted = $true
    Send-TaskRemoteBytes $evidenceRunnerName $evidenceRunnerBytes $taskServer.WebBase
    if ((Get-TaskHash (Get-TaskRemoteBytes $evidenceRunnerName $false $taskServer.WebBase)) -ne (Get-TaskHash $evidenceRunnerBytes)) { throw 'Runner hash verification failed.' }
    $evidenceRunnerUrl = 'https://406665027.site.yru.ac.th/' + $evidenceRunnerName
    try {
        Invoke-WebRequest -Uri $evidenceRunnerUrl -Method Get -UseBasicParsing -TimeoutSec 30 | Out-Null
        throw 'Runner allowed unauthenticated access.'
    } catch {
        if ($null -eq $_.Exception.Response -or [int]$_.Exception.Response.StatusCode -ne 404) { throw 'Runner authorization check failed.' }
    }
    Write-Output 'Temporary runner rejects unauthenticated requests.'
    try {
        $evidenceResponse = Invoke-WebRequest -Uri $evidenceRunnerUrl -Method Post -Headers @{ 'X-Codex-Migration-Token' = $evidenceToken } -UseBasicParsing -TimeoutSec 60
    } catch {
        # The endpoint only returns sanitized phase and exception type.
        if ($_.ErrorDetails.Message) { Write-Output $_.ErrorDetails.Message }
        throw 'Live evidence verification failed.'
    }
    $evidenceResult = $evidenceResponse.Content | ConvertFrom-Json
    if (!$evidenceResult.ok -or !$evidenceResult.original_image_unchanged) { throw 'Live evidence verification did not complete.' }
    Write-Output ($evidenceResult | ConvertTo-Json -Compress)
    try {
        Invoke-WebRequest -Uri $evidenceResult.evidence_url -MaximumRedirection 0 -UseBasicParsing -TimeoutSec 30 | Out-Null
    } catch {
        if ($null -eq $_.Exception.Response -or [int]$_.Exception.Response.StatusCode -ne 302) { throw 'Evidence authentication verification failed.' }
    }
    Write-Output 'Live evidence route requires authentication.'
} finally {
    if ($evidenceAttempted) {
        $evidenceDeleteRequest = New-TaskFtpRequest $evidenceRunnerName ([Net.WebRequestMethods+Ftp]::DeleteFile) $taskServer.WebBase
        $evidenceDeleteResponse = $evidenceDeleteRequest.GetResponse()
        $evidenceDeleteResponse.Close()
        if ($null -ne (Get-TaskRemoteBytes $evidenceRunnerName $true $taskServer.WebBase)) { throw 'Runner cleanup verification failed.' }
        Write-Output 'Temporary runner removed and absence verified.'
    }
}
