@echo off
echo Testing database connection...
php artisan config:clear
php artisan cache:clear
php artisan db:show
pause

