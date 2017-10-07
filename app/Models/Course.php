<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $primaryKey = 'courses_id';

    public function setCourseTypeAttribute($value){
    	switch ($value) {
    		case 1:
    			$this->attributes['course_type'] = 'video';
    			break;
    		
    		case 2:
    			$this->attributes['course_type'] = 'text';
    			break;

    		case 3:
    			$this->attributes['course_type'] = 'image';
    			break;

			case 4:
    			$this->attributes['course_type'] = 'audio';
    			break;

    		default:
    			$this->attributes['course_type'] = '';
    			break;
    	}
    }

    public function setPriceAttribute($value){
        switch ($value) {
            case 0:
                $this->attributes['price'] = 'free';
                break;
            
            case 1:
                $this->attributes['price'] = 'paid';
                break;

            default:
                $this->attributes['price'] = '';
                break;
        }
    }

    public function setLevelAttribute($value){
        switch ($value) {
            case 1:
                $this->attributes['level'] = 'beginner';
                break;
            
            case 2:
                $this->attributes['level'] = 'intermediate';
                break;

            case 3:
                $this->attributes['level'] = 'advanced';
                break;

            default:
                $this->attributes['level'] = '';
                break;
        }
    }
}