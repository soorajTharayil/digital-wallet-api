# Digital Wallet API (Laravel 11)

## Overview
Secure JWT-authenticated Laravel 11 backend for a multi-currency digital wallet featuring atomic transactions, fraud detection, and per-user rate limiting.

**✨ Now with SQLite support for portable, zero-configuration usage!**

## Prerequisites
- PHP 8.2+ (with SQLite extension - usually included)
- Composer 2.x
- Git
- Postman or Thunder Client (optional)

**Note:** MySQL is optional. SQLite is the default and requires no database server setup!

## Quick Start (SQLite - Recommended)

### Option A: SQLite (Portable - No MySQL Required)

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/wallet-api.git
cd wallet-api

# 2. Install dependencies
composer install

# 3. Create SQLite database file
php artisan db:create-sqlite
# Or manually: touch database/database.sqlite

# 4. Copy and configure environment
cp .env.example .env
# Edit .env and ensure: DB_CONNECTION=sqlite

# 5. Generate keys and secrets
php artisan key:generate
php artisan jwt:secret

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Serve the API
php artisan serve
```

API now available at `http://127.0.0.1:8000` with SQLite! 🎉

### Option B: MySQL (Production)

```bash
# 1-2. Same as above
git clone https://github.com/yourusername/wallet-api.git
cd wallet-api
composer install

# 3. Create MySQL database
# Via phpMyAdmin or command line:
# CREATE DATABASE wallet_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Configure environment
cp .env.example .env
# Edit .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=wallet_api
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 5-7. Same as SQLite
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

## Database Configuration

### SQLite (Default - Portable)
- ✅ No database server required
- ✅ Single file database (`database/database.sqlite`)
- ✅ Perfect for GitHub, portable usage, and quick setup
- ✅ Works with JWT authentication
- ✅ Works with Blade UI

**Configuration:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### MySQL (Production)
- ✅ Better for high-concurrency production
- ✅ Requires MySQL/MariaDB server

**Configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

**See [SQLITE_SETUP_GUIDE.md](SQLITE_SETUP_GUIDE.md) for detailed instructions on switching between databases.**

## Testing the API
- Import the provided Postman/Thunder Client collection (create one if not yet provided).
- Register a user (`POST /api/register`).
- Login (`POST /api/login`) and copy the `token` value.
- Apply `Authorization: Bearer <token>` header for protected endpoints.

## Public Demo – How to Test the Digital Wallet

This section explains how to test the Digital Wallet application through the web interface. The demo provides a safe, isolated environment where you can explore all wallet features without affecting other users' data.

### Getting Started

When you visit the deployed application URL, you will be automatically redirected to the **Login page**. From here, you have two options:

#### Option 1: Register a New Account

1. Click on the **Register** link (or navigate to the registration page)
2. Fill in the registration form with:
   - **Name**: Your full name
   - **Email**: A valid email address (must be unique)
   - **Password**: A secure password (minimum requirements apply)
3. Click **Register** to create your account
4. You will be automatically logged in and redirected to your wallet dashboard

#### Option 2: Login to Existing Account

1. Enter your registered **Email** and **Password**
2. Click **Login**
3. You will be redirected to your wallet dashboard

### Available Features

Once logged in, you can fully test all wallet functionality through the intuitive web interface:

#### 📊 View Wallet Balance
- Access your dashboard to see your current wallet balance
- View your default currency (USD, INR, or EUR)
- Check your account summary at a glance

#### 💰 Deposit Money
- Navigate to the **Deposit** page
- Enter the amount you want to deposit
- Select the currency (USD, INR, or EUR)
- Confirm the deposit to add funds to your wallet
- The transaction will be recorded in your history

#### 💸 Withdraw Money
- Go to the **Withdraw** page
- Enter the amount you want to withdraw
- Select the currency
- Confirm the withdrawal (subject to balance availability and daily limits)
- The funds will be deducted from your wallet

#### 🔄 Transfer Money
- Visit the **Transfer** page
- Enter the recipient's email address (must be a registered user)
- Specify the transfer amount and currency
- Add an optional description
- Confirm the transfer
- Both you and the recipient will see the transaction in your respective histories

#### 📜 View Transaction History
- Access the **Transactions** page to view all your wallet activity
- Filter transactions by:
  - Transaction type (Credit/Deposit or Debit/Withdrawal/Transfer)
  - Currency (USD, INR, EUR)
- View detailed information including:
  - Transaction date and time
  - Amount and currency
  - Transaction description
  - Related wallet information (for transfers)

### Safety & Data Isolation

🔒 **Your data is completely isolated and secure:**
- Each user account has its own separate wallet
- All transactions are private to your account
- You can only transfer money to other registered users (identified by email)
- The demo environment is safe for testing—feel free to experiment!
- All wallet operations use atomic database transactions to ensure data integrity

### Tips for Testing

- **Create multiple accounts** to test transfer functionality between users
- **Try different currencies** to see automatic exchange rate conversion
- **Test transaction limits** by attempting withdrawals that exceed your balance or daily limits
- **Explore the transaction history** to see how all operations are recorded
- **Test the fraud detection** by making rapid, high-value transactions (if configured)

### Need Help?

If you encounter any issues or have questions:
- Check the [SQLITE_SETUP_GUIDE.md](SQLITE_SETUP_GUIDE.md) for technical setup details
- Review the API endpoints documentation below for programmatic access
- All features are fully functional and ready for testing!

## Available Endpoints
- `POST /api/register`
- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`
- `POST /api/refresh`
- `GET /api/wallet`
- `POST /api/wallet/deposit`
- `POST /api/wallet/withdraw`
- `POST /api/wallet/transfer`
- `GET /api/transactions`

## Features
- Atomic wallet operations using database transactions.
- Multi-currency support using `exchange_rates` table.
- Daily debit limits and high-frequency fraud detection service.
- JWT authentication via `php-open-source-saver/jwt-auth`.
- Per-user rate limiting with Laravel `RateLimiter`.

## Useful Commands

### Database Commands
```bash
# Create SQLite database file
php artisan db:create-sqlite

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Fresh migration (drop all and re-run)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Development Commands
```bash
# Start development server
php artisan serve

# Run tests
php artisan test

# Clear caches
php artisan config:clear
php artisan cache:clear
```

## Deployment Notes
- Create production-specific `.env` with secure credentials.
- Configure cache/queue/mail as required.
- Monitor `storage/logs/laravel.log` for suspicious activity alerts.
