<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Http\Resources\Api\PostCategoryResource;

class PostCategoryApiController extends Controller
{
    public function index()
    {
        $categories = PostCategory::where('is_active', true)->latest()->get();
        return PostCategoryResource::collection($categories);
    }

    public function show($slug)
    {
        $category = PostCategory::where('is_active', true)
            ->whereTranslation('slug', $slug)
            ->firstOrFail();

        return new PostCategoryResource($category);
    }
}