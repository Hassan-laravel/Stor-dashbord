<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SocialMediaLink;
use App\Models\Post;
use App\Http\Resources\Api\SettingResource;

class GlobalApiController extends Controller
{
    public function initData()
    {
        $settings = Setting::first();
        $socialLinks = SocialMediaLink::where('is_active', true)->get();
        
        // جلب الصفحات الثابتة (مثل من نحن، سياسة الخصوصية) لوضعها في الفوتر
        $pages = Post::where('type', 'page')->where('is_active', true)
                    //  ->get(['id', 'slug'])  سيتم ترجمة العنوان تلقائياً بواسطة Astrotomic
                     ->get()
                     ->map(function($page) {
                         return [
                             'id' => $page->id,
                             'title' => $page->title,
                             'slug' => $page->slug
                         ];
                     });

        return response()->json([
            'settings'     => $settings ? new SettingResource($settings) : null,
            'social_links' => $socialLinks->map(function($link) {
                return [
                    'name' => $link->name,
                    'url'  => $link->url,
                    'icon' => $link->icon
                ];
            }),
            'pages'        => $pages
        ]);
    }
}