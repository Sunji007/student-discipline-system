param(
    [int]$PollSeconds = 10,
    [int]$SettleSeconds = 20
)

$projectRoot = Split-Path $PSScriptRoot -Parent
$logPath = Join-Path $projectRoot 'storage\logs\github-sync.log'

function Write-Log([string] $message) {
    $directory = Split-Path $logPath -Parent
    if (-not (Test-Path -LiteralPath $directory)) {
        New-Item -ItemType Directory -Path $directory -Force | Out-Null
    }
    "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') $message" | Add-Content -LiteralPath $logPath
}

function Get-Changes {
    $tracked = @(& git -C $projectRoot diff --name-only 2>$null)
    $untracked = @(& git -C $projectRoot ls-files --others --exclude-standard 2>$null)
    return @($tracked + $untracked | Where-Object { $_ })
}

function Sync-Changes {
    $branch = (& git -C $projectRoot branch --show-current 2>$null).Trim()
    if (-not $branch) {
        Write-Log 'Skipped sync: repository is in detached HEAD state.'
        return
    }

    & git -C $projectRoot diff --cached --quiet
    if ($LASTEXITCODE -eq 1) {
        Write-Log 'Skipped sync: manually staged changes were found.'
        return
    }
    if ($LASTEXITCODE -ne 0) {
        Write-Log 'Unable to inspect the staging area; will retry on the next change check.'
        return
    }

    $allowedExtensions = @('.php', '.js', '.css', '.scss', '.json', '.ps1')
    $changedFiles = Get-Changes
    $codeFiles = @($changedFiles | Where-Object {
        $extension = [System.IO.Path]::GetExtension($_).ToLowerInvariant()
        $allowedExtensions -contains $extension
    })

    if ($codeFiles.Count -eq 0) {
        Write-Log 'Skipped sync: no changed code files matched the allow-list.'
        return
    }

    $secretPattern = '(AKIA[0-9A-Z]{16}|ghp_[A-Za-z0-9]{36}|github_pat_[A-Za-z0-9_]{20,}|-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|(?:DB|MAIL|AWS|GITHUB)_PASSWORD\s*=\s*[^\s]+)'
    foreach ($file in $codeFiles) {
        $fullPath = Join-Path $projectRoot $file
        if ((Test-Path -LiteralPath $fullPath -PathType Leaf) -and (Select-String -LiteralPath $fullPath -Pattern $secretPattern -Quiet)) {
            Write-Log "Blocked sync: possible credential found in $file."
            return
        }
    }

    & git -C $projectRoot add -A -- $codeFiles 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Log 'Staging failed; will retry on the next change check.'
        return
    }

    & git -C $projectRoot diff --cached --quiet
    if ($LASTEXITCODE -eq 0) { return }
    if ($LASTEXITCODE -ne 1) {
        Write-Log 'Unable to inspect staged changes; will retry on the next change check.'
        return
    }

    & git -C $projectRoot commit -m 'chore: sync local code changes' 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Log 'Commit failed; will retry on the next change check.'
        return
    }

    & git -C $projectRoot push origin $branch 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Log "Pushed automatic commit to origin/$branch."
    }
    else {
        Write-Log "Push failed for origin/$branch. Commit remains local and will be retried after the next change."
    }
}

Write-Log "Watcher started (poll: $PollSeconds seconds, settle: $SettleSeconds seconds)."
$pendingSince = $null

while ($true) {
    $changes = Get-Changes
    if ($changes.Count -eq 0) {
        $pendingSince = $null
    }
    elseif ($null -eq $pendingSince) {
        $pendingSince = Get-Date
        Write-Log 'Changes detected; waiting for edits to settle.'
    }
    elseif (((Get-Date) - $pendingSince).TotalSeconds -ge $SettleSeconds) {
        Sync-Changes
        $pendingSince = $null
    }

    Start-Sleep -Seconds $PollSeconds
}
