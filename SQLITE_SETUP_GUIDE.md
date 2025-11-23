# SQLite Setup Guide - Portable Laravel Wallet API

This guide will help you set up the Digital Wallet API with SQLite for portable, zero-configuration usage. SQLite is perfect for:
- ✅ GitHub repositories (no MySQL setup required)
- ✅ Quick local development
- ✅ Portable applications
- ✅ CI/CD pipelines
- ✅ Demo environments

## Quick Start (SQLite - Recommended)

### Step 1: Create SQLite Database File

**Windows (PowerShell):**
```powershell
# Navigate to project directory
cd F:\Desktop\wallet-api-github

# Create database directory if it doesn't exist
New-Item -ItemType Directory -Force -Path database

# Create empty SQLite database file
New-Item -ItemType File -Path database\database.sqlite
```

**Linux/macOS:**
```bash
# Navigate to project directory
cd /path/to/wallet-api-github

# Create database directory and file
mkdir -p database
touch database/database.sqlite
```

**Alternative (Using Artisan):**
```bash
php artisan db:create-sqlite
```

### Step 2: Configure Environment

Copy the example environment file:
```bash
# Windows
copy .env.example .env

# Linux/macOS
cp .env.example .env
```

Edit `.env` and ensure these settings:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Note:** The path is relative to the project root. Laravel will automatically use `database_path('database.sqlite')` when `DB_CONNECTION=sqlite`.

### Step 3: Generate Application Keys

```bash
php artisan key:generate
php artisan jwt:secret
```

### Step 4: Run Migrations and Seeders

```bash
# Run all migrations
php artisan migrate

# Seed exchange rates
php artisan db:seed

# Or run both together
php artisan migrate --seed
```

### Step 5: Start the Server

```bash
php artisan serve
```

Your API is now running at `http://127.0.0.1:8000` with SQLite! 🎉

---

## Switching Between SQLite and MySQL

### Option A: Using SQLite (Portable)

**`.env` configuration:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Advantages:**
- ✅ No database server required
- ✅ Single file database
- ✅ Perfect for GitHub/portable usage
- ✅ Zero configuration
- ✅ Works with JWT authentication
- ✅ Works with Blade UI
- ✅ Fast for small to medium datasets

**Limitations:**
- ⚠️ Not ideal for high-concurrency production
- ⚠️ No concurrent writes (SQLite limitation)
- ⚠️ File-based (backup = copy file)

### Option B: Using MySQL (Production)

**`.env` configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Prerequisites:**
1. Install MySQL/MariaDB
2. Create database:
   ```sql
   CREATE DATABASE wallet_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Update `.env` with MySQL credentials
4. Run migrations:
   ```bash
   php artisan migrate --seed
   ```

**Advantages:**
- ✅ Better for production
- ✅ Handles concurrent writes
- ✅ Advanced features (stored procedures, triggers)
- ✅ Better for large datasets

---

## Auto-Detection Feature

The application automatically detects your database connection:

- **If `DB_CONNECTION=sqlite`**: Uses `database/database.sqlite` file
- **If `DB_CONNECTION=mysql`**: Uses MySQL with provided credentials

The `config/database.php` file handles this automatically:

```php
'sqlite' => [
    'driver' => 'sqlite',
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    // ...
],
```

---

## Database Commands

### SQLite Commands

```bash
# Create database file (if not exists)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Fresh migration (drop all tables and re-run)
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### MySQL Commands

```bash
# Same commands work for MySQL too!
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
```

---

## Verification

### Test SQLite Connection

```bash
php artisan tinker
```

Then in tinker:
```php
>>> DB::connection()->getDatabaseName()
=> "F:\Desktop\wallet-api-github\database\database.sqlite"

>>> DB::table('users')->count()
=> 0

>>> \App\Models\User::factory()->create()
=> App\Models\User {#1234 ...}
```

### Test API Endpoints

