<?php

namespace App\Http\Controllers\Api;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicBlogController
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()->with('category:id,name,slug');

        // Category filter (by slug)
        $categorySlug = $request->query('category');
        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Date filter (by created_at date)
        $date = $request->query('date'); // YYYY-MM-DD
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Search
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('short_description', 'like', '%'.$search.'%');
            });
        }

        $perPage = (int) $request->query('per_page', 6);
        $perPage = max(1, min(50, $perPage));

        $page = (int) $request->query('page', 1);
        $page = max(1, $page);

        $paginator = $query->orderByDesc('created_at')->paginate(
            perPage: $perPage,
            page: $page
        );

        $data = $paginator->getCollection()->map(function (Blog $blog) {
            $imageUrl = $blog->image;
            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = Storage::disk('public')->url($imageUrl);
            }

            return [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'short_description' => $blog->short_description,
                'content' => $blog->content,
                'image' => $imageUrl,
                'publish_date' => optional($blog->created_at)->format('Y-m-d'),
                'category' => optional($blog->category)->name,
                'category_slug' => optional($blog->category)->slug,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::query()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Blog::query()
            ->with('category:id,name,slug')
            ->where('category_id', $blog->category_id)
            ->where('id', '!=', $blog->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'short_description' => $blog->short_description,
                'content' => $blog->content, // stored HTML
                'image' => $blog->image && !str_starts_with($blog->image, 'http')
                    ? Storage::disk('public')->url($blog->image)
                    : $blog->image,
                'publish_date' => optional($blog->created_at)->format('Y-m-d'),
                'category' => optional($blog->category)->name,
                'category_slug' => optional($blog->category)->slug,
                'related' => $related->map(fn (Blog $b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'slug' => $b->slug,
                    'short_description' => $b->short_description,
                    'image' => $b->image,
                    'publish_date' => optional($b->created_at)->format('Y-m-d'),
                ])->values(),
            ],
        ]);
    }

    public function related(string $slug): JsonResponse
    {
        $blog = Blog::query()->where('slug', $slug)->firstOrFail();

        $related = Blog::query()
            ->with('category:id,name,slug')
            ->where('category_id', $blog->category_id)
            ->where('id', '!=', $blog->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => $related->map(function (Blog $b) {
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'slug' => $b->slug,
                    'short_description' => $b->short_description,
                    'image' => $b->image && !str_starts_with($b->image, 'http')
                        ? Storage::disk('public')->url($b->image)
                        : $b->image,
                    'publish_date' => optional($b->created_at)->format('Y-m-d'),
                ];
            })->values(),
        ]);
    }
}
