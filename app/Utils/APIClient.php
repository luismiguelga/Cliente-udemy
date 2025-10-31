<?php

namespace App\Utils;

use App\Services\AccessTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class APIClient
{
    protected array $headers = [];

    protected $attachments = [];

    protected array $data = [];

    protected $response;

    public static function make(): self
    {
        return new self;
    }

    public function withBearerToken():self
    {
        $accessToken = AccessTokenService::resolveAuthorization();

        $this->headers['Authorization'] = 'Bearer '.$accessToken->access_token;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function withAccessTokenData(): self
    {
        $this->data = [
            'grant_type' => 'password',
            'client_id' => config('services.api-udemy.client_id'),
            'client_secret' => config('services.api-udemy.client_secret'),
            'username' => request('email'),
            'password' => request('password'),
        ];

        return $this;
    }

    public function withRefreshTokenData(): self
    {
        $this->data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => auth()->user()->accessToken->refresh_token,
            'client_id' => config('services.api-udemy.client_id'),
            'client_secret' => config('services.api-udemy.client_secret'),
            'scope' => 'create-post read-post update-post delete-post',
        ];

        return $this;
    }

    public function post(string $endpoint): self
    {
        $url = self::getBaseUrl($endpoint);
        $request = Http::withHeaders($this->headers)->acceptJson();

        if (! empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $request = $request->attach($attachment['name'], $attachment['contents'], $attachment['filename']);
            }
        }

        $this->response = $request->timeout(360)->post($url, $this->data);

        return $this;
    }

    public static function getBaseUrl(string $path): string
    {
        return env('API_URL').$path;
    }
}
