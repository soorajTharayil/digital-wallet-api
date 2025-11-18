# Digital Wallet API – Project Structure & Execution Guide

## Root Directory Overview

- `artisan` – CLI entry point for running Laravel commands (`php artisan migrate`, `php artisan test`, etc.).
- `composer.json` / `composer.lock` – Define PHP package dependencies (Laravel framework, JWT library) and autoloading rules.
- `.env` *(generated locally)* – Stores environment-specific values such as database credentials, JWT secret, mail settings, and rate-limit overrides. Laravel reads these when bootstrapping.
- `phpunit.xml` – Configuration for automated tests.
- `README.md`, `USER_GUIDE.md`, `TEST_CASES.md` – Human-facing documentation for setup, manual testing, and scenario coverage.

## `app/` – Core Application Code

Laravel stores all business logic in `app/`. This folder is PSR-4 autoloaded, so each namespace maps to a directory.

### `app/Http/`

- `Controllers/`
  - `AuthController.php` – Handles registration, login, token refresh, and profile retrieval using JWT.
  - `WalletController.php` – Exposes wallet operations (show balance, deposit, withdraw, transfer) and orchestrates fraud checks and currency conversion.
  - `TransactionController.php` – Returns paginated wallet transactions with filters.
  - `Controller.php` – Base controller class from which others inherit shared behavior.
- `Middleware/`
  - `JwtMiddleware.php` – Validates incoming JWT tokens and short-circuits unauthorized requests.
  - `ForceJsonResponse.php` – Forces all responses to be JSON, aligning with API behavior and Postman expectations.
  - `RequestThrottleMiddleware.php` – Applies custom rate-limiting logic using configuration values for per-minute quotas.
  - `Authenticate.php`, `RedirectIfAuthenticated.php`, `ValidateSignature.php` – Standard Laravel middleware reused where needed.
- `Kernel.php` – Registers global middleware, API/web middleware groups, and aliases like `jwt.auth` that are used in route definitions.

### `app/Models/`

- `User.php` – Represents authenticated users, holds preferences (`default_currency`, `daily_debit_limit`) and relationships to wallets.
- `Wallet.php` – Manages wallet balances, currency metadata, and links to transactions.
- `Transaction.php` – Captures credits/debits with metadata to aid auditing and fraud checks.
- `ExchangeRate.php` – Stores exchange rates for currency conversion during deposits, withdrawals, and transfers.
  - *All models extend `Illuminate\Database\Eloquent\Model` and are wired to the database via Eloquent ORM.*

### `app/Services/`

- `FraudDetectionService.php` – Encapsulates logic that enforces daily debit limits and high-value transaction thresholds before debits execute.

### `app/Console/`

- `Kernel.php` – Registers Artisan commands (if any exist under `Console/Commands`) and scheduled tasks. Currently used to load console routes.

### `app/Providers/`

- `RouteServiceProvider.php` – Boots API rate-limiting rules, binds the `api` route group to `routes/api.php`, and centralizes routing-related configuration.

### `app/Exceptions/`

- `Handler.php` – Global exception handler (extends Laravel default) that could be customized to format API errors; currently inherits default behavior.

## `bootstrap/`

- `app.php` – Creates and configures the Laravel application instance. It registers providers (including `RouteServiceProvider`) and wires the API routes during startup. Executed by the HTTP entry point.

## `config/`

- `auth.php` – Authentication guards/providers settings; JWT auth middleware leverages these.
- `jwt.php` – Configuration for the PHPOpenSourceSaver JWT package (token TTL, hashing algorithms).
- `services.php` – Custom configuration values for wallet defaults, fraud detection thresholds, and request rate limits. These read from `.env` and are injected wherever `config('services.*')` is called.

## `database/`

- `migrations/`
  - `2025_01_01_000000_create_users_table.php` – Defines the core `users` table with auth fields and wallet preferences.
  - `2025_01_01_000100_create_wallets_table.php` – Creates wallets tied to users with balance and currency fields.
  - `2025_01_01_000200_create_transactions_table.php` – Stores each transaction record with type, amount, metadata, and foreign keys.
  - `2025_01_01_000300_create_exchange_rates_table.php` – Holds conversion rates between supported currencies.
  - `2025_11_11_160945_create_cache_table.php` – Supports Laravel’s cache driver when using the database backend.
