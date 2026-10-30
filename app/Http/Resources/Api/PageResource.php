<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
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
   
            
            // تاريخ النشر بصيغة احترافية للمقالات
            'created_at'       => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
