<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Generate JWT token for a user and set it in Authorization header
     */
    protected function actingAsJWT(User $user, string $guard = 'api')
    {
        $token = JWTAuth::fromUser($user);
        return $this->withToken($token);
    }
}

