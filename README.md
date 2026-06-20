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
6. To generate Laravel encryption key, execute
```bash
php artisan key:generate
```
After execution, you should see something like this in the .env file
> APP_KEY=base64:8f3k9d0aKJH2...random_string...

7. In the .env file, find
> DB_CONNECTION=mysql\
DB_HOST=127.0.0.1\
DB_PORT=3306\
DB_DATABASE=laravel\
DB_USERNAME=root\
DB_PASSWORD=

Modify it accordingly to ensure it matches your database configuration (**DB_DATABASE=inventory2_db** is mandatory).

8. Remember to save the changes.
9. Head over to Laragon and start it.
10. Create a new database, with the name inventory2_db. Keep the default collation. Press OK.
11. Head back to the VS Code project.
12. On the terminal, run
```bash
php artisan config:clear
```
13. Then, run
```bash
php artisan migrate --seed
```
14. Close HeidiSQL and reopen it. You should see that new tables are created in inventory2_db.
15. To run the server, execute
```bash
composer run dev
```
16. Enter [http://localhost:8000](http://localhost:8000) in the address bar. You should see a page with log in and register button. Make sure that Laragon is on.
17. To log in, enter one of the emails stored in the database. All user passwords are 'password'.
