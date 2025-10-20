@echo off
echo ========================================
echo FIX MARIADB CONNECTION ISSUE
echo ========================================
echo.

cd C:\xampp\mysql\bin

echo Running MySQL commands to fix permissions...
echo.

mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION;"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION;"
mysql -u root -e "FLUSH PRIVILEGES;"

echo.
echo ========================================
echo DONE! Try refreshing your browser now.
echo ========================================
pause

