<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ApiService
{
    public static function generateAccessToken(User $user, array $service): void
    {
        if (! $user->accessToken) {
            $path = self::getBaseUrl('oauth/token');

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($path, [
                'grant_type' => 'password',
                'client_id' => env('CLIENT_ID'),
                'client_secret' => env('CLIENT_SECRET'),
                'username' => request('email'),
                'password' => request('password'),
            ]);

            $response = $response->json();

            $user->accessToken()->create([
                'service_id' => $service['data']['id'],
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at' => now()->addSeconds($response['expires_in']),
            ]);
        }

    }

    public static function validateLogin(array $request): array|null
    {
        $path = self::getBaseUrl('login');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($path, [
            'email' => $request['email'],
            'password' => $request['password'],
        ]);

        if ($response->status() === 404) {
            return null;
        }

        $service = $response->json();

        $user = User::updateOrCreate([
            'email' => $request['email'],
        ], $service['data']);

        Auth::login($user);

        return ['service' => $service, 'user' => $user];

    }

    public static function getBaseUrl(string $path): string
    {
        return env('API_URL').$path;
    }
}
