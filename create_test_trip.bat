@echo off
REM Set environment for MySQL
setlocal enabledelayedexpansion

echo === Checking Current Status ===

REM Get dates from MySQL
for /f "tokens=1" %%a in ('"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot holidaysio -N -e "SELECT DATE(NOW());"') do (
    set TODAY=%%a
)
for /f "tokens=1" %%a in ('"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot holidaysio -N -e "SELECT DATE(DATE_SUB(NOW(), INTERVAL 4 DAY));"') do (
    set FOURDAYS=%%a
)

echo Today: !TODAY!
echo 4 days ago: !FOURDAYS!
echo.

REM Check what trips currently match the criteria
echo === Trips matching feedback criteria ===
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot holidaysio -e "SELECT id, title, start_date, end_date, feedback_request_sent_at FROM trips WHERE (DATE(end_date)='!FOURDAYS!' OR (end_date IS NULL AND DATE(start_date)='!FOURDAYS!')) AND feedback_request_sent_at IS NULL;"

echo.
echo === Creating test Activity Trip ===

REM Get a traveler_account_id with email
for /f "tokens=1" %%a in ('"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot holidaysio -N -e "SELECT id FROM traveler_accounts WHERE email IS NOT NULL LIMIT 1;"') do (
    set traveler_id=%%a
)

echo Using traveler_account_id: !traveler_id!

REM Insert test Activity Trip (start_date = 4 days ago, end_date = NULL)
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot holidaysio -e "INSERT INTO trips (traveler_account_id, title, start_date, end_date, status, created_at, updated_at) VALUES (!traveler_id!, 'Activity Trip', '!FOURDAYS!', NULL, 'planned', NOW(), NOW());"

echo.
echo Test Activity Trip created with:
echo   start_date = !FOURDAYS!
echo   end_date = NULL
echo   status = planned
echo.
echo Now run: php artisan feedback:send-requests
