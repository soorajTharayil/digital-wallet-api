@extends('layouts.app')

@section('title', 'Wallet')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="px-4 py-8 sm:px-0">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Wallet Details</h3>
                
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Balance</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ number_format($balance ?? 0, 2) }} {{ $currency ?? 'USD' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Currency</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $currency ?? 'USD' }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

