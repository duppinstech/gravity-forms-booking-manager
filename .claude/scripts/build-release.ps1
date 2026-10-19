# Builds release/gravity-forms-booking-manager.zip for WordPress upload and GitHub Releases.
# Copies the repo, then drops every path in .distignore. release/ is never packed, even if that line is removed.
$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$pluginDir = 'gravity-forms-booking-manager'
$stagingParent = Join-Path $root 'release\staging'
$staging = Join-Path $stagingParent $pluginDir
$outDir = Join-Path $root 'release'
$zipPath = Join-Path $outDir "$pluginDir.zip"
$distignore = Join-Path $root '.distignore'

if (-not (Test-Path $distignore)) {
    throw '.distignore is missing. The release zip is not built without it.'
}

function Get-DistIgnorePatterns {
    param([string]$Path)
    $patterns = @('release')
    foreach ($line in [IO.File]::ReadAllLines($Path)) {
        $trimmed = $line.Trim()
        if ($trimmed.Length -eq 0 -or $trimmed.StartsWith('#')) {
            continue
        }
        $patterns += ($trimmed -replace '\\', '/').TrimEnd('/')
    }
    return $patterns
}

function Test-DistExcluded {
    param([string]$Relative, [string[]]$Patterns)
    $rel = ($Relative -replace '\\', '/').TrimStart('/')
    $name = Split-Path $rel -Leaf
    foreach ($pattern in $Patterns) {
        if ($pattern.Contains('*')) {
            if ($name -like $pattern -or $rel -like $pattern) {
                return $true
            }
            continue
        }
        if ($rel -eq $pattern -or $rel.StartsWith($pattern + '/')) {
            return $true
        }
    }
    return $false
}

function Copy-ReleaseTree {
    param([string]$Source, [string]$Dest, [string]$Relative, [string[]]$Patterns)
    foreach ($item in Get-ChildItem -LiteralPath $Source -Force) {
        $child = if ($Relative) { "$Relative/$($item.Name)" } else { $item.Name }
        if (Test-DistExcluded $child $Patterns) {
            continue
        }
        $target = Join-Path $Dest $item.Name
        if ($item.PSIsContainer) {
            New-Item -ItemType Directory -Path $target -Force | Out-Null
            Copy-ReleaseTree $item.FullName $target $child $Patterns
        }
        else {
            Copy-Item -LiteralPath $item.FullName -Destination $target
        }
    }
}

$patterns = Get-DistIgnorePatterns $distignore

if (Test-Path $stagingParent) {
    Remove-Item $stagingParent -Recurse -Force
}
New-Item -ItemType Directory -Path $staging -Force | Out-Null
Copy-ReleaseTree $root $staging '' $patterns

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
& tar.exe -a -cf $zipPath -C $stagingParent $pluginDir
if ($LASTEXITCODE -ne 0) {
    throw "tar failed with exit code $LASTEXITCODE"
}

Remove-Item $stagingParent -Recurse -Force
Write-Host "Created: $zipPath"
