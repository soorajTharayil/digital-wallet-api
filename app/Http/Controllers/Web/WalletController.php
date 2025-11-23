<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WalletController as ApiWalletController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class WalletController extends Controller
{
    public function __construct(
        private readonly ApiWalletController $apiWalletController
    ) {
    }

    private function setAuthToken(Request $request): void
    {
        $token = Session::get('jwt_token');
        if ($token) {
            JWTAuth::setToken($token);
            JWTAuth::authenticate(); // This sets the user in the auth system
        }
    }

    public function dashboard(Request $request): View
    {
        try {
            $this->setAuthToken($request);
            $response = $this->apiWalletController->show();
            $data = $response->getData(true);

            return view('wallet.dashboard', [
                'balance' => $data['balance'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
                'recentTransactions' => $data['transactions'] ?? [],
            ]);
        } catch (\Exception $e) {
            return view('wallet.dashboard', [
                'balance' => 0,
                'currency' => 'USD',
                'recentTransactions' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show(Request $request): View
    {
        try {
            $this->setAuthToken($request);
            $response = $this->apiWalletController->show();
            $data = $response->getData(true);

            return view('wallet.show', [
                'balance' => $data['balance'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function showDeposit(): View
    {
        return view('wallet.deposit');
    }

    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->setAuthToken($request);
            $response = $this->apiWalletController->deposit($request);
            $data = $response->getData(true);

            return redirect()->route('dashboard')->with('success', $data['message'] ?? 'Deposit successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showWithdraw(): View
    {
        return view('wallet.withdraw');
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->setAuthToken($request);
            $response = $this->apiWalletController->withdraw($request);
            $data = $response->getData(true);

            return redirect()->route('dashboard')->with('success', $data['message'] ?? 'Withdrawal successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showTransfer(): View
    {
        return view('wallet.transfer');
    }

    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'string', 'email'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'in:USD,INR,EUR'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->setAuthToken($request);
            $response = $this->apiWalletController->transfer($request);
            $data = $response->getData(true);

            return redirect()->route('dashboard')->with('success', $data['message'] ?? 'Transfer successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}

