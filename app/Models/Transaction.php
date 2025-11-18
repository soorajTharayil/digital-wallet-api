<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public const TYPE_DEBIT = 'debit';
    public const TYPE_CREDIT = 'credit';

    protected $fillable = [
        'wallet_id',
        'related_wallet_id',
        'type',
        'amount',
        'currency',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet()
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }
}
