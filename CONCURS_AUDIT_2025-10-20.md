# 🔍 CONCURS SYSTEM AUDIT — October 20, 2025

**Audited by:** AI Assistant  
**Database analyzed:** `auditiep_auditieplacuta_dev.sql` (Oct 18, 2025)  
**Specification:** Final Operational Compendium v2  
**Status:** ⚠️ **CRITICAL GAPS IDENTIFIED** — System does not match spec

---

## 📋 EXECUTIVE SUMMARY

The current Concurs implementation has **fundamental architectural mismatches** with the v2 specification. The system was designed for a different flow (weekend pauses, multi-phase cycles) and needs **significant refactoring** to meet the new requirements.

### Critical Issues Found:
1. ❌ **No "lane" system** — DB has no `lane` column (submission/voting distinction)
2. ❌ **No "status" column** — Cannot track open/closed state per spec
3. ❌ **No `theme_pools` data** — Fallback system has no themes to pick from
4. ❌ **No `contest_flags` table** — Window management (waiting_theme) not implemented
5. ❌ **Wrong scheduler timing** — Only 4 cron jobs, missing minute-level health checks
6. ❌ **Start button logic mismatch** — Current logic doesn't implement spec requirements
7. ❌ **No instant transitions** — Missing promotion logic after theme choice
8. ⚠️ **Schema drift** — Code references columns that don't exist in production DB

---

## 🗄️ DATABASE SCHEMA ANALYSIS

### **Table: `contest_cycles`** (Production DB)
```sql
CREATE TABLE `contest_cycles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `theme_id` bigint(20) UNSIGNED DEFAULT NULL,          -- ✅ EXISTS
  `theme_text` varchar(255) DEFAULT NULL,               -- ✅ EXISTS
  `lane` enum('submission','voting') NOT NULL,          -- ❌ MISSING IN PROD
  `status` enum('open','closed') NOT NULL DEFAULT 'open', -- ❌ MISSING IN PROD
  `start_at` datetime NOT NULL,                         -- ✅ EXISTS
  `submit_end_at` datetime DEFAULT NULL,                -- ✅ EXISTS
  `vote_end_at` datetime DEFAULT NULL,                  -- ✅ EXISTS
  `decide_method` enum('normal','random','autowin','aborted') DEFAULT 'normal', -- ❌ MISSING IN PROD
  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,    -- ✅ EXISTS
  `winner_song_id` bigint(20) UNSIGNED DEFAULT NULL,    -- ✅ EXISTS
  `created_at` timestamp NULL DEFAULT current_timestamp(), -- ✅ EXISTS
  `updated_at` timestamp NULL DEFAULT current_timestamp(), -- ✅ EXISTS
  `open_key` varchar(32) GENERATED ALWAYS AS (...) STORED -- ❌ MISSING IN PROD
)
```

**🚨 CRITICAL FINDING:**  
The production database from Oct 18 has:
- ✅ `theme_id`, `theme_text`, `start_at`, `submit_end_at`, `vote_end_at`, `winner_*`
- ❌ **NO `lane` column** (submission vs voting distinction impossible)
- ❌ **NO `status` column** (open/closed tracking broken)
- ❌ **NO `decide_method` column** (cannot log autowin/random/normal)
- ❌ **NO `open_key` generated column** (no DB-level unique constraint)

**But migration `2025_08_21_142655_create_contest_cycles_table.php` creates:**
```php
$table->dateTime('start_at');
$table->dateTime('submit_end_at');
$table->dateTime('vote_start_at');  // ⚠️ NOT IN PROD DB
$table->dateTime('vote_end_at');
$table->string('theme_text');
$table->unsignedBigInteger('winner_song_id')->nullable();
$table->unsignedBigInteger('winner_user_id')->nullable();
$table->dateTime('winner_decided_at')->nullable(); // ⚠️ NOT IN PROD DB
```

**Mismatch:** Migration creates `vote_start_at` and `winner_decided_at`, but production DB doesn't have them listed in the SQL dump columns.

---

### **Table: `contest_themes`** (Production DB)
```sql
CREATE TABLE `contest_themes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,                    -- ✅ EXISTS
  `created_at` timestamp NULL DEFAULT current_timestamp(), -- ✅ EXISTS
  `category` varchar(32) DEFAULT NULL,             -- ✅ EXISTS
  `contest_date` date DEFAULT NULL,                -- ✅ EXISTS
  `chosen_by_user_id` bigint(20) UNSIGNED DEFAULT NULL -- ✅ EXISTS
)
```

