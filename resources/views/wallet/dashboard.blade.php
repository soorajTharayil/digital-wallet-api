@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-poppins font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
            Digital Wallet System
        </h1>
        <p class="text-gray-600 font-medium">Secure. Fast. Multi-Currency Wallet.</p>
    </div>

    <!-- Welcome Card -->
    <div class="mb-8">
        <div class="glass-effect rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Welcome back,</p>
                    <h2 class="text-2xl font-poppins font-bold text-gray-800">{{ session('user.name', 'User') }}!</h2>
                </div>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold text-xl">{{ substr(session('user.name', 'U'), 0, 1) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Balance Card -->
    <div class="mb-8">
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 text-white transform hover:scale-[1.02] transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm font-medium">Total Balance</p>
                        <h3 class="text-4xl font-poppins font-bold">
                            {{ number_format($balance ?? 0, 2) }} <span class="text-lg">{{ $currency ?? 'USD' }}</span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('wallet.deposit.show') }}" 
           class="group glass-effect rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-poppins font-bold text-gray-800">Deposit</h3>
                    <p class="text-sm text-gray-600">Add funds</p>
                </div>
            </div>
            <p class="text-gray-500 text-sm">Add money to your wallet instantly</p>
        </a>

        <a href="{{ route('wallet.withdraw.show') }}" 
           class="group glass-effect rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-400 to-pink-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-poppins font-bold text-gray-800">Withdraw</h3>
                    <p class="text-sm text-gray-600">Take out funds</p>
                </div>
            </div>
            <p class="text-gray-500 text-sm">Withdraw money from your wallet</p>
        </a>

        <a href="{{ route('wallet.transfer.show') }}" 
           class="group glass-effect rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-poppins font-bold text-gray-800">Transfer</h3>
                    <p class="text-sm text-gray-600">Send money</p>
                </div>
            </div>
            <p class="text-gray-500 text-sm">Send money to another user</p>
        </a>
    </div>

    <!-- Recent Transactions -->
    <div class="glass-effect rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-indigo-500 to-purple-600">
            <h3 class="text-xl font-poppins font-bold text-white flex items-center space-x-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Recent Transactions</span>
            </h3>
        </div>
        <div class="p-6">
            @if(isset($recentTransactions) && count($recentTransactions) > 0)
                <div class="space-y-4">
                    @foreach($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $transaction['type'] === 'credit' ? 'bg-green-100' : 'bg-red-100' }}">
                                @if($transaction['type'] === 'credit')
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $transaction['description'] ?? 'Transaction' }}</p>
                                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg {{ $transaction['type'] === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction['type'] === 'credit' ? '+' : '-' }}{{ number_format($transaction['amount'], 2) }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $transaction['currency'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <span>View All Transactions</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg mb-4">No recent transactions</p>
                    <a href="{{ route('transactions.index') }}" class="inline-flex items-center text-indigo-600 hover:text-purple-600 font-semibold">
                        <span>View All Transactions</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
