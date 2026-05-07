<?php

namespace App\Http\Controllers\Api;

use App\Models\Blog;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBlogController
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()->with('category:id,name,slug');

        $categorySlug = $request->query('category');
        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $blogs = $query->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'short_description', 'content', 'category_id', 'image', 'created_at', 'updated_at']);

        $blogs->transform(fn (Blog $blog) => [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'short_description' => $blog->short_description,
            'content' => $blog->content,
            'category_id' => $blog->category_id,
            'image' => $this->getImageUrl($blog->image),
            'imageUrl' => $this->getImageUrl($blog->image),
            'category' => optional($blog->category)->name,
            'created_at' => optional($blog->created_at)->toDateTimeString(),
            'updated_at' => optional($blog->updated_at)->toDateTimeString(),
        ]);

        return response()->json(['data' => $blogs->values()]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $baseSlug = Slug::toSlug($payload['title']);
        $slug = Slug::uniqueForModel(new Blog(), $baseSlug);

        $imagePath = $this->storeImage($request->file('image'));

        $blog = Blog::create([
            'title' => $payload['title'],
            'slug' => $slug,
            'short_description' => $payload['short_description'],
            'content' => $payload['content'],
            'category_id' => $payload['category_id'],
            'image' => $imagePath,
        ]);

        // Transform the blog for consistent output
        $blog->load('category:id,name,slug'); // Eager load category for the response
        $imageUrl = $this->getImageUrl($blog->image);

        return response()->json([
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'short_description' => $blog->short_description,
                'content' => $blog->content,
                'category_id' => $blog->category_id,
                'image' => $imageUrl,
                'imageUrl' => $imageUrl,
                'created_at' => optional($blog->created_at)->toDateTimeString(),
                'updated_at' => optional($blog->updated_at)->toDateTimeString(),
                'category' => optional($blog->category)->name,
            ],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $blog = Blog::query()->with('category:id,name,slug')->findOrFail($id);

        $imageUrl = $this->getImageUrl($blog->image);

        return response()->json([
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'short_description' => $blog->short_description,
                'content' => $blog->content,
                'category_id' => $blog->category_id,
                'image' => $imageUrl,
                'imageUrl' => $imageUrl,
                'created_at' => optional($blog->created_at)->toDateTimeString(),
                'updated_at' => optional($blog->updated_at)->toDateTimeString(),
                'category' => optional($blog->category)->name,
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $blog = Blog::query()->findOrFail($id);

        $baseSlug = Slug::toSlug($payload['title']);
        $slug = Slug::uniqueForModel($blog, $baseSlug);

        $imagePath = $blog->image;
        if ($request->hasFile('image')) {
            // Delete old image if it exists and is a local file
            $this->deleteImage($blog->image);
            // Store new image
            $imagePath = $this->storeImage($request->file('image'));
        }

        $blog->update([
            'title' => $payload['title'],
            'slug' => $slug,
            'short_description' => $payload['short_description'],
            'content' => $payload['content'],
            'category_id' => $payload['category_id'],
            'image' => $imagePath,
        ]);

        // Transform the blog for consistent output
        $blog->load('category:id,name,slug'); // Eager load category for the response
        $imageUrl = $this->getImageUrl($blog->image);

        return response()->json([
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'short_description' => $blog->short_description,
                'content' => $blog->content,
                'category_id' => $blog->category_id,
              'image' => $this->getImageUrl($blog->image),
              'imageUrl' => $this->getImageUrl($blog->image), // Correctly provided by backend
                'created_at' => optional($blog->created_at)->toDateTimeString(),
                'updated_at' => optional($blog->updated_at)->toDateTimeString(),
                'category' => optional($blog->category)->name,
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $blog = Blog::query()->findOrFail($id);

        $this->deleteImage($blog->image);

        $blog->delete();

        return response()->json(['message' => 'Blog deleted']);
    }

    private function storeImage($file): ?string
    {
        if (!$file) {
            return null;
        }

        // Store in 'storage/app/public/uploads' and return the relative path.
        return $file->store('uploads', 'public');
    }

    private function getImageUrl(?string $path): ?string
    {
        if ($path && !str_starts_with($path, 'http')) {
            return Storage::disk('public')->url($path);
        }

        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
