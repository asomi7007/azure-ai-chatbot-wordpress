# Azure AI Chatbot WordPress Plugin ZIP Creator with Verification
# Version: 2.0

param(
    [string]$Version = "3.0.0"
)

$ErrorActionPreference = "Stop"
$ScriptDir = $PSScriptRoot
$PluginDir = $ScriptDir
$TempDir = Join-Path $env:TEMP "azure-ai-chatbot-temp-$(Get-Date -Format 'yyyyMMddHHmmss')"
$ZipFileName = "azure-ai-chatbot-wordpress-$Version.zip"
$ZipFullPath = Join-Path $ScriptDir $ZipFileName

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Azure AI Chatbot WordPress ZIP Creator" -ForegroundColor Cyan
Write-Host "Version: $Version" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# 1. 모든 기존 ZIP 파일 삭제
Write-Host "[1/7] Cleaning up old ZIP files..." -ForegroundColor Yellow
$OldZips = Get-ChildItem -Path $ScriptDir -Filter "azure-ai-chatbot-wordpress-*.zip" -ErrorAction SilentlyContinue
if ($OldZips.Count -gt 0) {
    foreach ($zip in $OldZips) {
        Remove-Item -Path $zip.FullName -Force
        Write-Host "  - Deleted: $($zip.Name)" -ForegroundColor Gray
    }
    Write-Host "  - Removed $($OldZips.Count) old ZIP file(s)" -ForegroundColor Green
} else {
    Write-Host "  - No old ZIP files found" -ForegroundColor Gray
}

# 2. 모든 임시 디렉토리 정리
Write-Host "`n[2/7] Cleaning up ALL temporary directories..." -ForegroundColor Yellow
$AllTempDirs = Get-ChildItem -Path $env:TEMP -Filter "azure-ai-chatbot-temp*" -Directory -ErrorAction SilentlyContinue
if ($AllTempDirs.Count -gt 0) {
    foreach ($dir in $AllTempDirs) {
        try {
            Remove-Item -Path $dir.FullName -Recurse -Force -ErrorAction SilentlyContinue
            Write-Host "  - Removed: $($dir.Name)" -ForegroundColor Gray
        } catch {
            Write-Host "  - Could not remove: $($dir.Name) (in use)" -ForegroundColor DarkGray
        }
    }
}

# 새 임시 디렉토리 생성
New-Item -Path $TempDir -ItemType Directory -Force | Out-Null
Write-Host "  - Created: $TempDir" -ForegroundColor Green

# 3. 파일 복사
Write-Host "`n[3/7] Copying plugin files..." -ForegroundColor Yellow

# 복사할 항목 목록
$ItemsToCopy = @(
    "assets",
    "docs",
    "includes",
    "languages",
    "scripts",
    "templates",
    "azure-ai-chatbot.php",
    "CHANGELOG.md",
    "LICENSE",
    "README.md",
    "README-ko.md",
    "readme.txt",
    "test-agent-mode.sh",
    "test-chat-mode.sh"
)

# 대상 플러그인 디렉토리
$DestPluginDir = Join-Path $TempDir "azure-ai-chatbot-wordpress"
New-Item -Path $DestPluginDir -ItemType Directory | Out-Null

# 파일 복사
$TotalFilesCopied = 0
foreach ($item in $ItemsToCopy) {
    $sourcePath = Join-Path $PluginDir $item
    if (Test-Path $sourcePath) {
        Copy-Item -Path $sourcePath -Destination $DestPluginDir -Recurse -Force
        
        if (Test-Path $sourcePath -PathType Container) {
            $fileCount = (Get-ChildItem -Path $sourcePath -Recurse -File).Count
            Write-Host "  ✓ $item ($fileCount files)" -ForegroundColor Green
            $TotalFilesCopied += $fileCount
        } else {
            Write-Host "  ✓ $item" -ForegroundColor Green
            $TotalFilesCopied++
        }
    } else {
        Write-Host "  ✗ NOT FOUND: $item" -ForegroundColor Red
    }
}
Write-Host "  - Total: $TotalFilesCopied files copied" -ForegroundColor Cyan

# 4. ZIP 파일 생성
Write-Host "`n[4/7] Creating ZIP file..." -ForegroundColor Yellow

$BandizipPath = "C:\Program Files\Bandizip\bz.exe"

if (Test-Path $BandizipPath) {
    Write-Host "  - Using Bandizip (compression level 9)" -ForegroundColor Gray
    Push-Location $TempDir
    & $BandizipPath c -l:9 -r -fmt:zip -y $ZipFullPath "*" 2>&1 | Out-Null
    Pop-Location
} else {
    Write-Host "  - Using PowerShell built-in compression" -ForegroundColor Gray
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($TempDir, $ZipFullPath)
}

if (Test-Path $ZipFullPath) {
    $ZipSize = (Get-Item $ZipFullPath).Length / 1KB
    Write-Host "  ✓ ZIP created: $($ZipSize.ToString('N2')) KB" -ForegroundColor Green
} else {
    Write-Host "  ✗ ZIP creation FAILED!" -ForegroundColor Red
    exit 1
}