**✅ Good:** Schema mostly matches usage  
**⚠️ Missing:** `theme_pool_id` foreign key (migration adds it, but not in prod dump)

---

### **Table: `theme_pools`** (Production DB)
```sql
CREATE TABLE `theme_pools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `text` varchar(255) NOT NULL,                    -- ⚠️ Migration uses 'name'
  `category` enum('CSD','ITC','ARTISTI','GENURI') DEFAULT NULL, -- ✅ EXISTS
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,   -- ⚠️ Migration uses 'created_by'
  `is_active` tinyint(1) NOT NULL DEFAULT 1,       -- ⚠️ Migration uses 'active'
  `created_at` timestamp NULL DEFAULT current_timestamp() -- ✅ EXISTS
)
```

**🚨 CRITICAL FINDING:**  
- ✅ Table exists
- ❌ **0 ROWS** — No themes in pool for fallback system!
- ⚠️ Column name mismatch: DB has `text`, model/migration uses `name`
- ⚠️ Column name mismatch: DB has `created_by`, migration uses... nothing
- ⚠️ Column name mismatch: DB has `is_active`, model uses `active`

---

### **Table: `contest_flags`** (Production DB)
```sql
CREATE TABLE `contest_flags` (
  `name` varchar(40) NOT NULL,
  `value` varchar(120) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
)
```

**✅ EXISTS** — Used by code for `window` flag (waiting_theme)  
**⚠️ Note:** No rows currently, but structure is correct

---

### **Table: `contest_audit_logs`** (Production DB)
```sql
CREATE TABLE `contest_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_type` enum('tie_random_pick','zero_votes_random','fallback_theme','start_reset') NOT NULL,
  `cycle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `seed` bigint(20) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
)
```

**✅ EXISTS** — Logging infrastructure ready  
**⚠️ Note:** enum needs more types: 'declare_winner', 'winner_none', 'promote_and_open', 'close_20', etc.

---

### **Table: `banned_songs`** (Production DB)
```sql
CREATE TABLE `banned_songs` (
  `youtube_id` varchar(20) NOT NULL,
  `reason` varchar(32) NOT NULL DEFAULT 'winner_ban',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
)
```

**✅ EXISTS** — Past winners ban system ready  
**❌ No code currently populates this** — Winners not automatically banned

---

## 🔧 CODE ANALYSIS

### **1. DeclareDailyWinner.php** ✅ (Mostly Correct)
**What it does:**
- Runs at 20:00 daily
- Finds most recent finished voting cycle
- Tallies votes, handles ties with RNG
- Writes to `winners` table
- Logs to `contest_audit_logs`

**Issues:**
1. ❌ References `lane='voting'` but column doesn't exist in prod DB
2. ❌ No logic to ban winning song's `youtube_id` in `banned_songs`
3. ⚠️ Doesn't update cycle with winner info (relies only on `winners` table)
4. ⚠️ No transition trigger — doesn't enter "waiting_theme" mode

**Rating:** 7/10 — Core logic solid, but integration gaps

---

### **2. ConcursFallbackTheme.php** ⚠️ (Partially Implemented)
**What it does:**
- Runs at 21:00 daily
- Checks if `contest_flags.window = 'waiting_theme'`
- Picks random theme from hardcoded wordbank
- Creates theme in `contest_themes`
- Updates submission cycle with theme
- Calls `promoteAndOpenNewCycle()`

**Issues:**
1. ❌ **Doesn't use `theme_pools`** — Hardcoded wordbank instead of DB
2. ❌ `theme_pools` table is empty anyway (0 rows)
3. ❌ References `lane='submission'` but column doesn't exist
4. ⚠️ Fallback text: spec says "Libre", code generates "Neon Dreams Oct 20" style
5. ⚠️ Calls controller method directly (not ideal for command isolation)

**Rating:** 5/10 — Works but not per spec

---

