<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Curl;
class HomeController extends Controller
{
    public function display()
    {
    	    $resp = Curl::to('https://newsapi.org/v1/articles')
            ->withData( array(
		    'source' => 'reuters',
		    'sortBy' => 'top',
            'apiKey' => 'f673162dd55d425487b36d8de9ce4ffe'
		) )
        ->returnResponseObject()
        ->asJson()
        ->get();



      
		
	$res=$resp->content;
        return view('pages.home')->with(['news'=>$res->articles]);
    }
}
