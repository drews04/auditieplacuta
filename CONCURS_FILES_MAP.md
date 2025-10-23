# 📁 CONCURS SYSTEM — Complete File Map

**Date:** October 20, 2025  
**Purpose:** Quick reference for all files involved in the Concurs (song competition) system

---

## 🗂️ FILE STRUCTURE

```
auditieplacuta/
│
├── 📦 DATABASE
│   ├── migrations/
│   │   ├── 2025_08_19_121932_create_theme_pool_table.php
│   │   ├── 2025_08_19_123733_create_contest_themes_table.php
│   │   ├── 2025_08_19_170355_fix_songs_theme_fk_to_contest_themes.php
│   │   ├── 2025_08_20_003914_update_songs_for_contest_flow.php
│   │   ├── 2025_08_21_142655_create_contest_cycles_table.php         ⚠️ NEEDS UPDATE
│   │   ├── 2025_08_21_142835_add_cycle_id_to_songs_table.php
│   │   ├── 2025_08_21_143127_add_cycle_id_to_votes_table.php
│   │   ├── 2025_08_21_153112_add_cycle_id_to_winners_table.php
│   │   ├── 2025_08_30_061312_add_unique_vote_per_cycle.php
│   │   ├── 2025_08_30_064330_create_theme_likes_table.php
│   │   ├── 2025_09_01_040831_alter_contest_themes_add_likeable_fields.php
│   │   ├── 2025_09_01_051806_add_unique_index_to_theme_likes.php
│   │   ├── 2025_09_01_065238_add_chosen_by_user_id_to_contest_themes.php
│   │   ├── 2025_09_14_115610_harden_votes_uniques_only.php
│   │   ├── 2025_09_14_121358_add_youtube_id_unique_to_songs.php
│   │   ├── 2025_09_17_174355_add_poster_url_to_contest_cycles_table.php
│   │   └── 2025_10_07_032709_drop_tiebreaks_table.php
│   │
│   ├── seeders/
│   │   └── ThemePoolSeeder.php                                       ❌ NEEDS CREATION
│   │
│   └── schema/
│       └── mysql-schema.sql                                           ✅ Production backup
│
├── 📱 MODELS
│   ├── app/Models/
│   │   ├── Song.php                                                   ✅ GOOD
│   │   ├── Vote.php                                                   ✅ GOOD
│   │   ├── Winner.php                                                 ✅ GOOD
│   │   ├── ContestCycle.php                                           ⚠️ NEEDS UPDATE
│   │   ├── ContestTheme.php                                           ✅ GOOD
│   │   ├── ThemePool.php                                              ⚠️ FIX COLUMN NAMES
│   │   └── ThemeLike.php                                              ✅ GOOD
│   │
├── 🎮 CONTROLLERS
│   ├── app/Http/Controllers/
│   │   ├── SongController.php                                         ⚠️ MAJOR REFACTOR NEEDED
│   │   │   ├── showTodaySongs()          → Main /concurs page
│   │   │   ├── uploadPage()              → /concurs/p/upload
│   │   │   ├── votePage()                → /concurs/p/vote
│   │   │   ├── uploadSong()              → POST /concurs/upload       ⚠️ Add ban check
│   │   │   ├── voteForSong()             → POST /concurs/vote         ⚠️ Add window check
│   │   │   └── todayList()               → AJAX song list loader
│   │   │
│   │   ├── ConcursTemaController.php                                  ⚠️ REFACTOR NEEDED
│   │   │   ├── create()                  → Winner theme picker page
│   │   │   └── store()                   → POST theme choice          ⚠️ Add instant transitions
│   │   │
│   │   ├── Admin/
│   │   │   └── ConcursAdminController.php                             🔴 CRITICAL REFACTOR
│   │   │       ├── dashboard()           → Admin widget
│   │   │       ├── start()               → START BUTTON               🔴 REWRITE REQUIRED
│   │   │       ├── startSubmission()     → Open submission lane       ⚠️ Fix schema refs
│   │   │       ├── health()              → JSON health endpoint       ⚠️ Fix schema refs
│   │   │       ├── closeAtTwenty()       → Manual 20:00 close         ⚠️ Fix schema refs
│   │   │       └── promoteAndOpenNewCycle() → Manual promotion        ⚠️ Fix schema refs
│   │   │
│   │   ├── ThemeLikeController.php                                    ✅ GOOD
│   │   ├── ConcursArchiveController.php                               ✅ GOOD
│   │   └── Admin/ConcursPosterController.php                          ✅ GOOD
│   │
├── ⚙️ COMMANDS
│   ├── app/Console/Commands/
│   │   ├── DeclareDailyWinner.php                                     ⚠️ ADD BAN INSERT
│   │   │   └── concurs:declare-winner    → Runs at 20:00
│   │   │
│   │   ├── ConcursFallbackTheme.php                                   ⚠️ USE THEME_POOLS
│   │   │   └── concurs:fallback-theme    → Runs at 21:00
│   │   │
│   │   ├── ConcursHealthCheck.php                                     ⚠️ FIX SCHEMA REFS
│   │   │   └── concurs:health            → Manual diagnostic
│   │   │
│   │   ├── ConcursInheritPoster.php                                   ⚠️ TRIGGER INSTANTLY
│   │   │   └── concurs:inherit-poster    → Runs at 00:02
│   │   │
│   │   └── ConcursAutoRepair.php                                      ❌ NEEDS CREATION
│   │       └── concurs:auto-repair       → Should run every minute
│   │
├── 🛠️ SERVICES
│   ├── app/Services/
│   │   ├── ContestScheduler.php                                       ⚠️ UNUSED? (Check usage)
│   │   ├── AwardPoints.php                                            ✅ GOOD (called from Kernel)
│   │   └── ConcursCycleManager.php                                    ❌ NEEDS CREATION (shared logic)
│   │
├── 🎨 VIEWS
│   ├── resources/views/
│   │   ├── concurs.blade.php                                          ⚠️ ADD READ-ONLY BANNER
│   │   ├── concurs/
│   │   │   ├── upload.blade.php                                       ⚠️ ADD READ-ONLY BANNER
│   │   │   └── vote.blade.php                                         ⚠️ ADD READ-ONLY BANNER
│   │   │
│   │   └── partials/
│   │       ├── theme_picker.blade.php                                 ✅ GOOD
│   │       ├── songs_list.blade.php                                   ✅ GOOD
│   │       └── youtube_modal.blade.php                                ✅ GOOD
│   │
├── 💎 FRONTEND ASSETS
│   ├── public/
│   │   ├── js/
│   │   │   ├── concurs.js                                             ⚠️ REMOVE WEEKEND LOGIC
│   │   │   └── theme-like.js                                          ✅ GOOD
│   │   │
│   │   └── assets/css/
│   │       ├── concurs.css                                            ✅ GOOD
│   │       ├── concurs-winner.css                                     ✅ GOOD
│   │       ├── vote-btn.css                                           ✅ GOOD
│   │       ├── alege-tema.css                                         ✅ GOOD
│   │       ├── theme-like.css                                         ✅ GOOD
│   │       ├── youtube-modal.css                                      ✅ GOOD
│   │       └── concurs-mobile.css                                     ✅ GOOD
│   │
├── 🗺️ ROUTES
│   └── routes/
│       └── web.php                                                    ✅ GOOD (all routes defined)
│           ├── GET  /concurs                        → showTodaySongs()
│           ├── GET  /concurs/p/upload               → uploadPage()
│           ├── GET  /concurs/p/vote                 → votePage()
│           ├── POST /concurs/upload                 → uploadSong()
│           ├── POST /concurs/vote                   → voteForSong()
│           ├── GET  /concurs/alege-tema             → create()
│           ├── POST /concurs/alege-tema             → store()
│           ├── POST /admin/concurs/start            → start()
│           ├── POST /admin/concurs/promote          → promoteAndOpenNewCycle()
│           ├── GET  /admin/concurs/health           → health()
│           └── POST /admin/concurs/close-20         → closeAtTwenty()
│
├── ⏰ SCHEDULER
│   └── app/Console/
│       └── Kernel.php                                                 🔴 ADD MINUTE CRON
│           ├── 20:00 → concurs:declare-winner
│           ├── 20:35 → award-points
│           ├── 21:00 → concurs:fallback-theme
│           ├── 00:02 → concurs:inherit-poster
│           └── MISSING: */1 * * * * → concurs:auto-repair             🔴 ADD THIS
│
└── 🧪 TESTS
    └── tests/Feature/
        └── ConcursDaySimulatorTest.php                                ⚠️ MAY NEED UPDATE

```

