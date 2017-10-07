<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $primaryKey = 'sub_categories_id';
    
    protected $fillable = ['view_count'];

    public function getSubCategoryNameAttribute($value){
        return strtolower($value);
    }

    public function setSubCategoryNameAttribute($value){
        $this->attributes['sub_category_name'] = strtolower($value);
    }
}
