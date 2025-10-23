# 🚀 PRODUCTION DEPLOYMENT GUIDE - Concurs Rebuild

**Date:** 2025-10-20  
**Branch:** `release/2025-10-13-first-day-online`  
**Critical:** Contains database changes + code restructure

---

## ⚠️ IMPORTANT: READ BEFORE DEPLOYING

This deployment includes:
1. ✅ **Code restructure** (controllers, views, routes moved)
2. ✅ **3 database migrations** (new columns, new table)
3. ✅ **Bug fixes** (theme likes, posters, modal backdrops)

**DOWNTIME:** ~2 minutes (for migrations)

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### **1. Backup Production Database**
```bash
ssh user@auditieplacuta.ro
cd /path/to/project
php artisan backup:run  # Or manual mysqldump
```

**Manual backup:**
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### **2. Check Current State**
```bash
# On production server
php artisan migrate:status

# Verify these tables exist:
mysql -u username -p -e "SHOW TABLES LIKE 'contest_%'; SHOW TABLES LIKE 'banned_songs';"
```

---

## 🎯 DEPLOYMENT STEPS

### **OPTION A: Standard Git Deployment (Recommended)**

#### **Step 1: On Local, Commit Everything**
```bash
cd c:\xampp\htdocs\auditieplacuta

# Stage all changes
git add .

# Commit with descriptive message
git commit -m "Concurs rebuild: restructure + migrations + fixes

- Restructured controllers/views to Concurs/ folder
- Added poster_url to contest_cycles
- Created banned_songs table
- Added chosen_by_user_id to contest_themes
- Fixed theme likes (use theme_id not polymorphic)
- Fixed modal backdrops
- Updated routes for new structure"

# Push to your branch
git push origin release/2025-10-13-first-day-online
```

#### **Step 2: On Production Server**

```bash
# SSH to server
ssh user@auditieplacuta.ro

# Navigate to project
cd /var/www/auditieplacuta  # Or your path

# Put site in maintenance mode
php artisan down --message="Updating competition system" --retry=60

# Pull latest code
git fetch origin
git pull origin release/2025-10-13-first-day-online

# Install/update dependencies (if composer.json changed)
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Bring site back up
php artisan up

# Restart queue workers (if you have any)
php artisan queue:restart
```

---

### **OPTION B: Manual File Upload + SQL (If No Git Access)**

#### **Step 1: Export Changed Files**

Create a zip of changed files:
```bash
# On local
cd c:\xampp\htdocs\auditieplacuta

# Create deployment package
# Include these directories/files:
- app/Http/Controllers/Concurs/
- app/Console/Commands/Concurs/
- resources/views/concurs/
- routes/web.php
- database/migrations/2025_10_20_20000*.php
- public/js/concurs.js
- public/js/theme-like.js
```

#### **Step 2: Upload via FTP/SFTP**
- Upload to production, **replacing** old files
- Make sure permissions are correct (755 for directories, 644 for files)

#### **Step 3: Run Migrations Manually**

**SSH to server:**
```bash
ssh user@auditieplacuta.ro
cd /var/www/auditieplacuta

# Put in maintenance
php artisan down

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Bring back up
php artisan up
```

---

### **OPTION C: Manual SQL (Emergency Only)**

**If migrations fail, run SQL directly:**

```sql
-- 1. Add poster_url column
ALTER TABLE contest_cycles 
ADD COLUMN poster_url VARCHAR(500) NULL 
AFTER theme_text;

-- 2. Create banned_songs table
CREATE TABLE IF NOT EXISTS banned_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    youtube_id VARCHAR(255) UNIQUE NOT NULL,
    song_title VARCHAR(500),
    banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Add chosen_by_user_id to contest_themes
ALTER TABLE contest_themes 
ADD COLUMN chosen_by_user_id BIGINT UNSIGNED NULL 
AFTER name;

ALTER TABLE contest_themes 
ADD CONSTRAINT fk_contest_themes_chosen_by 
FOREIGN KEY (chosen_by_user_id) 
REFERENCES users(id) 
ON DELETE SET NULL;

-- 4. Verify changes
DESCRIBE contest_cycles;
DESCRIBE contest_themes;
DESCRIBE banned_songs;
```

**Then manually insert migration records:**
```sql
INSERT INTO migrations (migration, batch) VALUES
('2025_10_20_200001_add_poster_url_to_contest_cycles', 
 (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations)),
('2025_10_20_200002_create_banned_songs_table', 
 (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations)),
('2025_10_20_200003_add_chosen_by_user_id_to_contest_themes', 
 (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));
```

---

## ✅ POST-DEPLOYMENT VERIFICATION

### **1. Check Database**
```bash
mysql -u username -p -e "
DESCRIBE contest_cycles;
DESCRIBE banned_songs;
DESCRIBE contest_themes;
"
```

