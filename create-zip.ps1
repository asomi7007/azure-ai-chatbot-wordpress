# Azure AI Chatbot WordPress Plugin ZIP Creator
# This script creates a distribution ZIP file for the plugin

param(
    [string]$Version = "2.4.0"
)

$ZipFileName = "azure-ai-chatbot-wordpress-$Version.zip"
$BandizipPath = "C:\Program Files\Bandizip\bz.exe"

# Prepare temp folder for packaging
$PluginFolderName = "azure-ai-chatbot-wordpress"
$TempFolder = "temp-zip-$Version"
$PluginTempPath = Join-Path $TempFolder $PluginFolderName

if (Test-Path $TempFolder) {
    Remove-Item $TempFolder -Recurse -Force
}
New-Item -ItemType Directory -Path $PluginTempPath -Force | Out-Null

Write-Host "Copying plugin files to temporary folder..." -ForegroundColor Cyan

# Exclude patterns
$ExcludePatterns = @(
    '*.zip',
    '.git*',
    '.github',
    'node_modules',
    'temp-*',
    '.DS_Store',
    'Thumbs.db',
    '.editorconfig',
    'create-zip.ps1',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'phpunit.xml'
)

# Copy files excluding patterns
Get-ChildItem -Path . -Recurse | Where-Object {
    $item = $_
    $exclude = $false
    foreach ($pattern in $ExcludePatterns) {
        if ($item.Name -like $pattern -or 
            $item.FullName -like "*\.git\*" -or 
            $item.FullName -like "*\.github\*" -or
            $item.FullName -like "*\temp-*\*") {
            $exclude = $true
            break
        }
    }
    -not $exclude
} | ForEach-Object {
    $relativePath = $_.FullName.Replace($PSScriptRoot, "").TrimStart("\")
    $targetPath = Join-Path $PluginTempPath $relativePath
    $targetDir = Split-Path $targetPath -Parent
    
    if (-not (Test-Path $targetDir)) {
        New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    }
    
    if (-not $_.PSIsContainer) {
        Copy-Item $_.FullName -Destination $targetPath -Force
    }
}

# Remove old ZIP if exists
if (Test-Path $ZipFileName) {
    Remove-Item $ZipFileName -Force
    Write-Host "Removed existing ZIP file" -ForegroundColor Yellow
}

# Check if Bandizip exists
if (Test-Path $BandizipPath) {
    Write-Host "Creating ZIP file with Bandizip: $ZipFileName" -ForegroundColor Green
    
    # Create ZIP with Bandizip (compression level 9)
    # ZIP the plugin folder inside temp folder
    $ZipFullPath = Join-Path $PSScriptRoot $ZipFileName
    Push-Location $TempFolder
    & $BandizipPath c -l:9 -fmt:zip -r $ZipFullPath $PluginFolderName
    Pop-Location
    
    # Clean up temp folder
    Remove-Item $TempFolder -Recurse -Force
    
    if (Test-Path $ZipFileName) {
        $FileSize = (Get-Item $ZipFileName).Length / 1KB
        Write-Host "ZIP file created successfully: $ZipFileName ($([math]::Round($FileSize, 2)) KB)" -ForegroundColor Green
    } else {
        Write-Host "Failed to create ZIP file" -ForegroundColor Red
    }
} else {
    Write-Host "Bandizip not found at: $BandizipPath" -ForegroundColor Red
    Write-Host "Using PowerShell Compress-Archive instead..." -ForegroundColor Yellow
    
    # Fallback to PowerShell's Compress-Archive
    $TempFolder = "temp-zip-$Version"
    
    # Create temp folder
    if (Test-Path $TempFolder) {
        Remove-Item $TempFolder -Recurse -Force
    }
    New-Item -ItemType Directory -Path $TempFolder | Out-Null
    
    # Copy files to temp folder (excluding unwanted files)
    $ExcludePatterns = @(
        '*.zip',
        '.git*',
        'node_modules',
        'temp-*',
        '.DS_Store',
        'Thumbs.db',
        'create-zip.ps1',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'phpunit.xml'
    )
    
    Get-ChildItem -Path . -Recurse | Where-Object {
        $item = $_
        $exclude = $false
        foreach ($pattern in $ExcludePatterns) {
            if ($item.Name -like $pattern -or $item.FullName -like "*\.git\*" -or $item.FullName -like "*\.github\*") {
                $exclude = $true
                break
            }
        }
        -not $exclude
    } | ForEach-Object {
        $relativePath = $_.FullName.Replace($PSScriptRoot, "").TrimStart("\")
        $targetPath = Join-Path $TempFolder $relativePath
        $targetDir = Split-Path $targetPath -Parent
        
        if (-not (Test-Path $targetDir)) {
            New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
        }
        
        if (-not $_.PSIsContainer) {
            Copy-Item $_.FullName -Destination $targetPath
        }
    }
    
    # Create ZIP
    if (Test-Path $ZipFileName) {
        Remove-Item $ZipFileName -Force
    }
    
    Compress-Archive -Path "$TempFolder\*" -DestinationPath $ZipFileName -CompressionLevel Optimal
    
    # Clean up temp folder
    Remove-Item $TempFolder -Recurse -Force
    
    if (Test-Path $ZipFileName) {
        $FileSize = (Get-Item $ZipFileName).Length / 1KB
        Write-Host "ZIP file created successfully: $ZipFileName ($([math]::Round($FileSize, 2)) KB)" -ForegroundColor Green
    } else {
        Write-Host "Failed to create ZIP file" -ForegroundColor Red
    }
}
