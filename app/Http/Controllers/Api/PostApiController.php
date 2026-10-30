<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Resources\Api\PostResource;
use Illuminate\Http\Request;

class PostApiController extends Controller
{
public function index(Request $request)
    {
        // استعلام أساسي لجلب المقالات/الصفحات المفعلة مع ترجماتها
        $query = Post::with('translations')->where('is_active', true);

        // التحقق من وجود فلتر 'type' في الرابط وتطبيقه
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // جلب النتائج
        $posts = $query->get(); // استخدم paginate() إذا كنت بحاجة لترقيم الصفحات

        return PostResource::collection($posts);
    }

    // جلب مقال أو صفحة واحدة باستخدام الـ Slug للـ SEO
    public function show($slug)
    {
        $post = Post::with('category')
            ->where('is_active', true)
            ->whereTranslation('slug', $slug)
            ->firstOrFail();

        return new PostResource($post);
    }
}