1. **Register a user:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{"name":"Test User","email":"test@example.com","password":"password123"}'
   ```

2. **Login:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password123"}'
   ```

---

## JWT Authentication with SQLite

JWT authentication works perfectly with SQLite! The JWT package (`php-open-source-saver/jwt-auth`) is database-agnostic and only uses the database for:
- User authentication (stored in `users` table)
- Token blacklisting (if enabled, uses `cache` table)

**No special configuration needed!** Just ensure:
1. `JWT_SECRET` is set in `.env`
2. Run `php artisan jwt:secret` to generate it
3. Users table exists (via migrations)

---

## Blade UI with SQLite

The Blade UI works seamlessly with SQLite. All web routes use the same Eloquent models and database queries, which are database-agnostic.

**Test the Blade UI:**
1. Start server: `php artisan serve`
2. Visit: `http://127.0.0.1:8000`
3. Register/Login through the web interface
4. All wallet operations work identically to API

---

## Troubleshooting

### Issue: "SQLSTATE[HY000] [14] unable to open database file"

**Solution:**
```bash
# Ensure database directory exists and is writable
mkdir -p database
chmod 755 database
touch database/database.sqlite
chmod 644 database/database.sqlite
```

### Issue: "Database file is locked"

**Solution:**
- Close any database browsers (DB Browser for SQLite, etc.)
- Ensure only one PHP process is accessing the database
- For production, consider MySQL for concurrent access

### Issue: "Migration fails with enum error"

**Solution:**
SQLite doesn't support native ENUM types, but Laravel handles this automatically by using CHECK constraints. If you encounter issues, ensure you're using Laravel 11+.

### Issue: "JWT authentication not working"

**Solution:**
```bash
# Regenerate JWT secret
php artisan jwt:secret --force

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

---

## Production Considerations

### When to Use SQLite:
- ✅ Small to medium applications
- ✅ Low to moderate traffic
- ✅ Single-server deployments
- ✅ Development/staging environments
- ✅ Portable applications

### When to Use MySQL:
- ✅ High-traffic applications
- ✅ Multiple concurrent writes
- ✅ Large datasets (>100GB)
- ✅ Multi-server deployments
- ✅ Production environments with high concurrency

---

## Backup and Restore

### SQLite Backup

**Simple file copy:**
```bash
# Backup
cp database/database.sqlite database/database.sqlite.backup

# Restore
cp database/database.sqlite.backup database/database.sqlite
```

**Using Artisan:**
```bash
# Export data (if you create a custom command)
php artisan db:export database/backup.sql
```

### MySQL Backup

```bash
# Export
mysqldump -u root -p wallet_api > backup.sql

# Import
mysql -u root -p wallet_api < backup.sql
```

---

## Migration from MySQL to SQLite (or vice versa)

### MySQL → SQLite

1. Export MySQL data:
   ```bash
   mysqldump -u root -p wallet_api > export.sql
   ```

2. Create SQLite database:
   ```bash
   touch database/database.sqlite
   ```

3. Update `.env`:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

4. Run migrations:
   ```bash
   php artisan migrate:fresh
   ```

5. Import data (manual or via seeder)

### SQLite → MySQL

1. Create MySQL database:
   ```sql
   CREATE DATABASE wallet_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Update `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=wallet_api
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

3. Run migrations:
   ```bash
   php artisan migrate:fresh
   ```

4. Import data (manual or via seeder)

---

## Summary

✅ **SQLite is now the default** - Perfect for portable, GitHub-ready usage  
✅ **Auto-detection** - Automatically uses SQLite when `DB_CONNECTION=sqlite`  
✅ **JWT works** - No special configuration needed  
✅ **Blade UI works** - All web routes function identically  
✅ **Easy switching** - Change `.env` to switch between SQLite and MySQL  
✅ **Production-ready** - Both database options are fully supported  

Your Laravel Wallet API is now **fully portable** and ready for GitHub! 🚀

