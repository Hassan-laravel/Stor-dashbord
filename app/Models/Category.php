<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Category extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = ['parent_id', 'image', 'is_active'];
    
    // الحقول التي ستتم ترجمتها وتمريرها آلياً لجدول الترجمة
    public $translatedAttributes = ['name', 'slug', 'description'];

    // العلاقات للتصنيفات الفرعية والرئيسية
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }
}