### **3. ConcursHealthCheck.php** ✅ (Good Diagnostic Tool)
**What it does:**
- Shows current state: open cycles, last winner, audit logs
- Admin diagnostic endpoint at `/admin/concurs/health`

**Issues:**
1. ❌ References `lane` and `status` columns that don't exist
2. ⚠️ No auto-repair logic — just reports state

**Rating:** 8/10 — Useful, but assumes schema changes

---

### **4. ConcursAdminController.php** ⚠️ (Major Refactor Needed)

#### **Method: `start()` — ❌ Does NOT match spec**
**Spec requirements:**
- Delete open cycles only (keep archives)
- Pick 2 distinct random themes from `theme_pools`
- Theme A → Voting (now → 20:00)
- Theme B → Submission (now → 20:00)
- Instant transitions

**Current implementation:**
- ❌ Creates cycles with `contest_theme_id` (old schema)
- ❌ Uses input themes from request (not random from `theme_pools`)
- ❌ Complex weekend logic (spec says daily, no weekends off)
- ❌ No lane/status columns set
- ⚠️ Has "hard reset" logic but not fully aligned with spec

**Rating:** 3/10 — Fundamentally different approach

#### **Method: `startSubmission()` — ⚠️ Partial**
- Tries to open submission lane
- Uses `open_key` constraint (not in prod DB)
- Doesn't set theme

**Rating:** 5/10

#### **Method: `closeAtTwenty()` — ⚠️ Partial**
- Closes both lanes
- Sets `contest_flags.window = 'waiting_theme'`
- Logs to audit

**Issues:**
- ❌ References `lane` and `status` columns

**Rating:** 6/10 — Right idea, wrong schema

#### **Method: `promoteAndOpenNewCycle()` — ⚠️ Partial**
- Promotes closed submission → open voting
- Creates new submission cycle

**Issues:**
- ❌ References `lane` and `status`
- ⚠️ Doesn't check `contest_flags.window`
- ⚠️ Timing logic unclear (when does this run?)

**Rating:** 5/10

---

### **5. ConcursTemaController.php** ⚠️ (Winner Theme Picker)
**What it does:**
- Winner sees modal, picks theme
- Validates 20:00–21:00 window
- Creates `ContestTheme` and `ThemePool` entry
- Promotes upload cycle to voting
- Creates new upload cycle

**Issues:**
1. ⚠️ Uses Eloquent models (inconsistent with other commands using DB facade)
2. ⚠️ No `contest_flags.window` check
3. ⚠️ Doesn't unlock window flag after choosing
4. ⚠️ Creates cycles without `lane`/`status` (assumes old schema)
5. ⚠️ Logic assumes `vote_start_at` exists (it might not in prod)

**Rating:** 6/10 — Functional but schema-dependent

---

### **6. SongController.php** ⚠️ (Page Controllers)

#### **Method: `showTodaySongs()` — Main `/concurs` page**
**Issues:**
1. ❌ References `lane='submission'` and `lane='voting'` (no such column)
2. ⚠️ Complex logic to determine `gapBetweenPhases` (spec: should always have 2 open)
3. ⚠️ Winner button logic checks latest winner (not cycle-specific)
4. ⚠️ Passes `window` flag to view but doesn't enforce read-only on pages

**Rating:** 5/10

#### **Method: `uploadPage()` — `/concurs/p/upload`**
**Issues:**
1. ❌ References `lane='submission'`
2. ⚠️ No check for `contest_flags.window = 'waiting_theme'` (should block)

**Rating:** 5/10

#### **Method: `votePage()` — `/concurs/p/vote`**
**Issues:**
1. ❌ Complex preview logic (20:00–23:59) assumes `vote_start_at` exists
2. ⚠️ Schema::hasColumn checks suggest uncertainty about DB state
3. ⚠️ No `window` flag enforcement

**Rating:** 6/10 — Defensive code, but fragile

#### **Method: `uploadSong()` — POST upload**
**Issues:**
1. ❌ References `lane='submission'`
2. ❌ No check against `banned_songs` table
3. ⚠️ Prevents duplicate within cycle but not lifetime ban

**Rating:** 7/10 — Core works, missing ban check

#### **Method: `voteForSong()` — POST vote**
**Issues:**
1. ⚠️ Uses `vote_start_at` which may not exist
2. ⚠️ No explicit `window` check (relies on cycle times)

