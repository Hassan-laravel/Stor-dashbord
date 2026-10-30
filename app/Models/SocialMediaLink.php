<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class SocialMediaLink extends Model implements TranslatableContract
{
    use Translatable;

    protected $translatedAttributes = ['name', 'url'];
    protected $fillable = ['icon', 'is_active'];
}