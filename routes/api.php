<?php

use Illuminate\Support\Facades\Route;

Route::get('/categories', [\App\Http\Controllers\Api\PublicCategoryController::class, 'index']);
Route::get('/blogs', [\App\Http\Controllers\Api\PublicBlogController::class, 'index']);
Route::get('/blogs/{slug}', [\App\Http\Controllers\Api\PublicBlogController::class, 'show']);
Route::get('/blogs/{slug}/related', [\App\Http\Controllers\Api\PublicBlogController::class, 'related']);

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AdminAuthController::class, 'logout']);

    // Categories CRUD
    Route::get('/categories', [\App\Http\Controllers\Api\AdminCategoryController::class, 'index']);
    Route::post('/categories', [\App\Http\Controllers\Api\AdminCategoryController::class, 'store']);
    Route::put('/categories/{id}', [\App\Http\Controllers\Api\AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [\App\Http\Controllers\Api\AdminCategoryController::class, 'destroy']);

    // Blogs CRUD
    Route::get('/blogs', [\App\Http\Controllers\Api\AdminBlogController::class, 'index']);
    Route::get('/blogs/{id}', [\App\Http\Controllers\Api\AdminBlogController::class, 'show']);
    Route::post('/blogs', [\App\Http\Controllers\Api\AdminBlogController::class, 'store']);
    Route::put('/blogs/{id}', [\App\Http\Controllers\Api\AdminBlogController::class, 'update']);
    Route::delete('/blogs/{id}', [\App\Http\Controllers\Api\AdminBlogController::class, 'destroy']);

    // CKEditor upload
    Route::post('/ckeditor/upload', [\App\Http\Controllers\Api\AdminCkeditorUploadController::class, 'upload']);
});

Route::post('/admin/login', [\App\Http\Controllers\Api\AdminAuthController::class, 'login']);
