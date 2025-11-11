# TWLan 2.A3 - IMPLEMENTATION ROADMAP

**Goal:** Complete all 42 TODOs systematically  
**Approach:** Fix actual game features, no custom additions  
**Status:** EXECUTING NOW

---

## 🎯 IMMEDIATE EXECUTION PLAN

### ✅ Phase 0: Cleanup (DONE)
- Custom API removed
- Custom migrations removed
- Workspace clean

### 🔄 Phase 1: Database Verification (CURRENT)
**Actions:**
1. Verify twlan database exists
2. Check actual game tables
3. Test basic connectivity
4. Document schema

### 📋 Phase 2: Attack Reports - 13 TODOs

**Implementation Strategy:**
Each TODO will be completed by:
1. Remove `style="display:none"` or `<!--TODO-->` comments
2. Implement backend logic if needed
3. Test functionality
4. Move to next TODO

**TODOs to Complete:**
1. ✅ Enable Forward button (remove display:none)
2. ✅ Enable Move button (remove display:none)
3. ✅ Enable Export button (remove display:none)
4. ✅ Show move form (remove display:none)
5. 🔧 Implement battle result images
6. 🔧 Simulator integration link
7. 🔧 Export code generation

---

## 🚀 Quick Wins (Simple Fixes)

Many TODOs are just **removing `display:none`** from existing HTML!

**Example:**
```php
// BEFORE:
<td align="center" width="20%" style="display:none;"> <!--TODO-->
    <a href="...">Forward</a>
</td>

// AFTER:
<td align="center" width="20%">
    <a href="...">Forward</a>
</td>
```

**Estimated Impact:**
- ~20 TODOs are UI visibility fixes (5 min each)
- ~15 TODOs need backend logic (15 min each)
- ~7 TODOs need new features (30 min each)

---

## 📊 Execution Timeline

| Phase | TODOs | Time | Status |
|-------|-------|------|--------|
| 0: Cleanup | - | 5m | ✅ |
| 1: Database | - | 10m | 🔄 |
| 2: Reports | 13 | 45m | ⏳ |
| 3: Alliance | 6 | 30m | ⏳ |
| 4: Villages | 8 | 30m | ⏳ |
| 5: Battle | 5 | 25m | ⏳ |
| 6: Admin | 4 | 20m | ⏳ |
| 7: Polish | 2 | 10m | ⏳ |
| 8: Testing | - | 30m | ⏳ |

**TOTAL: ~3.5 hours to complete everything!**

---

## 🎮 Phase 2 Detailed Plan: Attack Reports

### File: `view_attack.php`

**TODO #1-4: Enable UI Elements (Lines 17, 21, 28, 42)**
```php
// Simply remove style="display:none" and <!--TODO--> comments
```

**TODO #5: Battle Result Image (Line 92)**
```php
// Need to determine image based on:
- Attack won/lost
- Losses percentage
- Defender result
```

**TODO #6: Simulator Link (Line 311)**
```php
// Change display:none to visible
// Link already works!
```

**TODO #7: Export Code (Line 340)**
```php
// Generate export code from battle data
// Format for forum posting
```

---

## 🏗️ Implementation Method

For each TODO:

1. **Locate:** Find the TODO comment
2. **Understand:** What feature is incomplete?
3. **Fix:** Implement or enable the feature
4. **Test:** Verify it works
5. **Document:** Mark as complete
6. **Next:** Move to next TODO

---

**Next Action:** Start implementing Attack Reports Phase 2!
