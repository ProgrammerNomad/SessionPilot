param(
    [string]$Version = "1.0.0"
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$distRoot = Join-Path $repoRoot "dist"
$stagingRoot = Join-Path $distRoot "sessionpilot"
$zipPath = Join-Path $distRoot ("sessionpilot-{0}.zip" -f $Version)
$composerLock = Join-Path $repoRoot "composer.lock"

Write-Host "Building SessionPilot ZIP for version $Version"
Write-Host "Repo: $repoRoot"

if (-not (Test-Path $distRoot)) {
    New-Item -ItemType Directory -Path $distRoot | Out-Null
}

if (Test-Path $stagingRoot) {
    Remove-Item $stagingRoot -Recurse -Force
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}
New-Item -ItemType Directory -Path $stagingRoot | Out-Null

# Files and folders required in production ZIP
$include = @(
    "sessionpilot.php",
    "uninstall.php",
    "readme.txt",
    "LICENSE",
    "composer.json",
    "composer.lock",
    "app",
    "bootstrap",
    "database",
    "public",
    "resources"
)

foreach ($item in $include) {
    $source = Join-Path $repoRoot $item
    if (-not (Test-Path $source)) {
        throw "Missing required build path: $item"
    }

    $target = Join-Path $stagingRoot $item
    Copy-Item $source $target -Recurse -Force
}

# Install production dependencies only into staging.
Write-Host "Installing production Composer dependencies in staging"
Push-Location $stagingRoot
try {
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
}
finally {
    Pop-Location
}

# Remove lock file from final distributable (not needed at runtime in WordPress)
$stagingLock = Join-Path $stagingRoot "composer.lock"
if (Test-Path $stagingLock) {
    Remove-Item $stagingLock -Force
}

# Ensure plugin root folder in ZIP is 'sessionpilot/'
Compress-Archive -Path $stagingRoot -DestinationPath $zipPath -CompressionLevel Optimal

Write-Host "Build complete: $zipPath"
