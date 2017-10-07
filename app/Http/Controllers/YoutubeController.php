<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Youtube;
use Curl;
class YoutubeController extends Controller
{
    public function display($id,$token)
    {
        
        $id=urlencode($id);
        if($token=='0')
        {
            $token="";
        }

        $params = array(
    'q'             => $id,
    'type'          => 'video',
    'part'          => 'id, snippet',
    'maxResults'    => 10,
    'pageToken'     => $token,
    'topicId'      => '/m/07c1v',
    'relevanceLanguage' => 'en'
    );

$data=array();

// Make intial call. with second argument to reveal page info such as page tokens
$search = Youtube::searchAdvanced($params, true);
$data2=array("nextPageToken"=>($search['info']['nextPageToken']));
array_push($data,$data2);
$rating=5;
foreach($search['results'] as $key)
     {
        if(isset($key->id->videoId))
            $videoId=$key->id->videoId;
        else
            $videoId="";
        if(isset($key->snippet->title))
            $title=$key->snippet->title;
        else
            $title="";
        if(isset($key->snippet->description))
            $description=$key->snippet->description;
        else
            $description="";
        if(isset($key->snippet->thumbnails->default->url))
            $thumbnail1=$key->snippet->thumbnails->default->url;
        else
            $thumbnail1="";
        if(isset($key->snippet->thumbnails->medium->url))
            $thumbnail2=$key->snippet->thumbnails->medium->url;
        else
            $thumbnail2="";
        if(isset($key->snippet->thumbnails->high->url))
            $thumbnail3=$key->snippet->thumbnails->high->url;
        else
            $thumbnail3="";
        if(isset($key->snippet->publishedAt))
            $date=$key->snippet->publishedAt;
        else
            $date="";

     $data1=array("VideoId"=>$videoId,"title"=>$title,"description"=>$description,"thumbnail1"=>$thumbnail1,"thumbnail2"=>$thumbnail2,"thumbnail3"=>$thumbnail3,"date"=>$date,"rating"=>$rating,"type"=>"video","no_of_files"=>0); 
     $rating=$rating-0.4;
     array_push($data,$data1);
     
   
     }

     
    return json_encode($data);

    }
     public function tags()
    {
        $curl = new Curl();
        $curl->get('https://www.googleapis.com/youtube/v3/videos', array(
        'key' => 'AIzaSyBxUk90KaJVsNLjBeqjFdcjh8dnlnnjE7k',
        'fields' => 'items(snippet(title,description,tags))',
        'part' => 'snippet',
        'id' => 'cSRgVD8-zxM'
        ));
        $response = Curl::to('https://www.googleapis.com/youtube/v3/videos', array(
        'key' => 'AIzaSyBxUk90KaJVsNLjBeqjFdcjh8dnlnnjE7k',
        'fields' => 'items(snippet(title,description,tags))',
        'part' => 'snippet',
        'id' => 'cSRgVD8-zxM'
        ))
        ->returnResponseObject()
        ->get();

        $content = $response->content;
       
        print_r($content);


    }
}