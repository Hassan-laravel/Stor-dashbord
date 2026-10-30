<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
public function index(Request $request)
    {
        // جلب المخازن والتصنيفات لإرسالها إلى قوائم الفلترة المنسدلة
        $stores = Store::where('is_active', true)->get();
        $categories = Category::all(); // أو جلب الفعالة فقط حسب رغبتك

        // بناء الاستعلام الأساسي
        $query = Product::with(['translations', 'store', 'category'])->latest();

        // 1. الفلترة حسب الاسم (في جدول الترجمات)
        if ($request->filled('search')) {
            $query->whereTranslationLike('title', '%' . $request->search . '%');
        }

        // 2. الفلترة حسب المخزن
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // 3. الفلترة حسب التصنيف
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // جلب النتائج مع الاحتفاظ ببيانات الفلترة في روابط الصفحات (Pagination)
        $products = $query->paginate(10)->withQueryString();

        return view('admin.products.index', compact('products', 'stores', 'categories'));
    }
    public function create()
    {
        $locales = config('translatable.locales');
        $stores = Store::where('is_active', true)->get();
        $categories = Category::with('translations')->get();

        return view('admin.products.create', compact('locales', 'stores', 'categories'));
    }

    public function store(Request $request)
    {
        $locales = config('translatable.locales');
        $data = $request->all();

        // معالجة الروابط (Slugs)
        foreach ($locales as $locale) {
            if (!empty($data[$locale]['title'])) {
                $slugText = !empty($data[$locale]['slug']) ? $data[$locale]['slug'] : $data[$locale]['title'];
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $slug = trim($slug, '-');
                $data[$locale]['slug'] = mb_strtolower($slug);
            }
        }
        $request->merge($data);

        // قواعد التحقق
        $rules = [
            'store_id'     => 'required|exists:stores,id',
            'category_id'  => 'required|exists:categories,id',
            'product_code' => [
                'required',
                'string',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('store_id', $request->store_id);
                })
            ],
            'is_active'    => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.title"] = 'nullable|string|max:255';
            $rules["{$locale}.slug"]  = 'nullable|string|unique:product_translations,slug';
            $rules["{$locale}.content"] = 'nullable|string';
            $rules["{$locale}.meta_title"] = 'nullable|string|max:60';
            $rules["{$locale}.meta_description"] = 'nullable|string|max:160';
            $rules["{$locale}.meta_keywords"] = 'nullable|string';
        }

        $request->validate($rules);

        // الاتصال بالـ API لسحب البيانات بناءً على المتجر والـ Code
        $store = Store::findOrFail($request->store_id);
        $storeName = strtolower(trim($store->name));

        $apiData = match (true) {
            str_contains($storeName, 'amazon') => $this->fetchFromAmazon($request->input('product_code')),
            str_contains($storeName, 'bol')    => $this->fetchFromBol($request->input('product_code')),
            default                            => [],
        };
        // تجهيز مصفوفة الحفظ الأساسية
        $productData = [
            'store_id'       => $request->input('store_id'),
            'category_id'    => $request->input('category_id'),
            'product_code'   => $request->input('product_code'),
            'is_active'       => $request->input('is_active', 1),
            'price'          => $apiData['price'] ?? null,
            'original_price'      => $apiData['original_price'] ?? null,
            'discount_percentage' => $apiData['discount_percentage'] ?? 0,
            'image_url'      => $apiData['image_url'] ?? null,
            'gallery_images' => $apiData['gallery_images'] ?? null,
            'affiliate_url'  => $apiData['link'] ?? null,

            // إضافة البيانات الجديدة للحفظ:
            'rating'           => $apiData['rating'] ?? null,
            'reviews_count'    => $apiData['reviews_count'] ?? null,
            'sales_volume'     => $apiData['sales_volume'] ?? null,
            'is_amazon_choice' => $apiData['is_amazon_choice'] ?? false,
            'availability'     => $apiData['availability'] ?? null,
        ];

        // إضافة بيانات اللغات
        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.title"))) {
                $productData[$locale] = $request->input($locale);
            }
        }

        Product::create($productData);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج وجلب بياناته بنجاح.');
    }

    public function edit(Product $product)
    {
        $locales = config('translatable.locales');
        $stores = Store::where('is_active', true)->get();
        $categories = Category::with('translations')->get();

        return view('admin.products.edit', compact('product', 'locales', 'stores', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $locales = config('translatable.locales');
        $data = $request->all();

        foreach ($locales as $locale) {
            if (!empty($data[$locale]['title'])) {
                $slugText = !empty($data[$locale]['slug']) ? $data[$locale]['slug'] : $data[$locale]['title'];
                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slugText);
                $slug = trim($slug, '-');
                $data[$locale]['slug'] = mb_strtolower($slug);
            }
        }
        $request->merge($data);

        $rules = [
            'store_id'     => 'required|exists:stores,id',
            'category_id'  => 'required|exists:categories,id',
            'product_code' => [
                'required',
                'string',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('store_id', $request->store_id);
                })->ignore($product->id)
            ],
            'is_active'    => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.title"] = 'nullable|string|max:255';
            $rules["{$locale}.slug"] = [
                'nullable',
                'string',
                Rule::unique('product_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($product->id, 'product_id')
            ];
            $rules["{$locale}.content"] = 'nullable|string';
            $rules["{$locale}.meta_title"] = 'nullable|string|max:60';
            $rules["{$locale}.meta_description"] = 'nullable|string|max:160';
            $rules["{$locale}.meta_keywords"] = 'nullable|string';
        }

        $request->validate($rules);

        // 1. تجهيز البيانات الأساسية (التي تدخلها يدوياً فقط)
        $productData = [
            'store_id'     => $request->input('store_id'),
            'category_id'  => $request->input('category_id'),
            'product_code' => $request->input('product_code'),
            'is_active'     => $request->has('is_active') ? $request->input('is_active') : $product->is_active,
        ];

// 2. تحديث بيانات أمازون في حال تغيير رمز المنتج، أو إذا كانت البيانات الجديدة (كالسعر القديم) غير موجودة
        if ($product->product_code !== $request->input('product_code') || is_null($product->original_price)) {
            $store = Store::findOrFail($request->store_id);
            $apiData = match (strtolower(trim($store->name))) {
                'amazon' => $this->fetchFromAmazon($request->input('product_code')),
                'bol'    => $this->fetchFromBol($request->input('product_code')),
                default  => [],
            };

            // حقن البيانات الجديدة (وإذا فشل الـ API نحتفظ بالبيانات القديمة كاحتياطي)
            $productData['price']               = $apiData['price'] ?? $product->price;
            $productData['original_price']      = $apiData['original_price'] ?? $product->original_price; // الحقل الجديد
            $productData['discount_percentage'] = $apiData['discount_percentage'] ?? $product->discount_percentage; // الحقل الجديد
            $productData['image_url']           = $apiData['image_url'] ?? $product->image_url;
            $productData['gallery_images']      = $apiData['gallery_images'] ?? $product->gallery_images;
            $productData['affiliate_url']       = $apiData['link'] ?? $product->affiliate_url;
            $productData['rating']              = $apiData['rating'] ?? $product->rating;
            $productData['reviews_count']       = $apiData['reviews_count'] ?? $product->reviews_count;
            $productData['sales_volume']        = $apiData['sales_volume'] ?? $product->sales_volume;
            $productData['is_amazon_choice']    = $apiData['is_amazon_choice'] ?? $product->is_amazon_choice;
            $productData['availability']        = $apiData['availability'] ?? $product->availability;
        }

        // 3. معالجة اللغات
        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.title"))) {
                $productData[$locale] = $request->input($locale);
            } else {
                if ($product->hasTranslation($locale)) {
                    $product->deleteTranslation($locale);
                }
            }
        }

        // 4. الحفظ النهائي
        $product->update($productData);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تعديل المنتج بنجاح.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    // ==========================================
    // دوال المساعدة للاتصال بالـ APIs
    // ==========================================

    private function fetchFromAmazon($asin)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'real-time-amazon-data.p.rapidapi.com',
                'x-rapidapi-key'  => env('RAPIDAPI_KEY', '7207c2114bmshde8026f3a04791ap1b8418jsn808470addeaf'),
            ])->get('https://real-time-amazon-data.p.rapidapi.com/product-details', [
                'asin'    => $asin,
                'country' => 'NL',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // تنظيف وتجهيز السعر العشري
                // $rawPrice = $data['data']['product_price'] ?? null;
                // $cleanPrice = $rawPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawPrice)) : null;
                // // استخراج نسبة الخصم وتنظيفها من أي نصوص (مثل علامة % أو -)
                // $rawDiscount = $data['data']['savings_percentage'] ?? 0;
                // $cleanDiscount = (int) preg_replace('/[^0-9]/', '', $rawDiscount);
                $rawPrice = $data['data']['product_price'] ?? null;
                $cleanPrice = $rawPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawPrice)) : null;

                // 2. تنظيف وتجهيز السعر الأصلي (قبل الخصم)
                $rawOriginalPrice = $data['data']['product_original_price'] ?? null;
                $cleanOriginalPrice = $rawOriginalPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawOriginalPrice)) : null;

                // 3. حساب نسبة الخصم برمجياً بدقة
                $cleanDiscount = 0;
                if ($cleanOriginalPrice && $cleanPrice && $cleanOriginalPrice > $cleanPrice) {
                    // المعادلة: ((السعر الأصلي - السعر الحالي) / السعر الأصلي) * 100
                    $cleanDiscount = (int) round((($cleanOriginalPrice - $cleanPrice) / $cleanOriginalPrice) * 100);
                }
                return [
                    'price'          => $cleanPrice,
                    'original_price'      => $cleanOriginalPrice,
                    'discount_percentage' => $cleanDiscount,
                    'image_url'      => $data['data']['product_photo'] ?? null,
                    'gallery_images' => isset($data['data']['product_photos']) ? json_encode($data['data']['product_photos']) : null,
                    'link'           => "https://www.amazon.nl/dp/{$asin}?tag=" . env('AMAZON_AFFILIATE_TAG', 'your_tag-21'),

                    // البيانات الجديدة:
                    'rating'           => $data['data']['product_star_rating'] ?? null,
                    'reviews_count'    => $data['data']['product_num_ratings'] ?? null,
                    'sales_volume'     => $data['data']['sales_volume'] ?? null,
                    'is_amazon_choice' => $data['data']['is_amazon_choice'] ?? false,
                    'availability'     => $data['data']['product_availability'] ?? null,
                ];
            } else {
                Log::error("RapidAPI Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("RapidAPI Exception: " . $e->getMessage());
        }

        return ['price' => null, 'image_url' => null, 'gallery_images' => null, 'link' => null];
    }

    private function fetchFromBol($ean)
    {
        return [
            'price'            => 199.99,
            'original_price'      => 249.99,
            'discount_percentage' => 0,
            'image_url'        => 'https://media.s-bol.com/x7Qy83492834/500x500.jpg', // رابط صورة افتراضي من بول
            'gallery_images'   => null,
            'link'             => "https://www.bol.com/nl/nl/p/product/{$ean}/?Referrer=" . env('BOL_AFFILIATE_TAG', 'ADVNL-0000000'),
            'rating'           => '4.8',
            'reviews_count'    => 342,
            'availability'     => 'Op voorraad',
            'sales_volume'     => null,
            'is_amazon_choice' => false,
        ];
        // try {
        //     $url = 'https://api.apify.com/v2/actors/abotapi~bol-com-scraper/run-sync?token=' . env('APIFY_TOKEN');

        //     // بناء رابط المنتج لتمريره إلى الأداة
        //     $productUrl = "https://www.bol.com/nl/nl/p/product/{$ean}/";

        //     $response = \Illuminate\Support\Facades\Http::timeout(60)->withHeaders([
        //         'Content-Type' => 'application/json',
        //     ])->post($url, [
        //         // الهيكل القياسي لمدخلات Apify Scrapers
        //         "startUrls" => [
        //             ["url" => $productUrl]
        //         ]
        //     ]);

        //     if ($response->successful()) {
        //         $results = $response->json();

        //         // أدوات Apify تعيد البيانات عادة داخل مصفوفة، لذا نأخذ العنصر الأول [0]
        //         // تأكد من أن الهيكل الراجع يطابق هذا، أو قد تحتاج للوصول إليه عبر $results[0]
        //         $data = $results[0] ?? $results;

        //         $rawPrice = $data['price'] ?? null;
        //         $cleanPrice = $rawPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawPrice)) : null;

        //         return [
        //             'price'            => $cleanPrice,
        //             'image_url'        => $data['image'] ?? $data['main_image'] ?? null,
        //             'gallery_images'   => isset($data['images']) ? json_encode($data['images']) : null,
        //             'link'             => "{$productUrl}?Referrer=" . env('BOL_AFFILIATE_TAG', 'ADVNL-0000000'),
        //             'rating'           => $data['rating'] ?? null,
        //             'reviews_count'    => $data['reviews_count'] ?? null,
        //             'availability'     => $data['availability'] ?? $data['stock'] ?? null,
        //             'sales_volume'     => null,
        //             'is_amazon_choice' => false,
        //         ];
        //     } else {
        //         // تسجيل الخطأ لمعرفة السبب بدقة
        //         \Illuminate\Support\Facades\Log::error("Bol Apify Error: " . $response->body());
        //     }
        // } catch (\Exception $e) {
        //     \Illuminate\Support\Facades\Log::error("Bol Apify Exception: " . $e->getMessage());
        // }

        // return [];
    }
}
