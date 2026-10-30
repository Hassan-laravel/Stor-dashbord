<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Setting extends Model implements TranslatableContract
{
    use Translatable;

    protected $translatedAttributes = ['site_name', 'site_description', 'address'];
    protected $fillable = ['logo', 'favicon', 'contact_email', 'contact_phone', 'facebook_url', 'twitter_url', 'instagram_url'];
}