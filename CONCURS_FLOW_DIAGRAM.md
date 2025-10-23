# 🔄 CONCURS SYSTEM FLOW — Visual Diagram

**Date:** October 20, 2025  
**Purpose:** Understand how the Concurs system should work (per spec v2)

---

## 📅 DAILY CYCLE FLOW (CURRENT vs SPEC)

### **✅ SPEC V2 — How It SHOULD Work**

```
┌──────────────────────────────────────────────────────────────────────┐
│  ANY TIME — Admin presses START BUTTON                              │
├──────────────────────────────────────────────────────────────────────┤
│  1. Check for open cycles → show Hard Reset confirm if exists        │
│  2. Delete open cycles (keep archives)                               │
│  3. Pick 2 RANDOM themes from theme_pools (distinct)                 │
│  4. Create Theme A → Voting cycle (lane='voting', now→20:00)        │
│  5. Create Theme B → Submission cycle (lane='submission', now→20:00) │
│  6. System now has 2 live cycles running simultaneously              │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  00:00 – 19:59 — ACTIVE PHASE (Every day, no weekends off)          │
├──────────────────────────────────────────────────────────────────────┤
│  VOTING CYCLE (Yesterday's uploads)                                  │
│    • Users see songs from Theme A                                    │
│    • Vote buttons active                                             │
│    • 1 vote/user/cycle                                               │
│    • Cannot vote own song                                            │
│                                                                       │
│  SUBMISSION CYCLE (Today's theme)                                    │
│    • Users upload for Theme B                                        │
│    • 1 upload/user/cycle                                             │
│    • Duplicate YouTube links rejected                                │
│    • Past winners banned (check banned_songs table)                  │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  20:00 — CLOSE & DECIDE                                              │
├──────────────────────────────────────────────────────────────────────┤
│  [CRON: concurs:declare-winner runs]                                 │
│                                                                       │
│  1. Close VOTING cycle instantly                                     │
│  2. Tally votes:                                                     │
│     • 1 submission → autowin                                         │
│     • 2+ submissions:                                                │
│       ├─ Highest votes → normal win                                  │
│       └─ Tie → random pick (RNG, logged with seed)                   │
│     • 0 submissions → no winner, skip to fallback                    │
│  3. Write to winners table (cycle_id, user_id, song_id, method)     │
│  4. Insert winning song's youtube_id into banned_songs               │
│  5. Set contest_flags.window = 'waiting_theme'                       │
│  6. Both /concurs/upload and /concurs/vote → READ-ONLY              │
│  7. Show banner: "Așteptăm tema nouă... până la 21:00"             │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  20:00 – 21:00 — WINNER THEME WINDOW                                │
├──────────────────────────────────────────────────────────────────────┤
│  Winner visits /concurs:                                             │
│    • Modal auto-shows (one-time): "Felicitări! Alege tema mâine"   │
│    • Two options:                                                    │
│      1. "Alege tema" → Opens Theme-Picker modal                      │
│      2. "Închide" → Modal hides, button remains visible             │
│                                                                       │
│  Theme-Picker Modal:                                                 │
│    • Select category (CSD / ITC / Artiști / Genuri)                 │
│    • Enter theme text (max 60 chars)                                 │
│    • On submit:                                                      │
│      [ConcursTemaController::store() runs]                           │
│      ├─ Validate 20:00–21:00 window                                 │
│      ├─ Create ContestTheme (chosen_by_user_id = winner)            │
│      ├─ Close SUBMISSION cycle (status='closed')                     │
│      ├─ Promote closed submission → VOTING (lane='voting', open)    │
│      ├─ Create NEW SUBMISSION cycle (lane='submission', open)       │
│      ├─ Set contest_flags.window = NULL                             │
│      ├─ Trigger concurs:inherit-poster (instant)                    │
│      └─ Redirect to /concurs (now shows new theme)                  │
│                                                                       │
│  INSTANT TRANSITIONS:                                                │
│    • /concurs/upload updates with new theme (Theme C)               │
│    • /concurs/vote shows yesterday's uploads (Theme B)              │
│    • Both pages re-open for interaction                             │
│    • Users can upload + vote immediately                             │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
                      ┌─────────────────┐
                      │ Winner chose?   │
                      └─────────────────┘
                       YES ↓        ↓ NO (after 21:00)
                           ↓        ↓
┌──────────────────────────────────────────────────────────────────────┐
│  21:00 — FALLBACK TRIGGER (If winner didn't choose)                 │
├──────────────────────────────────────────────────────────────────────┤
│  [CRON: concurs:fallback-theme runs]                                 │
│                                                                       │
│  1. Check contest_flags.window === 'waiting_theme'                   │
│  2. Pick random theme from theme_pools WHERE is_active=1             │
│     • If theme_pools empty → use "Libre"                             │
│  3. Create ContestTheme (chosen_by_user_id = NULL)                   │
│  4. Close SUBMISSION cycle (status='closed')                         │
│  5. Promote closed submission → VOTING (lane='voting', open)         │
│  6. Create NEW SUBMISSION cycle (lane='submission', open)            │
│  7. Set contest_flags.window = NULL                                  │
│  8. Log to contest_audit_logs (event_type='fallback_theme', seed)    │
│  9. Trigger concurs:inherit-poster                                   │
│ 10. INSTANT TRANSITIONS (same as winner path)                        │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  EVERY MINUTE — AUTO-REPAIR (Resilience Layer)                      │
├──────────────────────────────────────────────────────────────────────┤
│  [CRON: concurs:auto-repair runs every minute]                       │
│                                                                       │
│  Health Checks:                                                      │
│    1. Count open cycles:                                             │
│       • Should be exactly 2 (one submission, one voting)             │
│       • If 0 → auto-seed new pair from theme_pools                   │
│       • If 1 → log warning, attempt repair                           │
│       • If 3+ → log error, close duplicates                          │
│                                                                       │
│    2. Check deadlines:                                               │
│       • If vote_end_at < now AND status='open' → force close         │
│       • If submit_end_at < now AND status='open' → force close       │
│                                                                       │
│    3. Check stuck waiting_theme:                                     │
│       • If window='waiting_theme' AND now > 21:00 + 1h → force      │
│         fallback                                                     │
│                                                                       │
│    4. Verify constraints:                                            │
│       • No duplicate youtube_id in same cycle                        │
│       • No banned songs uploaded                                     │
│       • All cycles have valid theme_id or theme_text                 │
│                                                                       │
│  Self-Healing:                                                       │
│    • If inconsistency detected → attempt automatic fix               │
│    • If fix fails → log error, notify admin (future: email/Slack)   │
│    • Log all repairs to contest_audit_logs                           │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
                    ┌───────────────────────┐
                    │  LOOP BACK TO 00:00   │
                    │  (Next day's cycle)   │
                    └───────────────────────┘
```

