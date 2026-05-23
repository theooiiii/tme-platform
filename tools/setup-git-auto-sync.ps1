[CmdletBinding()]
param(
    [string]$ProjectRoot = '',
    [string]$Branch = 'dev',
    [string]$RemoteName = 'origin',
    [string]$RemoteUrl = 'https://github.com/NstiTheo/tme-platform.git',
    [string]$GitPath = 'C:\Program Files\Git\cmd\git.exe',
    [string]$PhpPath = 'C:\xampp\php\php.exe',
    [switch]$StartWatcher
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

    throw 'Git nao encontrado. Instale o Git antes de continuar.'
}

function Invoke-Git {
    $Arguments = @($args)
    & $script:Git @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao executar: git $($Arguments -join ' ')"
    }
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

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $scriptDirectory = $PSScriptRoot
    if ([string]::IsNullOrWhiteSpace($scriptDirectory)) {
        $scriptDirectory = Split-Path -Parent $MyInvocation.MyCommand.Path
    }
    $ProjectRoot = Join-Path $scriptDirectory '..'
}

$ProjectRoot = (Resolve-Path -LiteralPath $ProjectRoot).Path
$script:Git = Resolve-Git

$inside = Get-GitOptionalOutput -C $ProjectRoot rev-parse --is-inside-work-tree
if ($inside -ne 'true') {
    Invoke-Git -C $ProjectRoot init -b $Branch
}

Invoke-Git -C $ProjectRoot config pull.rebase true
Invoke-Git -C $ProjectRoot config core.autocrlf true

$currentBranch = Get-GitOptionalOutput -C $ProjectRoot branch --show-current
if ([string]::IsNullOrWhiteSpace($currentBranch)) {
    Invoke-Git -C $ProjectRoot checkout -B $Branch
}
elseif ($currentBranch -ne $Branch) {
    $existingBranch = Get-GitOptionalOutput -C $ProjectRoot branch --list $Branch
    if ([string]::IsNullOrWhiteSpace($existingBranch)) {
        Invoke-Git -C $ProjectRoot switch -c $Branch
    }
    else {
        Invoke-Git -C $ProjectRoot switch $Branch
    }
}

$remote = Get-GitOptionalOutput -C $ProjectRoot remote get-url $RemoteName
if ([string]::IsNullOrWhiteSpace($remote)) {
    Invoke-Git -C $ProjectRoot remote add $RemoteName $RemoteUrl
}
elseif ($remote -ne $RemoteUrl) {
    throw "Remote $RemoteName ja esta configurado com outra URL: $remote"
}

$validator = Join-Path $ProjectRoot 'tools\validate-project.ps1'
& powershell.exe -NoProfile -ExecutionPolicy Bypass -File $validator -ProjectRoot $ProjectRoot -PhpPath $PhpPath
if ($LASTEXITCODE -ne 0) {
    throw 'Validacao falhou. Automacao nao foi iniciada.'
}

Write-Host "[OK] Git auto-sync configurado em $RemoteName/$Branch."

if ($StartWatcher) {
    $syncScript = Join-Path $ProjectRoot 'tools\git-auto-sync.ps1'
    $arguments = @(
        '-NoProfile',
        '-ExecutionPolicy', 'Bypass',
        '-File', "`"$syncScript`"",
        '-ProjectRoot', "`"$ProjectRoot`"",
        '-Branch', $Branch,
        '-RemoteName', $RemoteName,
        '-RemoteUrl', $RemoteUrl,
        '-GitPath', "`"$script:Git`"",
        '-PhpPath', "`"$PhpPath`""
    )

    Start-Process -FilePath powershell.exe -ArgumentList $arguments -WindowStyle Hidden
    Write-Host "[OK] Monitor git-auto-sync iniciado em segundo plano."
}
