<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'categories_id';

 	protected $fillable = ['view_count'];

    public function getCategoryNameAttribute($value){
        return strtolower($value);
    }

    public function setCategoryNameAttribute($value){
        $this->attributes['category_name'] = strtolower($value);
    }
}
