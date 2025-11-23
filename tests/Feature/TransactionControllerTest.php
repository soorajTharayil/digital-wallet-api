<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can view their transaction history
     */
    public function test_authenticated_user_can_view_transaction_history(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(15)->withWallet($wallet)->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'amount',
                        'currency',
                        'description',
                        'created_at',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        $this->assertCount(15, $response->json('data'));
    }

    /**
     * Test transaction history is paginated
     */
    public function test_transaction_history_is_paginated(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(25)->withWallet($wallet)->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'per_page' => 10,
                'total' => 25,
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test transaction history can be filtered by type
     */
    public function test_transaction_history_can_be_filtered_by_type(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(5)->credit()->withWallet($wallet)->create();
        Transaction::factory()->count(3)->debit()->withWallet($wallet)->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?type=credit');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));

        foreach ($response->json('data') as $transaction) {
            $this->assertEquals('credit', $transaction['type']);
        }

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?type=debit');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));

        foreach ($response->json('data') as $transaction) {
            $this->assertEquals('debit', $transaction['type']);
        }
    }

    /**
     * Test transaction history can be filtered by currency
     */
    public function test_transaction_history_can_be_filtered_by_currency(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(5)->withWallet($wallet)->withCurrency('USD')->create();
        Transaction::factory()->count(3)->withWallet($wallet)->withCurrency('EUR')->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?currency=USD');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));

        foreach ($response->json('data') as $transaction) {
            $this->assertEquals('USD', $transaction['currency']);
        }
    }

    /**
     * Test transaction history can be filtered by multiple criteria
     */
    public function test_transaction_history_can_be_filtered_by_multiple_criteria(): void
    {
        $user = User::factory()->withCurrency('USD')->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(3)
            ->credit()
            ->withWallet($wallet)
            ->withCurrency('USD')
            ->create();

        Transaction::factory()->count(2)
            ->debit()
            ->withWallet($wallet)
            ->withCurrency('USD')
            ->create();

        Transaction::factory()->count(4)
            ->credit()
            ->withWallet($wallet)
            ->withCurrency('EUR')
            ->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?type=credit&currency=USD');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));

        foreach ($response->json('data') as $transaction) {
            $this->assertEquals('credit', $transaction['type']);
            $this->assertEquals('USD', $transaction['currency']);
        }
    }

    /**
     * Test transaction history validation fails with invalid type
     */
    public function test_transaction_history_fails_with_invalid_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?type=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test transaction history validation fails with invalid currency
     */
    public function test_transaction_history_fails_with_invalid_currency(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?currency=GBP');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    /**
     * Test transaction history validation fails with invalid per_page
     */
    public function test_transaction_history_fails_with_invalid_per_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?per_page=200');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test transaction history validation fails with negative per_page
     */
    public function test_transaction_history_fails_with_negative_per_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?per_page=-1');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test unauthenticated user cannot view transaction history
     */
    public function test_unauthenticated_user_cannot_view_transaction_history(): void
    {
        $response = $this->getJson('/api/transactions');

        $response->assertStatus(401);
    }

    /**
     * Test transaction history returns empty when user has no transactions
     */
    public function test_transaction_history_returns_empty_when_no_transactions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'total' => 0,
            ]);
    }

    /**
     * Test transaction history is ordered by latest first
     */
    public function test_transaction_history_is_ordered_by_latest_first(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        $oldTransaction = Transaction::factory()
            ->withWallet($wallet)
            ->yesterday()
            ->create();

        $newTransaction = Transaction::factory()
            ->withWallet($wallet)
            ->today()
            ->create();

        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions');

        $response->assertStatus(200);
        $transactions = $response->json('data');

        $this->assertEquals($newTransaction->id, $transactions[0]['id']);
        $this->assertEquals($oldTransaction->id, $transactions[1]['id']);
    }

    /**
     * Test user can only see their own transactions
     */
    public function test_user_can_only_see_own_transactions(): void
    {
        $user1 = User::factory()->create();
        $wallet1 = $user1->wallet;

        $user2 = User::factory()->create();
        $wallet2 = $user2->wallet;

        Transaction::factory()->count(5)->withWallet($wallet1)->create();
        Transaction::factory()->count(3)->withWallet($wallet2)->create();

        $response = $this->actingAsJWT($user1)
            ->getJson('/api/transactions');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));

        // Verify all transactions belong to user1's wallet
        foreach ($response->json('data') as $transaction) {
            $this->assertEquals($wallet1->id, $transaction['wallet_id']);
        }
    }

    /**
     * Test transaction history pagination works correctly
     */
    public function test_transaction_history_pagination_works_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;

        Transaction::factory()->count(25)->withWallet($wallet)->create();

        // First page
        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'current_page' => 1,
                'per_page' => 10,
                'total' => 25,
                'last_page' => 3,
            ]);

        // Second page
        $response = $this->actingAsJWT($user)
            ->getJson('/api/transactions?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'current_page' => 2,
            ]);
    }
}

