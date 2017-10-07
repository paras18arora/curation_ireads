<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class ArticleController extends Controller
{
    public function search($id,$page)
    {      
   
    	  $pieces=explode(" ",$id);
        $articles = new \ArrayObject();
        $k=0;
        $data=array();
        $search_term = urlencode($id);
        $index=$page;

        // curl for medium articles
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://medium.com/search/posts?q='.$search_term.'&page='.$index.'&format=json',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_SSL_VERIFYPEER => 0
        ));

        $json = curl_exec($curl);
        curl_close($curl);
        $json = substr($json,16);
        $json_array = json_decode($json);
        $rating_medium = 4.9;
          $posts = $json_array->payload->value;
          $no_of_results = count($posts);
          //$posts[0]->title;exit;
          for($j=0;$j<$no_of_results;$j++) 
          {

          if(isset($posts[$j]->title))
            {
                $title = $posts[$j]->title; 
            }
            else
            {
                $title = "";    
            }
          if(isset($posts[$j]->virtuals->subtitle))
            {
                $subtitle = $posts[$j]->virtuals->subtitle; 
            }
            else
            {
                $subtitle = "";    
            }


            // Medium Link Making starts here
          if(isset($posts[$j]->uniqueSlug))
            {
                $unique_slug = $posts[$j]->uniqueSlug; 
            }
            else
            {
                $unique_slug = "";
            }
            if($unique_slug == "")
            {
                if(isset($posts[$j]->slug))
                {

                    $unique_slug = $posts[$j]->slug."-".$posts[$j]->id;
                }
            }

          if(isset($posts[$j]->homeCollection->domain))
            {
                $domain = $posts[$j]->homeCollection->domain; 
            }
            else
            {
                $domain = "medium.com";    
            }
            $domain = "https://".$domain;

            if($domain == "https://medium.com")
            {
              if(isset($posts[$j]->approvedHomeCollection->slug))
              {
                  $slug = $posts[$j]->approvedHomeCollection->slug; 
              }
              
              else
              {
                  $slug = "";    
              }                
            }
            else
            {
              $slug = "";
            }
            if ($domain == "https://medium.com")
            $url = $domain."/".$slug."/".$unique_slug;
            else
              $url = $domain."/".$unique_slug;

            // Medium Link Making ends here

          if(isset($posts[$j]->virtuals->previewImage->imageId))
            {
                $image = $posts[$j]->virtuals->previewImage->imageId; 
            }
            else
            {
                $image = "";    
            }
           

          if(isset($posts[$j]->homeCollection->tags))
            {
                $tags = $posts[$j]->homeCollection->tags; 
            }
            else
            {
                $tags = "";    
            }

            $source1="medium.com";

            $data1=array("title"=>$title,"description"=>$subtitle,"link"=>$url,"imagesrc"=>$image,"rating"=>$rating_medium,"keywords"=>$tags,"source"=>$source1,"type"=>"article","link"=>$url,"no_of_files"=>0);
            array_push($data,$data1);
            $k++;
            $rating_medium = $rating_medium - 0.3;

          }

          //pagination logic 
        $perPage = 10;
        $take = 10;
        $skip = 0;
        $skip = $page * $perPage;
        if($take < 1) { $take = 1; }
        if($skip < 0) { $skip = 0; }


    for($i=0;$i<sizeof($pieces);$i++)
		{
        $business=DB::table('business_datas')
        ->where('keywords', 'LIKE', '%'.$pieces[$i].' '.'%')
        ->take($perPage)
        ->skip($skip)        
        ->get();
		
		$digitalocean= DB::table('data')->where('category_id', '=', '1')
        ->where('keywords', 'LIKE', '%'.$pieces[$i].' '.'%')
        ->take($perPage)
        ->skip($skip) 
        ->get();
        
        $articles->append($business);
        $articles->append($digitalocean);
        }

        $rating_else = 4.8;

        for($j=0;$j<2;$j++)
        {

        for($i=0;$i<sizeof($articles[$j]);$i++)
        {

        	if(isset($articles[$j][$i]->id))
     		$id1=$articles[$j][$i]->id;
     	    else
     		$id1="";
     	    if(isset($articles[$j][$i]->title))
     		$title1=$articles[$j][$i]->title;
     	    else
     		$title1="";
     	    if(isset($articles[$j][$i]->link))
     		$link1=$articles[$j][$i]->link;
     	    else
     		$link1="";
     	    if(isset($articles[$j][$i]->description))
     		$description1=$articles[$j][$i]->description;
     	    else
     		$description1="";
     	    if(isset($articles[$j][$i]->imagesrc))
     		$imagesrc1=$articles[$j][$i]->imagesrc;
     	    else
     		$imagesrc1="";
     	    if(isset($articles[$j][$i]->keywords))
     		$keywords1=$articles[$j][$i]->keywords;
     	    else
     		$keywords1="";
     	    if(isset($articles[$j][$i]->source))
     		$source1=$articles[$j][$i]->source;
     	    else
     		$source1="";
     	 if(isset($articles[$j][$i]->category_id))
     		$category_id=$articles[$j][$i]->category_id;
     	    else
     		$category_id="";
     	$data1=array("id"=>$id1,"title"=>$title1,"description"=>$description1,"link"=>$link1,"imagesrc"=>$imagesrc1,"keywords"=>$keywords1,"source"=>$source1,"rating"=>$rating_else,"type"=>"database_article","category_id"=>$category_id,"no_of_files"=>0);
     	array_push($data,$data1);
     	$k++;
      $rating_else = $rating_else - 0.2;
        }

    }

        return json_encode($data);
    }

    
    public function viewarticle(Request $request)
    {
    	$db_ext = DB::connection('mysql2');
        $link = $request->route('id'); 
        $source=$request->route('source');

        if($source=="medium.com")
        {
           $link=str_replace("+","/",$link);
           $link = $link. '?format=json';
        $curl2 = curl_init();  // for accessing each article content based on the link we get from above result
        curl_setopt_array($curl2, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $link,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_SSL_VERIFYPEER => 0,  
            CURLOPT_FOLLOWLOCATION => true
            ));

        $article = curl_exec($curl2);
        $post = substr($article,16);
        //$post1 = json_decode($post,true);
        //$id=$post1["payload"]["value"]["id"];
        $post = json_decode($post,true);

            $html_content = "
            <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
            <style>
        p+pre {
            margin-top: 0;
        }
        pre {
            display: block;
            box-sizing: border-box;
            white-space: pre;
            word-wrap: normal;
            overflow: auto!important;
            padding: 13px 17px;
            font-size: 14px;
            margin: 28px 0;
        }
        code, pre {
            background-color: rgba(0,0,0,.05);
            border-radius: 3px;
        }
        code, kbd, pre, samp {
            font-family: monospace,monospace;
            font-size: 1em;
        }
        pre {
            overflow: auto;
        }
        user agent stylesheet
        pre, xmp, plaintext, listing {
            display: block;
            font-family: monospace;
            white-space: pre;
            margin: 1em 0px 1em;
        }
        h4{
        font-weight: 700;
        }
        h2{
        font-weight: 700;
        }
        p{
          -webkit-font-smoothing: antialiased;
          word-wrap: break-word;
          word-spacing:5px;
          font-size: 1.1em;
        }
        .article{
          color: rgba(0,0,0,.9);
          font-size: 1.1em;
          font-weight: normal;
        }
        </style>
        <center>";
         if(isset($post["payload"]["value"]["content"]["bodyModel"]["paragraphs"]))
        $lines = count($post["payload"]["value"]["content"]["bodyModel"]["paragraphs"]);
    else
        $lines=0;
          for($k=0;$k<$lines;$k++)
          {
                    $href="";
                    $para = $post["payload"]["value"]["content"]["bodyModel"]["paragraphs"][$k];

                    if ($para["type"] == 1) //code present
                    {
                        if(isset($para["text"]))
                        {
                        $type1 = "<p>".$para["text"]."</p>";
                        $type1 =  $type1."<br>";
                        }
                        else
                        $type1="";

                        $html_content = $html_content.$type1;
                    } 
                    elseif($para["type"] == 2) //title of the post
                    {
                        if(isset($para["text"]))
                       $type2 = "<h1>" .$para["text"]. "</h1>";
                   else
                    $type2="";
                       $html_content = $html_content.$type2;
                    }
                    elseif($para["type"] == 4) //image
                     {

                        if(isset($para["metadata"]["id"]))
                        {
                        $img_link = "https://cdn-images-1.medium.com/max/800/" .$para["metadata"]["id"] ;
                        $img = "<img style='margin-bottom:20px;' src=".$img_link."/>";
                    }
                    else
                    $img="";
                        $html_content = $html_content.$img;
                    }
                    elseif ($para["type"] == 14)
                    {
                         $type14="<a href=".$para['markups']['0']['href']."><h3>".$para['text']."</h3></a>";
                          $html_content = $html_content.$type14;
                    }
                    elseif ($para["type"] == 3) //heading present
                    {   
                        $flag=0;
                        if(isset($para["markups"]))
                        {
                        if($para["markups"]  != NULL)
                        {
                            if(isset($para["markups"]["0"]["href"]))
                            $href = $para["markups"]["0"]["href"];
                            else
                                $href="";
                             $html_content = $html_content.$href;
                            $flag=1;
                        }
                        if($flag==1)
                        {

                            $type3 = "<a href=".$href." target='_blank'><h4>Heading Link -" .$para["text"]. "</h4></a>";
                            $html_content = $html_content.$type3;
                        }
                        else
                        {
                            $type3 = "<h4>" .$para["text"]. "</h4>";
                            $html_content = $html_content.$type3;
                        }
                    }
                    }
                    elseif ($para["type"] == 8) //code present
                    { 
                        $type8 = "<pre>".$para["text"]."</pre>";
                        $type8 = $type8."<br>";
                        $html_content = $html_content.$type8;
                    } 
                    elseif ($para["type"] == 13) //light heading
                    {
                        $type13 = "<h4>" .$para["text"]. "</h4>";
                        $type13 = $type13."<br>";
                        $html_content = $html_content.$type13;
                    }
                    else
                    {
                        $typeelse = "<p>" . $para["text"]. "</p>";
                        $html_content = $html_content.$typeelse;
                    }              
             }
               
             $html_content = $html_content."</center>";   
             $data=array();
             array_push($data,$html_content,$source);

            curl_close($curl2);

            
          return json_encode($data);
        }
        else
        {
		if(substr($link,0,1)!="b")
		{
		$content= $db_ext->table('data')->where('id', '=', $link)->first();
	    }
	    else
	    {
	    	$content= $db_ext->table('business_datas')->where('id', '=', $link)->first();
	    }
      $data1=array();
      array_push($data,$link,$source);
	    return json_encode($data1);
       }
    }
}