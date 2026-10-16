# Builds gravity-forms-booking-manager.zip for WordPress upload / GitHub Releases.
$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$staging = Join-Path $root 'release\staging\gravity-forms-booking-manager'
$outDir = Join-Path $root 'release'
$zipPath = Join-Path $outDir 'gravity-forms-booking-manager.zip'

$include = @(
    'gravity-forms-booking-manager.php',
    'includes',
    'assets',
    'README.md',
    'CHANGELOG.md',
    'LICENSE',
    'SECURITY.md',
    'PRIVACY.md',
    'COMMERCIAL.md',
    'THIRD-PARTY-NOTICES.md',
    'uninstall.php',
    'languages'
)

if (Test-Path $staging) {
    Remove-Item $staging -Recurse -Force
}

New-Item -ItemType Directory -Path $staging -Force | Out-Null

foreach ($item in $include) {
    $source = Join-Path $root $item
    if (-not (Test-Path $source)) {
        Write-Warning "Skipping missing: $item"
        continue
    }
    Copy-Item $source (Join-Path $staging (Split-Path $item -Leaf)) -Recurse -Force
}

if (-not (Test-Path $outDir)) {
    New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# tar writes forward-slash paths. Compress-Archive on Windows uses backslashes, which WordPress on Linux will not install.
$tar = Get-Command tar.exe -ErrorAction SilentlyContinue
if (-not $tar) {
    throw 'tar.exe is required to build a WordPress-installable zip.'
}
& tar.exe -a -cf $zipPath -C (Join-Path $root 'release\staging') 'gravity-forms-booking-manager'
if ($LASTEXITCODE -ne 0) {
    throw "tar failed with exit code $LASTEXITCODE"
}

Remove-Item (Join-Path $root 'release\staging') -Recurse -Force

Write-Host "Created: $zipPath"