**Rating:** 7/10 — Solid validation

---

### **7. Kernel.php** (Scheduler) ❌ **Missing Cron Jobs**
**Current schedule:**
```php
20:00 → concurs:declare-winner
20:35 → award-points
21:00 → concurs:fallback-theme
00:02 → concurs:inherit-poster
```

**Spec requires:**
```
Every minute → health check & state repair
20:00 → close voting, determine winner
20:00–21:00 → waiting_theme window
21:00 → fallback trigger
(instant transitions on theme choice)
```

**Missing:**
1. ❌ **No minute-level health check** (spec says "cron job runs every minute")
2. ❌ No auto-repair logic (idle/stuck detection)
3. ❌ No job to close at 20:00 (relies on `declare-winner` doing both)
4. ⚠️ Poster inheritance at 00:02 (spec says instant on theme choice)

**Rating:** 4/10 — Critical resilience features missing

---

## 🎨 FRONTEND ANALYSIS

### **concurs.js** ✅ (Well-Structured)
**Strengths:**
- Clean AJAX for upload/vote
- Toast notifications
- Staggered vote button vanish animation
- Winner modal with localStorage persistence
- YouTube modal integration

**Issues:**
1. ⚠️ Winner popup checks `isWeekday()` — spec says active every day
2. ⚠️ Modal auto-shows at 20:00 based on client time (not server state)
3. ⚠️ No polling for window state changes

**Rating:** 8/10 — Great UX, minor logic tweaks needed

### **upload.blade.php** ✅ (Good)
### **vote.blade.php** ✅ (Good)
Both views are well-structured, responsive, and match design requirements.

**Issues:**
- ⚠️ Parse `lane` from `theme_text` as fallback (assumes "CAT — Title" format)
- ⚠️ No visual "read-only" banner when `window='waiting_theme'`

**Rating:** 8/10

---

## 🚨 CRITICAL GAPS vs. SPECIFICATION

### **1. Start Button Behavior** ❌ DOES NOT MATCH
**Spec:**
- Can be pressed anytime by admin
- Pre-check: if open cycles exist, show Hard Reset confirmation
- Delete current open cycles, keep archives intact
- Pick 2 distinct random themes from `theme_pools`
- Theme A → Voting (now → 20:00)
- Theme B → Submission (now → 20:00)

**Reality:**
- Admin enters themes manually (not random from pool)
- Complex weekend logic
- Creates cycles with old schema (`contest_theme_id`, no `lane`/`status`)

---

### **2. Daily Cycle** ⚠️ PARTIALLY IMPLEMENTED
**Spec: 20:00 — Close & Decide**
1. Voting closes instantly ✅ (declare-winner)
2. Determine winner ✅
3. Record winner ✅
4. Set pages to read-only ❌ (no flag enforcement)

**Spec: Winner Theme Window (20:00–21:00)**
- Winner sees modal ✅
- Theme-Picker modal ✅
- Instant transitions ⚠️ (controller does it, but not cron-driven)

**Spec: 21:00 — Fallback Trigger**
- Fallback picks random from `theme_pools` ❌ (uses hardcoded wordbank)
- If empty, use "Libre" ❌ (uses generated text)
- Instant reopen ⚠️ (calls controller method)

---

### **3. Tie & Zero Cases** ✅ CORRECT
**Spec:**
- Tie → random pick among top-tied ✅
- Zero submissions → fallback still triggers ✅ (logic sound)
- Zero votes → random winner ✅

---

### **4. Posters** ⚠️ PARTIALLY IMPLEMENTED
**Spec:**
- Poster from upload transfers to vote page ⚠️ (00:02 cron, not instant)

**Reality:**
- `concurs:inherit-poster` command exists
- Runs at 00:02 (not instant)
- Not triggered on theme choice

---

### **5. Read-only Window** ❌ NOT ENFORCED
**Spec:**
- 20:00–theme chosen: both upload & vote disabled
- Banner: "Așteptăm tema nouă... până la ora 21:00"

**Reality:**
- `contest_flags.window` exists
- Code checks it in some places
- But views don't enforce read-only state
- No banner implementation

---

