param(
    [string]$Version = "1.0.0"
)

$ErrorActionPreference = "Stop"

$repoRoot    = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$distRoot    = Join-Path $repoRoot "dist"
$stagingRoot = Join-Path $distRoot "sessionpilot"
$zipPath     = Join-Path $distRoot ("sessionpilot-{0}.zip" -f $Version)

Write-Host "=== SessionPilot Release Build v$Version ===" -ForegroundColor Cyan
Write-Host "Repo: $repoRoot"

# ---------------------------------------------------------------------------
# Step 1: Build front-end assets (Vite)
# ---------------------------------------------------------------------------
Write-Host "`n[1/5] Building front-end assets (npm run build)..."
Push-Location $repoRoot
try {
    npm run build --if-present 2>&1 | Write-Host
}
finally {
    Pop-Location
}

# Verify compiled assets exist
$compiledCss = Join-Path $repoRoot "public\css\admin.css"
$compiledJs  = Join-Path $repoRoot "public\js\admin.js"
if (-not (Test-Path $compiledCss)) { throw "Compiled CSS not found. Vite build may have failed: $compiledCss" }
if (-not (Test-Path $compiledJs))  { throw "Compiled JS not found. Vite build may have failed: $compiledJs" }
Write-Host "  Assets OK: public/css/admin.css, public/js/admin.js" -ForegroundColor Green

# ---------------------------------------------------------------------------
# Step 2: Prepare staging directory
# ---------------------------------------------------------------------------
Write-Host "`n[2/5] Preparing staging directory..."
if (-not (Test-Path $distRoot)) { New-Item -ItemType Directory -Path $distRoot | Out-Null }
if (Test-Path $stagingRoot)     { Remove-Item $stagingRoot -Recurse -Force }
if (Test-Path $zipPath)         { Remove-Item $zipPath -Force }
New-Item -ItemType Directory -Path $stagingRoot | Out-Null

# ---------------------------------------------------------------------------
# Step 3: Copy production files
# ---------------------------------------------------------------------------
Write-Host "`n[3/5] Copying production files..."

# Top-level files
foreach ($file in @("sessionpilot.php", "uninstall.php", "readme.txt", "LICENSE", "composer.json", "composer.lock")) {
    $source = Join-Path $repoRoot $file
    if (-not (Test-Path $source)) { throw "Missing required file: $file" }
    Copy-Item $source (Join-Path $stagingRoot $file) -Force
    Write-Host "  Copied: $file"
}

# Directories: app, bootstrap, database
foreach ($dir in @("app", "bootstrap", "database")) {
    $source = Join-Path $repoRoot $dir
    if (-not (Test-Path $source)) { throw "Missing required directory: $dir" }
    Copy-Item $source (Join-Path $stagingRoot $dir) -Recurse -Force
    Write-Host "  Copied: $dir/"
}

# public/ — exclude .vite/ (manifest not needed; assets are enqueued directly)
$publicSrc = Join-Path $repoRoot "public"
$publicDst = Join-Path $stagingRoot "public"
Copy-Item $publicSrc $publicDst -Recurse -Force
$viteCacheDir = Join-Path $publicDst ".vite"
if (Test-Path $viteCacheDir) {
    Remove-Item $viteCacheDir -Recurse -Force
    Write-Host "  Removed: public/.vite/ (build manifest, not needed at runtime)"
}
Write-Host "  Copied: public/"

# resources/views/ only — source CSS/JS (resources/css, resources/js) are excluded
$viewsSrc = Join-Path $repoRoot "resources\views"
$viewsDst = Join-Path $stagingRoot "resources\views"
if (-not (Test-Path $viewsSrc)) { throw "Missing: resources/views/" }
New-Item -ItemType Directory -Path (Join-Path $stagingRoot "resources") -Force | Out-Null
Copy-Item $viewsSrc $viewsDst -Recurse -Force
Write-Host "  Copied: resources/views/ (source CSS/JS excluded — compiled assets in public/)"

# resources/lang/ if present
$langSrc = Join-Path $repoRoot "resources\lang"
if (Test-Path $langSrc) {
    Copy-Item $langSrc (Join-Path $stagingRoot "resources\lang") -Recurse -Force
    Write-Host "  Copied: resources/lang/"
}

# ---------------------------------------------------------------------------
# Step 4: Install production Composer dependencies in staging
# ---------------------------------------------------------------------------
# Install production dependencies only into staging.
Write-Host "[4/5] Installing production Composer dependencies..."
Push-Location $stagingRoot
try {
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
}
finally {
    Pop-Location
}

# Remove only the lock file — composer.json must stay so Acorn's PackageManifest
# can discover SessionPilotServiceProvider when the plugin is active in WordPress.
$stagingLock = Join-Path $stagingRoot "composer.lock"
if (Test-Path $stagingLock) { Remove-Item $stagingLock -Force }

# Clear bootstrap/cache/ — must exist but be empty (Acorn writes here at runtime)
$cacheDir = Join-Path $stagingRoot "bootstrap\cache"
if (Test-Path $cacheDir) {
    Get-ChildItem $cacheDir -File | Where-Object { $_.Name -ne ".gitkeep" } | Remove-Item -Force
}

# ---------------------------------------------------------------------------
# Step 5: Create ZIP
# ---------------------------------------------------------------------------
Write-Host "`n[5/5] Creating ZIP: $zipPath"
Compress-Archive -Path $stagingRoot -DestinationPath $zipPath -CompressionLevel Optimal

$zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "`n=== Build complete ===" -ForegroundColor Green
Write-Host "  Output : $zipPath"
Write-Host "  Size   : ${zipSize} MB"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. git tag v$Version"
Write-Host "  2. git push origin v$Version"
Write-Host "  3. Create GitHub Release for v$Version and attach: sessionpilot-$Version.zip"
