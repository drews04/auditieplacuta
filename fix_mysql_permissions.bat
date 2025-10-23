@echo off
echo ==========================================
echo FIXING MARIADB PERMISSIONS
echo ==========================================
echo.
echo STEP 1: Open XAMPP Control Panel
echo STEP 2: Click STOP on MySQL
echo STEP 3: Press ANY KEY here to continue...
pause

echo.
echo Starting MySQL in safe mode (skip-grant-tables)...
cd C:\xampp\mysql\bin
start "MySQL Safe Mode" mysqld.exe --skip-grant-tables --skip-networking

echo Waiting for MySQL to start...
timeout /t 5

echo.
echo Fixing user permissions...
mysql.exe --user=root --execute="FLUSH PRIVILEGES; CREATE USER IF NOT EXISTS 'root'@'localhost'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; FLUSH PRIVILEGES;" 2>nul

echo.
echo Stopping safe mode MySQL...
taskkill /F /IM mysqld.exe /T 2>nul
timeout /t 3

echo.
echo ==========================================
echo DONE! Now:
echo 1. Go to XAMPP Control Panel
echo 2. Click START on MySQL
echo 3. Try your site again
echo ==========================================
pause