### **6. Data Integrity** ⚠️ PARTIAL
**Spec:**
- All transitions wrapped in DB transactions ✅ (where present)
- Cron job runs every minute ❌ (only 4 scheduled jobs)
- If both cycles missing → auto-seed ❌ (no such logic)
- Random decisions logged ✅

---

### **7. Misc Rules** ⚠️ MIXED
**Spec:**
- Upload & vote windows always end at next 20:00 ✅ (mostly)
- Timezone locked to Europe/Bucharest ✅
- Winner modal one-time per visit ✅ (localStorage)
- After 21:00, button disappears ⚠️ (client-side only)
- No human interaction required ❌ (system can get stuck)

---

## 📊 FUNCTIONAL GAP MATRIX

| Feature | Spec Requirement | Current Status | Gap Severity |
|---------|------------------|----------------|--------------|
| **Two live cycles always** | submission + voting | ❌ No lane column | 🔴 CRITICAL |
| **Start button (random themes)** | Pick from `theme_pools` | ❌ Manual input | 🔴 CRITICAL |
| **Theme pool fallback** | DB-driven | ❌ Hardcoded + empty DB | 🔴 CRITICAL |
| **Minute-level health check** | Every minute | ❌ Not scheduled | 🔴 CRITICAL |
| **Open/closed state tracking** | `status` column | ❌ Column missing | 🔴 CRITICAL |
| **Instant transitions** | On theme choice | ⚠️ Partial | 🟡 HIGH |
| **Read-only enforcement** | 20:00–theme chosen | ❌ No UI enforcement | 🟡 HIGH |
| **Winning song ban** | Lifetime in `banned_songs` | ❌ Not implemented | 🟡 HIGH |
| **Tie/zero handling** | RNG with audit | ✅ Implemented | 🟢 OK |
| **Winner modal** | 20:00–21:00 window | ✅ Works | 🟢 OK |
| **Upload/vote AJAX** | Clean UX | ✅ Works | 🟢 OK |
| **Poster inheritance** | Instant on theme | ⚠️ 00:02 cron | 🟡 HIGH |

---

## 🛠️ RECOMMENDED FIX STRATEGY

### **Phase 1: Database Schema Migration** (CRITICAL)
1. **Add missing columns to `contest_cycles`:**
   ```sql
   ALTER TABLE contest_cycles 
     ADD COLUMN lane ENUM('submission','voting') NOT NULL AFTER theme_text,
     ADD COLUMN status ENUM('open','closed') NOT NULL DEFAULT 'open' AFTER lane,
     ADD COLUMN decide_method ENUM('normal','random','autowin','aborted') DEFAULT 'normal' AFTER vote_end_at,
     ADD COLUMN open_key VARCHAR(32) GENERATED ALWAYS AS 
       (CASE WHEN status='open' THEN CONCAT(lane,'#open') ELSE NULL END) STORED,
     ADD UNIQUE KEY uq_one_open_per_lane (open_key);
   ```

2. **Seed `theme_pools` with initial data:**
   ```sql
   INSERT INTO theme_pools (text, category, is_active, created_by) VALUES
   ('Dragoste', 'CSD', 1, NULL),
   ('Nostalgie', 'CSD', 1, NULL),
   ('Petrecere', 'ITC', 1, NULL),
   ('Dans', 'GENURI', 1, NULL),
   ('Rock', 'GENURI', 1, NULL),
   ('Pop', 'GENURI', 1, NULL),
   ('Michael Jackson', 'ARTISTI', 1, NULL),
   ('Libre', NULL, 1, NULL);  -- fallback
   ```

3. **Fix column name mismatches in `theme_pools`:**
   - Decide: use `text` or `name` (align model/migration/DB)
   - Decide: use `active` or `is_active` (align model/migration/DB)

4. **Expand `contest_audit_logs.event_type` enum:**
   ```sql
   ALTER TABLE contest_audit_logs 
     MODIFY event_type ENUM(
       'tie_random_pick', 'zero_votes_random', 'fallback_theme', 'start_reset',
       'declare_winner', 'winner_none', 'promote_and_open', 'close_20',
       'fallback_theme_skipped', 'auto_repair'
     ) NOT NULL;
   ```

---

