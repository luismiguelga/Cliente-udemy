<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\APIClient;

class PostController extends Controller
{
    public function store()
    {
        if (! auth()->user()->accessToken || auth()->user()->accessToken->expires_at->isPast()) {
            $response = APIClient::make()
                ->withHeaders(['Accept' => 'application/json'])
                ->withData([
                    'grant_type' => 'refresh_token',
                    'refresh_token' => auth()->user()->accessToken->refresh_token,
                    'client_id' => config('services.api-udemy.client_id'),
                    'client_secret' => config('services.api-udemy.client_secret'),
                    'scope' => 'create-post read-post update-post delete-post',
                ])->post('oauth/token');

            $access_token = $response->response()->json();

            if ($response->response()->status() != 400) {
                auth()->user()->accessToken->update([
                    'access_token' => $access_token['access_token'],
                    'refresh_token' => $access_token['refresh_token'],
                    'expires_at' => now()->addSecond($access_token['expires_in']),
                ]);
            }

        }

        $response = APIClient::make()
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.auth()->user()->accessToken->access_token,
            ])->withData([
                'name' => 'Post1',
                'slug' => 'Post4',
                'extract' => 'extract',
                'body' => 'body',
                'category_id' => 1,
            ])->post('api/posts');

        return $response->response()->json();
    }
}
