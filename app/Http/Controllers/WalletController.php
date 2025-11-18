<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\FraudDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function __construct(private readonly FraudDetectionService $fraudService)
    {
    }

    public function show(): JsonResponse
    {
        $wallet = auth()->user()->wallet()->with('transactions')->first();

        return response()->json([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'transactions' => $wallet->transactions()->latest()->limit(10)->get(),
        ]);
    }

    public function deposit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = auth()->user()->wallet;

        return DB::transaction(function () use ($wallet, $data) {
            $amount = $this->convertAmount($data['amount'], $data['currency'] ?? $wallet->currency, $wallet->currency);

            $wallet->increment('balance', $amount);

            $wallet->transactions()->create([
                'type' => Transaction::TYPE_CREDIT,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'description' => $data['description'] ?? 'Deposit',
                'metadata' => [
                    'source_currency' => $data['currency'] ?? $wallet->currency,
                    'original_amount' => $data['amount'],
                ],
            ]);

            return response()->json([
                'message' => 'Deposit successful.',
                'balance' => $wallet->balance,
            ]);
        });
    }

    public function withdraw(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $wallet = $user->wallet;
        $amount = $this->convertAmount($data['amount'], $data['currency'] ?? $wallet->currency, $wallet->currency);

        $this->guardAgainstFraud($user, $amount);

        if ($wallet->balance < $amount) {
            throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
        }

        return DB::transaction(function () use ($wallet, $amount, $data) {
            $wallet->decrement('balance', $amount);

            $wallet->transactions()->create([
                'type' => Transaction::TYPE_DEBIT,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'description' => $data['description'] ?? 'Withdrawal',
                'metadata' => [
                    'source_currency' => $data['currency'] ?? $wallet->currency,
                    'original_amount' => $data['amount'],
                ],
            ]);

            return response()->json([
                'message' => 'Withdrawal successful.',
                'balance' => $wallet->balance,
            ]);
        });
    }

    public function transfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recipient_email' => ['required', 'string', 'email', 'exists:users,email'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $sender = auth()->user();
        $senderWallet = $sender->wallet;

        if ($data['recipient_email'] === $sender->email) {
            throw ValidationException::withMessages(['recipient_email' => ['Cannot transfer to self.']]);
        }

        $recipient = User::where('email', $data['recipient_email'])->firstOrFail();
        $recipientWallet = $recipient->wallet;

        $transferAmountSenderCurrency = $this->convertAmount($data['amount'], $data['currency'] ?? $senderWallet->currency, $senderWallet->currency);

        $this->guardAgainstFraud($sender, $transferAmountSenderCurrency);

        if ($senderWallet->balance < $transferAmountSenderCurrency) {
            throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
        }

        return DB::transaction(function () use ($senderWallet, $recipientWallet, $transferAmountSenderCurrency, $data) {
            $recipientAmount = $this->convertAmount(
                $data['amount'],
                $data['currency'] ?? $senderWallet->currency,
                $recipientWallet->currency
            );

            $senderWallet->decrement('balance', $transferAmountSenderCurrency);
            $recipientWallet->increment('balance', $recipientAmount);

            $senderWallet->transactions()->create([
                'type' => Transaction::TYPE_DEBIT,
                'amount' => $transferAmountSenderCurrency,
                'currency' => $senderWallet->currency,
                'description' => $data['description'] ?? 'Transfer sent',
                'metadata' => [
                    'recipient_wallet_id' => $recipientWallet->id,
                    'original_amount' => $data['amount'],
                    'source_currency' => $data['currency'] ?? $senderWallet->currency,
                ],
            ]);

            $recipientWallet->transactions()->create([
                'type' => Transaction::TYPE_CREDIT,
                'amount' => $recipientAmount,
                'currency' => $recipientWallet->currency,
                'description' => $data['description'] ?? 'Transfer received',
                'metadata' => [
                    'sender_wallet_id' => $senderWallet->id,
                    'original_amount' => $data['amount'],
                    'source_currency' => $data['currency'] ?? $senderWallet->currency,
                ],
            ]);

            return response()->json([
                'message' => 'Transfer successful.',
                'balance' => $senderWallet->balance,
            ]);
        });
    }

    private function guardAgainstFraud(User $user, float $amount): void
    {
        if ($this->fraudService->exceedsDailyLimit($user, $amount)) {
            throw ValidationException::withMessages([
                'amount' => ['Daily debit limit exceeded.'],
            ]);
        }

        if ($this->fraudService->detectHighFrequency($user, $amount)) {
            logger()->warning('Suspicious activity detected.', [
                'user_id' => $user->id,
                'amount' => $amount,
            ]);

            throw ValidationException::withMessages([
                'amount' => ['Transaction flagged as suspicious. Please try again later.'],
            ]);
        }
    }

    private function convertAmount(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return round($amount, 2);
        }

        $rate = DB::table('exchange_rates')
            ->where('base_currency', $fromCurrency)
            ->where('target_currency', $toCurrency)
            ->value('rate');

        if (! $rate) {
            throw ValidationException::withMessages([
                'currency' => ["Exchange rate from {$fromCurrency} to {$toCurrency} not configured."],
            ]);
        }

        return round($amount * $rate, 2);
    }
}
