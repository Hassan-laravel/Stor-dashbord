<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\StoreApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Http\Controllers\Api\SocialMediaApiController;
use App\Http\Controllers\Api\GlobalApiController;

Route::middleware('api.locale')->prefix('v1')->group(function () {
    
    // الإعدادات العامة للموقع
    Route::get('/init-data', [GlobalApiController::class, 'initData']);
    
    // المنتجات
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{slug}', [ProductApiController::class, 'show']);
    
    // تصنيفات المنتجات
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);
    
    // المخازن
    Route::get('/stores', [StoreApiController::class, 'index']);
    Route::get('/stores/{id}', [StoreApiController::class, 'show']);
    
    // المقالات والصفحات
    Route::get('/posts', [PostApiController::class, 'index']);
    Route::get('/posts/{slug}', [PostApiController::class, 'show']);
    
    // تصنيفات المقالات
    Route::get('/post-categories', [PostCategoryApiController::class, 'index']);
    Route::get('/post-categories/{slug}', [PostCategoryApiController::class, 'show']);
    
    // روابط السوشيال ميديا (كنقطة نهاية مستقلة إذا لم ترغب بدمجها في init-data)
    Route::get('/social-media', [SocialMediaApiController::class, 'index']);
});