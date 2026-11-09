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
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // جلب المخازن والتصنيفات لإرسالها إلى قوائم الفلترة المنسدلة
        $stores = Store::where('is_active', true)->get();
        $categories = Category::all();

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

        // جلب النتائج مع الاحتفاظ ببيانات الفلترة في روابط الصفحات
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
        $locales = ['nl', 'en', 'ar', 'tr']; // اللغات المعتمدة للترجمة والسيو

        $request->validate([
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
        ]);

        // الاتصال بالـ API لسحب البيانات بناءً على المتجر والـ Code (ASIN)
        $store = Store::findOrFail($request->store_id);
        $storeName = strtolower(trim($store->name));

        $apiData = match (true) {
            str_contains($storeName, 'amazon') => $this->fetchFromAmazon($request->input('product_code')),
            str_contains($storeName, 'bol')    => $this->fetchFromBol($request->input('product_code')),
            default                            => [],
        };

        if (empty($apiData['price']) && empty($apiData['image_url'])) {
            return back()->with('error', 'فشل جلب بيانات المنتج من المتجر، تأكد من رمز المنتج (ASIN).')->withInput();
        }

        // النصوص الأساسية الواردة باللغة الهولندية من أمازون
        $titleNl = $apiData['title'] ?? 'Unnamed Product';
        $rawDescriptionNl = $apiData['description'] ?? "Product specifications for product code: {$request->input('product_code')}";

        // جلب اسم التصنيف الأساسي للـ slug
        $category = Category::find($request->input('category_id'));
        $categoryNameOriginal = $category?->translate('en')?->name ?? $category?->name ?? 'category';

        // تخزين المنتج الأساسي مع مصفوفة توزيع التقييمات
        $product = Product::create([
            'store_id'            => $request->input('store_id'),
            'category_id'         => $request->input('category_id'),
            'product_code'        => $request->input('product_code'),
            'is_active'           => $request->input('is_active', 1),
            'price'               => $apiData['price'] ?? null,
            'original_price'      => $apiData['original_price'] ?? null,
            'discount_percentage' => $apiData['discount_percentage'] ?? 0,
            'image_url'           => $apiData['image_url'] ?? null,
            'gallery_images'      => $apiData['gallery_images'] ?? null,
            'affiliate_url'       => $apiData['link'] ?? null,
            'rating'              => $apiData['rating'] ?? null,
            'reviews_count'       => $apiData['reviews_count'] ?? null,
            'rating_distribution' => $apiData['rating_distribution'] ?? null, // <-- تخزين توزيع التقييمات
            'sales_volume'        => $apiData['sales_volume'] ?? null,
            'is_amazon_choice'    => $apiData['is_amazon_choice'] ?? false,
            'availability'        => $apiData['availability'] ?? null,
        ]);

        // حلقة تكرار لإضافة الترجمات والسيو لكل اللغات تلقائياً
        foreach ($locales as $locale) {
            if ($locale === 'nl') {
                $title = $titleNl;
                $rawDescription = $rawDescriptionNl;
                $categoryName = $categoryNameOriginal;
            } else {
                $title = $this->translateText($titleNl, 'nl', $locale);
                $rawDescription = $this->translateText($rawDescriptionNl, 'nl', $locale);
                $categoryName = $this->translateText($categoryNameOriginal, 'nl', $locale);
            }

            // تنسيق وصف السيو والكلمات المفتاحية
            $cleanMetaDescription = trim(preg_replace('/\s+/', ' ', strip_tags($rawDescription)));
            $cleanKeywords = implode(', ', array_unique(array_filter(explode(' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', $title)), function ($word) {
                return mb_strlen($word) > 2;
            })));

            // توليد الـ Slug المكون من (اسم التصنيف + أول 5 كلمات من العنوان)
            $titleWords = explode(' ', trim($title));
            $firstFiveWords = implode(' ', array_slice($titleWords, 0, 5));
            $slugBase = $categoryName . ' ' . $firstFiveWords;

            if ($locale === 'ar') {
                $slug = trim(preg_replace('/[^\p{L}\p{N}\s-]+/u', '', mb_strtolower($slugBase)));
                $slug = preg_replace('/\s+/u', '-', $slug);
            } else {
                $slug = Str::slug($slugBase);
            }

            // حفظ الترجمة والسيو في جدول product_translations
            $translation = $product->translateOrNew($locale);
            $translation->title          = $title;
            $translation->slug           = $slug;
            $translation->content        = $rawDescription;
            $translation->meta_title     = $title;
            $translation->meta_description = $cleanMetaDescription;
            $translation->meta_keywords  = $cleanKeywords;
            $translation->save();
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج وجلب بياناته مع الترجمة وتفاصيل التقييمات والسيو بنجاح.');
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
            $rules["{$locale}.meta_title"] = 'nullable|string';
            $rules["{$locale}.meta_description"] = 'nullable|string';
            $rules["{$locale}.meta_keywords"] = 'nullable|string';
        }

        $request->validate($rules);

        // 1. تجهيز البيانات الأساسية
        $productData = [
            'store_id'     => $request->input('store_id'),
            'category_id'  => $request->input('category_id'),
            'product_code' => $request->input('product_code'),
            'is_active'    => $request->has('is_active') ? $request->input('is_active') : $product->is_active,
        ];

        // 2. تحديث بيانات أمازون في حال تغيير رمز المنتج، أو إذا كانت البيانات غير موجودة
        if ($product->product_code !== $request->input('product_code') || is_null($product->original_price)) {
            $store = Store::findOrFail($request->store_id);
            $apiData = match (strtolower(trim($store->name))) {
                'amazon' => $this->fetchFromAmazon($request->input('product_code')),
                'bol'    => $this->fetchFromBol($request->input('product_code')),
                default  => [],
            };

            // حقن البيانات الجديدة مع الاحتفاظ بالقديمة كاحتياطي عند الفشل
            $productData['price']                 = $apiData['price'] ?? $product->price;
            $productData['original_price']        = $apiData['original_price'] ?? $product->original_price;
            $productData['discount_percentage']   = $apiData['discount_percentage'] ?? $product->discount_percentage;
            $productData['image_url']             = $apiData['image_url'] ?? $product->image_url;
            $productData['gallery_images']        = $apiData['gallery_images'] ?? $product->gallery_images;
            $productData['affiliate_url']         = $apiData['link'] ?? $product->affiliate_url;
            $productData['rating']                = $apiData['rating'] ?? $product->rating;
            $productData['reviews_count']         = $apiData['reviews_count'] ?? $product->reviews_count;
            $productData['rating_distribution']   = $apiData['rating_distribution'] ?? $product->rating_distribution;
            $productData['sales_volume']          = $apiData['sales_volume'] ?? $product->sales_volume;
            $productData['is_amazon_choice']      = $apiData['is_amazon_choice'] ?? $product->is_amazon_choice;
            $productData['availability']          = $apiData['availability'] ?? $product->availability;
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
    // دوال المساعدة للاتصال بالـ APIs والترجمة
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
                $productDetails = $data['data'] ?? [];

                $rawPrice = $productDetails['product_price'] ?? null;
                $cleanPrice = $rawPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawPrice)) : null;

                $rawOriginalPrice = $productDetails['product_original_price'] ?? null;
                $cleanOriginalPrice = $rawOriginalPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawOriginalPrice)) : null;

                $cleanDiscount = 0;
                if ($cleanOriginalPrice && $cleanPrice && $cleanOriginalPrice > $cleanPrice) {
                    $cleanDiscount = (int) round((($cleanOriginalPrice - $cleanPrice) / $cleanOriginalPrice) * 100);
                }

                // استخراج الوصف
                $productDescription = null;
                if (!empty($productDetails['about_product']) && is_array($productDetails['about_product'])) {
                    $productDescription = implode("<br>- ", $productDetails['about_product']);
                } elseif (!empty($productDetails['product_description'])) {
                    $productDescription = $productDetails['product_description'];
                } elseif (!empty($productDetails['about_this_item']) && is_array($productDetails['about_this_item'])) {
                    $productDescription = implode("<br>- ", $productDetails['about_this_item']);
                }

                // التقاط توزيع التقييمات من المستوى الرئيسي أو الفرعي
                $ratingDistribution = $data['rating_distribution'] ?? $productDetails['rating_distribution'] ?? null;

                return [
                    'title'               => $productDetails['product_title'] ?? null,
                    'price'               => $cleanPrice,
                    'original_price'      => $cleanOriginalPrice,
                    'discount_percentage' => $cleanDiscount,
                    'description'         => $productDescription ? "- " . $productDescription : null,
                    'image_url'           => $productDetails['product_photo'] ?? null,
                    'gallery_images'      => isset($productDetails['product_photos']) ? json_encode($productDetails['product_photos']) : null,
                    'link'                => "https://www.amazon.nl/dp/{$asin}?tag=" . env('AMAZON_AFFILIATE_TAG', 'reviewstop-21'),
                    'rating'              => $productDetails['product_star_rating'] ?? null,
                    'reviews_count'       => $productDetails['product_num_ratings'] ?? null,
                    'rating_distribution' => $ratingDistribution,
                    'sales_volume'        => $productDetails['sales_volume'] ?? null,
                    'is_amazon_choice'    => $productDetails['is_amazon_choice'] ?? false,
                    'availability'        => $productDetails['product_availability'] ?? null,
                ];
            } else {
                Log::error("RapidAPI Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("RapidAPI Exception: " . $e->getMessage());
        }

        return ['price' => null, 'image_url' => null, 'gallery_images' => null, 'link' => null, 'description' => null, 'rating_distribution' => null];
    }

    private function fetchFromBol($ean)
    {
        return [
            'title'               => 'Bol Product',
            'price'               => 199.99,
            'original_price'      => 249.99,
            'discount_percentage' => 0,
            'description'         => 'Bol description specifications.',
            'image_url'           => 'https://media.s-bol.com/x7Qy83492834/500x500.jpg',
            'gallery_images'      => null,
            'link'                => "https://www.bol.com/nl/nl/p/product/{$ean}/?Referrer=" . env('BOL_AFFILIATE_TAG', 'ADVNL-0000000'),
            'rating'              => '4.8',
            'reviews_count'       => 342,
            'rating_distribution' => null,
            'availability'        => 'Op voorraad',
            'sales_volume'        => null,
            'is_amazon_choice'    => false,
        ];
    }

    private function translateText($text, $sourceLang, $targetLang)
    {
        if (empty($text)) return '';

        try {
            $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . $sourceLang . "&tl=" . $targetLang . "&dt=t&q=" . urlencode($text);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $result = json_decode($response, true);
                $translatedText = '';

                if (isset($result[0])) {
                    foreach ($result[0] as $segment) {
                        $translatedText .= $segment[0] ?? '';
                    }
                }

                return !empty($translatedText) ? $translatedText : $text;
            }
        } catch (\Exception $e) {
            Log::error("Translation Exception: " . $e->getMessage());
        }

        return $text;
    }

    public function bulkDelete(Request $request)
    {
        $actionType = $request->input('action_type');

        if ($actionType === 'selected') {
            $productIds = $request->input('product_ids');

            if (empty($productIds)) {
                return back()->with('error', 'الرجاء تحديد منتج واحد على الأقل من الجدول للحذف.');
            }

            Product::whereIn('id', $productIds)->delete();

            return back()->with('success', 'تم حذف المنتجات المحددة بنجاح.');
        }

        if ($actionType === 'conditional') {
            $storeId = $request->input('cond_store_id');
            $categoryId = $request->input('cond_category_id');

            if (empty($storeId) || empty($categoryId)) {
                return back()->with('error', 'عذراً، يجب تحديد نوع المخزن ونوع التصنيف معاً لإتمام عملية الحذف الجماعي المشروط.');
            }

            $deletedCount = Product::where('store_id', $storeId)
                                   ->where('category_id', $categoryId)
                                   ->delete();

            if ($deletedCount > 0) {
                return back()->with('success', "تم بنجاح حذف {$deletedCount} منتجاً المرتبطين بالمتجر والتصنيف المحددين.");
            }

            return back()->with('error', 'لا توجد أي منتجات تطابق هذا المتجر والتصنيف للحذف.');
        }

        return back()->with('error', 'إجراء غير معروف.');
    }
}