---

## 📊 FILES BY PRIORITY

### 🔴 P0 — MUST FIX BEFORE PRODUCTION

1. **`database/migrations/YYYY_MM_DD_HHMMSS_add_lane_status_to_contest_cycles.php`**  
   ❌ **CREATE NEW** — Add `lane`, `status`, `decide_method`, `open_key` columns

2. **`database/seeders/ThemePoolSeeder.php`**  
   ❌ **CREATE NEW** — Seed 20+ themes across categories

3. **`app/Models/ThemePool.php`**  
   ⚠️ **FIX** — Align column names with production DB (`text`, `is_active`, `created_by`)

4. **`app/Http/Controllers/Admin/ConcursAdminController.php`**  
   🔴 **REWRITE** — `start()` method must pick random themes from `theme_pools`

5. **`app/Console/Commands/ConcursAutoRepair.php`**  
   ❌ **CREATE NEW** — Minute-level health check + auto-repair logic

6. **`app/Console/Kernel.php`**  
   🔴 **ADD** — Schedule `concurs:auto-repair` to run `->everyMinute()`

---

### 🟡 P1 — HIGH PRIORITY

7. **`app/Console/Commands/DeclareDailyWinner.php`**  
   ⚠️ **ADD** — Insert winning song's `youtube_id` into `banned_songs` table

