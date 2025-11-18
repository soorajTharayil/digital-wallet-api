# Manual Test Cases

## 1. User Registration
- **Endpoint**: `POST /api/register`
- **Body**:
  ```json
  {
    "name": "Alice",
    "email": "alice@example.com",
    "password": "secret123",
    "password_confirmation": "secret123",
    "default_currency": "USD"
  }
  ```
- **Expected**: `201 Created`, JWT token returned, wallet auto-created.
- **Negative**: Missing `password_confirmation` → `422 Unprocessable Entity`.

## 2. Login
- **Endpoint**: `POST /api/login`
- **Body**:
  ```json
  {
    "email": "alice@example.com",
    "password": "secret123"
  }
  ```
- **Expected**: `200 OK` with token payload.
- **Negative**: Wrong password → `422 Invalid credentials`.

## 3. Fetch Profile
- **Endpoint**: `GET /api/me`
- **Auth**: Bearer token.
- **Expected**: `200 OK` with user details.
- **Negative**: Missing token → `401 Unauthorized`.

## 4. Wallet Balance
- **Endpoint**: `GET /api/wallet`
- **Expected**: `200 OK` with balance, currency, recent transactions.

## 5. Deposit Funds
- **Endpoint**: `POST /api/wallet/deposit`
- **Body**:
  ```json
  {
    "amount": 300,
    "currency": "USD",
    "description": "Initial deposit"
  }
  ```
- **Expected**: `200 OK`, balance increased.
- **Negative**: `amount` < 1 → `422` validation error.

## 6. Withdraw Funds
- **Endpoint**: `POST /api/wallet/withdraw`
- **Body**:
  ```json
  {
    "amount": 100,
    "currency": "USD",
    "description": "ATM withdrawal"
  }
  ```
- **Expected**: `200 OK`, balance decreased.
- **Negative**: Amount exceeds balance → `422 Insufficient balance`.

## 7. Transfer Funds
- **Endpoint**: `POST /api/wallet/transfer`
- **Body**:
  ```json
  {
    "recipient_email": "bob@example.com",
    "amount": 50,
    "currency": "USD",
    "description": "Rent split"
  }
  ```
- **Expected**: `200 OK`, sender debited, recipient credited.
- **Edge**: Self-transfer → `422 Cannot transfer to self`.


## 8. Transaction History Retrieval
- **Endpoint**: `GET /api/transactions`
- **Auth**: Bearer token.
- **Expected**: `200 OK` with paginated list showing `type`, `amount`, `currency`, `description`, and timestamps in descending order.
- **Negative**: Missing token → `401 Unauthorized`; invalid `type` query param (e.g., `bonus`) → `422` validation error.

## 9. Transaction Filtering
- **Endpoint**: `GET /api/transactions?type=credit&per_page=5`
- **Expected**: `200 OK` with filtered paginated response.


---

## Advanced Feature Test Cases

### A. Multi-Currency Support

#### TC-A1: Deposit With Currency Conversion (Positive)
- **Description**: Validate that deposits in a non-default currency are converted using configured exchange rates.
- **Endpoint**: `POST /api/wallet/deposit`
- **Method**: `POST`
- **Headers**:
  - `Authorization: Bearer {{jwt_token}}`
  - `Content-Type: application/json`
- **Sample Request Body**:
  ```json
  {
    "amount": 100,
    "currency": "EUR",
    "description": "Salary payout in EUR"
  }
  ```
- **Expected Response**:
  - **Status**: `200 OK`
  - **Body** (example assuming EUR→USD rate 1.08):
    ```json
    {
      "message": "Deposit successful.",
      "balance": 1080.00
    }
    ```
- **Postman Notes**:
  - Pre-request script can ensure `{{jwt_token}}` is set from login request.
- **Expected Result**:
  - Wallet balance increases by converted amount in default currency (e.g., USD).
  - Latest transaction metadata shows `"source_currency": "EUR"` and `"original_amount": 100`.

#### TC-A2: Transfer Between Different Currency Wallets (Positive)
- **Description**: Ensure cross-currency transfer converts correctly for sender and recipient.
- **Endpoint**: `POST /api/wallet/transfer`
- **Headers**:
  - `Authorization: Bearer {{sender_token}}`
- **Sample Request Body**:
  ```json
  {
    "recipient_email": "inr.user@example.com",
    "amount": 50,
    "currency": "USD",
    "description": "Gift to INR wallet user"
  }
  ```
- **Expected Response**:
  - **Status**: `200 OK`
  - **Body** (sender view):
    ```json
    {
      "message": "Transfer successful.",
      "balance": 450.00
    }
    ```
- **Postman Notes**:
  - Use separate environment variables `{{sender_token}}`, `{{recipient_token}}`.
- **Expected Result**:
  - Sender wallet debited in USD by converted amount.
  - Recipient wallet credited in INR using USD→INR rate.
  - Transaction logs reflect respective currencies and metadata.

#### TC-A3: Invalid Currency (Negative)
- **Description**: Attempt transfer with unsupported currency (AUD not in allowed list: USD, INR, EUR).
- **Endpoint**: `POST /api/wallet/transfer`
- **Headers**: 
  - `Authorization: Bearer {token}`
  - `Accept: application/json`
  - `Content-Type: application/json`
- **Sample Request Body**:
  ```json
  {
    "recipient_email": "eur.user@example.com",
    "amount": 75,
    "currency": "AUD",
    "description": "Unsupported currency transfer"
  }
  ```
