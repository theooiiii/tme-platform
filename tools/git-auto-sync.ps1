[CmdletBinding()]
param(
    [switch]$Once,
    [string]$ProjectRoot = '',
    [string]$Branch = 'dev',
    [string]$RemoteName = 'origin',
    [string]$RemoteUrl = 'https://github.com/NstiTheo/tme-platform.git',
    [string]$GitPath = 'C:\Program Files\Git\cmd\git.exe',
    [string]$PhpPath = 'C:\xampp\php\php.exe',
    [int]$DebounceSeconds = 4
)

$ErrorActionPreference = 'Stop'

function Resolve-Git {
    if ($GitPath -and (Test-Path -LiteralPath $GitPath)) {
        return (Resolve-Path -LiteralPath $GitPath).Path
    }

    $command = Get-Command git -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    throw 'Git nao encontrado. Instale o Git antes de ativar a automacao.'
}

function Invoke-Git {
    $Arguments = @($args)
    & $script:Git @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao executar: git $($Arguments -join ' ')"
    }
}

function Get-GitOutput {
    $Arguments = @($args)
    $output = & $script:Git @Arguments 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao executar: git $($Arguments -join ' ')`n$($output -join [Environment]::NewLine)"
    }

    return @($output)
}

function Get-GitOptionalOutput {
    $Arguments = @($args)
    try {
        $output = & $script:Git @Arguments 2>$null
        if ($LASTEXITCODE -ne 0) {
            return $null
        }

        return (($output -join [Environment]::NewLine).Trim())
    }
    catch {
        return $null
    }
}

function Write-AutoSyncLog {
    param([string]$Message)

    $line = "[{0}] {1}" -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Message
    Add-Content -LiteralPath $script:LogFile -Value $line -Encoding UTF8
    Write-Host $line
}

function Test-ExcludedRelativePath {
    param([string]$RelativePath)

    $normalized = ($RelativePath -replace '/', '\').TrimStart('\')
    $fileName = Split-Path $normalized -Leaf

    return (
        $normalized -eq '.git' -or
        $normalized -like '.git\*' -or
        $normalized -eq 'vendor' -or
        $normalized -like 'vendor\*' -or
        $normalized -eq 'node_modules' -or
        $normalized -like 'node_modules\*' -or
        $normalized -eq '.automation' -or
        $normalized -like '.automation\*' -or
        $normalized -like 'storage\logs\*' -or
        $normalized -like 'storage\cache\*' -or
        $normalized -like 'storage\temp\*' -or
        $normalized -like 'public\uploads\*' -or
        $fileName -eq '.env' -or
        $fileName -like '*.log' -or
        $fileName -like '*.tmp' -or
        $fileName -like '*.cache'
    )
}

function ConvertTo-RelativePath {
    param([string]$FullPath)

    if ($FullPath.StartsWith($script:ProjectRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
        return $FullPath.Substring($script:ProjectRoot.Length).TrimStart('\', '/')
    }

    return $FullPath
}

function Ensure-GitRepository {
    $inside = Get-GitOptionalOutput -C $script:ProjectRoot rev-parse --is-inside-work-tree
    if ($inside -ne 'true') {
        Write-AutoSyncLog "Inicializando repositorio Git local na branch $Branch."
        Invoke-Git -C $script:ProjectRoot init -b $Branch
    }

    Invoke-Git -C $script:ProjectRoot config pull.rebase true
    Invoke-Git -C $script:ProjectRoot config core.autocrlf true

    $currentBranch = Get-GitOptionalOutput -C $script:ProjectRoot branch --show-current

    if ([string]::IsNullOrWhiteSpace($currentBranch)) {
        Invoke-Git -C $script:ProjectRoot checkout -B $Branch
    }
    elseif ($currentBranch -ne $Branch) {
        $existingBranch = Get-GitOptionalOutput -C $script:ProjectRoot branch --list $Branch
        if ([string]::IsNullOrWhiteSpace($existingBranch)) {
            Invoke-Git -C $script:ProjectRoot switch -c $Branch
        }
        else {
            Invoke-Git -C $script:ProjectRoot switch $Branch
        }
    }

    $remote = Get-GitOptionalOutput -C $script:ProjectRoot remote get-url $RemoteName

    if ([string]::IsNullOrWhiteSpace($remote)) {
        if ([string]::IsNullOrWhiteSpace($RemoteUrl)) {
            throw 'Remote GitHub ausente. Informe -RemoteUrl para ativar o push automatico.'
        }

        Invoke-Git -C $script:ProjectRoot remote add $RemoteName $RemoteUrl
        Write-AutoSyncLog "Remote $RemoteName configurado para $RemoteUrl."
    }
    elseif ($RemoteUrl -and $remote -ne $RemoteUrl) {
        throw "Remote $RemoteName ja existe com outra URL ($remote). Nao sobrescrevi automaticamente."
    }
}

function Get-ChangedFiles {
    $status = Get-GitOutput -C $script:ProjectRoot status --porcelain=v1 -uall
    $files = New-Object System.Collections.Generic.List[string]

    foreach ($line in $status) {
        if ([string]::IsNullOrWhiteSpace($line) -or $line.Length -lt 4) {
            continue
        }

        $path = $line.Substring(3).Trim()
        if ($path -like '* -> *') {
            $path = ($path -split ' -> ')[-1]
        }

        $path = $path.Trim('"')
        if (-not (Test-ExcludedRelativePath $path)) {
            $files.Add($path) | Out-Null
        }
    }

    return @($files | Select-Object -Unique)
}

function Test-CriticalPath {
    param([string]$RelativePath)

    $normalized = ($RelativePath -replace '/', '\').TrimStart('\')

    return (
        $normalized -eq '.htaccess' -or
        $normalized -eq '.env.example' -or
        $normalized -eq 'README.md' -or
        $normalized -eq 'public\index.php' -or
        $normalized -like 'config\*.php' -or
        $normalized -like 'app\core\*.php' -or
        $normalized -like 'database\*.sql' -or
        $normalized -like 'database\migrations\*.sql' -or
        $normalized -like 'database\seeds\*.sql'
    )
}

function Backup-CriticalFiles {
    param([string[]]$Files)

    $criticalFiles = @($Files | Where-Object { Test-CriticalPath $_ })
    if ($criticalFiles.Count -eq 0) {
        return
    }

    $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    foreach ($relative in $criticalFiles) {
        $source = Join-Path $script:ProjectRoot $relative
        if (-not (Test-Path -LiteralPath $source -PathType Leaf)) {
            continue
        }

        $destination = Join-Path (Join-Path $script:BackupRoot $stamp) $relative
        $destinationDirectory = Split-Path $destination -Parent
        New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
        Copy-Item -LiteralPath $source -Destination $destination -Force
        Write-AutoSyncLog "Backup criado para arquivo critico: $relative"
    }
}

function Invoke-ProjectValidation {
    $validator = Join-Path $script:ProjectRoot 'tools\validate-project.ps1'
    if (-not (Test-Path -LiteralPath $validator -PathType Leaf)) {
        throw 'Validador do projeto nao encontrado.'
    }

    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File $validator -ProjectRoot $script:ProjectRoot -PhpPath $PhpPath
    if ($LASTEXITCODE -ne 0) {
        throw 'Validacao falhou. Commit e push foram bloqueados.'
    }
}

function New-AutoCommitMessage {
    param([string[]]$Files)

    $normalized = @($Files | ForEach-Object { $_ -replace '/', '\' })
    $type = 'chore'
    $area = 'projeto'

    if (($normalized | Where-Object { $_ -notlike '*.md' }).Count -eq 0) {
        $type = 'docs'
        $area = 'documentacao'
    }
    elseif (($normalized | Where-Object { $_ -like 'database\*' -or $_ -like '*.sql' }).Count -gt 0) {
        $type = 'db'
        $area = 'banco de dados'
    }
    elseif (($normalized | Where-Object { $_ -like 'assets\css\*' }).Count -gt 0) {
        $type = 'style'
        $area = 'estilos'
    }
    elseif (($normalized | Where-Object { $_ -like 'assets\js\*' }).Count -gt 0) {
        $type = 'feat'
        $area = 'javascript'
    }
    elseif (($normalized | Where-Object { $_ -like 'app\controllers\*' -or $_ -like 'app\models\*' -or $_ -like 'app\views\*' }).Count -gt 0) {
        $type = 'feat'
        $area = 'camadas MVC'
    }
    elseif (($normalized | Where-Object { $_ -like 'config\*' }).Count -gt 0) {
        $type = 'chore'
        $area = 'configuracoes'
    }

    $fileLines = @($Files | Select-Object -First 12 | ForEach-Object { "- $_" })
    if ($Files.Count -gt 12) {
        $fileLines += "- ... e mais $($Files.Count - 12) arquivo(s)"
    }

    return @(
        "$type(auto-sync): atualizar $area",
        '',
        'Arquivos sincronizados automaticamente:',
        ($fileLines -join [Environment]::NewLine)
    ) -join [Environment]::NewLine
}

function Sync-Changes {
    Ensure-GitRepository

    $changedFiles = @(Get-ChangedFiles)
    if ($changedFiles.Count -eq 0) {
        Write-AutoSyncLog 'Nenhuma alteracao relevante para sincronizar.'
        return
    }

    Write-AutoSyncLog "Alteracoes relevantes detectadas: $($changedFiles.Count)."
    Backup-CriticalFiles -Files $changedFiles
    Invoke-ProjectValidation

    Invoke-Git -C $script:ProjectRoot add -A -- .

    try {
        & $script:Git -C $script:ProjectRoot reset -q HEAD -- .env 2>$null
        & $script:Git -C $script:ProjectRoot reset -q HEAD -- .automation 2>$null
    }
    catch {
        # Initial repositories may not have HEAD yet; ignored files are still protected by .gitignore.
    }

    $stagedFiles = @(Get-GitOutput -C $script:ProjectRoot diff --cached --name-only)
    $stagedFiles = @($stagedFiles | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })

    if ($stagedFiles.Count -eq 0) {
        Write-AutoSyncLog 'Nada ficou preparado para commit apos filtros de seguranca.'
        return
    }

    if (($stagedFiles | Where-Object { $_ -eq '.env' -or $_ -like '.automation/*' }).Count -gt 0) {
        throw 'Arquivo sensivel foi preparado para commit. Operacao bloqueada.'
    }

    $message = New-AutoCommitMessage -Files $stagedFiles
    $messageFile = Join-Path $env:TEMP ("tme-auto-commit-{0}.txt" -f ([guid]::NewGuid().ToString('N')))
    Set-Content -LiteralPath $messageFile -Value $message -Encoding ASCII

    try {
        Invoke-Git -C $script:ProjectRoot commit -F $messageFile
    }
    finally {
        Remove-Item -LiteralPath $messageFile -Force -ErrorAction SilentlyContinue
    }

    $remote = Get-GitOptionalOutput -C $script:ProjectRoot remote get-url $RemoteName
    if ([string]::IsNullOrWhiteSpace($remote)) {
        Write-AutoSyncLog 'Commit local criado, mas push pulado porque remote esta ausente.'
        return
    }

    $remoteBranch = Get-GitOptionalOutput -C $script:ProjectRoot ls-remote --exit-code --heads $RemoteName $Branch
    if (-not [string]::IsNullOrWhiteSpace($remoteBranch)) {
        Invoke-Git -C $script:ProjectRoot pull --rebase $RemoteName $Branch
    }

    Invoke-Git -C $script:ProjectRoot push -u $RemoteName $Branch
    Write-AutoSyncLog "Commit enviado para $RemoteName/$Branch."
}

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $scriptDirectory = $PSScriptRoot
    if ([string]::IsNullOrWhiteSpace($scriptDirectory)) {
        $scriptDirectory = Split-Path -Parent $MyInvocation.MyCommand.Path
    }
    $ProjectRoot = Join-Path $scriptDirectory '..'
}

$ProjectRoot = (Resolve-Path -LiteralPath $ProjectRoot).Path
$script:ProjectRoot = $ProjectRoot
$script:Git = Resolve-Git
$script:LogDirectory = Join-Path $ProjectRoot '.automation\logs'
$script:BackupRoot = Join-Path $ProjectRoot '.automation\backups'
$script:LogFile = Join-Path $script:LogDirectory 'git-auto-sync.log'

New-Item -ItemType Directory -Path $script:LogDirectory -Force | Out-Null
New-Item -ItemType Directory -Path $script:BackupRoot -Force | Out-Null

if ($Once) {
    Sync-Changes
    exit 0
}

Ensure-GitRepository
Write-AutoSyncLog "Monitorando $ProjectRoot em tempo real na branch $Branch."

$state = [hashtable]::Synchronized(@{
    Pending = $false
    LastChange = Get-Date
    ProjectRoot = $ProjectRoot
    LogFile = $script:LogFile
})

$eventAction = {
    $fullPath = $Event.SourceEventArgs.FullPath
    $relative = $fullPath.Substring($Event.MessageData.ProjectRoot.Length).TrimStart('\', '/')
    $normalized = ($relative -replace '/', '\').TrimStart('\')
    $fileName = Split-Path $normalized -Leaf

    if (
        $normalized -eq '.git' -or
        $normalized -like '.git\*' -or
        $normalized -eq 'vendor' -or
        $normalized -like 'vendor\*' -or
        $normalized -eq 'node_modules' -or
        $normalized -like 'node_modules\*' -or
        $normalized -eq '.automation' -or
        $normalized -like '.automation\*' -or
        $normalized -like 'storage\logs\*' -or
        $normalized -like 'storage\cache\*' -or
        $normalized -like 'storage\temp\*' -or
        $normalized -like 'public\uploads\*' -or
        $fileName -eq '.env' -or
        $fileName -like '*.log' -or
        $fileName -like '*.tmp' -or
        $fileName -like '*.cache'
    ) {
        return
    }

    $Event.MessageData.Pending = $true
    $Event.MessageData.LastChange = Get-Date
    Add-Content -LiteralPath $Event.MessageData.LogFile -Value ("[{0}] Alteracao detectada: {1} {2}" -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Event.SourceEventArgs.ChangeType, $relative) -Encoding UTF8
}

$watcher = New-Object System.IO.FileSystemWatcher
$watcher.Path = $ProjectRoot
$watcher.Filter = '*'
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter = [System.IO.NotifyFilters]'FileName, DirectoryName, LastWrite, Size'
$watcher.EnableRaisingEvents = $true

$subscriptions = @()
$subscriptions += Register-ObjectEvent -InputObject $watcher -EventName Changed -Action $eventAction -MessageData $state
$subscriptions += Register-ObjectEvent -InputObject $watcher -EventName Created -Action $eventAction -MessageData $state
$subscriptions += Register-ObjectEvent -InputObject $watcher -EventName Deleted -Action $eventAction -MessageData $state
$subscriptions += Register-ObjectEvent -InputObject $watcher -EventName Renamed -Action $eventAction -MessageData $state

try {
    while ($true) {
        Start-Sleep -Seconds 1
        if ($state.Pending -and ((New-TimeSpan -Start $state.LastChange -End (Get-Date)).TotalSeconds -ge $DebounceSeconds)) {
            $state.Pending = $false
            try {
                Sync-Changes
            }
            catch {
                Write-AutoSyncLog "Sincronizacao bloqueada: $($_.Exception.Message)"
            }
        }
    }
}
finally {
    foreach ($subscription in $subscriptions) {
        Unregister-Event -SubscriptionId $subscription.Id -ErrorAction SilentlyContinue
    }
    $watcher.Dispose()
}
