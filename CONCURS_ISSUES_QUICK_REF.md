# 🚨 CONCURS CRITICAL ISSUES — Quick Reference

**Date:** October 20, 2025  
**Status:** ⚠️ **NOT PRODUCTION READY**

---

## 🔴 BLOCKING ISSUES (P0)

### 1. **Missing Database Columns**
```sql
-- contest_cycles table is MISSING:
- lane ENUM('submission','voting')       ← CRITICAL: Can't distinguish cycle types
- status ENUM('open','closed')           ← CRITICAL: Can't track state
- decide_method ENUM(...)                ← Can't log how winner was chosen
- open_key VARCHAR(32) GENERATED         ← No DB-level unique constraint
```

**Impact:** Core two-lane system completely broken. Code references columns that don't exist.

**Fix:** Run migration to add columns + backfill existing data.

---

### 2. **Empty Theme Pool**
```sql
SELECT COUNT(*) FROM theme_pools;  -- Result: 0
```

**Impact:** Fallback system has nothing to pick from. At 21:00, system generates random text instead of using DB.

**Fix:** Seed at least 20–30 themes across categories (CSD, ITC, ARTISTI, GENURI).

---

### 3. **Column Name Mismatches**
| Table | Production DB | Model/Code | Status |
|-------|---------------|------------|--------|
| `theme_pools` | `text` | `name` | ❌ Mismatch |
| `theme_pools` | `is_active` | `active` | ❌ Mismatch |
| `theme_pools` | `created_by` | (not in model) | ⚠️ Missing |

**Impact:** Queries may fail, Eloquent inserts wrong columns.

**Fix:** Standardize on production DB column names.

---

### 4. **Start Button Wrong Logic**
**Spec:** Pick 2 random themes from `theme_pools`, create submission + voting cycles.  
**Reality:** Admin enters themes manually, creates cycles with old schema.

**Impact:** System doesn't self-sustain as designed.

**Fix:** Rewrite `ConcursAdminController::start()` per audit doc.

---

### 5. **No Health Check Cron**
**Spec:** "Cron job runs every minute to check deadlines and repair state."  
**Reality:** Only 4 scheduled jobs (20:00, 20:35, 21:00, 00:02), no minute-level checks.

**Impact:** System can get stuck with no cycles, no auto-recovery.

**Fix:** Add `concurs:auto-repair` command scheduled `->everyMinute()`.

---

## 🟡 HIGH PRIORITY (P1)

### 6. **Winning Song Not Banned**
**Spec:** "Past winning songs lifetime-banned."  
**Reality:** No code populates `banned_songs` table.

**Impact:** Same song can win multiple times.

**Fix:** In `DeclareDailyWinner`, insert `youtube_id` into `banned_songs`. Check on upload.

---

### 7. **Read-Only Not Enforced**
**Spec:** "Both upload & vote disabled 20:00–theme chosen. Show banner."  
**Reality:** `contest_flags.window` exists but not checked in upload/vote actions or views.

**Impact:** Users can upload/vote during waiting period.

**Fix:** Add guard in `uploadSong()` and `voteForSong()`, show banner in Blade.

---

### 8. **Instant Transitions Delayed**
**Spec:** "When winner picks theme, both pages update instantly."  
**Reality:** Poster inheritance runs at 00:02 next day.

**Impact:** 4–6 hour delay before posters match cycles.

**Fix:** Trigger `concurs:inherit-poster` immediately in `ConcursTemaController::store()`.

---

### 9. **Fallback Uses Hardcoded Text**
**Spec:** "Pick random from `theme_pools`, if empty use 'Libre'."  
**Reality:** Generates "Neon Dreams Oct 20" style text from hardcoded array.

**Fix:** Query `theme_pools`, only use hardcoded if DB empty.

---

## 🔵 MEDIUM PRIORITY (P2)

### 10. **Weekend Logic Present**
**Spec:** "Active days: every day (no weekends off)."  
**Reality:** Code has `isWeekday()` checks in JS, weekend skip logic in controller.

**Impact:** Confusion, potential gaps.

**Fix:** Remove all weekend logic.

---

### 11. **Schema Drift**
**Reality:** Migrations create columns (`vote_start_at`, `winner_decided_at`) not in production DB dump.

**Impact:** Uncertainty about actual DB state.

**Fix:** Audit production DB, align migrations, document discrepancies.

---

### 12. **No Auto-Repair Logic**
**Reality:** If both cycles missing, system stays idle until admin intervenes.

**Fix:** In minute-level health check, auto-seed pair if both missing.

---

## 📊 FILES NEEDING CHANGES

### **Must Change (P0)**
1. `database/migrations/` — Add lane/status columns
2. `database/seeders/ThemePoolSeeder.php` — Seed themes
3. `app/Models/ThemePool.php` — Fix column names
4. `app/Http/Controllers/Admin/ConcursAdminController.php` — Rewrite `start()`
5. `app/Console/Commands/` — Create `ConcursAutoRepair.php`
6. `app/Console/Kernel.php` — Schedule `->everyMinute()`

### **Should Change (P1)**
1. `app/Console/Commands/DeclareDailyWinner.php` — Add banned_songs insert
2. `app/Console/Commands/ConcursFallbackTheme.php` — Use theme_pools
3. `app/Http/Controllers/SongController.php` — Enforce read-only, check bans
4. `app/Http/Controllers/ConcursTemaController.php` — Trigger poster instantly
5. `resources/views/concurs/upload.blade.php` — Add read-only banner
6. `resources/views/concurs/vote.blade.php` — Add read-only banner

### **Nice to Fix (P2)**
1. `public/js/concurs.js` — Remove `isWeekday()` logic
2. All code referencing `lane`/`status` — Ensure it works after migration

---

## 🛠️ IMPLEMENTATION ORDER

### **Week 1: Critical Fixes**
**Day 1–2:** Schema migration (add columns, backfill data)  
**Day 3:** Seed theme_pools, fix column name mismatches  
**Day 4:** Rewrite Start button logic  
**Day 5:** Add health check cron

### **Week 2: High Priority**
**Day 1:** Implement winner ban system  
**Day 2:** Enforce read-only mode  
**Day 3:** Fix instant transitions (poster)  
**Day 4:** Fix fallback to use theme_pools  
**Day 5:** Testing & validation

---

## ✅ SUCCESS CRITERIA

Before going live, verify:
```sql
-- 1. Two open cycles always
SELECT lane, status, theme_text, vote_end_at 
FROM contest_cycles 
WHERE status='open';
-- Should show exactly 2 rows: one submission, one voting

-- 2. Theme pool populated
SELECT COUNT(*) FROM theme_pools WHERE is_active=1;
-- Should be >= 10

-- 3. Window state
SELECT name, value FROM contest_flags WHERE name='window';
-- Should be NULL or 'waiting_theme' (between 20:00–21:00)

-- 4. Health check scheduled
php artisan schedule:list | grep auto-repair
-- Should run every minute
```

---

## 📞 CONTACT

**Any questions before starting fixes?**  
Reply with specific concern and I'll provide detailed implementation guidance.

**Ready to proceed?**  
Say "start Phase 1" and I'll generate the migration file + seeder.

---

**End of Quick Reference**