---

## ❌ CURRENT IMPLEMENTATION — How It ACTUALLY Works (Broken)

```
┌──────────────────────────────────────────────────────────────────────┐
│  Admin presses START                                                 │
├──────────────────────────────────────────────────────────────────────┤
│  ❌ Admin ENTERS themes manually (not random from DB)                │
│  ❌ Creates cycles with contest_theme_id (old schema)                │
│  ❌ No lane/status columns set (columns don't exist)                 │
│  ⚠️ Complex weekend skip logic (spec says no weekends off)          │
│  ⚠️ Uses vote_start_at (may not exist in production DB)             │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  00:00 – 19:59 — Users upload/vote                                  │
├──────────────────────────────────────────────────────────────────────┤
│  ⚠️ Controllers reference lane='submission' (column doesn't exist)   │
│  ⚠️ No check against banned_songs table                              │
│  ⚠️ Duplicate ban only within cycle, not lifetime                    │
│  ✅ Vote/upload logic mostly correct                                 │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  20:00 — CLOSE & DECIDE                                              │
├──────────────────────────────────────────────────────────────────────┤
│  [CRON: concurs:declare-winner runs]                                 │
│                                                                       │
│  ✅ Tallies votes correctly                                          │
│  ✅ Handles ties with RNG (logged with seed)                         │
│  ✅ Writes to winners table                                          │
│  ❌ Does NOT insert into banned_songs                                │
│  ❌ Does NOT set contest_flags.window = 'waiting_theme'              │
│  ❌ Pages stay interactive (no read-only enforcement)                │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  20:00 – 21:00 — Winner picks theme                                 │
├──────────────────────────────────────────────────────────────────────┤
│  ✅ Winner modal shows (localStorage-based)                          │
│  ⚠️ Checks isWeekday() on client side (should be active every day)  │
│  ✅ Theme-Picker modal works                                         │
│  ⚠️ ConcursTemaController::store() creates cycles without lane/status│
│  ⚠️ Doesn't unlock contest_flags.window                              │
│  ⚠️ Poster inheritance delayed to 00:02 (not instant)                │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  21:00 — FALLBACK (If winner didn't choose)                         │
├──────────────────────────────────────────────────────────────────────┤
│  [CRON: concurs:fallback-theme runs]                                 │
│                                                                       │
│  ⚠️ Checks contest_flags.window (correct)                            │
│  ❌ Uses HARDCODED wordbank (not theme_pools DB)                     │
│  ❌ theme_pools table is empty anyway (0 rows)                       │
│  ⚠️ Generates "Neon Dreams Oct 20" (spec says "Libre")              │
│  ⚠️ Calls controller method directly (not best practice)             │
│  ✅ Logs to audit                                                    │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
┌──────────────────────────────────────────────────────────────────────┐
│  NO AUTO-REPAIR (System can get stuck)                              │
├──────────────────────────────────────────────────────────────────────┤
│  ❌ No minute-level health check scheduled                           │
│  ❌ If both cycles missing → stays idle forever                      │
│  ❌ If deadlines pass but cycles not closed → stays stuck            │
│  ❌ No self-healing logic                                            │
│  ⚠️ Manual admin intervention required                               │
└──────────────────────────────────────────────────────────────────────┘
                                ↓
                    ┌───────────────────────┐
                    │  SYSTEM MAY BE STUCK  │
                    │  (No resilience)      │
                    └───────────────────────┘
```

