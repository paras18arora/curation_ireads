<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\CategoryController as CategoryController;

class HeaderComposer
{
	public function compose(View $view){
		$request = new \Illuminate\Http\Request();
	    $category = new CategoryController();
	    $req = $category->emptyFilter($request->all());
	    if(!empty($req['cat']) || !empty($req['topic'])){
	        $category->modifyViewCount($req);
	    }
	    $view->with('req',$req);
	}
}