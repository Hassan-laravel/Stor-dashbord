<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = PostCategory::where('is_active', true)->get();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $locales = config('translatable.locales');

        $rules = [
            'type'             => 'required|in:article,page',
            'post_category_id' => 'required_if:type,article|nullable|exists:post_categories,id',
            'is_active'        => 'boolean',
        ];

        // جعل جميع حقول اللغات اختيارية (nullable)
        foreach ($locales as $locale) {
            $rules["{$locale}.title"] = 'nullable|string|max:255';
            $rules["{$locale}.content"] = 'nullable|string';
            $rules["{$locale}.meta_title"] = 'nullable|string|max:60';
            $rules["{$locale}.meta_description"] = 'nullable|string|max:160';
        }

        $request->validate($rules);

        $postData = [
            'type'             => $request->type,
            'post_category_id' => $request->type === 'article' ? $request->post_category_id : null,
            'is_active'        => $request->has('is_active') ? 1 : 0,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.title"))) {
                $postData[$locale] = $request->input($locale);

                $slugText = !empty($request->input("{$locale}.slug")) ? $request->input("{$locale}.slug") : $request->input("{$locale}.title");
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $postData[$locale]['slug'] = trim(mb_strtolower($slug), '-');
            }
        }

        Post::create($postData);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }
    public function edit(Post $post)
    {
        // جلب التصنيفات الفعالة لعرضها في القائمة المنسدلة
        $categories = PostCategory::where('is_active', true)->get();

        return view('admin.posts.edit', compact('post', 'categories'));
    }
    public function update(Request $request, Post $post)
    {
        $locales = config('translatable.locales');

        $rules = [
            'type'             => 'required|in:article,page',
            'post_category_id' => 'required_if:type,article|nullable|exists:post_categories,id',
            'is_active'        => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.title"] = 'nullable|string|max:255';
            $rules["{$locale}.content"] = 'nullable|string';
            $rules["{$locale}.meta_title"] = 'nullable|string|max:60';
            $rules["{$locale}.meta_description"] = 'nullable|string|max:160';
        }

        $request->validate($rules);

        $postData = [
            'type'             => $request->type,
            'post_category_id' => $request->type === 'article' ? $request->post_category_id : null,
            'is_active'        => $request->has('is_active') ? 1 : 0,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.title"))) {
                $postData[$locale] = $request->input($locale);

                $slugText = !empty($request->input("{$locale}.slug")) ? $request->input("{$locale}.slug") : $request->input("{$locale}.title");
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $postData[$locale]['slug'] = trim(mb_strtolower($slug), '-');
            } else {
                if ($post->hasTranslation($locale)) {
                    $post->deleteTranslation($locale);
                }
            }
        }

        $post->update($postData);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
