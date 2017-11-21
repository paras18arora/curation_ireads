<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DetailsController extends Controller
{

    protected function display(Request $req){
    	$title=$req->title;
    	$description=$req->description;
    	$rating=$req->rating;
        $videoid=$req->videoid;
        $id=$req->id;
        $source=$req->source;
    	$imagesrc=$req->imagesrc;
    	$author=$req->author;
        $no_of_files=$req->no_of_files;
        $link=$req->link;
        $type=$req->type;
        $isbn=$req->isbn;
        $recommend_data=array();
        $Arr = json_decode($req->dataa, true); 
        $i=0;
        foreach ($Arr as $key) {
            if($i==10)
                break;
            if($key['title']==$title)
                continue;
            else 
                array_push($recommend_data, $key);
            $i++;
        }

    	return view('pages.details')->with(['title' => $title,'videoid' => $videoid,'isbn' => $isbn,'id' => $id,'source' => $source,'type1' => $type,'recommend_data' => $recommend_data,'data' => $Arr,'description' => $description,'rating'=>$rating,'imagesrc' => $imagesrc,'author'=>$author,'link' => $link,'no_of_files'=>$no_of_files]);
    }

    protected function play(Request $req){
        $type1=$req->type1;
       
        if($type1=='created_article' && $req->no_of_files!=0)
        {
        $id=$req->id;
        $id=substr($id,0,-1);
        $file=array();
        $link=$req->link;
        $link1='/tutorials/'.$id.'/';
        $title=$req->title;
       
        $imagesrc=$req->imagesrc;
        for($i=0;$i<$req->no_of_files;$i++){
            $file[$i] = file_get_contents($link1.($i+1).'.html', FILE_USE_INCLUDE_PATH);
        }
        return view('pages.player')->with(['file'=>$file,'val'=>'other','id'=>$id,'title'=>$title,'imagesrc'=>$imagesrc,'type1'=>$type1,'totalfiles'=>$req->no_of_files,'no_of_files'=>$req->no_of_files]);
    }
        else if($type1=='article')
        {
        $link=$req->link;
        $link1=$link;
        $title=$req->title;
        
        $imagesrc=$req->imagesrc;
        //$link = "https://medium.com/@producthunt/8-programs-to-help-you-learn-no-school-required-d6658612bf2e?format=json";
        $link=substr($link,0,-1);
        $link = $link. '?format=json';
        $curl2 = curl_init();  // for accessing each article content based on the link we get from above result
        curl_setopt_array($curl2, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $link,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_SSL_VERIFYPEER => 0  ,
            CURLOPT_FOLLOWLOCATION => true
            ));

        $article = curl_exec($curl2);
        $post = substr($article,16);
        $post1 = json_decode($post,true);
       
      
   
        curl_close($curl2);


    	return view('pages.player')->with(['post'=>$post,'link'=>$link,'val'=>'medium','title'=>$title,'imagesrc'=>$imagesrc,'no_of_files'=>0,'totalfiles'=>1,'type1'=>$type1]);
    }
    else if($type1=='database_article')
        {
        $link=$req->link;
        $id=$req->id;
        $id=substr($id,0,-1);
        $title=$req->title;
        $source=$req->source;
        $imagesrc=$req->imagesrc;
 
        if($source=='entrepreneur.com'){
        $link1="b".$id.".php";

       
        $file = file_get_contents('business_articles/'.$link1, FILE_USE_INCLUDE_PATH);
        }
        else{
        $link1="a".$id.".php";
      
        $file = file_get_contents('articles/'.$link1, FILE_USE_INCLUDE_PATH);
        }
        
        return view('pages.player')->with(['source'=>$source,'file'=>$file,'val'=>'other','id'=>$id,'link'=>$link,'title'=>$title,'no_of_files'=>0,'totalfiles'=>1,'imagesrc'=>$imagesrc,'type1'=>$type1]);
    }
    else if($type1=='book')
        {

        $isbn=$req->isbn;
        $isbn=str_replace(" ","",$isbn);
      
        $title=$req->title;
    

        
       
 
        return view('pages.player')->with(['isbn'=>$isbn,'no_of_files'=>0,'totalfiles'=>1,'title'=>$title,'type1'=>$type1]);
    }
    else if($type1=='video')
        {

        $videoid=$req->videoid;
        $videoid=str_replace(" ","",$videoid);
        $description=$req->description;
      
        $title=$req->title;
     

   

       
 
        return view('pages.player')->with(['videoid'=>$videoid,'no_of_files'=>0,'totalfiles'=>1,'title'=>$title,'description'=>$description,'type1'=>$type1]);
    }


    }
    protected function news(Request $req)
    {

        $url=$req->link;
        $image=$req->imagesrc;
        
        $description=$req->description;
      
        $title=$req->title;
       

   

       
 
        return view('pages.news')->with(['title'=>$title,'no_of_files'=>0,'totalfiles'=>1,'description'=>$description,'image'=>$image,'val'=>'','file'=>'','url'=>$url,'type1'=>'news']);
    }

}
