<?php

namespace App\Http\Controllers;
use DB;
use Log;
use Illuminate\Http\Request;

class saveController extends Controller
{
    public function save(Request $request)
    {
       $title=$request->title;
       $tags=$request->tags;
       $description=$request->description;
       $tutorial_tags="";
       $totalfiledata=$request->totalfiledata;
       $author=$request->author;
       foreach ($tags as $key) {
         $tutorial_tags=$tutorial_tags.$key." ";
       }
      
       Log::info($tutorial_tags);

       $id1 = DB::table('tutorials')->insertGetId(
        array('title' => $title, 'no_of_files' => count($totalfiledata) ,'tags' => $tutorial_tags,'description' => $description,'Author' => $author)
        );
       Log::info('insert');

       $path="tutorials/".$id1;
       if (!file_exists($path)) {
         mkdir($path, 0777, true);
       }
     
       $i=1;
       foreach ($totalfiledata as $key) {
       	$myfile = fopen("tutorials/".$id1."/".$i.".html", "w") or die("Unable to open file!");
        $i++;
        fwrite($myfile, $key);
        fclose($myfile);
        
       }
    
    }
}
