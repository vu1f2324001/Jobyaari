<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));
Route::get('/blogs', fn () => view('blogs.index'));
Route::get('/blogs/{slug}', fn ($slug) => view('blogs.show', ['slug' => $slug]));

Route::get('/admin/login', fn () => view('admin.login'))->name('admin.login');
Route::get('/admin/categories', fn () => view('admin.categories'))->name('admin.categories');
Route::get('/admin/blogs', fn () => view('admin.blogs'))->name('admin.blogs');
Route::get('/admin/blogs/new', fn () => view('admin.blogs_edit'))->name('admin.blogs.new');
Route::get('/admin/blogs/{id}/edit', fn ($id) => view('admin.blogs_edit', ['id' => $id]))->name('admin.blogs.edit');
