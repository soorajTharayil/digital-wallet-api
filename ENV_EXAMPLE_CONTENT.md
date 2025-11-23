# .env.example Content

Since `.env.example` is protected, copy this content to create your `.env.example` file:

```env
APP_NAME="Digital Wallet API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ============================================
# DATABASE CONFIGURATION
# ============================================
# Option A: SQLite (Portable/Default - No MySQL required)
# Perfect for GitHub, portable usage, and quick setup
DB_CONNECTION=sqlite
DB_DATABASE="${DB_DATABASE:-database/database.sqlite}"

# Option B: MySQL (Production/Advanced usage)
# Uncomment and configure if you prefer MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=wallet_api
# DB_USERNAME=root
# DB_PASSWORD=
# DB_SOCKET=

# ============================================
# JWT AUTHENTICATION
# ============================================
JWT_SECRET=
JWT_PUBLIC_KEY=
JWT_PRIVATE_KEY=
JWT_PASSPHRASE=
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=0

# ============================================
# CACHE & SESSION
# ============================================
CACHE_STORE=database
CACHE_PREFIX=
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# ============================================
# QUEUE
# ============================================
QUEUE_CONNECTION=database

# ============================================
# MAIL
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# APPLICATION SETTINGS
# ============================================
DEFAULT_CURRENCY=USD
DEFAULT_DAILY_DEBIT_LIMIT=10000

# ============================================
# RATE LIMITING
# ============================================
RATE_LIMIT_PER_MINUTE=60

# ============================================
# REDIS (Optional - for advanced caching)
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

## Instructions

1. Create `.env.example` file in the project root
2. Copy the content above into it
3. The file will be used as a template for new installations

## Key Points

- **SQLite is default**: `DB_CONNECTION=sqlite` is set by default
- **Auto-detection**: Path automatically resolves to `database/database.sqlite`
- **MySQL option**: Commented out but ready to use
- **All settings included**: JWT, cache, session, mail, etc.

