# Digital Wallet API (Laravel 11)

## Overview
Secure JWT-authenticated Laravel 11 backend for a multi-currency digital wallet featuring atomic transactions, fraud detection, and per-user rate limiting.

## Prerequisites
- Windows 10
- XAMPP or Laragon (PHP 8.2+, Apache, MySQL 8.x)
- Composer 2.x
- Git
- Postman or Thunder Client

## Quick Start (Windows 10 + XAMPP)

```powershell
# 1. Open PowerShell as Administrator
cd C:\xampp\htdocs

# 2. Clone the repository
git clone https://github.com/yourusername/wallet-api.git
cd wallet-api

# 3. Copy environment variables
copy .env.example .env

# 4. Install dependencies
composer install

# 5. Generate keys and secrets
php artisan key:generate
php artisan jwt:secret

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Serve the API
php artisan serve
```

API now available at `http://127.0.0.1:8000`.

## Environment Setup (phpMyAdmin)
1. Launch XAMPP Control Panel and start Apache + MySQL.
2. Browse to `http://localhost/phpmyadmin/`.
3. Create database `wallet_api` with collation `utf8mb4_unicode_ci`.
4. Update `.env` with correct MySQL credentials if they differ, then run `php artisan config:clear`.

## Testing the API
- Import the provided Postman/Thunder Client collection (create one if not yet provided).
- Register a user (`POST /api/register`).
- Login (`POST /api/login`) and copy the `token` value.
- Apply `Authorization: Bearer <token>` header for protected endpoints.

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
- `php artisan migrate:fresh --seed`
- `php artisan serve`
- `php artisan test`

## Deployment Notes
- Create production-specific `.env` with secure credentials.
- Configure cache/queue/mail as required.
- Monitor `storage/logs/laravel.log` for suspicious activity alerts.
