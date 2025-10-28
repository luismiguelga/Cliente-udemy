<?php

namespace App\Services;

use App\Models\User;
use App\Utils\APIClient;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionService
{
    public static function storeAccessToken(User $user, array $service): void
    {
        if (! $user->accessToken || $user->accessToken->expires_at->isPast()) {
            $response = APIClient::make()
                ->withHeaders(['Accept' => 'application/json'])
                ->withData([
                    'grant_type' => 'password',
                    'client_id' => config('services.api-udemy.client_id'),
                    'client_secret' => config('services.api-udemy.client_secret'),
                    'username' => request('email'),
                    'password' => request('password'),
                    'scope' => 'create-post read-post update-post delete-post'
                ])
                ->post('oauth/token');

            $response = $response->response()->json();
            $user->accessToken()?->delete();

            $user->accessToken()->create([
                'service_id' => $service['data']['id'],
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at' => now()->addSeconds($response['expires_in']),
            ]);
        }

    }

    public static function validateLogin(array $request): ?array
    {
        $response = APIClient::make()
            ->withHeaders(['Accept' => 'application/json'])
            ->withData([
                'email' => $request['email'],
                'password' => $request['password'],
            ])
            ->post('api/login');

        $response = $response->response();

        if ($response->status() === 404) {
            return null;
        }

        $user = User::updateOrCreate([
            'email' => $request['email'],
        ], $response['data']);

        Auth::login($user);

        return ['service' => $response->json(), 'user' => $user];

    }
}
