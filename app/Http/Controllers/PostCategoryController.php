<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        $categories = PostCategory::latest()->paginate(10);
        return view('admin.post-categories.index', compact('categories'));
    }

    /**
     * عرض صفحة إضافة تصنيف جديد
     */
    public function create()
    {
        return view('admin.post-categories.create');
    }

    /**
     * حفظ التصنيف الجديد في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $locales = config('translatable.locales');
        
        $rules = [
            'is_active' => 'boolean',
        ];

        // تطبيق قواعد التحقق على جميع اللغات المفعلة
        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'required|string|max:255';
        }

        $request->validate($rules);

        $categoryData = [
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        // معالجة اللغات وإنشاء الروابط (Slug)
        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name"))) {
                $categoryData[$locale] = $request->input($locale);
                
                // توليد Slug من الاسم إذا لم يقم المستخدم بإدخاله يدوياً
                $slugText = !empty($request->input("{$locale}.slug")) ? $request->input("{$locale}.slug") : $request->input("{$locale}.name");
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $categoryData[$locale]['slug'] = trim(mb_strtolower($slug), '-');
            }
        }

        PostCategory::create($categoryData);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'تم إضافة تصنيف المقالات بنجاح.');
    }

    /**
     * عرض صفحة تعديل التصنيف
     */
    public function edit(PostCategory $postCategory)
    {
        return view('admin.post-categories.edit', compact('postCategory'));
    }

    /**
     * تحديث بيانات التصنيف
     */
    public function update(Request $request, PostCategory $postCategory)
    {
        $locales = config('translatable.locales');
        
        $rules = [
            'is_active' => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $categoryData = [
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name"))) {
                $categoryData[$locale] = $request->input($locale);
                
                $slugText = !empty($request->input("{$locale}.slug")) ? $request->input("{$locale}.slug") : $request->input("{$locale}.name");
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $categoryData[$locale]['slug'] = trim(mb_strtolower($slug), '-');
            } else {
                // حذف الترجمة إذا قام المستخدم بمسح الاسم أثناء التعديل
                if ($postCategory->hasTranslation($locale)) {
                    $postCategory->deleteTranslation($locale);
                }
            }
        }

        $postCategory->update($categoryData);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'تم تعديل التصنيف بنجاح.');
    }

    /**
     * حذف التصنيف
     */
    public function destroy(PostCategory $postCategory)
    {
        $postCategory->delete();
        
        return redirect()->route('admin.post-categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح.');
    }
}