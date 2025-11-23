<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController as ApiTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TransactionController extends Controller
{
    public function __construct(
        private readonly ApiTransactionController $apiTransactionController
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

    public function index(Request $request): View
    {
        $token = Session::get('jwt_token');
        
        if (!$token) {
            return redirect()->route('login.show')->with('error', 'Please login to continue.');
        }

        try {
            $this->setAuthToken($request);
            $response = $this->apiTransactionController->index($request);
            $data = $response->getData(true);

            $transactions = $data['data'] ?? [];
            $pagination = [
                'current_page' => $data['current_page'] ?? 1,
                'last_page' => $data['last_page'] ?? 1,
                'per_page' => $data['per_page'] ?? 20,
                'total' => $data['total'] ?? 0,
            ];
            
            return view('transactions.index', [
                'transactions' => $transactions,
                'pagination' => $pagination,
                'filters' => $request->only(['type', 'currency']),
            ]);
        } catch (\Exception $e) {
            return view('transactions.index', [
                'transactions' => [],
                'pagination' => null,
                'error' => 'Failed to load transactions: ' . $e->getMessage(),
            ]);
        }
    }
}

