@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-poppins font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
            Digital Wallet System
        </h1>
        <p class="text-gray-600 font-medium">Secure. Fast. Multi-Currency Wallet.</p>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-poppins font-bold text-gray-800 flex items-center space-x-2">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Transaction History</span>
        </h2>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-3 rounded-xl text-gray-700 font-medium hover:bg-gray-100 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="glass-effect rounded-2xl shadow-xl mb-6 p-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                <select id="type" 
                        name="type" 
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-300">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
            </div>

            <div>
                <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">Currency</label>
                <select id="currency" 
                        name="currency" 
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-300">
                    <option value="">All Currencies</option>
                    <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="INR" {{ request('currency') == 'INR' ? 'selected' : '' }}>INR</option>
                    <option value="EUR" {{ request('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
            </div>

            <div>
                <label for="per_page" class="block text-sm font-semibold text-gray-700 mb-2">Per Page</label>
                <select id="per_page" 
                        name="per_page" 
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-300">
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="glass-effect rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-indigo-500 to-purple-600">
            <h3 class="text-xl font-poppins font-bold text-white">All Transactions</h3>
        </div>
        <div class="p-6">
            @if(isset($error))
                <div class="text-center py-12">
                    <div class="w-20 h-20 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-red-600 font-medium">{{ $error }}</p>
                </div>
            @elseif(isset($transactions) && count($transactions) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Currency</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold 
                                        {{ $transaction['type'] === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        @if($transaction['type'] === 'credit')
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        @endif
                                        {{ ucfirst($transaction['type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold {{ $transaction['type'] === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction['type'] === 'credit' ? '+' : '-' }}{{ number_format($transaction['amount'], 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-lg bg-indigo-100 text-indigo-800 text-sm font-medium">
                                        {{ $transaction['currency'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $transaction['description'] ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y H:i') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($pagination) && $pagination['last_page'] > 1)
                <div class="mt-6 flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl">
                    <div class="text-sm text-gray-700 font-medium">
                        Showing <span class="font-bold">{{ (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 }}</span> 
                        to <span class="font-bold">{{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}</span> 
                        of <span class="font-bold">{{ $pagination['total'] }}</span> results
                    </div>
                    <div class="flex space-x-2">
                        @if($pagination['current_page'] > 1)
                            <a href="{{ route('transactions.index', array_merge($filters ?? [], ['page' => $pagination['current_page'] - 1])) }}" 
                               class="inline-flex items-center px-4 py-2 rounded-xl border-2 border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </a>
                        @endif
                        @if($pagination['current_page'] < $pagination['last_page'])
                            <a href="{{ route('transactions.index', array_merge($filters ?? [], ['page' => $pagination['current_page'] + 1])) }}" 
                               class="inline-flex items-center px-4 py-2 rounded-xl border-2 border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                Next
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">No transactions found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
