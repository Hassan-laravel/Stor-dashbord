<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'slug'];
}