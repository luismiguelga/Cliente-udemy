<?php

namespace App\Services;

use App\Utils\APIClient;
use Illuminate\Http\JsonResponse;

class PostService
{
    public static function store(): array
    {
        $response = APIClient::make()
            ->withBearerToken()
            ->withHeaders(['Accept' => 'application/json'])
            ->withData([
                'name' => 'Post1',
                'slug' => 'Post5',
                'extract' => 'extract',
                'body' => 'body',
                'category_id' => 1,
            ])
            ->post('api/posts')
            ->response();

        return $response->json();
    }
}
