<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test wallet is automatically created when user is created
     */
    public function test_wallet_is_automatically_created_when_user_is_created(): void
    {
        $user = User::factory()->withCurrency('USD')->create();

        $this->assertNotNull($user->wallet);
        $this->assertInstanceOf(Wallet::class, $user->wallet);
        $this->assertEquals(0, $user->wallet->balance);
        $this->assertEquals('USD', $user->wallet->currency);
    }

    /**
     * Test wallet currency matches user default currency
     */
    public function test_wallet_currency_matches_user_default_currency(): void
    {
        $user = User::factory()->withCurrency('EUR')->create();

        $this->assertEquals('EUR', $user->wallet->currency);
        $this->assertEquals($user->default_currency, $user->wallet->currency);
    }

    /**
     * Test user has one wallet relationship
     */
    public function test_user_has_one_wallet_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Wallet::class, $user->wallet);
        $this->assertEquals($user->id, $user->wallet->user_id);
    }

    /**
     * Test user can access transactions through wallet
     */
    public function test_user_can_access_transactions_through_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        $this->assertNotNull($user->wallet->transactions);
        $this->assertCount(0, $user->wallet->transactions);
    }

    /**
     * Test JWT identifier returns user ID
     */
    public function test_jwt_identifier_returns_user_id(): void
    {
        $user = User::factory()->create();

        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    /**
     * Test JWT custom claims include default currency
     */
    public function test_jwt_custom_claims_include_default_currency(): void
    {
        $user = User::factory()->withCurrency('INR')->create();

        $claims = $user->getJWTCustomClaims();

        $this->assertArrayHasKey('default_currency', $claims);
        $this->assertEquals('INR', $claims['default_currency']);
    }
}

