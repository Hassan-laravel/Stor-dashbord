<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use App\Http\Resources\Api\SocialMediaResource;

class SocialMediaApiController extends Controller
{
    public function index()
    {
        $links = SocialMediaLink::where('is_active', true)->latest()->get();
        return SocialMediaResource::collection($links);
    }
}