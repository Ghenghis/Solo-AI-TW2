# Diagram Extraction Script
# Extracts all embedded Mermaid diagrams from markdown files to standalone .mmd files

param(
    [string]$DocsPath = ".\docs",
    [string]$DiagramsPath = ".\diagrams",
    [switch]$DryRun = $false
)

Write-Host "🔍 Diagram Extraction Tool" -ForegroundColor Cyan
Write-Host "==========================`n" -ForegroundColor Cyan

$files = @(
    @{Name="SYSTEM_BLUEPRINTS.md"; Expected=13},
    @{Name="REVERSE_ENGINEERING_GUIDE.md"; Expected=11},
    @{Name="GAME_LOGIC_COMPLETE.md"; Expected=10},
    @{Name="DATABASE_COMPLETE.md"; Expected=5},
    @{Name="API_DATABASE_SPECS.md"; Expected=2},
    @{Name="API_ENDPOINTS_COMPLETE.md"; Expected=1},
    @{Name="BACKEND_COMPLETE.md"; Expected=1},
    @{Name="FRONTEND_COMPLETE.md"; Expected=1}
)

$totalExtracted = 0
$totalReplaced = 0

foreach ($fileInfo in $files) {
    $fileName = $fileInfo.Name
    $filePath = Join-Path $DocsPath $fileName
    
    if (-not (Test-Path $filePath)) {
        Write-Host "⚠️  File not found: $fileName" -ForegroundColor Yellow
        continue
    }
    
    Write-Host "📄 Processing: $fileName" -ForegroundColor Green
    
    $content = Get-Content $filePath -Raw
    $diagramCount = 0
    $newContent = $content
    
    # Find all mermaid code blocks
    $pattern = '(?s)```mermaid\s*(.*?)```'
    $matches = [regex]::Matches($content, $pattern)
    
    Write-Host "   Found $($matches.Count) embedded diagrams (expected: $($fileInfo.Expected))"
    
    foreach ($match in $matches) {
        $diagramCount++
        $diagramContent = $match.Groups[1].Value.Trim()
        
        # Generate diagram filename based on content
        $firstLine = ($diagramContent -split "`n")[0].Trim()
        $diagramType = "unknown"
        
        if ($firstLine -match "graph|flowchart") { $diagramType = "flowchart" }
        elseif ($firstLine -match "sequenceDiagram") { $diagramType = "sequence" }
        elseif ($firstLine -match "erDiagram") { $diagramType = "er-diagram" }
        elseif ($firstLine -match "classDiagram") { $diagramType = "class-diagram" }
        elseif ($firstLine -match "stateDiagram") { $diagramType = "state-diagram" }
        elseif ($firstLine -match "gantt") { $diagramType = "gantt" }
        
        $baseName = $fileName -replace '\.md$', ''
        $diagramFileName = "$baseName-$diagramType-$diagramCount.mmd"
        $diagramFilePath = Join-Path $DiagramsPath $diagramFileName
        
        if (-not $DryRun) {
            # Save diagram to file
            Set-Content -Path $diagramFilePath -Value $diagramContent -NoNewline
            Write-Host "   ✅ Extracted: $diagramFileName" -ForegroundColor Green
            
            # Replace in markdown with reference
            $reference = "See: [../diagrams/$diagramFileName](../diagrams/$diagramFileName)"
            $newContent = $newContent -replace [regex]::Escape($match.Value), $reference
            $totalReplaced++
        }
        else {
            Write-Host "   [DRY RUN] Would extract: $diagramFileName" -ForegroundColor Yellow
        }
        
        $totalExtracted++
    }
    
    if (-not $DryRun -and $diagramCount -gt 0) {
        # Save updated markdown
        Set-Content -Path $filePath -Value $newContent -NoNewline
        Write-Host "   📝 Updated markdown file with $diagramCount references`n" -ForegroundColor Cyan
    }
}

Write-Host "`n=========================="  -ForegroundColor Cyan
Write-Host "✅ Extraction Complete!" -ForegroundColor Green
Write-Host "   Total diagrams extracted: $totalExtracted" -ForegroundColor White
Write-Host "   Total references replaced: $totalReplaced`n" -ForegroundColor White

if ($DryRun) {
    Write-Host "ℹ️  This was a DRY RUN. Run without -DryRun to apply changes." -ForegroundColor Yellow
}
