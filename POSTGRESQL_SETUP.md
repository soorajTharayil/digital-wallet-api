# PostgreSQL Setup Guide for Laravel

## Current Status
✅ PostgreSQL credentials configured in `.env`  
❌ PHP PostgreSQL extension (pdo_pgsql) not enabled

## Error Message
```
could not find driver (Connection: pgsql)
```

This means PHP cannot connect to PostgreSQL because the `pdo_pgsql` extension is not enabled.

---

## Solution: Enable PostgreSQL Extension in PHP

### Step 1: Find PHP Configuration File

```bash
php --ini
```

Look for: **"Loaded Configuration File"** - this is your `php.ini` file location.

### Step 2: Edit php.ini

1. Open `php.ini` in a text editor (as Administrator)
2. Find the line: `;extension=pdo_pgsql`
3. Remove the semicolon (`;`) to uncomment it:
   ```ini
   extension=pdo_pgsql
   ```
4. Also check for: `;extension=pgsql` and uncomment it:
   ```ini
   extension=pgsql
   ```
5. Save the file

### Step 3: Restart Web Server

**If using XAMPP:**
- Stop Apache in XAMPP Control Panel
- Start Apache again

**If using Laragon:**
- Restart Laragon

**If using built-in server:**
- Stop current server (Ctrl+C)
- Start again: `php artisan serve`

### Step 4: Verify Extension is Loaded

```bash
php -m | findstr pdo_pgsql
php -m | findstr pgsql
```

You should see both `pdo_pgsql` and `pgsql` in the list.

### Step 5: Test Database Connection

```bash
php artisan migrate
```

---

## Alternative: Install PostgreSQL Extension (If Not Present)

If the extensions don't exist in your PHP installation:

### For XAMPP:

1. Download PostgreSQL DLL files:
   - Go to: https://pecl.php.net/package/pdo_pgsql
   - Download the Windows DLL for your PHP version
   - Or use: https://windows.php.net/downloads/pecl/releases/

2. Copy DLL files to PHP extension directory:
   - Usually: `C:\xampp\php\ext\`

3. Edit `php.ini`:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

4. Restart Apache

### For Laragon:

Laragon usually includes PostgreSQL extensions. Just uncomment them in `php.ini`.

---

## Quick Check Commands

```bash
# Check if extension is loaded
php -r "echo extension_loaded('pdo_pgsql') ? 'YES' : 'NO';"

# List all loaded extensions
php -m

# Check PHP configuration file location
php --ini
```

---

## Your Current .env Configuration

```env
DB_CONNECTION=pgsql
DB_HOST=dpg-d4hb1f2li9vc73e3fbu0-a
DB_PORT=5432
DB_DATABASE=digital_wallet_db_yi3s
DB_USERNAME=digital_wallet_db_yi3s_user
DB_PASSWORD=FOFY0wKWQdjHImaSdpf2PiqQYwxSwWm5
```

✅ Configuration is correct - you just need to enable the PHP extension!

---

## After Enabling Extension

Once the extension is enabled, run:

```bash
# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

---

## Troubleshooting

### Issue: "extension=pdo_pgsql" not found in php.ini

**Solution:** The extension might not be installed. Download it from PECL or use a PHP build that includes it.

### Issue: Still getting "could not find driver"

**Solution:**
1. Verify extension is uncommented in php.ini
2. Restart web server completely
3. Check PHP error logs
4. Verify PostgreSQL server is accessible from your network

### Issue: Connection timeout

**Solution:** Your PostgreSQL host might require SSL or have firewall restrictions. Check your database provider's connection requirements.

---

## Need Help?

- Check PHP error logs: `storage/logs/laravel.log`
- Test connection manually: `php artisan tinker` then `DB::connection()->getPdo();`
- Verify PostgreSQL server is running and accessible

