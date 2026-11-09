<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Product extends Model implements TranslatableContract
{
    use Translatable;

protected $fillable = [
        'store_id',
        'category_id',
        'product_code',
        'in_stock',
        'is_active',
        'price',
        'original_price',
        'discount_percentage',
        'image_url',
        'gallery_images',
        'affiliate_url',
        
        // أضف الحقول الجديدة هنا للسماح بحفظها
        'rating',
        'reviews_count',
        'sales_volume',
        'is_amazon_choice',
        'availability',
        'rating_distribution',
    ];
    public $translatedAttributes = [
        'title', 'slug', 'content', 'meta_title', 'meta_description', 'meta_keywords'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'in_stock' => 'boolean',
        'rating_distribution' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}