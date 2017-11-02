<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
class tutorialController extends Controller
{
    public function display($id,$token)
    {
        $pieces=explode(" ",$id);
      
        $rating=4.9;
        $skip=$token*10;
        $tutorials=new \ArrayObject();
        $data=array();
        if(sizeof($pieces))
        $take=10/sizeof($pieces);
        else
        $take=0;


        for($j=0;$j<sizeof($pieces);$j++) {
    
           $items=DB::table('tutorials')
           ->where('tags', 'LIKE', '%'.$pieces[$j].' '.'%')
           ->take($take)
           ->skip($skip)        
           ->get();
          
       
          
        $tutorials=$items;
        
       
        for($i=0;$i<sizeof($tutorials);$i++)
        {

          
        	if(isset($tutorials[$i]->id))
     		$id1=$tutorials[$i]->id;
     	    else
     		$id1="";
     	    if(isset($tutorials[$i]->title))
     		$title=$tutorials[$i]->title;
     	    else
     		$title="";
     	    if(isset($tutorials[$i]->description))
     		$description=$tutorials[$i]->description;
     	    else
     		$description="";
     	    if(isset($tutorials[$i]->tags))
     		$keywords=$tutorials[$i]->tags;
     	    else
     		$keywords="";
     	    if(isset($tutorials[$i]->no_of_files))
     		$no_of_files=$tutorials[$i]->no_of_files;
     	    else
     		$no_of_files="";
     		$source="created";
     	$data1=array("id"=>$id1,"title"=>$title,"description"=>$description,"keywords"=>$keywords,"source"=>$source,"rating"=>$rating,"type"=>"article","no_of_files"=>$no_of_files,"imagesrc"=>'');
     	array_push($data,$data1);
      $rating = $rating - 0.15;
        }
    }
    return json_encode($data);

    }
}