@echo off
echo ========================================
echo EMERGENCY MARIADB FIX
echo ========================================
echo.
echo This will:
echo 1. Stop MariaDB
echo 2. Start in skip-grant-tables mode
echo 3. Fix user permissions
echo 4. Restart normally
echo.
pause

echo.
echo Step 1: Stopping MariaDB...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 >nul

echo Step 2: Starting MariaDB in safe mode...
cd C:\xampp\mysql\bin
start /B mysqld --skip-grant-tables --skip-networking
timeout /t 5 >nul

echo Step 3: Fixing permissions...
mysql -u root -e "FLUSH PRIVILEGES;"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION;"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION;"
mysql -u root -e "FLUSH PRIVILEGES;"

echo Step 4: Restarting MariaDB normally...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 >nul

echo.
echo ========================================
echo NOW: Go to XAMPP Control Panel and START MySQL
echo ========================================
pause

