<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Api\CategoryResource;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    // جلب جميع التصنيفات الفعالة
public function index()
    {
        // افترض أن parent_id في قاعدة البيانات يحمل قيمة null للتصنيفات الرئيسية
        $categories = Category::whereNull('parent_id') 
            ->with(['translations', 'children' => function($query) {
                // جلب ترجمات الأبناء أيضاً
                $query->with('translations')->where('is_active', true);
            }])
            ->where('is_active', true)
            ->get();

        return CategoryResource::collection($categories);
    }

    // جلب تفاصيل تصنيف معين باستخدام الـ Slug
    public function show($slug)
    {
        $category = Category::where('is_active', true)
            ->whereTranslation('slug', $slug)
            ->firstOrFail();

        return new CategoryResource($category);
    }
}