---

## 🗄️ DATABASE STATE FLOW

### **✅ SPEC — Ideal Two-Lane System**

```
┌────────────────────────────────────────────────────────────────────────┐
│  contest_cycles table (at any given moment)                            │
├────────────────────────────────────────────────────────────────────────┤
│  id | lane       | status | theme_text  | start_at  | vote_end_at     │
│  ───┼────────────┼────────┼─────────────┼───────────┼─────────────────│
│  42 | voting     | open   | "Dragoste"  | today 00  | today 20:00     │
│  43 | submission | open   | "Nostalgie" | today 00  | today 20:00     │
└────────────────────────────────────────────────────────────────────────┘
                         ↓ (20:00 hits)
┌────────────────────────────────────────────────────────────────────────┐
│  concurs:declare-winner runs                                           │
│  • Closes id=42 (voting): status='closed', writes winner              │
│  • Keeps id=43 open (submission ongoing)                              │
│  • Sets contest_flags.window = 'waiting_theme'                        │
└────────────────────────────────────────────────────────────────────────┘
                         ↓ (winner picks theme OR 21:00 fallback)
┌────────────────────────────────────────────────────────────────────────┐
│  Promotion & new cycle creation                                        │
│  • Close id=43 (submission): status='closed'                          │
│  • Update id=43: lane='voting', status='open', vote_end_at=tomorrow   │
│  • Create id=44: lane='submission', status='open', theme="Rock"       │
│  • Unlock contest_flags.window = NULL                                 │
└────────────────────────────────────────────────────────────────────────┘
                         ↓ (result)
┌────────────────────────────────────────────────────────────────────────┐
│  contest_cycles table (new state)                                      │
├────────────────────────────────────────────────────────────────────────┤
│  id | lane       | status | theme_text  | start_at   | vote_end_at    │
│  ───┼────────────┼────────┼─────────────┼────────────┼────────────────│
│  43 | voting     | open   | "Nostalgie" | today 20:05| tomorrow 20:00 │
│  44 | submission | open   | "Rock"      | today 20:05| tomorrow 20:00 │
└────────────────────────────────────────────────────────────────────────┘
         ↑ yesterday's uploads     ↑ today's new theme
     (now being voted on)      (accepting uploads now)
```

### **❌ CURRENT — Schema Missing Critical Columns**

```
┌────────────────────────────────────────────────────────────────────────┐
│  contest_cycles table (production DB as of Oct 18, 2025)               │
├────────────────────────────────────────────────────────────────────────┤
│  id | theme_id | theme_text | start_at | submit_end | vote_end        │
│  ───┼──────────┼────────────┼──────────┼────────────┼─────────────────│
│  1  | 1        | Kickoff A  | Oct 17   | NULL       | Oct 17 20:00    │
│  2  | 2        | Kickoff B  | Oct 17   | NULL       | Oct 17 20:00    │
│  3  | 2        | Kickoff B  | Oct 17   | Oct 18 20  | NULL            │
│                                                                         │
│  ❌ NO 'lane' COLUMN (can't distinguish submission vs voting)          │
│  ❌ NO 'status' COLUMN (can't track open vs closed)                    │
│  ❌ NO 'decide_method' COLUMN (can't log how winner chosen)            │
│  ❌ NO 'open_key' COLUMN (no unique constraint for single-open-per-lane│
└────────────────────────────────────────────────────────────────────────┘

Code tries to query:
  WHERE lane='submission' AND status='open'
              ↑                      ↑
         DOESN'T EXIST          DOESN'T EXIST
              ↓                      ↓
         ERROR / UNDEFINED BEHAVIOR
```

