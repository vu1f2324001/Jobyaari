<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderByDesc('id')
            ->get(['id', 'name', 'slug', 'created_at', 'updated_at']);

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $baseSlug = Slug::toSlug($payload['name']);
        $slug = Slug::uniqueForModel(new Category(), $baseSlug);

        $category = Category::create([
            'name' => $payload['name'],
            'slug' => $slug,
        ]);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = Category::query()->findOrFail($id);

        $baseSlug = Slug::toSlug($payload['name']);
        $slug = Slug::uniqueForModel($category, $baseSlug);

        $category->update([
            'name' => $payload['name'],
            'slug' => $slug,
        ]);

        return response()->json(['data' => $category]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::query()->findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
