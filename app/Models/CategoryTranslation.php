<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    // جدول الترجمة لا يحتاج إلى created_at و updated_at
    public $timestamps = false;
    
    protected $fillable = ['name', 'slug', 'description'];
}