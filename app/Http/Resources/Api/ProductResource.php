<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
           'title'            => $this->title, // سيجلب الاسم حسب اللغة المحددة تلقائياً
            'slug'             => $this->slug,
            'description'      => $this->content,
            'price'            => (float) $this->price,
            'original_price'      => $this->original_price ? (float) $this->original_price : null,
            'discount_percentage' => (int) $this->discount_percentage,
            'image_url'        => $this->image_url,
            'gallery_images'   => $this->gallery_images ? json_decode($this->gallery_images) : [],
            'affiliate_url'   => $this->affiliate_url,
            'rating'           => (float) $this->rating,
            'reviews_count'    => (int) $this->reviews_count,
            'sales_volume'     => (int) $this->sales_volume,
            'is_amazon_choice' => (bool) $this->is_amazon_choice,
            
            // جلب العلاقات
            'category'         => new CategoryResource($this->whenLoaded('category')),
            'store'            => new StoreResource($this->whenLoaded('store')),
        ];
    }
}