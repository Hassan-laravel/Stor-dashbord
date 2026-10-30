<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'store'])->where('is_active', true);

        // 1. الفلترة حسب المخزن (Store)
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // 2. الفلترة حسب التصنيف
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. البحث بالاسم (في جدول الترجمة)
        if ($request->filled('search')) {
            $query->whereTranslationLike('title', '%' . $request->search . '%');
        }

        // 4. الفرز (الترتيب) - الأكثر مبيعاً، الأعلى تقييماً، الأحدث
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'top_rated':
                    $query->orderByDesc('rating');
                    break;
                case 'best_selling':
                    $query->orderByDesc('sales_volume');
                    break;
                case 'price_asc':
                    $query->orderBy('price');
                    break;
                case 'price_desc':
                    $query->orderByDesc('price');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(15);

        return ProductResource::collection($products);
    }

    // دالة لعرض منتج واحد بتفاصيله للمقارنة أو لصفحة المنتج
// طلب تفاصيل المنتج بناءً على الـ Slug بدلاً من الـ ID
    public function show($slug)
    {
        // استخدام whereTranslation للبحث في جدول ترجمات المنتجات
        $product = Product::with(['category', 'store'])
            ->where('is_active', true)
            ->whereTranslation('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}