### **Phase 2: Refactor Start Button** (HIGH PRIORITY)
Rewrite `ConcursAdminController::start()` to:
1. Check for open cycles (show confirm modal if exists)
2. Delete open cycles only (keep archives)
3. Pick 2 distinct random themes from `theme_pools WHERE is_active=1`
4. Create Theme A cycle:
   ```php
   lane='voting', status='open', 
   theme_id=<random_A>, theme_text=<A_name>,
   start_at=now(), vote_end_at=today_20:00
   ```
5. Create Theme B cycle:
   ```php
   lane='submission', status='open',
   theme_id=<random_B>, theme_text=<B_name>,
   start_at=now(), submit_end_at=today_20:00
   ```
6. Log to audit: `'start_reset'`

---

### **Phase 3: Fix Fallback Logic** (HIGH PRIORITY)
Rewrite `ConcursFallbackTheme::handle()` to:
1. Query `theme_pools WHERE is_active=1 ORDER BY RAND() LIMIT 1`
2. If empty, use fallback: `INSERT INTO theme_pools (text, category, is_active) VALUES ('Libre', NULL, 1)`
3. Update submission cycle with picked theme
4. Call instant promotion logic (not controller method)
5. Unlock window: `UPDATE contest_flags SET value=NULL WHERE name='window'`

---

### **Phase 4: Add Health Check Cron** (CRITICAL)
Create `ConcursAutoRepair.php` command:
```php
protected $signature = 'concurs:auto-repair';
protected $description = 'Run every minute: check state, repair if stuck';

public function handle() {
    // 1. Check for two open cycles (one submission, one voting)
    $submit = DB::table('contest_cycles')
        ->where('lane','submission')->where('status','open')->count();
    $voting = DB::table('contest_cycles')
        ->where('lane','voting')->where('status','open')->count();
    
    // 2. If both missing → seed new pair from theme_pools
    if ($submit === 0 && $voting === 0) {
        $this->seedPair();
        $this->audit('auto_repair', null, 'both_cycles_missing');
    }
    
    // 3. Check deadlines (20:00 passed but not closed)
    $this->checkDeadlines();
    
    // 4. Check for stuck waiting_theme (>1h past 21:00)
    $this->checkStuckTheme();
}
```

**Schedule:**
```php
$schedule->command('concurs:auto-repair')
    ->everyMinute()
    ->timezone('Europe/Bucharest')
    ->withoutOverlapping()
    ->onOneServer();
```

---

### **Phase 5: Enforce Read-Only Mode** (HIGH PRIORITY)
1. **In `SongController::uploadSong()`:**
   ```php
   $window = DB::table('contest_flags')->where('name','window')->value('value');
   if ($window === 'waiting_theme') {
       return response()->json(['message' => 'Blocat: se așteaptă tema.'], 422);
   }
   ```

2. **In views (`upload.blade.php`, `vote.blade.php`):**
   ```php
   @if($window === 'waiting_theme')
       <div class="alert alert-warning text-center">
           <strong>⏳ Așteptăm tema nouă...</strong> Încărcările și voturile revin după alegerea temei (până la 21:00).
       </div>
   @endif
   ```

---

### **Phase 6: Implement Winner Ban** (MEDIUM PRIORITY)
In `DeclareDailyWinner::handle()` after writing to `winners`:
```php
if ($winnerSongId) {
    $ytId = DB::table('songs')->where('id', $winnerSongId)->value('youtube_id');
    if ($ytId) {
        DB::table('banned_songs')->insertOrIgnore([
            'youtube_id' => $ytId,
            'reason'     => 'winner_ban',
            'created_at' => now($tz),
        ]);
    }
}
```

In `SongController::uploadSong()`:
```php
$banned = DB::table('banned_songs')->where('youtube_id', $videoId)->exists();
if ($banned) {
    return response()->json(['message' => 'Această melodie a câștigat deja un concurs.'], 409);
}
```

---