**Verify these columns exist:**
- ✅ `contest_cycles.poster_url` (varchar 500)
- ✅ `contest_themes.chosen_by_user_id` (bigint)
- ✅ `banned_songs` table exists

### **2. Check Site**
Visit these URLs and verify no errors:
- ✅ https://auditieplacuta.ro/concurs
- ✅ https://auditieplacuta.ro/concurs/p/upload
- ✅ https://auditieplacuta.ro/concurs/p/vote

### **3. Test Critical Features**
- ✅ Upload a song (as regular user)
- ✅ Like a theme (heart icon)
- ✅ Upload a poster (as admin)
- ✅ Click "Pornire Concurs" (as admin) - DON'T submit unless you want to reset

### **4. Check Logs**
```bash
tail -f storage/logs/laravel.log
```
Look for any errors related to:
- Missing columns
- Missing tables
- Route not found

---

## 🔧 TROUBLESHOOTING

### **Error: "Column 'poster_url' not found"**
**Fix:** Run migration manually:
```bash
php artisan migrate --path=database/migrations/2025_10_20_200001_add_poster_url_to_contest_cycles.php --force
```

### **Error: "Table 'banned_songs' doesn't exist"**
**Fix:** Run migration manually:
```bash
php artisan migrate --path=database/migrations/2025_10_20_200002_create_banned_songs_table.php --force
```

### **Error: "Route [concurs.something] not defined"**
**Fix:** Clear route cache:
```bash
php artisan route:clear
php artisan cache:clear
```

### **Error: "View not found"**
**Fix:** Clear view cache:
```bash
php artisan view:clear
```

### **Error: "Class not found"**
**Fix:** Regenerate autoload:
```bash
composer dump-autoload
```

---

## 🔄 ROLLBACK PLAN (If Something Goes Wrong)

### **Option 1: Git Rollback**
```bash
# On production
php artisan down

# Rollback code
git reset --hard HEAD~1

# Rollback migrations
php artisan migrate:rollback --step=3 --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan up
```

### **Option 2: Database Restore**
```bash
# Restore from backup
mysql -u username -p database_name < backup_20251020_123456.sql

# Then fix code or rollback code
```

---

## 📊 WHAT CHANGED (Summary)

### **Database:**
1. `contest_cycles` + `poster_url` column
2. `banned_songs` table created
3. `contest_themes` + `chosen_by_user_id` column

### **Code Structure:**
```
OLD:                                    NEW:
app/Http/Controllers/               →   app/Http/Controllers/Concurs/
  ConcursTemaController.php         →     ThemeController.php
  Header/Concurs/                   →     (reorganized into Concurs/)
                                            - ConcursController.php
                                            - UploadController.php
                                            - VoteController.php
                                            - ArchiveController.php
                                            Admin/
                                              - CycleController.php
                                              - PosterController.php

resources/views/                    →   resources/views/concurs/
  concurs.blade.php                 →     index.blade.php
  partials/winner_recap.blade.php   →     partials/winner_recap.blade.php
  concurs/upload.blade.php          →     upload.blade.php
  concurs/vote.blade.php            →     vote.blade.php
```

### **Routes:**
- All consolidated under `// CONCURS SYSTEM` section
- Clean grouping: public routes, auth routes, admin routes
- Legacy redirects maintained

### **Bug Fixes:**
- ✅ Theme likes use `theme_id` (not polymorphic)
- ✅ Modal backdrops properly cleaned up
- ✅ Start button uses hyphen `-` not em dash `—`
- ✅ Real-time song list updates after upload

---

## ⏱️ ESTIMATED DEPLOYMENT TIME

- **Preparation:** 10 minutes
- **Deployment:** 5 minutes
- **Verification:** 5 minutes
- **Total:** ~20 minutes

**Site Downtime:** ~2 minutes (during migrations)

---

## 📞 SUPPORT

If anything goes wrong:
1. Check `storage/logs/laravel.log`
2. Run `php artisan migrate:status`
3. Verify database columns exist
4. If stuck, restore from backup and contact dev

---

## ✅ DEPLOYMENT COMPLETE CHECKLIST

After deployment, verify:

- [ ] Site loads without errors
- [ ] Can view `/concurs` page
- [ ] Can upload songs
- [ ] Can vote on songs
- [ ] Theme likes work (heart icon toggles)
- [ ] Posters upload/replace/remove (admin)
- [ ] Start button works (admin) - TEST CAREFULLY
- [ ] No errors in `laravel.log`
- [ ] Database columns exist
- [ ] Migrations show as "Ran"

---

**DEPLOYMENT PREPARED BY:** AI Assistant  
**TESTED ON:** Local (c:\xampp\htdocs\auditieplacuta)  
**READY FOR PRODUCTION:** ✅ YES

