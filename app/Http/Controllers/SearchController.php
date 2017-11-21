<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CategoryController as CategoryController;
use Curl;
use Route;
class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->category = new CategoryController();
        $this->req = $this->category->emptyFilter($request->all());
        if(!empty($this->req['cat']) || !empty($this->req['topic'])){
            $this->category->modifyViewCount($this->req);
        }
    }

    protected function display(Request $request){
       $tags=array("python","java","c++","mysql","tutorials","arrays","cookies","programming","computer","cake php","php","laravel","frameworks","tutorials","networks","compiler","login","signup","html","css","javascript","jquery","bootstrap","machine","learning","open source","xml","json","mvc","nodejs","entreprenuer","leader","php"); 
       $keyword=$request->q;
       $course_type=$request->course_type;
       $nexttoken=$request['nexttoken'];
       $paginatevalue=$request['paginatevalue'];
       $nextYoutubetoken=$request->nextYoutubetoken;
       $filter1=$request->filter1;
       $filter2=$request->filter2;
       $filter3=$request->filter3;
       $filter4=$request->filter4;
       if(!isset($request->filter_value))
       {
       $data=array();
       $req = Request::create('/youtube/'.urlencode($keyword).'/'.$nextYoutubetoken, 'GET');
       $instance = Route::dispatch($req)->getContent();
       $req1 = Request::create('/googlebooks_search/'.urlencode($keyword).'/'.$nexttoken, 'GET');
       $instance1 = Route::dispatch($req1)->getContent();
       $req2 = Request::create('/searcharticle/'.urlencode($keyword).'/'.$nexttoken, 'GET');
       $instance2 = Route::dispatch($req2)->getContent();
       $req3 = Request::create('/tutorials/'.urlencode($keyword).'/'.$nexttoken, 'GET');
       $instance3 = Route::dispatch($req3)->getContent();
       $instance = json_decode($instance, true);
       $instance1 = json_decode($instance1, true);
       $instance2 = json_decode($instance2, true);
       $instance3 = json_decode($instance3, true);


       $i=0;

if (is_array($instance1) || is_object($instance1))  
{
  foreach ($instance1 as $key) 
       {
        foreach ($tags as $tag) {
         if(stripos($key['title'],$tag)!==false || stripos($key['description'],$tag)!==false)
          {
      
        
       array_push($data,$key);
       break;
     }
     }
       }
     }
     if (is_array($instance3) || is_object($instance3))  
{
  foreach ($instance3 as $key) 
       {
        foreach ($tags as $tag) {
         
          if(stripos($key['title'],$tag)!==false || stripos($key['description'],$tag)!==false || stripos($key['keywords'],$tag)!==false)
          {
      
       
       array_push($data,$key);
       break;
     }

     }
       }
     }
     
 if (is_array($instance2) || is_object($instance2))  
{
       foreach ($instance2 as $key)
        {
         foreach ($tags as $tag) {
         if(stripos($key['title'],$tag)!==false || stripos($key['description'],$tag)!==false)
          {
      
        
       array_push($data,$key);
       break;
     }
     }
        }
      }
    if (is_array($instance) || is_object($instance))  
       {
       

       foreach ($instance as $key) 
        {
        $i++;
        if($i==1)
          continue;
        foreach ($tags as $tag) {
          if(stripos($key['title'],$tag)!==false || stripos($key['description'],$tag)!==false)
          {
      
        
       array_push($data,$key);
       break;
     }
     }
       }
     
     }
     $token=$instance[0]['nextPageToken'];
     usort($data,function($a, $b){ 
    if($a['rating'] >= $b['rating'])
      return -1;
    else if($a['rating'] < $b['rating'])
      return 1;
    else
      return 0;
    
});
     if(sizeof($data)<10)
     {
      $data=[];
     }
    
     return view('pages.search')->with(['req' => $this->req,'data1' => $data,'data' => $data,'paginatevalue' => $paginatevalue,'youtube_token' => $token,'nexttoken'=>$nexttoken,'course_type' => $course_type,'filter1' => $filter1,'filter2' => $filter2,'filter3' => $filter3,'filter4' => $filter4,'filter_value'=>'0']);
   }
   else
   {
   
     $keyword=$request->q;
     $course_type=$request->course_type;
     $nexttoken=$request['nexttoken'];
     $nextYoutubetoken=$request->nextYoutubetoken;
     $token=$request->nextYoutubetoken;
     $data=array();
     $Arr = json_decode($request->data, true); 

     if($request->filter1!="")
     {
      foreach ($Arr as $key) {
        if($key['type']=="video")
          array_push($data,$key);
      }
     }
     if($request->filter2!="")
     {
      foreach ($Arr as $key) {
        if($key['type']=="book")
          array_push($data,$key);
      }
     }
     if($request->filter3!="")
     {
      foreach ($Arr as $key) {
        if($key['type']=="article" || $key['type']=="business_article" || $key['type']=="created_article")
          array_push($data,$key);
      }
     }
     if($request->filter4!="")
     {
      foreach ($Arr as $key) {
        if($key['type']=="created_article")
          array_push($data,$key);
      }
     }
     if($request->filter1=="" && $request->filter2=="" && $request->filter3=="" && $request->filter4=="")
     {
      foreach ($Arr as $key) {
          array_push($data,$key);
      }
     }
     usort($data,function($a, $b){ 
    if($a['rating'] >= $b['rating'])
      return -1;
    else if($a['rating'] < $b['rating'])
      return 1;
    else
      return 0;
    
});
   return view('pages.search')->with(['req' => $this->req,'data1' => $data,'data' => $Arr,'paginatevalue' => $paginatevalue,'youtube_token' => $token,'nexttoken'=>$nexttoken,'course_type' => $course_type,'filter1' => $filter1,'filter2' => $filter2,'filter3' => $filter3,'filter4' => $filter4,'filter_value'=>'1']);
}
   


     
     
      
       
       
       // $courses = $this->category->getCourses($this->req);
       // $courses = $this->search($courses,$this->req['q']);
       // $courses = $this->category->paginateCourse($courses,10,$request);





    	
    }

    protected function search($courses,$q){
    	$filtered_courses = $courses;
        if(!empty($q)){
            $filtered_courses = $filtered_courses->filter(function ($value, $key) use ($q) {
                if(stripos($value->course_name,$q) !== false)
                return true;
            });
            $keywords = explode(' ',$q);
            if(count($keywords) > 1){
                foreach ($keywords as $word) {
                    $filtered = $courses->filter(function ($value, $key) use ($word) {
                        if(stripos($value->course_name,$word) !== false){
                            return true;
                        }
                    });
                    $filtered_courses = $filtered_courses->merge($filtered);
                    $filtered_courses = $filtered_courses->unique('courses_id');
                }
            }
        }
    	return $filtered_courses;
    }
}