- `seeders/`
  - `DatabaseSeeder.php` – Entry point for seeding; can call additional seeders.
  - `ExchangeRateSeeder.php` – Populates baseline currency exchange rates for functional tests and demo flows.

## `public/`

- `index.php` – **HTTP entry point**. PHP-FPM or Apache routes all web traffic here. It loads `../vendor/autoload.php`, bootstraps the framework via `bootstrap/app.php`, and hands the request to Laravel’s HTTP kernel.

## `routes/`

- `api.php` – Defines REST endpoints consumed in Postman. Routes for `/register`, `/login`, authenticated wallet operations, and `/transactions` are grouped under JWT and throttle middleware.
- `console.php` – Registers Artisan-only commands. Useful for queue workers or scheduled tasks.

## `storage/`

- Contains runtime-generated files:
  - `app/` – Application-specific files (exports, temporary storage).
  - `framework/` – Cache, sessions, compiled views.
  - `logs/laravel.log` – Centralized log file capturing request/exception details, invaluable for debugging.

## `tests/`

- `Feature/` – Houses HTTP-level feature tests. Placeholder directory ready for test classes that simulate API flows (register → deposit → transfer).

## `vendor/`

- Composer-installed dependencies (Laravel core, Carbon, PHPOpenSourceSaver JWT library, PHPUnit). Autoloaded via `vendor/autoload.php`.

## Additional Supporting Files

- `TEST_CASES.md` – Enumerates manual/automated cases (e.g., successful transfer, rate-limit breach, JWT expiration) to verify API behavior.
- `USER_GUIDE.md` – Step-by-step usage instructions for onboarding testers or stakeholders.

---

## Request Lifecycle: Digital Wallet API

1. **API Call from Postman**
   - User sends a request (e.g., `POST /api/wallet/transfer`) with JWT token and payload.
   - Postman targets the application URL (local or deployed) routed to `public/index.php`.

2. **Bootstrap Phase**
   - `public/index.php` loads `vendor/autoload.php` and bootstraps Laravel via `bootstrap/app.php`.
   - Laravel resolves the HTTP kernel defined in `app/Http/Kernel.php`, which prepares middleware stacks.

3. **Middleware & Routing**
   - Global/API middleware run: `ForceJsonResponse`, throttle, route bindings.
   - Route-specific middleware `jwt.auth` (from `JwtMiddleware.php`) authenticates the token.
   - Laravel matches the request to the appropriate route in `routes/api.php`.

4. **Controller Execution**
   - Matched controller method (e.g., `WalletController@transfer`) receives a validated `Request`.
   - Input validation occurs; invalid data triggers JSON error responses.

5. **Business Logic & Models**
   - Controller interacts with Eloquent models (`User`, `Wallet`, `Transaction`, `ExchangeRate`) and services (`FraudDetectionService`) to perform operations.
   - Database reads/writes are wrapped in transactions to keep wallet balances consistent.
   - Currency conversion uses exchange rates stored via migrations/seeders.

6. **Response Formation**
   - Controller returns a structured JSON response (`response()->json([...])`).
   - Middleware ensures headers specify JSON and that throttling metrics are updated.
   - Response propagates back through the kernel to the HTTP server, then to Postman.

7. **Logging & Post-Processing**
   - Any warnings (e.g., fraud detection flags) are logged to `storage/logs/laravel.log`.
   - Rate limiter keys are cleared or updated for the next request.

---

## Key Components Summary

- **Major Files**
  - `.env` – Environment settings (DB host, JWT secret, rate limits).
  - `composer.json` – Declares Laravel framework, JWT package, and autoload rules.
  - `app/Http/Kernel.php` – Configures middleware groups/aliases (`jwt.auth`, `force.json`).
  - `app/Providers/RouteServiceProvider.php` – Boots API routes and rate-limits.
  - `public/index.php` – First PHP script executed for all HTTP traffic.
- **Request Entry & Flow**
  - Entry: `public/index.php` → bootstrap → HTTP kernel.
  - Routes: `routes/api.php` picks a controller method protected by middleware.
  - Controllers: Call services/models → database → return JSON response.
- **MVC in This Project**
  - **Model** – Eloquent classes (`User`, `Wallet`, `Transaction`, `ExchangeRate`) map to MySQL tables.
  - **View** – JSON responses constructed in controllers replace traditional Blade views for this API.
  - **Controller** – `AuthController`, `WalletController`, `TransactionController` validate requests, call business logic, and shape responses.



