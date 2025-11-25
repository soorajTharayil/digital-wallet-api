# PowerShell Script to Enable PostgreSQL Extension in PHP
# Run this script as Administrator

$phpIniPath = "C:\xampp\php\php.ini"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Enabling PostgreSQL Extension in PHP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if php.ini exists
if (-not (Test-Path $phpIniPath)) {
    Write-Host "❌ ERROR: php.ini not found at: $phpIniPath" -ForegroundColor Red
    Write-Host "Please update the path in this script." -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Found php.ini at: $phpIniPath" -ForegroundColor Green
Write-Host ""

# Read php.ini content
$content = Get-Content $phpIniPath -Raw

# Check current state
$pdoPgsqlCommented = $content -match ';extension=pdo_pgsql'
$pgsqlCommented = $content -match ';extension=pgsql'
$pdoPgsqlEnabled = $content -match '^extension=pdo_pgsql'
$pgsqlEnabled = $content -match '^extension=pgsql'

Write-Host "Current Status:" -ForegroundColor Yellow
Write-Host "  pdo_pgsql: $(if ($pdoPgsqlEnabled) { '✅ Enabled' } elseif ($pdoPgsqlCommented) { '⚠️ Commented (needs uncommenting)' } else { '❌ Not found' })"
Write-Host "  pgsql:     $(if ($pgsqlEnabled) { '✅ Enabled' } elseif ($pgsqlCommented) { '⚠️ Commented (needs uncommenting)' } else { '❌ Not found' })"
Write-Host ""

# Make changes
$modified = $false

if ($pdoPgsqlCommented) {
    $content = $content -replace ';extension=pdo_pgsql', 'extension=pdo_pgsql'
    Write-Host "✅ Uncommented: extension=pdo_pgsql" -ForegroundColor Green
    $modified = $true
}

if ($pgsqlCommented) {
    $content = $content -replace ';extension=pgsql', 'extension=pgsql'
    Write-Host "✅ Uncommented: extension=pgsql" -ForegroundColor Green
    $modified = $true
}

if (-not $modified) {
    Write-Host "ℹ️  No changes needed - extensions are already configured." -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Please restart your web server (Apache in XAMPP) for changes to take effect." -ForegroundColor Yellow
    exit 0
}

# Backup original file
$backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $phpIniPath $backupPath
Write-Host "✅ Created backup: $backupPath" -ForegroundColor Green
Write-Host ""

# Write modified content
Set-Content -Path $phpIniPath -Value $content -NoNewline
Write-Host "✅ Updated php.ini successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. Restart Apache in XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Run: php artisan config:clear" -ForegroundColor White
Write-Host "3. Run: php artisan migrate" -ForegroundColor White
Write-Host ""

