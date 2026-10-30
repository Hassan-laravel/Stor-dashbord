<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class PostCategory extends Model implements TranslatableContract
{
    use Translatable;

    protected $translatedAttributes = ['name', 'slug'];
    protected $fillable = ['is_active'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}