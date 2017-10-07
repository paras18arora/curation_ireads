<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Course;


class CategoryController extends Controller
{
    protected function display(Request $request){

    	$courses = '';
        $req = $this->emptyFilter($request->all());
        
    	if(!empty($req['cat']) || !empty($req['topic'])){
    		$this->modifyViewCount($req);
        }
	    $courses = $this->getCourses($req);
        $courses = $this->paginateCourse($courses,10,$request);
    	return view('pages.category')->with(['courses' => $courses,'req' => $req]);
    }

    public function modifyViewCount($inputs){
    	if(!empty($inputs['topic'])){
    		SubCategory::whereSubCategoryName(str_replace('-',' ',$inputs['topic']))->increment('view_count');
    	}
    	else{
    		Category::whereCategoryName(str_replace('-',' ',$inputs['cat']))->increment('view_count');
    	}
    }

    public function getFilters(){
    	$category = Category::orderBy('view_count', 'desc')->get();
    	$sub_category = SubCategory::orderBy('view_count', 'desc')->get();
    	$filters[0] = $category;
    	$filters[1] = $sub_category;
    	return $filters;
    }

    public function emptyFilter($req){
        if(!isset($req['cat'])){
            $req['cat'] = ''; 
        }
        if(!isset($req['topic'])){
            $req['topic'] = ''; 
        }
        if(!isset($req['course_type'])){
            $req['course_type'] = ''; 
        }
        if(!isset($req['price'])){
            $req['price'] = ''; 
        }
        if(!isset($req['level'])){
            $req['level'] = ''; 
        }
        if(!isset($req['q'])){
            $req['q'] = ''; 
        }
        return $req;
    }

    public function getCourses($inputs){
    	$courses = '';
    	if(!empty($inputs['topic'])){
    		$courses = Course::join('sub_categories','courses.sub_cat','=','sub_categories.sub_categories_id')
    							->join('categories','sub_categories.categories_id','=','categories.categories_id')
    							->whereSubCategoryName(str_replace('-',' ',$inputs['topic']))
    							->get();
    	}
    	else if(!empty($inputs['cat'])){
    		$courses = Course::join('sub_categories','courses.sub_cat','=','sub_categories.sub_categories_id')
    							->join('categories','sub_categories.categories_id','=','categories.categories_id')
    							->whereCategoryName(str_replace('-',' ',$inputs['cat']))
    							->get();
    	}
        else{
            $courses = Course::all();
        }
        $courses = $this->filterCourse($courses,$inputs);
    	return $courses;
    }

    public function filterCourse($courses,$req){
        $filtered_courses = $courses;
        if(!empty($req['course_type'])){
            $filtered_courses = $filtered_courses->filter(function ($value, $key) use ($req) {
                return $value->course_type == $req['course_type'];
            });
        }
        if(!empty($req['price'])){
            $filtered_courses = $filtered_courses->filter(function ($value, $key) use ($req) {
                return $value->price == $req['price'];
            });
        }
        if(!empty($req['level'])){
            $filtered_courses = $filtered_courses->filter(function ($value, $key) use ($req) {
                return $value->level == $req['level'];
            });
        }
        return $filtered_courses;
    }

    public function paginateCourse($collection,$perPage,$request){
        $page = Input::get('page', 1);
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice($collection->toArray(), $offset, $perPage, true), // Only grab the items we need
            count($collection), // Total items
            $perPage, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // We need this so we can keep all old query parameters from the url
        );
    }
}
