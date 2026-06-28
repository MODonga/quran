# Installation & Setup Guide 🛠

Follow these steps to set up the Quran Memorization Backend on your local machine.

## Prerequisites
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Database**: MySQL 8.x or SQLite
- **Extensions**: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`.

## 1. Clone & Install
```bash
git clone <repository-url>
cd quran-memorization
composer install
```

## 2. Environment Configuration
Create your `.env` file:
```bash
cp .env.example .env
php artisan key:generate
```
Update the DB settings in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quran_memorization
DB_USERNAME=root
DB_PASSWORD=
```

## 3. Database & Seeding
This project includes a custom seeder that imports the full Quran text and reciters list.
```bash
# Run migrations and seed the database
php artisan migrate --seed
```

## 4. Running the Server
```bash
php artisan serve
```
The API will be available at: `http://127.0.0.1:8000`

## 5. Verification & Testing
To ensure everything is working correctly:
```bash
# Run the automated test suite
php artisan test

# Or run manual verification scripts
php scripts/verify_algo.php
php scripts/verify_stats.php
```

## 6. Daily Backups
The project includes a backup script. You can set it up as a Cron Job:
```bash
# Manual execution
php scripts/backup.php
```

---
For API details, see **[API_REFERENCE.md](API_REFERENCE.md)**.
