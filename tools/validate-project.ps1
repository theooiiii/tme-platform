[CmdletBinding()]
param(
    [string]$ProjectRoot = '',
    [string]$PhpPath = 'C:\xampp\php\php.exe'
)

$ErrorActionPreference = 'Stop'

function Resolve-Tool {
    param(
        [string]$PreferredPath,
        [string]$CommandName
    )

    if ($PreferredPath -and (Test-Path -LiteralPath $PreferredPath)) {
        return (Resolve-Path -LiteralPath $PreferredPath).Path
    }

    $command = Get-Command $CommandName -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    return $null
}

function Add-ValidationError {
    param([string]$Message)
    $script:Errors.Add($Message) | Out-Null
    Write-Host "[ERRO] $Message" -ForegroundColor Red
}

function Add-ValidationWarning {
    param([string]$Message)
    $script:Warnings.Add($Message) | Out-Null
    Write-Host "[AVISO] $Message" -ForegroundColor Yellow
}

function Test-ExcludedFile {
    param([System.IO.FileInfo]$File)

    $relative = $File.FullName.Substring($ProjectRoot.Length).TrimStart('\', '/')
    $normalized = $relative -replace '/', '\'

    return (
        $normalized -like '.git\*' -or
        $normalized -like 'vendor\*' -or
        $normalized -like 'node_modules\*' -or
        $normalized -like '.automation\*' -or
        $normalized -like 'storage\logs\*' -or
        $normalized -like 'storage\cache\*' -or
        $normalized -like 'storage\temp\*' -or
        $normalized -like 'public\uploads\*'
    )
}

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $scriptDirectory = $PSScriptRoot
    if ([string]::IsNullOrWhiteSpace($scriptDirectory)) {
        $scriptDirectory = Split-Path -Parent $MyInvocation.MyCommand.Path
    }
    $ProjectRoot = Join-Path $scriptDirectory '..'
}

$ProjectRoot = (Resolve-Path -LiteralPath $ProjectRoot).Path
$Errors = New-Object System.Collections.Generic.List[string]
$Warnings = New-Object System.Collections.Generic.List[string]

Push-Location $ProjectRoot
try {
    $requiredDirectories = @(
        'app\controllers',
        'app\core',
        'app\models',
        'app\views',
        'config',
        'database',
        'public'
    )

    foreach ($directory in $requiredDirectories) {
        if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot $directory) -PathType Container)) {
            Add-ValidationError "Diretorio MVC obrigatorio ausente: $directory"
        }
    }

    $scanExtensions = @('.php', '.css', '.js', '.sql', '.md')
    $scanFiles = Get-ChildItem -LiteralPath $ProjectRoot -Recurse -File |
        Where-Object { $scanExtensions -contains $_.Extension.ToLowerInvariant() } |
        Where-Object { -not (Test-ExcludedFile $_) }

    foreach ($file in $scanFiles) {
        $relativePath = $file.FullName.Substring($ProjectRoot.Length).TrimStart('\', '/')
        $content = Get-Content -LiteralPath $file.FullName -Raw -ErrorAction Stop

        if ($content -match '(?m)^(<<<<<<<|=======|>>>>>>>)') {
            Add-ValidationError "Marcador de conflito Git encontrado em $relativePath"
        }

        if ($content.IndexOf([char]0) -ge 0) {
            Add-ValidationError "Caractere nulo encontrado em $relativePath"
        }

        if ($file.Extension.ToLowerInvariant() -eq '.css') {
            $openBraces = ([regex]::Matches($content, '\{')).Count
            $closeBraces = ([regex]::Matches($content, '\}')).Count
            if ($openBraces -ne $closeBraces) {
                Add-ValidationError "Quantidade de chaves CSS inconsistente em $relativePath"
            }
        }
    }

    $php = Resolve-Tool -PreferredPath $PhpPath -CommandName 'php'
    if (-not $php) {
        Add-ValidationError 'PHP CLI nao encontrado para validar sintaxe.'
    }
    else {
        $phpFiles = $scanFiles | Where-Object { $_.Extension.ToLowerInvariant() -eq '.php' }
        foreach ($file in $phpFiles) {
            $relativePath = $file.FullName.Substring($ProjectRoot.Length).TrimStart('\', '/')
            $output = & $php -l $file.FullName 2>&1
            if ($LASTEXITCODE -ne 0) {
                Add-ValidationError "Erro de sintaxe PHP em ${relativePath}: $($output -join ' ')"
            }
        }
    }

    $nodeWarningEmitted = $false
    $node = Resolve-Tool -PreferredPath $null -CommandName 'node'
    if ($node) {
        try {
            & $node --version >$null 2>&1
            if ($LASTEXITCODE -ne 0) {
                Add-ValidationWarning 'Node.js foi encontrado, mas nao executou corretamente; validacao sintatica JS foi pulada.'
                $nodeWarningEmitted = $true
                $node = $null
            }
        }
        catch {
            Add-ValidationWarning "Node.js foi encontrado, mas nao pode ser executado: $($_.Exception.Message)"
            $nodeWarningEmitted = $true
            $node = $null
        }
    }

    if ($node) {
        $jsFiles = $scanFiles | Where-Object { $_.Extension.ToLowerInvariant() -eq '.js' }
        foreach ($file in $jsFiles) {
            $relativePath = $file.FullName.Substring($ProjectRoot.Length).TrimStart('\', '/')
            $output = & $node --check $file.FullName 2>&1
            if ($LASTEXITCODE -ne 0) {
                Add-ValidationError "Erro de sintaxe JavaScript em ${relativePath}: $($output -join ' ')"
            }
        }
    }
    elseif (-not $nodeWarningEmitted) {
        Add-ValidationWarning 'Node.js nao encontrado; validacao sintatica JS foi pulada.'
    }

    $git = Resolve-Tool -PreferredPath 'C:\Program Files\Git\cmd\git.exe' -CommandName 'git'
    if ($git -and (Test-Path -LiteralPath (Join-Path $ProjectRoot '.git'))) {
        & $git -C $ProjectRoot check-ignore -q .env
        if ($LASTEXITCODE -ne 0) {
            Add-ValidationError '.env precisa permanecer ignorado pelo Git.'
        }
    }

    if ($Errors.Count -gt 0) {
        Write-Host "[FALHA] Validacao interrompida com $($Errors.Count) erro(s)." -ForegroundColor Red
        exit 1
    }

    Write-Host "[OK] Validacao concluida com sucesso. Avisos: $($Warnings.Count)."
    exit 0
}
finally {
    Pop-Location
}
