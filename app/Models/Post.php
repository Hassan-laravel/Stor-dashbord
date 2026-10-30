<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;

    protected $translatedAttributes = ['title', 'slug', 'content', 'meta_title', 'meta_description'];
    
    protected $fillable = [
        'post_category_id',
        'type', // 'article' or 'page'
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }
}