<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'in:credit,debit'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $transactions = auth()->user()
            ->wallet
            ->transactions()
            ->when(isset($validated['type']), fn ($q) => $q->where('type', $validated['type']))
            ->when(isset($validated['currency']), fn ($q) => $q->where('currency', $validated['currency']))
            ->latest()
            ->paginate($validated['per_page'] ?? 20);

        return response()->json($transactions);
    }
}