# 5. ZIP 파일 구조 확인
Write-Host "`n[5/7] Checking ZIP structure..." -ForegroundColor Yellow

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::OpenRead($ZipFullPath)

$ZipEntries = $zip.Entries | Select-Object -First 10
Write-Host "  - Total entries in ZIP: $($zip.Entries.Count)" -ForegroundColor Gray
Write-Host "  - First 10 entries:" -ForegroundColor Gray
foreach ($entry in $ZipEntries) {
    Write-Host "    $($entry.FullName)" -ForegroundColor DarkGray
}

# 6. 중요 파일 내용 검증
Write-Host "`n[6/7] Verifying critical file contents..." -ForegroundColor Yellow

$CriticalFiles = @(
    @{
        ZipPath = "azure-ai-chatbot-wordpress/azure-ai-chatbot.php"
        LocalPath = "azure-ai-chatbot.php"
        CheckPattern = "Version:\s*$Version"
    },
    @{
        ZipPath = "azure-ai-chatbot-wordpress/templates/oauth-auto-setup.php"
        LocalPath = "templates\oauth-auto-setup.php"
        CheckPattern = "json_encode\(__\("
    },
    @{
        ZipPath = "azure-ai-chatbot-wordpress/templates/settings-page.php"
        LocalPath = "templates\settings-page.php"
        CheckPattern = "Azure AI Chatbot 설정"
    }
)

$AllVerified = $true

foreach ($file in $CriticalFiles) {
    $entry = $zip.Entries | Where-Object { $_.FullName -eq $file.ZipPath }
    
    if (-not $entry) {
        Write-Host "  ✗ $($file.ZipPath) - NOT FOUND IN ZIP!" -ForegroundColor Red
        $AllVerified = $false
        continue
    }
    
    # ZIP 파일 내용 읽기
    $stream = $entry.Open()
    $reader = New-Object System.IO.StreamReader($stream)
    $zipContent = $reader.ReadToEnd()
    $reader.Close()
    $stream.Close()
    
    # 원본 파일 읽기
    $localPath = Join-Path $PluginDir $file.LocalPath
    if (-not (Test-Path $localPath)) {
        Write-Host "  ✗ $($file.LocalPath) - ORIGINAL NOT FOUND!" -ForegroundColor Red
        $AllVerified = $false
        continue
    }
    
    $localContent = Get-Content -Path $localPath -Raw -Encoding UTF8
    
    # 내용 비교
    $zipHash = (Get-FileHash -InputStream ([System.IO.MemoryStream]::new([System.Text.Encoding]::UTF8.GetBytes($zipContent)))).Hash
    $localHash = (Get-FileHash -Path $localPath).Hash
    
    if ($zipHash -eq $localHash) {
        Write-Host "  ✓ $($file.LocalPath) - MATCHED (Hash: $($zipHash.Substring(0,8))...)" -ForegroundColor Green
        
        # 패턴 확인
        if ($zipContent -match $file.CheckPattern) {
            Write-Host "    ✓ Pattern found: $($file.CheckPattern)" -ForegroundColor DarkGreen
        } else {
            Write-Host "    ✗ Pattern NOT found: $($file.CheckPattern)" -ForegroundColor Red
            $AllVerified = $false
        }
    } else {
        Write-Host "  ✗ $($file.LocalPath) - MISMATCH!" -ForegroundColor Red
        Write-Host "    ZIP:   $zipHash" -ForegroundColor Yellow
        Write-Host "    Local: $localHash" -ForegroundColor Yellow
        $AllVerified = $false
        
        # 라인 수 비교
        $zipLines = ($zipContent -split "`n").Count
        $localLines = ($localContent -split "`n").Count
        Write-Host "    ZIP: $zipLines lines, Local: $localLines lines" -ForegroundColor Yellow
    }
}

$zip.Dispose()

# 7. 정리
Write-Host "`n[7/7] Cleaning up..." -ForegroundColor Yellow
Remove-Item -Path $TempDir -Recurse -Force
Write-Host "  - Removed temporary directory" -ForegroundColor Gray

# 최종 결과
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "FINAL RESULT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($AllVerified) {
    Write-Host "✓ ALL VERIFICATIONS PASSED!" -ForegroundColor Green
} else {
    Write-Host "✗ SOME VERIFICATIONS FAILED!" -ForegroundColor Red
    Write-Host "  Please check the ZIP file manually!" -ForegroundColor Yellow
}

Write-Host "`nZIP File Information:" -ForegroundColor White
Write-Host "  Name:      $ZipFileName" -ForegroundColor Gray
Write-Host "  Size:      $($ZipSize.ToString('N2')) KB" -ForegroundColor Gray
Write-Host "  Full Path: $ZipFullPath" -ForegroundColor Gray
Write-Host "`n========================================`n" -ForegroundColor Cyan

# 최종 경로를 클립보드에 복사
Set-Clipboard -Value $ZipFullPath
Write-Host "✓ Full path copied to clipboard!" -ForegroundColor Green
