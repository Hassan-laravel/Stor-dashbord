<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BolProductController extends Controller
{
    // عرض قائمة منتجات بول
    public function index(Request $request)
    {
        $stores = Store::where('is_active', true)->get();
        $categories = Category::all();

        $query = Product::with(['translations', 'store', 'category'])
            ->whereHas('store', function($q) {
                $q->where('name', 'like', '%bol%');
            })
            ->latest();

        if ($request->filled('search')) {
            $query->whereTranslationLike('title', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(10)->withQueryString();

        return view('admin.bol-products.index', compact('products', 'stores', 'categories'));
    }

    public function create()
    {
        $locales = ['nl', 'en', 'ar', 'tr'];
        $stores = Store::where('is_active', true)->where('name', 'like', '%bol%')->get();
        $categories = Category::with('translations')->get();

        return view('admin.bol-products.create', compact('locales', 'stores', 'categories'));
    }

    // إضافة منتج جديد من بول مباشرة باستخدام كود الـ EAN
    public function store(Request $request)
    {
        $locales = ['nl', 'en', 'ar', 'tr'];

        $request->validate([
            'store_id'     => 'required|exists:stores,id',
            'category_id'  => 'required|exists:categories,id',
            'product_code' => [
                'required',
                'string',
            ],
            'is_active'    => 'boolean',
        ]);

        $ean = trim($request->input('product_code'));

        $exists = Product::where('store_id', $request->store_id)
            ->where('product_code', $ean)
            ->exists();

        if ($exists) {
            return back()->with('error', 'هذا المنتج موجود مسبقاً في هذا المتجر.')->withInput();
        }

        $apiData = $this->fetchFromBolApi($ean);

        if (empty($apiData['price']) && empty($apiData['image_url'])) {
            return back()->with('error', 'فشل جلب بيانات المنتج من Bol.com، تأكد من صحة كود الـ EAN.')->withInput();
        }

        $titleNl = $apiData['title'] ?? 'Bol Product';
        $rawDescriptionNl = $apiData['description'] ?? 'Bol product specifications and description.';

        $category = Category::find($request->input('category_id'));
        $categoryNameOriginal = $category?->translate('en')?->name ?? $category?->name ?? 'category';

        $product = Product::create([
            'store_id'            => $request->input('store_id'),
            'category_id'         => $request->input('category_id'),
            'product_code'        => $ean,
            'is_active'           => $request->input('is_active', 1),
            'price'               => $apiData['price'] ?? null,
            'original_price'      => $apiData['original_price'] ?? null,
            'discount_percentage' => $apiData['discount_percentage'] ?? 0,
            'image_url'           => $apiData['image_url'] ?? null,
            'gallery_images'      => $apiData['gallery_images'] ?? null,
            'affiliate_url'       => $apiData['link'] ?? null,
            'rating'              => $apiData['rating'] ?? 4.5,
            'reviews_count'       => $apiData['reviews_count'] ?? 10,
            'rating_distribution' => $apiData['rating_distribution'] ?? null,
            'sales_volume'        => $apiData['sales_volume'] ?? null,
            'is_amazon_choice'    => false,
            'availability'        => $apiData['availability'] ?? 'Op voorraad',
        ]);

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

            $cleanMetaDescription = trim(preg_replace('/\s+/', ' ', strip_tags($rawDescription)));
            $cleanKeywords = implode(', ', array_unique(array_filter(explode(' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', $title)), function ($word) {
                return mb_strlen($word) > 2;
            })));

            $titleWords = explode(' ', trim($title));
            $firstFiveWords = implode(' ', array_slice($titleWords, 0, 5));
            $slugBase = $categoryName . ' ' . $firstFiveWords;

            if ($locale === 'ar') {
                $slug = trim(preg_replace('/[^\p{L}\p{N}\s-]+/u', '', mb_strtolower($slugBase)));
                $slug = preg_replace('/\s+/u', '-', $slug);
            } else {
                $slug = Str::slug($slugBase);
            }

            $translation = $product->translateOrNew($locale);
            $translation->title            = $title;
            $translation->slug             = $slug;
            $translation->content          = $rawDescription;
            $translation->meta_title       = $title;
            $translation->meta_description = $cleanMetaDescription;
            $translation->meta_keywords    = $cleanKeywords;
            $translation->save();
        }

        return redirect()->route('admin.bol-products.index')
            ->with('success', 'تم إضافة منتج بول وترجمته بنجاح.');
    }

    // عرض صفحة التعديل
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $stores = Store::where('is_active', true)->get();
        $categories = Category::with('translations')->get();
        $locales = ['nl', 'en', 'ar', 'tr'];

        return view('admin.bol-products.edit', compact('product', 'stores', 'categories', 'locales'));
    }

    // تحديث المنتج
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'is_active'   => 'boolean',
        ]);

        $product->update([
            'store_id'    => $request->input('store_id'),
            'category_id' => $request->input('category_id'),
            'is_active'   => $request->input('is_active', $product->is_active),
            'price'       => $request->input('price', $product->price),
        ]);

        return redirect()->route('admin.bol-products.index')
            ->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.bol-products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    // ==========================================
    // دوال الاتصال الرسمية مع Bol.com API
    // ==========================================

    private function getBolAccessToken()
    {
        return Cache::remember('bol_access_token', now()->addMinutes(4), function () {
            try {
                $clientId = env('BOL_CLIENT_ID');
                $clientSecret = env('BOL_CLIENT_SECRET');

                $response = Http::withBasicAuth($clientId, $clientSecret)
                    ->withHeaders([
                        'Accept' => 'application/json',
                    ])
                    ->post('https://login.bol.com/token?grant_type=client_credentials');

                if ($response->successful()) {
                    return $response->json()['access_token'] ?? null;
                } else {
                    Log::error("Bol Token Error: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Bol Token Exception: " . $e->getMessage());
            }

            return null;
        });
    }
public function fetchFromBolApi($ean)
    {
        $token = $this->getBolAccessToken();

        if (!$token) {
            return [];
        }

        try {
            // 1. جلب البيانات الأساسية للمنتج
            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'nl',
                ])
                ->get("https://api.bol.com/marketing/catalog/v1/products/{$ean}", [
                    'country-code'          => 'NL',
                    'include-image'         => 'true',
                    'include-offer'         => 'true',
                    'include-rating'        => 'true',
                ]);

            if (!$response->successful()) {
                Log::error("Bol API Data Error: " . $response->body());
                if ($response->status() === 401) {
                    Cache::forget('bol_access_token');
                }
                return [];
            }

            $data = $response->json();

            $price = $data['offer']['price'] ?? 0.00;
            $originalPrice = $data['offer']['strikethroughPrice'] ?? null;
            
            $discountPercentage = 0;
            if ($originalPrice && $originalPrice > $price) {
                $discountPercentage = round((($originalPrice - $price) / $originalPrice) * 100);
            }

            $imageUrl = $data['image']['url'] ?? null;
           // رابط المنتج الأساسي من بول
            $productUrl = $data['url'] ?? "https://www.bol.com/nl/nl/p/product/{$ean}";
            
            // جلب كود الشريك مباشرة من الـ .env الذي قمت بإعداده مسبقاً
            $partnerId = env('BOL_AFFILIATE_TAG', '1556717'); 

            // بناء الرابط الرسمي لشبكة بول بالشكل الصحيح والمطابق لصورة الشراكة
            $affiliateUrl = "https://partner.bol.com/click/click?p=2&t=url&s={$partnerId}&f=TXL&url=" . urlencode($productUrl);
            // 2. جلب ألبوم الصور الكامل (Media Endpoint) بناءً على التوثيق الرسمي
          // 1. الصورة الأساسية دائماً كبداية مضمونة
// جلب ألبوم الصور بحيث نأخذ المقاسات الكبيرة والواضحة فقط ونستبعد المصغرة جداً
            $galleryImages = [];
            if ($imageUrl) {
                $galleryImages[] = $imageUrl;
            }

            try {
                $mediaResponse = Http::withToken($token)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get("https://api.bol.com/marketing/catalog/v1/products/{$ean}/media");

                if ($mediaResponse->successful()) {
                    $mediaData = $mediaResponse->json();
                    
                    $extractedUrls = [];
                    array_walk_recursive($mediaData, function ($value, $key) use (&$extractedUrls) {
                        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) && preg_match('/\.(jpg|jpeg|png|webp)/i', $value)) {
                            // استبعاد الاحجام الصغيرة جداً (مثل 48x، 100x، 124x) والاكتفاء بالصور الكبيرة أو الأصلية
                            if (!preg_match('/(48x|100x|124x|168x)/i', $value)) {
                                $extractedUrls[] = $value;
                            }
                        }
                    });

                    if (!empty($extractedUrls)) {
                        $galleryImages = array_merge($galleryImages, $extractedUrls);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Bol Media Exception: " . $e->getMessage());
            }

            // إزالة الروابط المكررة والاحتفاظ بنسخة فريدة ونظيفة
            $galleryImages = array_values(array_unique($galleryImages));
            // 3. جلب توزيع التقييمات وعدد المراجعات (Ratings Endpoint)
// 3. جلب توزيع التقييمات وعدد المراجعات (Ratings Endpoint) وتنسيقها على شكل أمازون
            $rating = $data['rating'] ?? 4.5;
            $reviewsCount = 0;
            $ratingDistribution = null;

            try {
                $ratingsResponse = Http::withToken($token)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get("https://api.bol.com/marketing/catalog/v1/products/{$ean}/ratings");

                if ($ratingsResponse->successful()) {
                    $ratingsData = $ratingsResponse->json();
                    $rating = $ratingsData['averageRating'] ?? $rating;
                    
                    if (!empty($ratingsData['ratings']) && is_array($ratingsData['ratings'])) {
                        $amazonStyleDistribution = [];
                        
                        foreach ($ratingsData['ratings'] as $rateItem) {
                            $star = $rateItem['rating'] ?? null;
                            $count = $rateItem['count'] ?? 0;
                            
                            if ($star !== null) {
                                // تخزين المفتاح كـ "رقم النجمة" والقيمة كـ "العدد" تماماً مثل أمازون {"1":7, "2":10, ...}
                                $amazonStyleDistribution[(string)$star] = $count;
                                $reviewsCount += $count;
                            }
                        }
                        
                        // تخزينها كـ JSON بالشكل المطلوب للفرونت إند
                        $ratingDistribution = json_encode($amazonStyleDistribution);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Bol Ratings Exception: " . $e->getMessage());
            }

            if ($reviewsCount === 0) {
                $reviewsCount = 5;
            }
            return [
                'title'               => $data['title'] ?? 'Bol Product ' . $ean,
                'price'               => $price,
                'original_price'      => $originalPrice,
                'discount_percentage' => $discountPercentage,
                'description'         => $data['description'] ?? null,
                'image_url'           => $imageUrl,
                'gallery_images'      => json_encode($galleryImages),
                'link'                => $affiliateUrl,
                'rating'              => $rating,
                'reviews_count'       => $reviewsCount,
                'rating_distribution' => $ratingDistribution,
                'availability'        => $data['offer']['deliveryDescription'] ?? 'Op voorraad',
            ];

        } catch (\Exception $e) {
            Log::error("Bol API Exception: " . $e->getMessage());
        }

        return [];
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

}