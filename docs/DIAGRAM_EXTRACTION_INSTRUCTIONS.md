# Diagram Extraction - Instructions

## Quick Start

```powershell
# DRY RUN (preview only - no changes)
.\scripts\extract-diagrams.ps1 -DryRun

# ACTUAL EXTRACTION (makes changes)
.\scripts\extract-diagrams.ps1
```

## What It Does

1. Scans 8 markdown files for embedded ```mermaid``` blocks
2. Extracts each diagram to a standalone `.mmd` file in `./diagrams/`
3. Replaces embedded diagrams with references: `See: [../diagrams/filename.mmd]`
4. Names files descriptively: `{source}-{type}-{number}.mmd`

## Expected Results

| File | Diagrams | Output Files |
|------|----------|--------------|
| SYSTEM_BLUEPRINTS.md | 13 | system-blueprints-*.mmd |
| REVERSE_ENGINEERING_GUIDE.md | 11 | reverse-engineering-guide-*.mmd |
| GAME_LOGIC_COMPLETE.md | 10 | game-logic-complete-*.mmd |
| DATABASE_COMPLETE.md | 5 | database-complete-*.mmd |
| API_DATABASE_SPECS.md | 2 | api-database-specs-*.mmd |
| API_ENDPOINTS_COMPLETE.md | 1 | api-endpoints-complete-*.mmd |
| BACKEND_COMPLETE.md | 1 | backend-complete-*.mmd |
| FRONTEND_COMPLETE.md | 1 | frontend-complete-*.mmd |
| **TOTAL** | **44** | **44 new .mmd files** |

## After Extraction

You'll have:
- ✅ 44 new standalone diagram files in `./diagrams/`
- ✅ 8 markdown files updated with diagram references
- ✅ Professional documentation structure
- ✅ Ready for enterprise review

## Then What?

After extraction completes, we restart **PASS 1 (NEW SYSTEM)** with the corrective/completive approach!