- **Expected Response**:
  - **Status**: `422 Unprocessable Entity`
  - **Body**:
    ```json
    {
      "status": "error",
      "message": "The given data was invalid.",
      "errors": {
        "currency": [
          "The selected currency is invalid."
        ]
      },
      "code": 422
    }
    ```
- **Expected Result**:
  - Request rejected with JSON error response (not HTML).
  - Validation error indicates currency must be USD, INR, or EUR.
  - No wallet balances change.

### B. Fraud Detection & Transaction Limits

#### TC-B1: Withdraw Within Daily Limit (Positive)
- **Description**: Perform withdrawals totaling below the user’s daily debit limit.
- **Endpoint**: `POST /api/wallet/withdraw`
- **Headers**: `Authorization: Bearer {{jwt_token}}`
- **Sample Request Body**:
  ```json
  {
    "amount": 150,
    "currency": "USD",
    "description": "Utility payment"
  }
  ```
- **Expected Response**:
  - **Status**: `200 OK`
  - **Body**:
    ```json
    {
      "message": "Withdrawal successful.",
      "balance": 850.00
    }
    ```
- **Expected Result**:
  - Withdrawal succeeds, daily debit tracker increments.
  - No fraud warnings logged.

#### TC-B2: Exceed Daily Debit Limit (Negative)
- **Description**: Attempt to exceed the configured daily limit (e.g., $10,000).
- **Endpoint**: `POST /api/wallet/withdraw`
- **Sample Request Body**:
  ```json
  {
    "amount": 5000,
    "currency": "USD",
    "description": "Large withdrawal attempt"
  }
  ```
- **Steps**:
  1. Run TC-B1 twice to accumulate $10,000 debits.
  2. Run this request to exceed the limit.
- **Expected Response**:
  - **Status**: `422 Unprocessable Entity`
  - **Body**:
    ```json
    {
      "message": "The given data was invalid.",
      "errors": {
        "amount": [
          "Daily debit limit exceeded."
        ]
      }
    }
    ```
- **Expected Result**:
  - Transaction blocked; wallet balance unchanged.
  - FraudDetectionService logs warning with user ID and amount.

#### TC-B3: High-Frequency High-Value Transactions (Negative)
- **Description**: Trigger fraud detection for rapid, high-value debits within configured window.
- **Preconditions**:
  - `.env` values: `FRAUD_HIGH_VALUE_THRESHOLD=5000`, `FRAUD_TIME_WINDOW_MINUTES=10`, `FRAUD_MAX_TRANSACTIONS=3`.
- **Endpoint**: `POST /api/wallet/withdraw`
- **Procedure**:
  1. Send three withdrawals of `$5000` within 10 minutes.
  2. Attempt a fourth withdrawal of `$5000`.
- **Sample Request Body** (for fourth attempt):
  ```json
  {
    "amount": 5000,
    "currency": "USD",
    "description": "Fourth high-value withdrawal"
  }
  ```
- **Expected Response**:
  - **Status**: `422 Unprocessable Entity`
  - **Body**:
    ```json
    {
      "message": "The given data was invalid.",
      "errors": {
        "amount": [
          "Transaction flagged as suspicious. Please try again later."
        ]
      }
    }
    ```
- **Expected Result**:
  - Request rejected; Laravel `storage/logs/laravel.log` contains `Suspicious activity detected.` entry.

### C. API Rate Limiting & Throttling

#### TC-C1: Requests Within Limit (Positive)
- **Description**: Confirm normal usage under throttle threshold succeeds.
- **Endpoint**: `GET /api/wallet`
- **Headers**: `Authorization: Bearer {{jwt_token}}`
- **Procedure**:
  - Send 5 requests spaced a few seconds apart (below configured limit of 60/min).
- **Expected Response**:
  - **Status**: `200 OK`
  - **Body**:
    ```json
    {
      "balance": 930.00,
      "currency": "USD",
      "transactions": [
        // latest 10 transactions
      ]
    }
    ```
- **Expected Result**:
  - All requests succeed.
  - No `Retry-After` headers present.

#### TC-C2: Exceed Per-Minute Limit (Negative)
- **Description**: Trigger rate limiter by sending >60 requests within one minute.
- **Endpoint**: `GET /api/wallet`
- **Procedure**:
  - In Postman, use Collection Runner or Pre-request script to execute 65 rapid requests.
- **Expected Response** (for request #61+):
  - **Status**: `429 Too Many Requests`
  - **Body**:
    ```json
    {
      "message": "Too many requests.",
      "retry_after_seconds": 25
    }
    ```
- **Expected Result**:
  - Laravel `RateLimiter::tooManyAttempts` blocks excess calls.
  - Response includes `retry_after_seconds` indicating wait time.

#### TC-C3: Custom Middleware Throttle with Auth Fallback
- **Description**: Validate custom `RequestThrottleMiddleware` behavior when user-scope is unavailable (unauthenticated request).
- **Endpoint**: `POST /api/login`
- **Procedure**:
  - Send repeated login attempts from same IP beyond limit defined in `services.rate_limits`.
- **Expected Response**:
  - **Status**: `429 Too Many Requests`
  - **Body**:
    ```json
    {
      "message": "Too many requests.",
      "retry_after_seconds": 60
    }
    ```
- **Expected Result**:
  - Middleware constructs key using caller IP.
  - Subsequent attempts remain blocked until cooldown expires.

---

> **Tip for Postman:** Save environment variables for `{{base_url}}`, `{{jwt_token}}`, and different user tokens. Sequence the requests in a collection to reproduce fraud and rate-limit scenarios reliably.