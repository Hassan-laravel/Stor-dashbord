<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting; // تأكد من أن نموذج (Model) الإعدادات لديك يحمل هذا الاسم
use App\Http\Resources\Api\SettingResource;
use Illuminate\Http\Request;

class SettingApiController extends Controller
{
    public function index(Request $request)
    {
        // جلب أول سجل للإعدادات في قاعدة البيانات
        $setting = Setting::first();

        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Settings not found',
                'data' => []
            ], 404);
        }

        // إرجاع البيانات باستخدام الـ Resource الموجود عندك
        return new SettingResource($setting);
    }
}