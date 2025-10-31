<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostService;

class PostController extends Controller
{
    public function store(): array
    {
        return PostService::store();
    }
}