8. **`app/Console/Commands/ConcursFallbackTheme.php`**  
   ⚠️ **REFACTOR** — Query `theme_pools` instead of hardcoded wordbank

9. **`app/Http/Controllers/SongController.php`**  
   ⚠️ **UPDATE** — `uploadSong()`: check `banned_songs` + `contest_flags.window`  
   ⚠️ **UPDATE** — `voteForSong()`: check `contest_flags.window`

10. **`app/Http/Controllers/ConcursTemaController.php`**  
    ⚠️ **UPDATE** — `store()`: trigger `concurs:inherit-poster` immediately

11. **`resources/views/concurs/upload.blade.php`**  
    ⚠️ **ADD** — Read-only banner when `$window === 'waiting_theme'`

12. **`resources/views/concurs/vote.blade.php`**  
    ⚠️ **ADD** — Read-only banner when `$window === 'waiting_theme'`

---

### 🔵 P2 — MEDIUM PRIORITY

13. **`public/js/concurs.js`**  
    ⚠️ **REMOVE** — `isWeekday()` logic (system active every day)

14. **`app/Services/ConcursCycleManager.php`**  
    ❌ **CREATE NEW** — Shared service for promote/open logic (reduce duplication)

15. **`app/Models/ContestCycle.php`**  
    ⚠️ **UPDATE** — Add casts/accessors for new columns (`lane`, `status`)

16. **`app/Console/Commands/ConcursHealthCheck.php`**  
    ⚠️ **FIX** — Update queries to use `lane` and `status` columns

---

## 🔗 DEPENDENCY GRAPH

```
DeclareDailyWinner.php
    ↓ (finds finished voting cycle)
    ↓ (tallies votes)
    ↓ (writes to winners table)
    ↓ (logs audit)
    ↓
    ↓ [SHOULD ALSO]
    ↓ ├─→ Insert into banned_songs
    ↓ └─→ Set contest_flags.window = 'waiting_theme'
    ↓
    ↓ (Winner sees modal in browser)
    ↓
ConcursTemaController::store()
    ↓ (winner picks theme)
    ↓ (validates 20:00–21:00 window)
    ↓ (creates theme)
    ↓ (promotes cycles)
    ↓ (unlocks window)
    ↓ [SHOULD ALSO]
    ↓ └─→ Trigger concurs:inherit-poster instantly
    ↓
    ↓ (if winner doesn't pick by 21:00)
    ↓
ConcursFallbackTheme.php
    ↓ (checks contest_flags.window)
    ↓ (picks random from theme_pools)   ← CURRENTLY BROKEN
    ↓ (updates cycle)
    ↓ (calls promoteAndOpenNewCycle)
    ↓
    ↓ (new cycles now open)
    ↓
SongController::uploadSong()
    ↓ (checks if submissions open)
    ↓ [SHOULD ALSO]
    ↓ ├─→ Check contest_flags.window
    ↓ └─→ Check banned_songs
    ↓
SongController::voteForSong()
    ↓ (checks if voting open)
    ↓ [SHOULD ALSO]
    ↓ └─→ Check contest_flags.window
```

---

## 🛡️ SAFETY CHECKS (Before Deployment)

```bash
# 1. Check database schema
php artisan migrate:status

# 2. Verify theme pool populated
php artisan tinker
>>> DB::table('theme_pools')->count()  # Should be >= 10

# 3. Verify scheduler
php artisan schedule:list

# 4. Test Start button (staging only)
php artisan tinker
>>> app(\App\Http\Controllers\Admin\ConcursAdminController::class)->start(request())

# 5. Test fallback
php artisan concurs:fallback-theme

# 6. Test health check
php artisan concurs:health
```

---

## 📞 IMPLEMENTATION CHECKLIST

Before marking as "complete", verify:

- [ ] Migration run successfully (lane/status columns exist)
- [ ] `theme_pools` table has >= 10 active themes
- [ ] `ThemePool` model uses correct column names
- [ ] Start button creates two cycles (submission + voting)
- [ ] `concurs:auto-repair` scheduled every minute
- [ ] Winning songs inserted into `banned_songs`
- [ ] Upload blocked when song is banned
- [ ] Upload/vote blocked when `window='waiting_theme'`
- [ ] Read-only banners show in views
- [ ] Fallback uses `theme_pools` (not hardcoded)
- [ ] Winner modal doesn't check weekday
- [ ] All code referencing `lane`/`status` works
- [ ] Full 20:00–21:00 flow tested end-to-end

---

**End of File Map**

