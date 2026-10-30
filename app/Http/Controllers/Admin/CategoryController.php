<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Store;
class CategoryController extends Controller
{
    public function index()
    {
        // جلب التصنيفات مع ترجماتها والتصنيف الأب إن وجد
        $categories = Category::with(['translations', 'parent'])->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

public function create()
    {
        $locales = config('translatable.locales');
        $mainCategories = Category::whereNull('parent_id')->withTranslation()->get();
        
        // جلب المتاجر المفعلة فقط لعرضها في القائمة
        $stores = Store::where('is_active', true)->get(); 
        
        return view('admin.categories.create', compact('locales', 'mainCategories', 'stores'));
    }

    public function store(Request $request)
    {
        $locales = config('translatable.locales');
        $data = $request->all();
        
        foreach ($locales as $locale) {
            if (!empty($data[$locale]['name'])) {
                $slugText = !empty($data[$locale]['slug']) ? $data[$locale]['slug'] : $data[$locale]['name'];
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $slug = trim($slug, '-');
                $data[$locale]['slug'] = mb_strtolower($slug);
            }
        }
        $request->merge($data);
        
        $rules = [
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'stores'    => 'nullable|array', // التأكد أن المتاجر تأتي كمصفوفة
            'stores.*'  => 'exists:stores,id' // التأكد أن أرقام المتاجر موجودة فعلياً
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'nullable|string|max:255';
            $rules["{$locale}.slug"] = 'nullable|string|unique:category_translations,slug';
            $rules["{$locale}.description"] = 'nullable|string';
        }

        $request->validate($rules);
        
        $categoryData = [
            'parent_id' => $request->input('parent_id'),
            'image'     => $request->input('image'),
            'is_active' => $request->input('is_active', 1),
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name"))) {
                $categoryData[$locale] = $request->input($locale);
            }
        }

        // حفظ التصنيف وتخزينه في متغير
        $category = Category::create($categoryData);

        // ربط التصنيف بالمتاجر المختارة في الجدول الوسيط (category_store)
        if ($request->has('stores')) {
            $category->stores()->sync($request->input('stores'));
        }
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إضافة التصنيف وربطه بالمتاجر بنجاح.');
    }

    public function edit(Category $category)
    {
        $locales = config('translatable.locales');
        $mainCategories = Category::whereNull('parent_id')
                                  ->where('id', '!=', $category->id)
                                  ->withTranslation()
                                  ->get();
                                  
        $stores = Store::where('is_active', true)->get();
        
        // جلب أرقام المتاجر المرتبطة حالياً بهذا التصنيف لتحديدها مسبقاً في واجهة التعديل
        $attachedStores = $category->stores->pluck('id')->toArray();
                                  
        return view('admin.categories.edit', compact('category', 'locales', 'mainCategories', 'stores', 'attachedStores'));
    }

    public function update(Request $request, Category $category)
    {
        $locales = config('translatable.locales');
        $data = $request->all();
        
        foreach ($locales as $locale) {
            if (!empty($data[$locale]['name'])) {
                $slugText = !empty($data[$locale]['slug']) ? $data[$locale]['slug'] : $data[$locale]['name'];
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $slug = trim($slug, '-');
                $data[$locale]['slug'] = mb_strtolower($slug);
            }
        }
        $request->merge($data);
        
        $rules = [
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'stores'    => 'nullable|array',
            'stores.*'  => 'exists:stores,id'
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'nullable|string|max:255';
            $rules["{$locale}.slug"] = [
                'nullable', 'string',
                Rule::unique('category_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($category->id, 'category_id')
            ];
            $rules["{$locale}.description"] = 'nullable|string';
        }

        $request->validate($rules);
        
        $categoryData = [
            'parent_id' => $request->input('parent_id'),
            'image'     => $request->input('image'),
            'is_active' => $request->has('is_active') ? $request->input('is_active') : $category->is_active,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name"))) {
                $categoryData[$locale] = $request->input($locale);
            } else {
                if ($category->hasTranslation($locale)) {
                    $category->deleteTranslation($locale);
                }
            }
        }

        $category->update($categoryData);

        // تحديث المتاجر المرتبطة: دالة sync ستقوم بحذف القديم وإضافة الجديد تلقائياً
        $category->stores()->sync($request->input('stores', []));
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تعديل التصنيف بنجاح.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح / Category deleted successfully.');
    }
}