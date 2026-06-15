@echo off
REM ─────────────────────────────────────────────────────────────
REM  Lance le serveur de dev avec PHP 8.4 (Herd) qui inclut
REM  l'extension intl requise par Filament (Number::format).
REM  NE PAS utiliser "php artisan serve" : le PHP du PATH (herd-lite
REM  8.5) n'a pas intl et provoque une erreur sur /admin/students.
REM ─────────────────────────────────────────────────────────────
"C:\Users\khali\.config\herd\bin\php84.bat" artisan serve %*
