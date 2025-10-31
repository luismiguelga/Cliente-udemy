<?php

namespace App\Services;

use App\Models\AccessToken;
use App\Utils\APIClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AccessTokenService
{
    public static function resolveAuthorization(): AccessToken
    {
        $accessToken = auth()->user()->accessToken;

        if (! $accessToken) {
            AccessTokenService::logout();
        }

        if ($accessToken->expires_at <= now()) {
            return AccessTokenService::refreshToken();
        }

        return $accessToken;
    }

    public static function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(route('login'));
    }

    private static function refreshToken(): AccessToken
    {
        if (! auth()->user()->accessToken || auth()->user()->accessToken->expires_at->isPast()) {
            $response = APIClient::make()
                ->withHeaders(['Accept' => 'application/json'])
                ->withRefreshTokenData()
                ->post('oauth/token')
                ->response();

            $access_token = $response->json();

            if ($response->status() != 400) {
                auth()->user()->accessToken->update([
                    'access_token' => $access_token['access_token'],
                    'refresh_token' => $access_token['refresh_token'],
                    'expires_at' => now()->addSecond($access_token['expires_in']),
                ]);
            }

        }

        return auth()->user()->accessToken;

    }
}
