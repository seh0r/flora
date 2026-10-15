@echo off
chcp 65001 >nul
cd /d "%~dp0"
if not exist "C:\xampp\php\php.exe" (
    echo PHP не найден в C:\xampp. Укажите свой путь к XAMPP в INSTALL.bat.
    pause
    exit /b 1
)
"C:\xampp\php\php.exe" tools\setup.php
if errorlevel 1 (
    echo Установка не выполнена. Прочитайте сообщение выше.
    pause
    exit /b 1
)
"C:\xampp\php\php.exe" tools\demo-orders.php
echo Готово. Откройте http://localhost/lepestok-practice/
pause
