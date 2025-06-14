@echo off
set "tanggal=%date:~10,4%-%date:~4,2%-%date:~7,2%"
set "backupfile=D:\backups\fixitnow\fixitnow_backup_%tanggal%.sql"
"C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe" -u root -p fixitnow_db > "%backupfile%"
echo Backup selesai: %backupfile%
