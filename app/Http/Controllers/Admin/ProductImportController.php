<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    /**
     * عرض صفحة الجلب الجماعي المستقلة
     */
    public function showBulkForm()
    {
        $stores = Store::all();
        $categories = Category::all();

        return view('admin.products.bulk-import', compact('stores', 'categories'));
    }

    /**
     * تنفيذ عملية الجلب والتخزين بالجملة مع الترجمة والسيو المُحسّن
     */
    public function bulkImport(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'category_id_node' => 'required|string',
            'store_id'         => 'required|exists:stores,id',
            'category_id'      => 'required|exists:categories,id',
            'limit'            => 'required|integer|min:1|max:100',
        ]);

        $amazonCategoryId = trim($request->input('category_id_node'));
        $limit             = (int) $request->input('limit', 10);
        $storeId           = $request->input('store_id');
        $categoryId        = $request->input('category_id');

        $rapidApiKey  = env('RAPIDAPI_KEY');
        $rapidApiHost = 'real-time-amazon-data.p.rapidapi.com';

        // اللغات المطلوبة للتخزين والترجمة
        $languages = ['nl', 'en', 'ar', 'tr'];

        try {
            $searchResponse = Http::withHeaders([
                'x-rapidapi-host' => $rapidApiHost,
                'x-rapidapi-key'  => $rapidApiKey,
            ])->get("https://{$rapidApiHost}/search", [
                'category' => $amazonCategoryId,
                'country'  => 'NL',
            ]);

            if ($searchResponse->failed()) {
                return back()->with('error', 'فشل الاتصال بـ API للبحث عن المنتجات.');
            }

            $searchData = $searchResponse->json('data.products') ?? [];
            $importedCount = 0;

            $categoryNameOriginal = Category::find($categoryId)?->translate('en')?->name ?? Category::find($categoryId)?->name ?? 'category';

            foreach (array_slice($searchData, 0, $limit) as $item) {
                $asin = $item['asin'] ?? null;
                if (!$asin) continue;

                if (Product::where('product_code', $asin)->exists()) {
                    continue;
                }

                $amazonData = $this->fetchFromAmazon($asin);

                if ($amazonData['price'] === null && $amazonData['image_url'] === null) {
                    continue;
                }

                $titleNl = $item['product_title'] ?? 'Unnamed Product';
                $rawDescriptionNl = $amazonData['description'] ?? "Product specifications for Amazon ASIN: {$asin}";

                // تخزين المنتج الأساسي مع مصفوفة التقييمات
                $product = Product::create([
                    'product_code'        => $asin,
                    'store_id'            => $storeId,
                    'category_id'         => $categoryId,
                    'price'               => $amazonData['price'],
                    'original_price'      => $amazonData['original_price'],
                    'discount_percentage' => $amazonData['discount_percentage'],
                    'rating'              => $amazonData['rating'] ?? 4.5,
                    'reviews_count'       => $amazonData['reviews_count'] ?? rand(10, 200),
                    'sales_volume'        => $amazonData['sales_volume'],
                    'rating_distribution' => $amazonData['rating_distribution'], // تخزين مصفوفة التقييمات هنا بنجاح
                    'image_url'           => $amazonData['image_url'],
                    'gallery_images'      => $amazonData['gallery_images'],
                    'affiliate_url'       => $amazonData['link'],
                    'is_active'           => true,
                ]);

                // حلقة تكرار لإضافة الترجمات لكل اللغات (nl, en, ar, tr)
                foreach ($languages as $locale) {
                    if ($locale === 'nl') {
                        $title = $titleNl;
                        $rawDescription = $rawDescriptionNl;
                        $categoryName = $categoryNameOriginal;
                    } else {
                        $title = $this->translateText($titleNl, 'nl', $locale);
                        $rawDescription = $this->translateText($rawDescriptionNl, 'nl', $locale);
                        $categoryName = $this->translateText($categoryNameOriginal, 'nl', $locale);
                    }

                    // تنسيق السيو بدون أسطر أو مسافات طويلة
                    $cleanMetaDescription = trim(preg_replace('/\s+/', ' ', strip_tags($rawDescription)));
                    $cleanKeywords = implode(', ', array_unique(array_filter(explode(' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', $title)), function ($word) {
                        return mb_strlen($word) > 2;
                    })));

                    // استخراج أول 5 كلمات من العنوان المترجم دمجاً مع اسم التصنيف للـ slug
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

                $importedCount++;
                usleep(500000); // نصف ثانية فاصل زمني لتجنب الضغط
            }

            return back()->with('success', "تم بنجاح جلب وتخزين {$importedCount} منتجاً مع الترجمة وتفاصيل التقييمات وبيانات السيو!");
        } catch (\Exception $e) {
            Log::error("Bulk Import Exception: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    private function fetchFromAmazon($asin)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'real-time-amazon-data.p.rapidapi.com',
                'x-rapidapi-key'  => env('RAPIDAPI_KEY'),
            ])->get('https://real-time-amazon-data.p.rapidapi.com/product-details', [
                'asin'    => $asin,
                'country' => 'NL',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // الاستجابة الرئيسية للـ API تحتوي على البيانات في المستوى الجذري وبعضها في مفتاح data
                $productDetails = $data['data'] ?? [];

                $rawPrice = $productDetails['product_price'] ?? null;
                $cleanPrice = $rawPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawPrice)) : null;

                $rawOriginalPrice = $productDetails['product_original_price'] ?? null;
                $cleanOriginalPrice = $rawOriginalPrice ? (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawOriginalPrice)) : null;

                $cleanDiscount = 0;
                if ($cleanOriginalPrice && $cleanPrice && $cleanOriginalPrice > $cleanPrice) {
                    $cleanDiscount = (int) round((($cleanOriginalPrice - $cleanPrice) / $cleanOriginalPrice) * 100);
                }

                // استخراج الوصف من about_product
                $productDescription = null;
                if (!empty($productDetails['about_product']) && is_array($productDetails['about_product'])) {
                    $productDescription = implode("<br>- ", $productDetails['about_product']);
                } elseif (!empty($productDetails['product_description'])) {
                    $productDescription = $productDetails['product_description'];
                } elseif (!empty($productDetails['about_this_item']) && is_array($productDetails['about_this_item'])) {
                    $productDescription = implode("<br>- ", $productDetails['about_this_item']);
                }

                // التقاط rating_distribution من المستوى الرئيسي للاستجابة ($data) أو من ($productDetails)
                $ratingDistribution = $data['rating_distribution'] ?? $productDetails['rating_distribution'] ?? null;

                return [
                    'price'               => $cleanPrice,
                    'original_price'      => $cleanOriginalPrice,
                    'discount_percentage' => $cleanDiscount,
                    'description'         => $productDescription ? "- " . $productDescription : null,
                    'image_url'           => $productDetails['product_photo'] ?? null,
                    'gallery_images'      => isset($productDetails['product_photos']) ? json_encode($productDetails['product_photos']) : null,
                    'link'                => "https://www.amazon.nl/dp/{$asin}?tag=" . env('AMAZON_AFFILIATE_TAG', 'reviewstop-21'),
                    'rating'              => $productDetails['product_star_rating'] ?? null,
                    'reviews_count'       => $productDetails['product_num_ratings'] ?? null,
                    'sales_volume'        => $productDetails['sales_volume'] ?? null,
                    'is_amazon_choice'    => $productDetails['is_amazon_choice'] ?? false,
                    'availability'        => $productDetails['product_availability'] ?? null,
                    'rating_distribution' => $ratingDistribution, // تم التقاطها بدقة من الجذر الرئيسي
                ];
            }
        } catch (\Exception $e) {
            Log::error("RapidAPI Exception: " . $e->getMessage());
        }

        return ['price' => null, 'image_url' => null, 'gallery_images' => null, 'link' => null, 'description' => null, 'rating_distribution' => null];
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