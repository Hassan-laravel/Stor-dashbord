<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name, // يُجلب تلقائياً باللغة المطلوبة
            'slug' => $this->slug,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            // إذا كان لديك صورة للتصنيف يمكنك إضافتها هكذا:
            // 'image_url' => $this->image ? asset('storage/' . $this->image) : null,
        ];
    }
}