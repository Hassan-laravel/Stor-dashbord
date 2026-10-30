<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SocialMediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'url'  => $this->url,
            'icon' => $this->icon, // كلاس FontAwesome (مثال: fab fa-facebook)
        ];
    }
}
