$null = [Console]::In.ReadToEnd()

$projectRoot = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$logPath = Join-Path $PSScriptRoot 'auto-push.log'

function Write-Log([string] $message) {
    "$(Get-Date -Format 's') $message" | Add-Content -LiteralPath $logPath
}

try {
    $branch = (& git -C $projectRoot branch --show-current 2>$null).Trim()
    if (-not $branch) {
        Write-Log 'Skipped: detached HEAD.'
    }
    else {
        & git -C $projectRoot add -A -- . 2>&1 | Out-Null
        if ($LASTEXITCODE -ne 0) { throw 'Git staging failed.' }

        & git -C $projectRoot diff --cached --quiet
        if ($LASTEXITCODE -eq 0) {
            Write-Log 'Skipped: no staged project changes.'
        }
        else {
            if ($LASTEXITCODE -ne 1) { throw 'Unable to inspect staged changes.' }
            & git -C $projectRoot commit -m 'chore: sync automated code changes' 2>&1 | Out-Null
            if ($LASTEXITCODE -ne 0) { throw 'Git commit failed.' }

            & git -C $projectRoot push origin $branch 2>&1 | Out-Null
            if ($LASTEXITCODE -ne 0) { throw 'Git push failed.' }
            Write-Log "Pushed automated commit to origin/$branch."
        }
    }
}
catch {
    Write-Log "Failed: $($_.Exception.Message)"
}

Write-Output '{}'
