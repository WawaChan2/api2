# Project Setup Guide
1. Paste this command on Windows Powershell to install PHP, Composer, and the Laravel installer.
```bash
# Run as administrator...
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```
2. Clone the repository to your preferred folder.
```bash
git clone https://github.com/WawaChan2/api2.git
```
3. Open the project on VS Code.
4. To install PHP dependencies and Node dependencies, execute
```bash
composer install
npm install
```
5. Copy .env.example and rename it to .env. On Windows Powershell, execute
```bash
Copy-Item .env.example .env
```
6. In the .env file, find
> DB_CONNECTION=mysql\
DB_HOST=127.0.0.1\
DB_PORT=3306\
DB_DATABASE=laravel\
DB_USERNAME=root\
DB_PASSWORD=

Modify it accordingly to ensure it matches your database configuration (**DB_DATABASE=inventory2_db** is mandatory).

7. Remember to save the changes.
8. Head over to Laragon and start it.
9. Create a new database, with the name inventory2_db. Keep the default collation. Press OK.
10. Head back to the VS Code project.
11. On the terminal, run
```bash
php artisan config:clear
```
12. Then, run
```bash
php artisan migrate
```
13. Close HeidiSQL and reopen it. You should see that new tables are created in inventory2_db.
