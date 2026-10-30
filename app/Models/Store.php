<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'domain', 'is_active'];

    // علاقة متعدد إلى متعدد مع التصنيفات
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // علاقة واحد إلى متعدد مع المنتجات
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}