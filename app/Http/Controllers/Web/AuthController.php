<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\AuthController as ApiAuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiAuthController $apiAuthController
    ) {
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'default_currency' => ['sometimes', 'in:USD,INR,EUR'],
            'daily_debit_limit' => ['sometimes', 'numeric', 'min:0'],
        ]);

        try {
            $response = $this->apiAuthController->register($request);
            $data = $response->getData(true);

            Session::put('jwt_token', $data['token']);

            // Set token for auth and fetch user data
            JWTAuth::setToken($data['token']);
            $user = JWTAuth::authenticate();
            
            if ($user) {
                Session::put('user', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'default_currency' => $user->default_currency,
                ]);
            }

            return redirect()->route('dashboard')->with('success', $data['message'] ?? 'Registration successful!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Registration failed: ' . $e->getMessage())->withInput();
        }
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $response = $this->apiAuthController->login($request);
            $data = $response->getData(true);

            Session::put('jwt_token', $data['token']);

            // Set token for auth and fetch user data
            JWTAuth::setToken($data['token']);
            $user = JWTAuth::authenticate();
            
            if ($user) {
                Session::put('user', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'default_currency' => $user->default_currency,
                ]);
            }

            return redirect()->route('dashboard')->with('success', $data['message'] ?? 'Login successful!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Invalid credentials.')->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Login failed: ' . $e->getMessage())->withInput();
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $token = Session::get('jwt_token');

        if ($token) {
            try {
                JWTAuth::setToken($token);
                $this->apiAuthController->logout($request);
            } catch (\Exception $e) {
                // Continue with logout even if API call fails
            }
        }

        Session::forget('jwt_token');
        Session::forget('user');

        return redirect()->route('login.show')->with('success', 'Logged out successfully.');
    }

    public function refresh(Request $request): RedirectResponse
    {
        $token = Session::get('jwt_token');

        if (!$token) {
            return redirect()->route('login.show')->with('error', 'Please login to continue.');
        }

        try {
            JWTAuth::setToken($token);
            $response = $this->apiAuthController->refresh($request);
            $data = $response->getData(true);

            Session::put('jwt_token', $data['token']);

            return back()->with('success', 'Token refreshed successfully.');
        } catch (\Exception $e) {
            Session::forget('jwt_token');
            Session::forget('user');
            return redirect()->route('login.show')->with('error', 'Session expired. Please login again.');
        }
    }
}

