# Interview Preparation Q&A - Digital Wallet API

## Table of Contents
1. [Project Overview & Architecture](#project-overview--architecture)
2. [Laravel Fundamentals](#laravel-fundamentals)
3. [Authentication & JWT](#authentication--jwt)
4. [Wallet Management](#wallet-management)
5. [Transaction Processing](#transaction-processing)
6. [Multi-Currency Support](#multi-currency-support)
7. [Fraud Detection](#fraud-detection)
8. [Rate Limiting & Security](#rate-limiting--security)
9. [Database & Models](#database--models)
10. [Code Quality & Best Practices](#code-quality--best-practices)
11. [Potential Improvements](#potential-improvements)

---

## Project Overview & Architecture

### Q1: Can you explain the overall architecture of this Digital Wallet API?

**Answer:**
The Digital Wallet API follows Laravel's MVC (Model-View-Controller) architecture pattern, adapted for a RESTful API:

- **Models** (`app/Models/`): Eloquent ORM models representing database entities (User, Wallet, Transaction, ExchangeRate)
- **Controllers** (`app/Http/Controllers/`): Handle HTTP requests, validate input, orchestrate business logic, and return JSON responses
- **Services** (`app/Services/`): Business logic separated from controllers (e.g., FraudDetectionService)
- **Middleware** (`app/Http/Middleware/`): Intercepts requests for authentication, rate limiting, and response formatting
- **Routes** (`routes/api.php`): Define API endpoints and apply middleware
- **Migrations** (`database/migrations/`): Database schema definitions
- **Config** (`config/`): Application configuration files

The request flow: `public/index.php` → Bootstrap → Middleware → Routes → Controllers → Models/Services → Database → JSON Response

---

### Q2: What is the request lifecycle in this application?

**Answer:**
1. **Entry Point**: HTTP request hits `public/index.php`
2. **Bootstrap**: Loads Composer autoloader and initializes Laravel via `bootstrap/app.php`
3. **HTTP Kernel**: `app/Http/Kernel.php` processes the request through middleware stack
4. **Middleware Execution**:
   - `ForceJsonResponse` ensures JSON responses
   - `throttle:api` applies rate limiting
   - `jwt.auth` validates JWT token for protected routes
5. **Routing**: `routes/api.php` matches URL to controller method
6. **Controller**: Validates input, calls services/models, performs business logic
7. **Database**: Eloquent models interact with MySQL database
8. **Response**: JSON response returned through middleware back to client

---

### Q3: Why did you choose Laravel for this project?

**Answer:**
Laravel is ideal for building RESTful APIs because:
- **Eloquent ORM**: Simplifies database interactions with relationships
- **Built-in Authentication**: JWT support via packages (php-open-source-saver/jwt-auth)
- **Middleware System**: Easy to implement authentication, rate limiting, and request transformation
- **Validation**: Powerful validation rules built-in
- **Database Migrations**: Version-controlled schema management
- **Service Container**: Dependency injection for clean, testable code
- **Artisan CLI**: Helpful commands for migrations, seeding, testing
- **Active Community**: Extensive documentation and packages

---

## Laravel Fundamentals

### Q4: What is Laravel and what are its key features?

**Answer:**
Laravel is a PHP web framework following MVC architecture. Key features:
- **Eloquent ORM**: Active Record pattern for database operations
- **Blade Templating**: (Not used in this API, but Laravel's templating engine)
- **Artisan CLI**: Command-line tool for migrations, seeding, testing
- **Middleware**: Intercept and filter HTTP requests
- **Service Container**: Dependency injection container
- **Routing**: Clean, expressive route definitions
- **Validation**: Built-in request validation
- **Migrations**: Database version control
- **Seeding**: Populate database with test data
- **Events & Listeners**: Event-driven programming
- **Queues**: Background job processing

---

### Q5: What is Eloquent ORM and how is it used in this project?

**Answer:**
Eloquent is Laravel's ORM (Object-Relational Mapping) that provides an ActiveRecord implementation. In this project:

- **Models**: `User`, `Wallet`, `Transaction`, `ExchangeRate` extend `Illuminate\Database\Eloquent\Model`
- **Relationships**:
  - `User` hasOne `Wallet`
  - `Wallet` belongsTo `User` and hasMany `Transaction`
  - `Transaction` belongsTo `Wallet`
- **Usage Examples**:
  ```php
  $user->wallet()->create([...])  // Create related model
  $wallet->transactions()->latest()->get()  // Query relationship
  User::where('email', $email)->first()  // Query builder
  ```

Eloquent provides:
- Automatic table mapping (User → users table)
- Relationship management
- Query builder methods
- Mass assignment protection via `$fillable`
- Attribute casting (e.g., `'balance' => 'decimal:2'`)

---

### Q6: What are Laravel Migrations and why are they important?

**Answer:**
Migrations are version-controlled database schema definitions. They:
- **Version Control**: Track database changes over time
- **Team Collaboration**: Ensure all developers have the same schema
- **Rollback**: Can undo changes with `php artisan migrate:rollback`
- **Consistency**: Same schema across development, staging, production

In this project:
- `create_users_table.php` - User authentication and preferences
- `create_wallets_table.php` - Wallet balances and currency
- `create_transactions_table.php` - Transaction history
- `create_exchange_rates_table.php` - Currency conversion rates

Each migration has `up()` (create/modify) and `down()` (rollback) methods.

---

### Q7: What is Middleware in Laravel?

**Answer:**
Middleware intercepts HTTP requests before they reach controllers. It can:
- **Authenticate**: Check if user is logged in (JWT validation)
- **Authorize**: Check permissions
- **Transform**: Modify requests/responses (ForceJsonResponse)
- **Rate Limit**: Throttle requests
- **Log**: Track requests

In this project:
- **JwtMiddleware**: Validates JWT tokens
- **ForceJsonResponse**: Ensures all responses are JSON
- **RequestThrottleMiddleware**: Custom rate limiting
- **Global Middleware**: Applied to all requests
- **Route Middleware**: Applied to specific routes/groups

Middleware is registered in `app/Http/Kernel.php` and applied in `routes/api.php`.

---

### Q8: What is the Service Container and Dependency Injection?

**Answer:**
Laravel's Service Container manages class dependencies and performs dependency injection automatically.

**Example from WalletController:**
```php
public function __construct(private readonly FraudDetectionService $fraudService)
{
}
```

Laravel automatically resolves `FraudDetectionService` and injects it. Benefits:
- **Loose Coupling**: Controllers don't directly instantiate dependencies
- **Testability**: Easy to mock services in tests
- **Single Responsibility**: Each class has one job
- **Reusability**: Services can be shared across controllers

---

### Q9: What is Composer and how does it work?

**Answer:**
Composer is PHP's dependency manager (like npm for Node.js). It:
- **Manages Packages**: Installs Laravel framework, JWT library, etc.
- **Autoloading**: Generates `vendor/autoload.php` for class autoloading
- **Version Control**: `composer.json` declares dependencies, `composer.lock` locks versions
- **PSR-4 Autoloading**: Maps namespaces to directories

Key packages in this project:
- `laravel/framework`: Core Laravel
- `php-open-source-saver/jwt-auth`: JWT authentication
- `phpunit/phpunit`: Testing framework

---

### Q10: What is the difference between `$fillable` and `$guarded` in Eloquent models?

**Answer:**
Both protect against mass assignment vulnerabilities:

- **`$fillable`**: Whitelist of attributes that CAN be mass-assigned
  ```php
  protected $fillable = ['name', 'email', 'password'];
  ```

- **`$guarded`**: Blacklist of attributes that CANNOT be mass-assigned
  ```php
  protected $guarded = ['id', 'is_admin'];
  ```

In this project, we use `$fillable` to explicitly allow safe fields like `balance`, `currency`, `type`, etc.

---

### Q11: What are Eloquent Relationships and which ones are used here?

**Answer:**
Eloquent relationships define how models relate to each other:

1. **hasOne**: One-to-one (User hasOne Wallet)
   ```php
   public function wallet() {
       return $this->hasOne(Wallet::class);
   }
   ```

2. **belongsTo**: Inverse of hasOne/hasMany (Wallet belongsTo User)
   ```php
   public function user() {
       return $this->belongsTo(User::class);
   }
   ```

3. **hasMany**: One-to-many (Wallet hasMany Transaction)
   ```php
   public function transactions() {
       return $this->hasMany(Transaction::class);
   }
   ```

Benefits: Easy querying (`$user->wallet->balance`), automatic foreign key handling, eager loading to prevent N+1 queries.

---

### Q12: What is the `.env` file and why is it important?

**Answer:**
`.env` (Environment) stores configuration that varies between environments:
- **Database credentials**: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- **JWT secret**: JWT_SECRET for token signing
- **App settings**: APP_ENV, APP_DEBUG
- **Custom config**: DEFAULT_CURRENCY, FRAUD_HIGH_VALUE_THRESHOLD, API_RATE_LIMIT_PER_MINUTE

**Why important:**
- **Security**: Never commit `.env` to version control (use `.env.example`)
- **Flexibility**: Different settings for dev/staging/production
- **Access**: Via `env()` helper or `config()` (which reads from config files that use `env()`)

---

## Authentication & JWT

### Q13: How does JWT authentication work in this project?

**Answer:**
JWT (JSON Web Token) is a stateless authentication mechanism:

1. **Registration/Login**: User provides credentials, server validates and generates JWT token
2. **Token Structure**: Header.Payload.Signature (base64 encoded)
3. **Client Storage**: Client stores token (localStorage, cookie, etc.)
4. **Request**: Client sends token in `Authorization: Bearer <token>` header
5. **Validation**: `JwtMiddleware` validates token signature and expiration
6. **User Context**: If valid, `auth()->user()` returns authenticated user

**Implementation:**
- Package: `php-open-source-saver/jwt-auth`
- User model implements `JWTSubject` interface
- `JwtMiddleware` validates tokens before protected routes
- Token expiration configured in `config/jwt.php`

---

### Q14: What is the difference between `auth()->user()` and `$request->user()`?

**Answer:**
Both return the authenticated user, but:
- **`auth()->user()`**: Global helper, works anywhere in the application
- **`$request->user()`**: Request-specific, only available in controllers/middleware with Request object

In this project, we use `auth()->user()` in controllers for consistency.

---

### Q15: How is the JWT token generated and what information does it contain?

**Answer:**
Token generation in `AuthController`:
```php
$token = JWTAuth::fromUser($user);
```

Token contains:
- **Header**: Algorithm (HS256) and token type
- **Payload**: User ID (from `getJWTIdentifier()`), custom claims (default_currency from `getJWTCustomClaims()`), expiration time
- **Signature**: HMAC signature using JWT_SECRET

Custom claims added in `User::getJWTCustomClaims()` include `default_currency` for quick access without database query.

---

### Q16: What happens when a JWT token expires?

**Answer:**
When token expires:
1. `JwtMiddleware` catches the exception
2. Returns 401 Unauthorized response
3. Client must call `/api/refresh` endpoint to get new token
4. Refresh endpoint validates old token and issues new one

Token TTL configured in `config/jwt.php` (default: 60 minutes).

---

## Wallet Management

### Q17: How is a wallet created for a new user?

**Answer:**
Using Eloquent Model Events in `User::booted()`:
```php
protected static function booted(): void
{
    static::created(function (self $user) {
        DB::transaction(function () use ($user) {
            $user->wallet()->create([
                'balance' => 0,
                'currency' => $user->default_currency,
            ]);
        });
    });
}
```

When a User is created, the `created` event fires automatically, creating a wallet with:
- Initial balance: 0
- Currency: User's default_currency (or system default)
- One-to-one relationship enforced by unique constraint on `user_id`

---

### Q18: Why is each user limited to one wallet?

**Answer:**
Business requirement: Each user has a single wallet. Enforced by:
- Database: `unique()` constraint on `user_id` in wallets table
- Relationship: `hasOne()` instead of `hasMany()`
- Simplicity: Easier to manage balance, transactions, and fraud detection

If multi-wallet support was needed, we'd change to `hasMany()` and add a `wallet_type` or `name` field.

---

### Q19: How is the wallet balance updated and why use database transactions?

**Answer:**
Balance updates use Eloquent's `increment()`/`decrement()` methods:
```php
$wallet->increment('balance', $amount);
$wallet->decrement('balance', $amount);
```

**Why transactions (`DB::transaction()`):**
- **Atomicity**: All-or-nothing - if transaction creation fails, balance update rolls back
- **Consistency**: Prevents race conditions (two simultaneous transfers)
- **Data Integrity**: Balance always matches sum of transactions

Example: If creating transaction record fails, balance change is undone automatically.

---

## Transaction Processing

### Q20: How does the deposit process work?

**Answer:**
1. **Validation**: Amount (min: 1), optional currency, optional description
2. **Currency Conversion**: If deposit currency differs from wallet currency, convert using exchange rates
3. **Database Transaction**: Wraps both operations
4. **Balance Update**: `$wallet->increment('balance', $convertedAmount)`
5. **Transaction Record**: Create Transaction with TYPE_CREDIT, store metadata (original amount, source currency)
6. **Response**: Return success message and updated balance

**Key Points:**
- Deposits are always credits
- Currency conversion happens automatically
- Metadata preserves original transaction details

---

### Q21: How does the transfer process work between two users?

**Answer:**
1. **Validation**: Recipient email exists, amount valid, sender ≠ recipient
2. **Fraud Check**: Verify daily limit and high-frequency detection
3. **Balance Check**: Sender has sufficient balance
4. **Currency Conversion**:
   - Convert to sender's currency for deduction
   - Convert to recipient's currency for credit
5. **Database Transaction**: Atomic operation
   - Decrement sender balance
   - Increment recipient balance
   - Create debit transaction for sender
   - Create credit transaction for recipient
6. **Response**: Return success and sender's new balance

**Why two transaction records?**
- Audit trail: Both parties see the transaction
- Different currencies: Each wallet stores in its own currency
- Metadata: Links transactions via `sender_wallet_id` / `recipient_wallet_id`

---

### Q22: What is the difference between credit and debit transactions?

**Answer:**
- **CREDIT (TYPE_CREDIT)**: Money coming INTO wallet
  - Deposits
  - Transfers received
  - Refunds
  
- **DEBIT (TYPE_DEBIT)**: Money going OUT of wallet
  - Withdrawals
  - Transfers sent
  - Fees

**Why important:**
- Fraud detection only checks debits (money leaving)
- Transaction history filtering by type
- Financial reporting and auditing

---

### Q23: Why store transaction metadata as JSON?

**Answer:**
The `metadata` field stores flexible, schema-less data:
```php
'metadata' => [
    'source_currency' => 'USD',
    'original_amount' => 100,
    'sender_wallet_id' => 5,
]
```

**Benefits:**
- **Flexibility**: Can add new fields without migration
- **Audit Trail**: Preserves original transaction context
- **Debugging**: Helps trace issues (what currency was used, original amount)
- **Future Features**: Can store payment gateway IDs, reference numbers, etc.

**Casting**: `protected $casts = ['metadata' => 'array']` automatically converts JSON ↔ array.

---

## Multi-Currency Support

### Q24: How does multi-currency conversion work?

**Answer:**
1. **Exchange Rates**: Stored in `exchange_rates` table (base_currency, target_currency, rate)
2. **Conversion Method**: `WalletController::convertAmount()`
   ```php
   $rate = DB::table('exchange_rates')
       ->where('base_currency', $fromCurrency)
       ->where('target_currency', $toCurrency)
       ->value('rate');
   return round($amount * $rate, 2);
   ```
3. **Usage**: Called during deposit, withdraw, and transfer
4. **Storage**: All balances stored in wallet's currency, transactions record original currency in metadata

**Example**: User deposits 100 USD to INR wallet (rate: 1 USD = 83 INR) → 8300 INR added to balance.

---

### Q25: How are exchange rates managed and updated?

**Answer:**
- **Storage**: `exchange_rates` table with `base_currency`, `target_currency`, `rate`
- **Seeding**: `ExchangeRateSeeder` populates initial rates
- **Manual Updates**: Can update via database or admin interface (not implemented)
- **Future**: Could integrate with external API (e.g., fixer.io) via scheduled job

**Current Implementation**: Static rates seeded at setup. In production, would need:
- Scheduled job to fetch latest rates
- API endpoint for admin updates
- Rate history table for auditing

---

### Q26: What happens if an exchange rate is missing?

**Answer:**
The `convertAmount()` method throws a validation exception:
```php
if (!$rate) {
    throw ValidationException::withMessages([
        'currency' => ["Exchange rate from {$fromCurrency} to {$toCurrency} not configured."],
    ]);
}
```

This prevents invalid conversions and alerts administrators to configure missing rates.

---

## Fraud Detection

### Q27: How does fraud detection work in this system?

**Answer:**
Two mechanisms implemented in `FraudDetectionService`:

1. **Daily Debit Limit**:
   - Sums all debits for user today
   - Checks if adding new debit exceeds `daily_debit_limit`
   - Prevents excessive withdrawals/transfers per day

2. **High-Frequency Detection**:
   - Checks if transaction amount ≥ threshold (default: $5000)
   - Counts high-value debits in time window (default: 10 minutes)
   - If count ≥ max (default: 3), flags as suspicious
   - Logs warning and blocks transaction

**Configuration**: All thresholds in `config/services.php` (read from `.env`)

---

### Q28: Why is fraud detection only applied to debits, not credits?

**Answer:**
- **Business Logic**: Credits (deposits, received transfers) add money - less risky
- **User Experience**: Don't want to block legitimate deposits
- **Focus**: Fraud typically involves money leaving the system (withdrawals, transfers)
- **Performance**: Fewer checks = faster credit processing

If needed, could add credit fraud detection (e.g., detect money laundering patterns).

---

### Q29: How would you improve fraud detection?

**Answer:**
Potential improvements:
1. **Machine Learning**: Pattern recognition for unusual behavior
2. **IP Geolocation**: Flag transactions from unusual locations
3. **Device Fingerprinting**: Detect account access from new devices
4. **Velocity Checks**: Multiple small transactions to bypass limits
5. **Recipient Analysis**: Flag transfers to known fraudulent accounts
6. **Time-based Patterns**: Unusual transaction times (e.g., 3 AM)
7. **Amount Patterns**: Round numbers, specific amounts (common in scams)
8. **Two-Factor Authentication**: Require 2FA for high-value transactions
9. **Manual Review Queue**: Flag suspicious transactions for human review
10. **Real-time Alerts**: Notify user/admin of suspicious activity

---

## Rate Limiting & Security

### Q30: How is API rate limiting implemented?

**Answer:**
Two layers:

1. **Laravel Built-in**: `throttle:api` middleware (in routes)
   - Uses `RateLimiter` facade
   - Default: 60 requests/minute per IP/user

2. **Custom Middleware**: `RequestThrottleMiddleware`
   - More granular control
   - Configurable via `config/services.php`
   - Uses user ID or IP as identifier
   - Returns 429 status with `retry_after_seconds`

**Configuration**:
```php
'rate_limits' => [
    'per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
    'per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
]
```

---

### Q31: Why is rate limiting important?

**Answer:**
- **DDoS Protection**: Prevents overwhelming server with requests
- **Abuse Prevention**: Stops automated attacks, brute force
- **Resource Management**: Ensures fair usage, prevents one user from consuming all resources
- **Cost Control**: Reduces server costs, database load
- **API Fairness**: Ensures all users get reasonable response times

---

### Q32: What security measures are implemented?

**Answer:**
1. **Password Hashing**: `Hash::make()` uses bcrypt (one-way hashing)
2. **JWT Authentication**: Secure token-based auth, tokens signed with secret
3. **Input Validation**: All requests validated before processing
4. **SQL Injection Prevention**: Eloquent ORM uses parameterized queries
5. **Mass Assignment Protection**: `$fillable` prevents unauthorized field updates
6. **CSRF Protection**: Not needed for stateless API, but Laravel provides it for web routes
7. **Rate Limiting**: Prevents brute force and DDoS
8. **Database Transactions**: Prevents data corruption
9. **Error Handling**: Doesn't expose sensitive info in error messages

**Missing (for production):**
- HTTPS enforcement
- API key rotation
- Request signing
- IP whitelisting for admin endpoints
- Security headers (CORS, XSS protection)

---

### Q33: What is the `ForceJsonResponse` middleware and why is it needed?

**Answer:**
```php
public function handle(Request $request, Closure $next)
{
    $request->headers->set('Accept', 'application/json');
    return $next($request);
}
```

**Purpose**: Forces Laravel to return JSON responses even if client doesn't send `Accept: application/json` header.

**Why needed**:
- **Consistency**: All API responses are JSON
- **Error Format**: Laravel's validation errors return as JSON instead of HTML
- **Postman Friendly**: Works better with API testing tools
- **Frontend Compatibility**: Frontend expects JSON, not HTML error pages

---

## Database & Models

### Q34: What database relationships exist in this project?

**Answer:**
1. **User → Wallet**: One-to-One (`hasOne` / `belongsTo`)
   - Each user has exactly one wallet
   - Foreign key: `wallets.user_id`

2. **Wallet → Transaction**: One-to-Many (`hasMany` / `belongsTo`)
   - Each wallet has many transactions
   - Foreign key: `transactions.wallet_id`

3. **Transaction → Wallet (related)**: Optional relationship
   - For transfers, links to recipient/sender wallet
   - Foreign key: `transactions.related_wallet_id`

**Benefits**: Easy querying (`$user->wallet->transactions`), automatic foreign key constraints, eager loading.

---

### Q35: What is eager loading and why is it important?

**Answer:**
Eager loading prevents N+1 query problems.

**Without Eager Loading** (N+1 problem):
```php
$wallets = Wallet::all();
foreach ($wallets as $wallet) {
    echo $wallet->user->name;  // 1 query per wallet!
}
// Total: 1 + N queries (N = number of wallets)
```

**With Eager Loading**:
```php
$wallets = Wallet::with('user')->get();
foreach ($wallets as $wallet) {
    echo $wallet->user->name;  // No additional queries!
}
// Total: 2 queries (1 for wallets, 1 for users)
```

**In this project**: `$wallet->with('transactions')->first()` loads wallet and transactions in 2 queries instead of N+1.

---

### Q36: What are database indexes and which ones are used?

**Answer:**
Indexes speed up queries by creating sorted data structures.

**Automatic indexes in this project**:
- Primary keys: `id` columns (auto-indexed)
- Foreign keys: `user_id`, `wallet_id` (auto-indexed for joins)
- Unique constraint: `wallets.user_id` (indexed for uniqueness)

**Missing indexes (potential improvements)**:
- `transactions.created_at` (for date filtering)
- `transactions.type` (for filtering by credit/debit)
- `users.email` (for login lookups - may already be unique/indexed)
- `exchange_rates.base_currency, target_currency` (composite index for lookups)

---

### Q37: What is the purpose of `timestamps()` in migrations?

**Answer:**
`$table->timestamps()` creates two columns:
- `created_at`: When record was created
- `updated_at`: When record was last updated

**Benefits**:
- **Auditing**: Track when records were created/modified
- **Sorting**: `->latest()` sorts by `created_at DESC`
- **Filtering**: `->whereDate('created_at', today())` for daily limits
- **Automatic**: Eloquent automatically updates `updated_at` on save

In this project, used for transaction history sorting and fraud detection date filtering.

---

## Code Quality & Best Practices

### Q38: Why use dependency injection in WalletController?

**Answer:**
```php
public function __construct(private readonly FraudDetectionService $fraudService)
{
}
```

**Benefits**:
1. **Testability**: Easy to mock `FraudDetectionService` in unit tests
2. **Loose Coupling**: Controller doesn't know how fraud detection works internally
3. **Single Responsibility**: Controller handles HTTP, service handles business logic
4. **Reusability**: Service can be used by other controllers
5. **Maintainability**: Changes to fraud detection don't affect controller

**Without DI**: Would need `new FraudDetectionService()` inside methods, harder to test and maintain.

---

### Q39: What is the purpose of validation in controllers?

**Answer:**
Request validation ensures:
- **Data Integrity**: Only valid data reaches business logic
- **Security**: Prevents SQL injection, XSS (via type checking)
- **User Experience**: Clear error messages for invalid input
- **Type Safety**: Converts strings to numbers, validates formats

**Example**:
```php
$data = $request->validate([
    'amount' => ['required', 'numeric', 'min:1'],
    'currency' => ['sometimes', 'in:USD,INR,EUR'],
]);
```

If validation fails, Laravel automatically returns 422 with error details. No need for manual checks.

---

### Q40: Why use constants for transaction types?

**Answer:**
```php
public const TYPE_DEBIT = 'debit';
public const TYPE_CREDIT = 'credit';
```

**Benefits**:
- **Type Safety**: IDE autocomplete, prevents typos
- **Refactoring**: Change value in one place
- **Readability**: `Transaction::TYPE_DEBIT` clearer than `'debit'`
- **Consistency**: Same value used everywhere

**Alternative**: Could use Enum (PHP 8.1+), but constants work in all PHP versions.

---

### Q41: What is the purpose of `protected $casts` in models?

**Answer:**
```php
protected $casts = [
    'balance' => 'decimal:2',
    'metadata' => 'array',
    'last_login_at' => 'datetime',
];
```

**Purpose**: Automatically converts database values to PHP types:
- `decimal:2`: String → float with 2 decimal places
- `array`: JSON string → PHP array (and vice versa)
- `datetime`: String → Carbon instance

**Benefits**:
- **Type Safety**: Always get expected type
- **Convenience**: No manual conversion needed
- **Consistency**: Same conversion everywhere

---

### Q42: Why wrap operations in `DB::transaction()`?

**Answer:**
Database transactions ensure ACID properties:

**Example from transfer**:
```php
return DB::transaction(function () use (...) {
    $senderWallet->decrement('balance', $amount);
    $recipientWallet->increment('balance', $amount);
    // Create transaction records...
});
```

**Benefits**:
- **Atomicity**: All succeed or all fail (no partial updates)
- **Consistency**: Database stays in valid state
- **Isolation**: Other queries see either old or new state, not intermediate
- **Durability**: Once committed, changes are permanent

**Without transactions**: If creating transaction record fails, balance would be updated but no record exists (data inconsistency).

---

## Potential Improvements

### Q43: What improvements would you make to this project?

**Answer:**

1. **Testing**:
   - Unit tests for services (FraudDetectionService)
   - Feature tests for API endpoints
   - Integration tests for currency conversion

2. **Error Handling**:
   - Custom exception classes
   - Consistent error response format
   - Better error messages

3. **Logging**:
   - Structured logging (JSON format)
   - Log levels (info, warning, error)
   - Log rotation

4. **Caching**:
   - Cache exchange rates (update hourly)
   - Cache user wallet (invalidate on transaction)
   - Redis for rate limiting

5. **Queue System**:
   - Async transaction processing
   - Email notifications
   - Fraud detection alerts

6. **API Documentation**:
   - Swagger/OpenAPI documentation
   - Postman collection

7. **Monitoring**:
   - Application performance monitoring (APM)
   - Error tracking (Sentry)
   - Metrics dashboard

8. **Security Enhancements**:
   - Two-factor authentication
   - IP whitelisting
   - Request signing
   - HTTPS enforcement

9. **Database Optimizations**:
   - Add missing indexes
   - Partition transaction table by date
   - Archive old transactions

10. **Features**:
    - Transaction search/filtering
    - Export transaction history
    - Recurring transfers
    - Transaction categories/tags

---

### Q44: How would you handle high traffic/scale this application?

**Answer:**

1. **Database**:
   - Read replicas for queries
   - Connection pooling
   - Query optimization and indexing
   - Database sharding by user_id

2. **Caching**:
   - Redis for session storage
   - Cache frequently accessed data (exchange rates, user profiles)
   - Cache query results

3. **Load Balancing**:
   - Multiple application servers behind load balancer
   - Session stickiness or stateless design (JWT helps)

4. **Queue System**:
   - Move heavy operations to background jobs
   - Use message queue (Redis, RabbitMQ, SQS)

5. **CDN**:
   - Serve static assets via CDN
   - API response caching where appropriate

6. **Monitoring**:
   - Real-time performance metrics
   - Auto-scaling based on load
   - Database query monitoring

7. **Code Optimizations**:
   - Eager loading to prevent N+1
   - Database query optimization
   - Response compression

---

### Q45: How would you implement real-time exchange rate updates?

**Answer:**

1. **External API Integration**:
   - Use service like fixer.io, exchangerate-api.com
   - Scheduled job (Laravel Scheduler) to fetch rates hourly/daily

2. **Implementation**:
   ```php
   // In app/Console/Kernel.php
   protected function schedule(Schedule $schedule)
   {
       $schedule->call(function () {
           // Fetch rates from API
           // Update exchange_rates table
       })->hourly();
   }
   ```

3. **Caching**:
   - Cache rates in Redis (update on schedule)
   - Fallback to database if cache miss

4. **Notifications**:
   - Alert admin if API fails
   - Log rate changes for audit

5. **Rate History**:
   - Store historical rates for reporting
   - Track rate changes over time

---

## Additional Laravel Questions

### Q46: What is the difference between `Route::get()` and `Route::post()`?

**Answer:**
- **GET**: Retrieves data (idempotent, no side effects)
  - Example: `GET /api/wallet` - Get wallet balance
  - Should not modify data

- **POST**: Creates new resources or performs actions
  - Example: `POST /api/wallet/deposit` - Create deposit transaction
  - Can modify data

**RESTful Conventions**:
- GET: Read
- POST: Create
- PUT/PATCH: Update
- DELETE: Delete

In this project: GET for reading (wallet, transactions), POST for actions (deposit, withdraw, transfer).

---

### Q47: What is the Artisan CLI and what commands are useful?

**Answer:**
Artisan is Laravel's command-line interface.

**Common Commands**:
- `php artisan migrate` - Run migrations
- `php artisan migrate:rollback` - Rollback last migration
- `php artisan db:seed` - Run seeders
- `php artisan make:controller` - Create controller
- `php artisan make:model` - Create model
- `php artisan make:migration` - Create migration
- `php artisan route:list` - List all routes
- `php artisan tinker` - Interactive PHP shell
- `php artisan test` - Run tests
- `php artisan cache:clear` - Clear cache

---

### Q48: What is the difference between `first()` and `get()` in Eloquent?

**Answer:**
- **`first()`**: Returns single model instance or null
  ```php
  $user = User::where('email', $email)->first();
  // Returns: User model or null
  ```

- **`get()`**: Returns collection of models
  ```php
  $users = User::where('active', true)->get();
  // Returns: Collection of User models
  ```

**Usage**:
- Use `first()` when expecting one result (login, find by ID)
- Use `get()` when expecting multiple results (list all, filtered results)

---

### Q49: What is the purpose of `php artisan tinker`?

**Answer:**
Tinker is an interactive REPL (Read-Eval-Print Loop) for Laravel.

**Use Cases**:
- **Testing**: Quickly test Eloquent queries
- **Debugging**: Inspect models, relationships
- **Data Manipulation**: Create/update records manually
- **Learning**: Experiment with Laravel features

**Example**:
```bash
php artisan tinker
>>> $user = User::first();
>>> $user->wallet->balance;
>>> Transaction::where('type', 'debit')->count();
```

---

### Q50: What is the Service Provider pattern in Laravel?

**Answer:**
Service Providers bootstrap application services (database, cache, queue, etc.).

**Key Providers**:
- `RouteServiceProvider`: Registers routes
- `AuthServiceProvider`: Registers authentication
- `EventServiceProvider`: Registers events/listeners

**In this project**: `RouteServiceProvider` in `app/Providers/` registers API routes and applies rate limiting.

**Custom Providers**: Can create to register services, bind interfaces to implementations, etc.

---

## Final Tips for the Interview

1. **Be Honest**: If you don't know something, admit it and explain how you'd find out
2. **Think Aloud**: Explain your thought process when answering
3. **Ask Questions**: Show interest by asking about their tech stack, team structure
4. **Code Examples**: Reference specific code from the project when possible
5. **Trade-offs**: Discuss pros/cons of design decisions
6. **Show Learning**: Mention what you'd do differently or want to learn

---

**Good luck with your interview! 🚀**

