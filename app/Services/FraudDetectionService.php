<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class FraudDetectionService
{
    public function exceedsDailyLimit(User $user, float $debitAmount): bool
    {
        $todayDebits = Transaction::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', Transaction::TYPE_DEBIT)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        return ($todayDebits + $debitAmount) > $user->daily_debit_limit;
    }

    public function detectHighFrequency(User $user, float $amount): bool
    {
        $threshold = (float) config('services.fraud.high_value_threshold');
        if ($amount < $threshold) {
            return false;
        }

        $windowStart = Carbon::now()->subMinutes((int) config('services.fraud.time_window_minutes'));

        $count = Transaction::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', Transaction::TYPE_DEBIT)
            ->where('amount', '>=', $threshold)
            ->where('created_at', '>=', $windowStart)
            ->count();

        return $count >= (int) config('services.fraud.max_transactions');
    }
}
