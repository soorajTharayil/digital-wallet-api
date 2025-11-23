<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedExchangeRates();
    }

    protected function seedExchangeRates(): void
    {
        $rates = [
            ['base_currency' => 'USD', 'target_currency' => 'USD', 'rate' => 1],
            ['base_currency' => 'USD', 'target_currency' => 'INR', 'rate' => 83.10],
            ['base_currency' => 'USD', 'target_currency' => 'EUR', 'rate' => 0.92],
            ['base_currency' => 'INR', 'target_currency' => 'USD', 'rate' => 0.012],
            ['base_currency' => 'INR', 'target_currency' => 'INR', 'rate' => 1],
            ['base_currency' => 'INR', 'target_currency' => 'EUR', 'rate' => 0.011],
            ['base_currency' => 'EUR', 'target_currency' => 'USD', 'rate' => 1.09],
            ['base_currency' => 'EUR', 'target_currency' => 'INR', 'rate' => 89.50],
            ['base_currency' => 'EUR', 'target_currency' => 'EUR', 'rate' => 1],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::create($rate);
        }
    }

    /**
     * Test authenticated user can view their wallet
     */
    public function test_authenticated_user_can_view_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create some transactions
        Transaction::factory()->count(5)->withWallet($wallet)->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'balance',
                'currency',
                'transactions' => [
                    '*' => [
                        'id',
                        'type',
                        'amount',
                        'currency',
                        'description',
                    ],
                ],
            ])
            ->assertJson([
                'balance' => (string) $wallet->balance,
                'currency' => $wallet->currency,
            ]);

        $this->assertCount(5, $response->json('transactions'));
    }

    /**
     * Test unauthenticated user cannot view wallet
     */
    public function test_unauthenticated_user_cannot_view_wallet(): void
    {
        $response = $this->getJson('/api/wallet');

        $response->assertStatus(401);
    }

    /**
     * Test user can deposit funds in same currency
     */
    public function test_user_can_deposit_funds_same_currency(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;
        $initialBalance = $wallet->balance;

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100.50,
                'currency' => 'USD',
                'description' => 'Test deposit',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'balance',
            ])
            ->assertJson([
                'message' => 'Deposit successful.',
            ]);

        $wallet->refresh();
        $this->assertEquals($initialBalance + 100.50, (float) $wallet->balance);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'type' => Transaction::TYPE_CREDIT,
            'amount' => 100.50,
            'currency' => 'USD',
            'description' => 'Test deposit',
        ]);
    }

    /**
     * Test user can deposit funds with currency conversion
     */
    public function test_user_can_deposit_funds_with_currency_conversion(): void
    {
        $user = User::factory()->withCurrency('INR')->create();
        $wallet = $user->wallet;
        $initialBalance = $wallet->balance;

        // Deposit 100 USD, should convert to INR (100 * 83.10 = 8310)
        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100,
                'currency' => 'USD',
            ]);

        $response->assertStatus(200);

        $wallet->refresh();
        $expectedBalance = $initialBalance + 8310.00;
        $this->assertEqualsWithDelta($expectedBalance, (float) $wallet->balance, 0.01);

        // Verify transaction metadata
        $transaction = Transaction::where('wallet_id', $wallet->id)
            ->where('type', Transaction::TYPE_CREDIT)
            ->latest()
            ->first();

        $this->assertEquals('INR', $transaction->currency);
        $this->assertEquals(8310.00, (float) $transaction->amount);
        $this->assertEquals('USD', $transaction->metadata['source_currency']);
        $this->assertEquals(100, $transaction->metadata['original_amount']);
    }

    /**
     * Test deposit fails with invalid amount
     */
    public function test_deposit_fails_with_invalid_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => -10,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test deposit fails with zero amount
     */
    public function test_deposit_fails_with_zero_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 0,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test deposit fails with invalid currency
     */
    public function test_deposit_fails_with_invalid_currency(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100,
                'currency' => 'GBP',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    /**
     * Test deposit fails with missing exchange rate
     */
    public function test_deposit_fails_with_missing_exchange_rate(): void
    {
        $user = User::factory()->withCurrency('USD')->create();

        // Remove exchange rate
        ExchangeRate::where('base_currency', 'EUR')
            ->where('target_currency', 'USD')
            ->delete();

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100,
                'currency' => 'EUR',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    /**
     * Test user can withdraw funds
     */
    public function test_user_can_withdraw_funds(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 1000.00]);

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/withdraw', [
                'amount' => 250.50,
                'currency' => 'USD',
                'description' => 'Test withdrawal',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Withdrawal successful.',
            ]);

        $wallet->refresh();
        $this->assertEquals(749.50, (float) $wallet->balance);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'type' => Transaction::TYPE_DEBIT,
            'amount' => 250.50,
            'currency' => 'USD',
        ]);
    }

    /**
     * Test withdrawal fails with insufficient balance
     */
    public function test_withdrawal_fails_with_insufficient_balance(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 100.00]);

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/withdraw', [
                'amount' => 500.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount'])
            ->assertJson([
                'errors' => [
                    'amount' => ['Insufficient balance.'],
                ],
            ]);
    }

    /**
     * Test withdrawal fails when exceeding daily limit
     */
    public function test_withdrawal_fails_when_exceeding_daily_limit(): void
    {
        $user = User::factory()->withCurrency('USD')->withDailyLimit(1000)->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 5000.00]);

        // Create transactions that already used 800 today
        Transaction::factory()
            ->count(3)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(800)
            ->today()
            ->create();

        // Try to withdraw 300 more (800 + 300 = 1100 > 1000 limit)
        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/withdraw', [
                'amount' => 300.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount'])
            ->assertJson([
                'errors' => [
                    'amount' => ['Daily debit limit exceeded.'],
                ],
            ]);
    }

    /**
     * Test withdrawal with currency conversion
     */
    public function test_user_can_withdraw_with_currency_conversion(): void
    {
        $user = User::factory()->withCurrency('INR')->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 10000.00]);

        // Withdraw 100 USD, should convert to INR (100 * 83.10 = 8310)
        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/withdraw', [
                'amount' => 100,
                'currency' => 'USD',
            ]);

        $response->assertStatus(200);

        $wallet->refresh();
        $expectedBalance = 10000.00 - 8310.00;
        $this->assertEqualsWithDelta($expectedBalance, (float) $wallet->balance, 0.01);
    }

    /**
     * Test user can transfer funds to another user
     */
    public function test_user_can_transfer_funds_to_another_user(): void
    {
        $sender = User::factory()->withCurrency('USD')->create();
        $senderWallet = $sender->wallet;
        $senderWallet->update(['balance' => 1000.00]);

        $recipient = User::factory()->withCurrency('USD')->create();
        $recipientWallet = $recipient->wallet;
        $recipientInitialBalance = $recipientWallet->balance;

        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => $recipient->email,
                'amount' => 250.00,
                'currency' => 'USD',
                'description' => 'Test transfer',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transfer successful.',
            ]);

        $senderWallet->refresh();
        $recipientWallet->refresh();

        $this->assertEquals(750.00, (float) $senderWallet->balance);
        $this->assertEquals($recipientInitialBalance + 250.00, (float) $recipientWallet->balance);

        // Verify both transactions were created
        $senderTransaction = Transaction::where('wallet_id', $senderWallet->id)
            ->where('type', Transaction::TYPE_DEBIT)
            ->latest()
            ->first();

        $recipientTransaction = Transaction::where('wallet_id', $recipientWallet->id)
            ->where('type', Transaction::TYPE_CREDIT)
            ->latest()
            ->first();

        $this->assertNotNull($senderTransaction);
        $this->assertNotNull($recipientTransaction);
        $this->assertEquals($recipientWallet->id, $senderTransaction->metadata['recipient_wallet_id']);
        $this->assertEquals($senderWallet->id, $recipientTransaction->metadata['sender_wallet_id']);
    }

    /**
     * Test transfer fails when sender has insufficient balance
     */
    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = User::factory()->withCurrency('USD')->create();
        $senderWallet = $sender->wallet;
        $senderWallet->update(['balance' => 100.00]);

        $recipient = User::factory()->create();

        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => $recipient->email,
                'amount' => 500.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount'])
            ->assertJson([
                'errors' => [
                    'amount' => ['Insufficient balance.'],
                ],
            ]);
    }

    /**
     * Test transfer fails when trying to transfer to self
     */
    public function test_transfer_fails_when_transferring_to_self(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 1000.00]);

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => $user->email,
                'amount' => 100.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_email'])
            ->assertJson([
                'errors' => [
                    'recipient_email' => ['Cannot transfer to self.'],
                ],
            ]);
    }

    /**
     * Test transfer fails with non-existent recipient
     */
    public function test_transfer_fails_with_nonexistent_recipient(): void
    {
        $sender = User::factory()->withCurrency('USD')->create();
        $senderWallet = $sender->wallet;
        $senderWallet->update(['balance' => 1000.00]);

        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => 'nonexistent@example.com',
                'amount' => 100.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_email']);
    }

    /**
     * Test transfer with currency conversion between different currencies
     */
    public function test_transfer_with_currency_conversion(): void
    {
        $sender = User::factory()->withCurrency('USD')->create();
        $senderWallet = $sender->wallet;
        $senderWallet->update(['balance' => 1000.00]);

        $recipient = User::factory()->withCurrency('INR')->create();
        $recipientWallet = $recipient->wallet;
        $recipientInitialBalance = $recipientWallet->balance;

        // Transfer 100 USD, should convert to INR for recipient
        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => $recipient->email,
                'amount' => 100,
                'currency' => 'USD',
            ]);

        $response->assertStatus(200);

        $senderWallet->refresh();
        $recipientWallet->refresh();

        $this->assertEquals(900.00, (float) $senderWallet->balance);
        $this->assertEqualsWithDelta($recipientInitialBalance + 8310.00, (float) $recipientWallet->balance, 0.01);

        // Verify recipient transaction has correct currency
        $recipientTransaction = Transaction::where('wallet_id', $recipientWallet->id)
            ->where('type', Transaction::TYPE_CREDIT)
            ->latest()
            ->first();

        $this->assertEquals('INR', $recipientTransaction->currency);
        $this->assertEqualsWithDelta(8310.00, (float) $recipientTransaction->amount, 0.01);
    }

    /**
     * Test transfer fails when exceeding daily limit
     */
    public function test_transfer_fails_when_exceeding_daily_limit(): void
    {
        $sender = User::factory()->withCurrency('USD')->withDailyLimit(1000)->create();
        $senderWallet = $sender->wallet;
        $senderWallet->update(['balance' => 5000.00]);

        $recipient = User::factory()->create();

        // Create transactions that already used 800 today
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($senderWallet)
            ->withAmount(800)
            ->today()
            ->create();

        // Try to transfer 300 more (800 + 300 = 1100 > 1000 limit)
        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => $recipient->email,
                'amount' => 300.00,
                'currency' => 'USD',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount'])
            ->assertJson([
                'errors' => [
                    'amount' => ['Daily debit limit exceeded.'],
                ],
            ]);
    }

    /**
     * Test transfer validation errors
     */
    public function test_transfer_fails_with_invalid_data(): void
    {
        $sender = User::factory()->create();

        $response = $this->actingAsJWT($sender)
            ->postJson('/api/wallet/transfer', [
                'recipient_email' => 'invalid-email',
                'amount' => -10,
                'currency' => 'GBP',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_email', 'amount', 'currency']);
    }

    /**
     * Test deposit uses default currency when not specified
     */
    public function test_deposit_uses_wallet_currency_when_not_specified(): void
    {
        $user = User::factory()->withCurrency('EUR')->create();
        $wallet = $user->wallet;
        $initialBalance = $wallet->balance;

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100.00,
            ]);

        $response->assertStatus(200);

        $wallet->refresh();
        $this->assertEquals($initialBalance + 100.00, (float) $wallet->balance);

        $transaction = Transaction::where('wallet_id', $wallet->id)->latest()->first();
        $this->assertEquals('EUR', $transaction->currency);
    }

    /**
     * Test withdrawal uses default currency when not specified
     */
    public function test_withdrawal_uses_wallet_currency_when_not_specified(): void
    {
        $user = User::factory()->withCurrency('EUR')->create();
        $wallet = $user->wallet;
        $wallet->update(['balance' => 1000.00]);

        $response = $this->actingAsJWT($user)
            ->postJson('/api/wallet/withdraw', [
                'amount' => 100.00,
            ]);

        $response->assertStatus(200);

        $wallet->refresh();
        $this->assertEquals(900.00, (float) $wallet->balance);
    }
}