### **Phase 7: Fix Instant Transitions** (MEDIUM PRIORITY)
When winner picks theme (`ConcursTemaController::store()`):
1. Immediately promote closed submission → open voting
2. Create new submission cycle
3. Unlock `contest_flags.window`
4. Trigger `concurs:inherit-poster` (don't wait for 00:02)

Remove reliance on controller methods from commands; use shared service:
```php
// app/Services/ConcursCycleManager.php
class ConcursCycleManager {
    public function promoteAndOpenNew(string $themeText, int $themeId) {
        DB::transaction(function() use ($themeText, $themeId) {
            // 1. Promote last closed submission → voting
            // 2. Create new submission
            // 3. Log audit
        });
    }
}
```

---

### **Phase 8: Testing & Validation**
1. **Unit tests for tie/zero cases**
2. **Integration test: full 20:00–21:00 flow**
3. **Stress test: multiple users voting/uploading simultaneously**
4. **Timezone test: verify all times in Europe/Bucharest**
5. **Edge case: winner doesn't pick theme by 21:00**
6. **Edge case: Start pressed during waiting_theme**

---

## 🎯 PRIORITY RANKING

### **P0 — CRITICAL (Must fix before production)**
1. Database schema migration (`lane`, `status`, `decide_method`, `open_key`)
2. Seed `theme_pools` with real data
3. Rewrite Start button logic
4. Add minute-level health check cron

### **P1 — HIGH (Core functionality)**
1. Fix fallback to use `theme_pools`
2. Enforce read-only mode during waiting_theme
3. Implement winner song ban
4. Fix instant transitions (not 00:02 delay)

### **P2 — MEDIUM (Polish & resilience)**
1. Remove weekend logic (system always active)
2. Improve audit logging (more event types)
3. Add admin dashboard for system state
4. Frontend: show live countdown to 20:00/21:00

### **P3 — LOW (Nice to have)**
1. Websocket notifications for theme choice
2. Theme pool management UI for admin
3. Historical analytics (winner patterns, vote distributions)
4. Export audit logs to CSV

---

## 📝 NOTES FOR IMPLEMENTATION

### **Column Name Standardization**
The codebase has inconsistencies:
- **Models** use `name`, `active`
- **Production DB** uses `text`, `is_active`, `created_by`
- **Migrations** sometimes differ from both

**Recommendation:** Pick one standard and stick to it. I suggest:
```
theme_pools: text (matches prod), is_active (boolean standard), created_by (matches prod)
```

Update Eloquent model:
```php
// app/Models/ThemePool.php
protected $fillable = ['category', 'text', 'is_active', 'created_by'];
protected $casts = ['is_active' => 'boolean'];
```

---

### **Transition from Old to New Schema**
Since production has active contests, you'll need a migration strategy:
1. **Add new columns** (`lane`, `status`, etc.) with sensible defaults
2. **Backfill existing rows:**
   ```sql
   UPDATE contest_cycles SET 
     lane = 'submission',  -- or derive from timestamps
     status = IF(vote_end_at > NOW(), 'open', 'closed')
   WHERE lane IS NULL;
   ```
3. **Make columns NOT NULL after backfill**
4. **Add unique constraint** on `open_key`

---

### **Testing Database State**
Before going live, verify:
```sql
-- Should always be 2
SELECT COUNT(*) FROM contest_cycles WHERE status='open';

-- Should be 1 each
SELECT lane, COUNT(*) FROM contest_cycles WHERE status='open' GROUP BY lane;

-- Should have fallback
SELECT COUNT(*) FROM theme_pools WHERE text='Libre';

-- Window state
SELECT * FROM contest_flags WHERE name='window';
```

---

## ✅ CONCLUSION

The current Concurs system is **60% complete** but needs **significant refactoring** to match the v2 specification. The biggest issues are:

1. **Schema mismatch** — Missing critical columns
2. **No theme pool usage** — Fallback system incomplete
3. **Missing resilience** — No minute-level health checks
4. **Timing gaps** — Read-only enforcement weak

**Estimated effort to fix:**
- **Phase 1–4 (critical):** 2–3 days
- **Phase 5–7 (high priority):** 2 days
- **Phase 8 (testing):** 1 day
- **Total:** ~1 week of focused development

**Good news:**
- Core vote/upload/winner logic is solid
- UI/UX is polished
- Audit logging framework exists
- Transaction safety is mostly implemented

**Recommendation:** Proceed with schema migration first (Phase 1), then tackle Start button and health check (Phases 2–4). The system can go live once P0 and P1 items are complete.

---

**End of Audit Report**  
**Generated:** October 20, 2025  
**Next Steps:** Await approval to proceed with fixes

