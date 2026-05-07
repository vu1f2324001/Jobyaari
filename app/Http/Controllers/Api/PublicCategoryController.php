<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class PublicCategoryController
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