---

## 🚨 CRITICAL FAILURE POINTS

### **1. Start Button Failure**
```
User clicks "Start Concurs"
    ↓
Admin enters themes manually
    ↓
ConcursAdminController::start() creates cycles with:
    • contest_theme_id (foreign key to old schema)
    • No lane/status columns (trying to set non-existent columns)
    ↓
DB error OR cycles created with wrong structure
    ↓
System appears to work but...
    ↓
Controllers query WHERE lane='submission'
    ↓
Returns 0 rows (column doesn't exist)
    ↓
/concurs/upload shows "Înscrierile sunt închise" (always)
```

### **2. Fallback Failure**
```
Winner doesn't pick theme by 21:00
    ↓
concurs:fallback-theme runs
    ↓
Tries to query theme_pools
    ↓
❌ Table is empty (0 rows)
    ↓
Falls back to hardcoded wordbank
    ↓
Picks "Neon Dreams Oct 20" (not spec-compliant "Libre")
    ↓
Tries to UPDATE contest_cycles WHERE lane='submission'
    ↓
❌ Column doesn't exist
    ↓
ERROR or no rows affected
    ↓
System stuck in waiting_theme mode forever
```

### **3. Health Check Failure**
```
Something goes wrong (cycles deleted, DB corrupted, etc.)
    ↓
Both open cycles missing
    ↓
Users visit /concurs
    ↓
Controllers query WHERE lane='submission'/'voting'
    ↓
Returns 0 rows
    ↓
Page shows: "Nu există rundă activă"
    ↓
❌ No auto-repair scheduled (no minute-level cron)
    ↓
System stays idle until admin manually intervenes
    ↓
Could be hours/days before noticed
```

---

## ✅ PROPOSED SOLUTION — After All Fixes Applied

```
┌────────────────────────────────────────────────────────────────────────┐
│  PHASE 1: Database Migration Complete                                  │
├────────────────────────────────────────────────────────────────────────┤
│  ✅ Added lane ENUM('submission','voting')                             │
│  ✅ Added status ENUM('open','closed')                                 │
│  ✅ Added decide_method ENUM(...)                                      │
│  ✅ Added open_key VARCHAR(32) GENERATED                               │
│  ✅ Backfilled existing rows with sensible defaults                    │
│  ✅ Seeded theme_pools with 20+ themes                                 │
│  ✅ Fixed ThemePool model column names                                 │
└────────────────────────────────────────────────────────────────────────┘
                                ↓
┌────────────────────────────────────────────────────────────────────────┐
│  PHASE 2: Start Button Refactored                                      │
├────────────────────────────────────────────────────────────────────────┤
│  ✅ Picks 2 random themes from theme_pools                             │
│  ✅ Creates submission + voting cycles with lane/status                │
│  ✅ Hard reset deletes open cycles only (keeps archives)               │
│  ✅ Works correctly every time                                         │
└────────────────────────────────────────────────────────────────────────┘
                                ↓
┌────────────────────────────────────────────────────────────────────────┐
│  PHASE 3: Fallback Fixed                                               │
├────────────────────────────────────────────────────────────────────────┤
│  ✅ Queries theme_pools (now populated)                                │
│  ✅ Picks random theme or "Libre" if empty                             │
│  ✅ Promotes cycles correctly using lane/status                        │
│  ✅ Unlocks window flag                                                │
│  ✅ Instant transitions work                                           │
└────────────────────────────────────────────────────────────────────────┘
                                ↓
┌────────────────────────────────────────────────────────────────────────┐
│  PHASE 4: Auto-Repair Running Every Minute                             │
├────────────────────────────────────────────────────────────────────────┤
│  ✅ Checks for 2 open cycles (one per lane)                            │
│  ✅ If missing → auto-seeds new pair from theme_pools                  │
│  ✅ If stuck → forces fallback or closes overdue cycles                │
│  ✅ Logs all repairs to audit                                          │
│  ✅ System self-heals, never stays idle                                │
└────────────────────────────────────────────────────────────────────────┘
                                ↓
┌────────────────────────────────────────────────────────────────────────┐
│  RESULT: Resilient, Self-Sustaining System                             │
├────────────────────────────────────────────────────────────────────────┤
│  • Always 2 live cycles (submission + voting)                          │
│  • Automatic recovery from errors                                      │
│  • No human intervention needed                                        │
│  • Fully spec-compliant                                                │
│  • Production-ready ✅                                                 │
└────────────────────────────────────────────────────────────────────────┘
```

---

**End of Flow Diagram**

