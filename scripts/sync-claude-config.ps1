# Copies agents, commands, rules, and skills into .claude/ for Claude Code.
# Does not modify .claude/settings.json or .claude/settings.local.json.

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$map = [ordered]@{
    'agents'   = '.claude\agents'
    'commands' = '.claude\commands'
    'rules'    = '.claude\rules'
    'skills'   = '.claude\skills'
}

foreach ($name in $map.Keys) {
    $source = Join-Path $root $name
    $dest = Join-Path $root $map[$name]

    if (-not (Test-Path $source)) {
        Write-Error "Missing source folder: $source"
    }

    if (Test-Path $dest) {
        Remove-Item $dest -Recurse -Force
    }

    Copy-Item $source $dest -Recurse -Force
    Write-Output "Synced $name -> $($map[$name])"
}
