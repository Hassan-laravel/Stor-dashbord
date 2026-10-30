<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Http\Resources\Api\StoreResource;

class StoreApiController extends Controller
{
    // جلب جميع المخازن الفعالة
    public function index()
    {
        $stores = Store::where('is_active', true)->latest()->get();
        return StoreResource::collection($stores);
    }

    // جلب تفاصيل المخزن (بالـ ID لعدم وجود ترجمات غالباً)
    public function show($id)
    {
        $store = Store::where('is_active', true)->findOrFail($id);
        return new StoreResource($store);
    }
}