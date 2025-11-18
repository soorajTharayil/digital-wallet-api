# User Guide

## Purpose
This guide explains how to run the Digital Wallet API on Windows 10 and exercise its endpoints using Postman.

## Prerequisites
- Completed setup steps from `README.md` (API reachable at `http://127.0.0.1:8000`).
- Postman or Thunder Client installed.

## JWT Workflow Overview
1. **Register** via `POST /api/register`. The response contains a JWT token.
2. **Login** via `POST /api/login` whenever authentication is needed again.
3. **Attach Token** in Postman: Authorization tab → Type `Bearer Token` → paste token.
4. **Use Protected Routes** (`/api/me`, `/api/wallet`, etc.) with the Bearer token header.
5. **Refresh Token** via `POST /api/refresh` before expiry to obtain a new token.
6. **Logout** via `POST /api/logout` to invalidate the current token.

## Step-by-Step Testing in Postman
1. **Create a Collection** named *Wallet API* with the base URL `http://127.0.0.1:8000`.
2. **Register User**
   - Method: `POST`
   - URL: `{{base_url}}/api/register`
   - Body (JSON): name, email, password, password_confirmation, default_currency.
3. **Login User**
   - Method: `POST`
   - URL: `{{base_url}}/api/login`
   - Body: email and password.
   - Save `token` value to an environment variable `jwt_token`.
4. **Configure Authorization**
   - For subsequent requests, set Authorization → Bearer Token → `{{jwt_token}}`.
5. **Check Wallet Balance**
   - `GET {{base_url}}/api/wallet`
   - Verify balance, currency, transaction preview.
6. **Deposit Funds**
   - `POST {{base_url}}/api/wallet/deposit`
   - Body: amount, currency (optional), description.
7. **Withdraw Funds**
   - `POST {{base_url}}/api/wallet/withdraw`
   - Body: amount, optional currency, description.
8. **Transfer Funds**
   - Ensure recipient exists; call `POST /api/wallet/transfer`.
   - Confirm sender balance decreases, recipient increases.
9. **View Transactions**
   - `GET {{base_url}}/api/transactions?type=debit&per_page=10`
   - Inspect pagination object and metadata.
10. **Exercise Fraud Controls**
    - Perform high-value withdrawals in quick succession; observe `422` responses and confirm logs in `storage/logs/laravel.log` if available.
11. **Rate Limit Validation**
    - Use Postman Runner to send >60 requests/minute; expect `429` with retry hint.

## Tips
- Use Postman Environments to store `base_url` and `jwt_token` variables.
- In Thunder Client, set global headers with `Authorization: Bearer {{jwt_token}}`.
- If requests return `401`, ensure token is present, prefixed with `Bearer`, and not expired.
- Monitor application logs via `storage/logs/laravel.log` for suspicious activity alerts.

## Troubleshooting
- **Database connection errors**: Confirm MySQL service is running and credentials match `.env`.
- **JWT secret missing**: Run `php artisan jwt:secret` after copying `.env`.
- **CORS issues**: Add CORS middleware or enable via `app/Http/Middleware/HandleCors.php` if integrating with a frontend.

## Next Steps
- Build automated integration tests in `tests/Feature`.
- Configure queue and notification channels for real-time fraud alerts.
- Deploy behind HTTPS with secure cookie settings for production usage.
