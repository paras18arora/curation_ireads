<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class filterController extends Controller
{
    protected function search(Request $request){

     $keyword=$request->q;
     $course_type=$request->course_type;
     $nexttoken=$request['nexttoken'];
     $nextYoutubetoken=$request->nextYoutubetoken;
     $data=array();
     $Arr = unserialize($request->data);
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
     		if($key['type']=="article")
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
     return view('pages.search')->with(['req' => $this->request,'data1' => $data,'data' => $Arr,'paginatevalue' => $paginatevalue,'youtube_token' => $token,'nexttoken'=>$nexttoken,'course_type' => $course_type,'filter1' => $filter1,'filter2' => $filter2,'filter3' => $filter3,'filter_value'=>'1']);;

}
}
