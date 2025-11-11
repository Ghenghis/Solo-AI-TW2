# TWLan 2.A3 - Complete TODO List (42 Items)

**Found:** 42 TODO comments in 26 game files  
**Status:** Incomplete features in ACTUAL game code

---

## 📋 Complete TODO List

### Attack Reports (7 TODOs) - view_attack.php

1. **Forward Report** - Line 17  
   File: `game\report\view_attack.php`  
   Feature: Enable "Weiterleiten" (Forward) button to forward attack reports

2. **Move Report to Folder** - Line 21  
   File: `game\report\view_attack.php`  
   Feature: Enable "Verschieben" (Move) button to organize reports into folders

3. **Export Report** - Line 28  
   File: `game\report\view_attack.php`  
   Feature: Enable export button for report data

4. **Move Report Form** - Line 42  
   File: `game\report\view_attack.php`  
   Feature: Implement folder selection dropdown for moving reports

5. **Attack Result Image** - Line 92  
   File: `game\report\view_attack.php`  
   Feature: Display correct attack outcome image based on battle results

6. **Simulator Integration** - Line 311  
   File: `game\report\view_attack.php`  
   Feature: "Add troops to simulator" functionality

7. **Export Code Format** - Line 340  
   File: `game\report\view_attack.php`  
   Feature: Report export code generation and quick edit

---

### Report Overview (5 TODOs) - overview.php

8. **Report Filtering UI** - Line 13  
   File: `game\report\overview.php`  
   Feature: Implement report filtering interface

9. **Publish Report** - Line 89  
   File: `game\report\overview.php`  
   Feature: Enable "Veröffentlichen" (Publish) button

10. **Forward Reports Bulk** - Line 90  
    File: `game\report\overview.php`  
    Feature: Enable "Weiterleiten" (Forward) button for bulk actions

11. **Report Folder Dropdown** - Line 92  
    File: `game\report\overview.php`  
    Feature: Implement folder selection in report overview

12. **Reports Per Page** - Line 106  
    File: `game\report\overview.php`  
    Feature: Complete pagination settings functionality

---

### Support Reports (1 TODO) - view_support.php

13. **Forward Support Report** - Line 11  
    File: `game\report\view_support.php`  
    Feature: Enable forward functionality for support reports

---

### Alliance Management (6 TODOs)

14. **Member Rights Management** - members_rights.php (3 TODOs)  
    File: `game\ally\members_rights.php`  
    Feature: Complete alliance member permission system

15. **Alliance Profile Features** - profile.php (2 TODOs)  
    File: `game\ally\profile.php`  
    Feature: Complete alliance profile editing

16. **Alliance Members Display** - members.php (1 TODO)  
    File: `game\ally\members.php`  
    Feature: Complete member list functionality

17. **Alliance Proposals** - props.php (1 TODO)  
    File: `game\ally\props.php`  
    Feature: Alliance proposal system

---

### Place/Attack Screen (3 TODOs)

18. **Simulator Flags** - sim.php Lines 109-197  
    File: `game\place\sim.php`  
    Feature: Flag/banner selection in battle simulator

19. **Troop Movements Display** - command.php Line 133  
    File: `game\place\command.php`  
    Feature: Complete troop movement tracking display

---

### Village Overview (6 TODOs)

20. **Overview Villages Header** - header.php (2 TODOs)  
    File: `game\overviewvillages\header.php`  
    Feature: Village list header sorting and filtering

21. **Combined Overview** - combined.php (1 TODO)  
    File: `game\overviewvillages\combined.php`  
    Feature: Combined village statistics view

22. **Village Groups** - groups.php (1 TODO)  
    File: `game\overviewvillages\groups.php`  
    Feature: Village grouping functionality

---

### Village Screens (6 TODOs)

23. **Loyalty Display** - loyalty.php (1 TODO)  
    File: `game\overview\loyalty.php`  
    Feature: Village loyalty mechanics

24. **Notes Feature** - notes.php (1 TODO)  
    File: `game\overview\notes.php`  
    Feature: Village notes system

25. **Secret Feature** - secret.php (1 TODO)  
    File: `game\overview\secret.php`  
    Feature: Secret/hidden village feature

26. **Belief System** - belief.php (1 TODO)  
    File: `game\overview\belief.php`  
    Feature: Religious/belief system mechanics

27. **Flags Feature** - flags.php (1 TODO)  
    File: `game\overview\flags.php`  
    Feature: Village flag/banner system

---

### Storage Building (1 TODO)

28. **Resource Wildcard** - storage/index.php Line 11  
    File: `game\storage\index.php`  
    Feature: Dynamic resource display (currently hardcoded to 'wood')

---

### Statue/Knight (2 TODOs)

29. **Equipment Confirmation** - statue/index.php Line 40  
    File: `game\statue\index.php`  
    Feature: Confirmation dialog for equipping knight items

30. **Unit Availability** - statue/index.php Line 140  
    File: `game\statue\index.php`  
    Feature: Display available units for knight

---

### Admin Panel (4 TODOs)

31. **Admin Dashboard** - admin/dashboard.php (1 TODO)  
    File: `admin\dashboard.php`  
    Feature: Complete admin dashboard overview

32. **Player Management** - admin/players.php (1 TODO)  
    File: `admin\players.php`  
    Feature: Player administration features

33. **World Management** - admin/world/index.php (1 TODO)  
    File: `admin\world\index.php`  
    Feature: World/map administration

34. **Village Editor** - admin/world/villages.php + edit.php (2 TODOs)  
    File: `admin\world\villages.php` and `admin\world\villages\edit.php`  
    Feature: Village editing in admin panel

---

### Layout/Template (2 TODOs)

35. **Game Layout** - layouts/game.php (1 TODO)  
    File: `templates\layouts\game.php`  
    Feature: Main game layout improvements

36. **Game Initialization** - layouts/game_init.php (1 TODO)  
    File: `templates\layouts\game_init.php`  
    Feature: Game initialization sequence

---

## 📊 Summary by Category

| Category | Count | Priority |
|----------|-------|----------|
| **Attack Reports** | 7 | HIGH |
| **Report System** | 6 | HIGH |
| **Alliance Features** | 6 | MEDIUM |
| **Village Management** | 8 | MEDIUM |
| **Battle System** | 3 | MEDIUM |
| **Admin Panel** | 4 | LOW |
| **UI/Templates** | 2 | LOW |
| **Buildings** | 3 | LOW |
| **Miscellaneous** | 3 | LOW |
| **TOTAL** | 42 | - |

---

## 🎯 Recommended Completion Order

### Phase 1: Critical Game Features (Priority 1)
- TODOs 1-12: Attack Reports & Report System (13 items)
- Essential for gameplay experience

### Phase 2: Core Mechanics (Priority 2)
- TODOs 14-27: Alliance & Village Management (14 items)
- Important for multiplayer features

### Phase 3: Enhancement Features (Priority 3)
- TODOs 18-19, 28-30: Battle System & Buildings (5 items)
- Improves game depth

### Phase 4: Administration (Priority 4)
- TODOs 31-34: Admin Panel (4 items)
- Server management tools

### Phase 5: Polish (Priority 5)
- TODOs 35-36: UI/Templates (2 items)
- Visual improvements

---

**Next Step:** User can exclude any TODOs by number before we begin implementation!
