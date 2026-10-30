<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type, // 'article' or 'page'
            'title'            => $this->title,
            'slug'             => $this->slug,
            'content'          => $this->content,
            
            // بيانات SEO
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            
            // جلب التصنيف المرتبط إن وجد
            'category'         => new PostCategoryResource($this->whenLoaded('category')),
            
            // تاريخ النشر بصيغة احترافية للمقالات
            'created_at'       => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
