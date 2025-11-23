<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FraudDetectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FraudDetectionService $fraudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudService = new FraudDetectionService();

        // Set default config values for testing
        config([
            'services.fraud.high_value_threshold' => 5000,
            'services.fraud.time_window_minutes' => 10,
            'services.fraud.max_transactions' => 3,
        ]);
    }

    /**
     * Test daily limit not exceeded when under limit
     */
    public function test_daily_limit_not_exceeded_when_under_limit(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();
        $wallet = $user->wallet;

        // Create transactions totaling 5000 today (e.g., 2 transactions of 2500 each)
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(2500)
            ->today()
            ->create();

        $result = $this->fraudService->exceedsDailyLimit($user, 4000);

        $this->assertFalse($result);
    }

    /**
     * Test daily limit exceeded when over limit
     */
    public function test_daily_limit_exceeded_when_over_limit(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();
        $wallet = $user->wallet;

        // Create transactions totaling 8000 today
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(8000)
            ->today()
            ->create();

        // Try to add 2500 more (8000 + 2500 = 10500 > 10000)
        $result = $this->fraudService->exceedsDailyLimit($user, 2500);

        $this->assertTrue($result);
    }

    /**
     * Test daily limit exactly at limit
     */
    public function test_daily_limit_exactly_at_limit(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();
        $wallet = $user->wallet;

        // Create transactions totaling 5000 today
        Transaction::factory()
            ->count(1)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(5000)
            ->today()
            ->create();

        // Try to add exactly 5000 more (5000 + 5000 = 10000, should not exceed)
        $result = $this->fraudService->exceedsDailyLimit($user, 5000);

        $this->assertFalse($result);
    }

    /**
     * Test daily limit only counts today's transactions
     */
    public function test_daily_limit_only_counts_todays_transactions(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();
        $wallet = $user->wallet;

        // Create transactions yesterday (should not count)
        Transaction::factory()
            ->count(5)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(9000)
            ->yesterday()
            ->create();

        // Create small transaction today
        Transaction::factory()
            ->count(1)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(1000)
            ->today()
            ->create();

        // Should be able to add 9000 more (1000 + 9000 = 10000, at limit)
        $result = $this->fraudService->exceedsDailyLimit($user, 9000);

        $this->assertFalse($result);
    }

    /**
     * Test daily limit only counts debit transactions
     */
    public function test_daily_limit_only_counts_debit_transactions(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();
        $wallet = $user->wallet;

        // Create large credit transactions (should not count)
        Transaction::factory()
            ->count(3)
            ->credit()
            ->withWallet($wallet)
            ->withAmount(50000)
            ->today()
            ->create();

        // Create small debit transaction
        Transaction::factory()
            ->count(1)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(1000)
            ->today()
            ->create();

        // Should be able to add 9000 more (only 1000 counted)
        $result = $this->fraudService->exceedsDailyLimit($user, 9000);

        $this->assertFalse($result);
    }

    /**
     * Test daily limit with zero existing transactions
     */
    public function test_daily_limit_with_zero_existing_transactions(): void
    {
        $user = User::factory()->withDailyLimit(10000)->create();

        $result = $this->fraudService->exceedsDailyLimit($user, 5000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection returns false for low amount
     */
    public function test_high_frequency_detection_returns_false_for_low_amount(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create many transactions below threshold
        Transaction::factory()
            ->count(10)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(1000) // Below 5000 threshold
            ->today()
            ->create();

        $result = $this->fraudService->detectHighFrequency($user, 1000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection returns true when threshold exceeded
     */
    public function test_high_frequency_detection_returns_true_when_threshold_exceeded(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 3 high-value transactions in the time window
        Transaction::factory()
            ->count(3)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000) // Above 5000 threshold
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        // Try to make another high-value transaction
        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertTrue($result);
    }

    /**
     * Test high frequency detection returns false when under threshold
     */
    public function test_high_frequency_detection_returns_false_when_under_threshold(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 2 high-value transactions (below max of 3)
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection only counts transactions in time window
     */
    public function test_high_frequency_detection_only_counts_transactions_in_time_window(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 3 high-value transactions outside time window (11 minutes ago)
        Transaction::factory()
            ->count(3)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(11)]);

        // Should not be flagged (transactions are outside 10-minute window)
        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection only counts high-value transactions
     */
    public function test_high_frequency_detection_only_counts_high_value_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create many low-value transactions
        Transaction::factory()
            ->count(10)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(1000) // Below threshold
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        // Create 2 high-value transactions
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000) // Above threshold
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        // Should not be flagged (only 2 high-value, need 3)
        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection with custom threshold
     */
    public function test_high_frequency_detection_with_custom_threshold(): void
    {
        config(['services.fraud.high_value_threshold' => 10000]);

        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 3 transactions at 10000 (at threshold)
        Transaction::factory()
            ->count(3)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(10000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 10000);

        $this->assertTrue($result);
    }

    /**
     * Test high frequency detection with custom time window
     */
    public function test_high_frequency_detection_with_custom_time_window(): void
    {
        config([
            'services.fraud.time_window_minutes' => 5,
            'services.fraud.max_transactions' => 2,
        ]);

        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 2 high-value transactions within 5-minute window
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(3)]);

        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertTrue($result);
    }

    /**
     * Test high frequency detection with custom max transactions
     */
    public function test_high_frequency_detection_with_custom_max_transactions(): void
    {
        config(['services.fraud.max_transactions' => 5]);

        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 4 high-value transactions (below max of 5)
        Transaction::factory()
            ->count(4)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertFalse($result);

        // Add one more to exceed
        Transaction::factory()
            ->count(1)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertTrue($result);
    }

    /**
     * Test high frequency detection only counts debit transactions
     */
    public function test_high_frequency_detection_only_counts_debit_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create many high-value credit transactions (should not count)
        Transaction::factory()
            ->count(10)
            ->credit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        // Create 2 high-value debit transactions
        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000)
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertFalse($result);
    }

    /**
     * Test high frequency detection with edge case at exact threshold
     */
    public function test_high_frequency_detection_at_exact_threshold(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create 3 transactions exactly at threshold
        Transaction::factory()
            ->count(3)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(5000) // Exactly at threshold
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        $result = $this->fraudService->detectHighFrequency($user, 5000);

        $this->assertTrue($result);
    }

    /**
     * Test high frequency detection with mixed transaction amounts
     */
    public function test_high_frequency_detection_with_mixed_transaction_amounts(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        // Create mix of high and low value transactions
        Transaction::factory()
            ->count(1)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000) // High value
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(1000) // Low value
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        Transaction::factory()
            ->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withAmount(6000) // High value
            ->create(['created_at' => Carbon::now()->subMinutes(5)]);

        // Total: 3 high-value transactions, should be flagged
        $result = $this->fraudService->detectHighFrequency($user, 6000);

        $this->assertTrue($result);
    }
}

