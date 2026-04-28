<?php

use Illuminate\Support\Facades\Route;

// All routes here require Sanctum auth + ABAC permission check
Route::middleware('auth:sanctum')->group(function () {

    // posts:view — viewer can only see published, editor/admin can see all
    Route::get('/posts', function() {
        return 'You are here';
    })->middleware(['abac:posts:view', 'field.guard:posts:view']);

    // posts:create — no conditions, just having the permission is enough
    Route::post('/posts', function() {
        return 'You are here';
    })->middleware(['abac:posts:create', 'field.guard:posts:create']);

    // posts:edit — editor can only edit their own posts (owner_only condition)
    // Route model binding passes {post} → AbacMiddleware extracts owner_id automatically
    Route::put('/posts/{post}', function() {
        return 'You are here';
    })->middleware(['abac:posts:edit', 'field.guard:posts:edit']);

    // posts:delete — same owner_only condition for editors
    Route::delete('/posts/{post}', function() {
        return 'You are here';
    })->middleware('abac:posts:delete');

    // users:manage — admin only, no conditions
    Route::get('/users', function() {
        return 'You are here';
    })->middleware('abac:users:manage');
});
