<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'site_name'        => $this->site_name,
            'site_description' => $this->site_description,
            'address'          => $this->address,
            'logo_url'         => $this->logo ? asset('storage/' . $this->logo) : null,
            'favicon_url'      => $this->favicon ? asset('storage/' . $this->favicon) : null,
            'contact_email'    => $this->contact_email,
            'contact_phone'    => $this->contact_phone,
        ];
    }
}