<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;

use Curl;

class GoogleBooksController extends Controller
{
    public function search($id,$start_index)
    {
    	$search_term=urlencode($id);    	
    	$startIndex = $start_index;
    	$maxResults = "10";
    	$app_key = 'AIzaSyAJh-UrF0kd6g0FHmAYrUmFex1cU2SVO9w';

	
		//print_r($curl);exit;
    $resp = Curl::to('https://www.googleapis.com/books/v1/volumes')
    ->withData( array(
		    'q' => $search_term,
		    'filter' => 'partial',
		    'startIndex' => $startIndex,
		    'maxResults' => $maxResults,
		    'key' => $app_key
		) )
        ->returnResponseObject()
        ->asJson()
        ->get();



      
		
	$res=$resp->content;
		   

		$response = array();
		$no_of_results = count($res->items);

		for($j=0;$j<$no_of_results;$j++) // looping over all results
		{
			if($res->items[$j]->volumeInfo->industryIdentifiers[0]->type=="ISBN_10" || $res->items[$j]->volumeInfo->industryIdentifiers[0]->type=="ISBN_13")
			{

			if(isset($res->items[$j]->volumeInfo->averageRating))
			{
				$rating = $res->items[$j]->volumeInfo->averageRating; // title of book
			}
			else
			{
				$rating = "2.5";	
			}

			if(isset($res->items[$j]->volumeInfo->title))
			{
				$title = $res->items[$j]->volumeInfo->title; // title of book
			}
			else
			{
				$title = "";	
			}
			if(isset($res->items[$j]->volumeInfo->industryIdentifiers[0]->identifier))
			{
				$isbn = $res->items[$j]->volumeInfo->industryIdentifiers[0]->identifier; 
			}
			else
			{
				$isbn = "";	
			}


			if(isset($res->items[$j]->volumeInfo->authors))
			{
				$no_of_authors = count($res->items[$j]->volumeInfo->authors); // no of authors	
				$authors = array();	
				for($i=0;$i<$no_of_authors;$i++)
				{
					array_push($authors,$res->items[$j]->volumeInfo->authors[$i]); // making array of authors
				}				
			}
			else
			{
				$no_of_authors = 0; // no of authors	
				$authors = array();	
				for($i=0;$i<$no_of_authors;$i++)
				{
					array_push($authors,$res->items[$j]->volumeInfo->authors[$i]); // making array of authors
				}				
			}


			if(isset($res->items[$j]->volumeInfo->publishedDate))
			{
				$published_date = $res->items[$j]->volumeInfo->publishedDate; //book publishing date
			}
			else
			{
				$published_date = "";
			}


			if(isset($res->items[$j]->volumeInfo->description))
			{
				$description = $res->items[$j]->volumeInfo->description; //book description
			}
			else
			{
				$description = "";
			}

			if(isset($res->items[$j]->volumeInfo->pageCount))
			{
				$page_count = $res->items[$j]->volumeInfo->pageCount; //book pages count
			}
			else
			{
				$page_count = "";	
			}


			if(isset($res->items[$j]->volumeInfo->categories))
			{
				$no_of_categories = count($res->items[$j]->volumeInfo->categories); // no of categories
				$categories = array();
				for($i=0;$i<$no_of_categories;$i++)
				{
					array_push($categories,$res->items[$j]->volumeInfo->categories[$i]);// making array of categories
				}
			}
			else
			{
				$no_of_categories = 0; // no of categories
				$categories = array();
				for($i=0;$i<$no_of_categories;$i++)
				{
					array_push($categories,$res->items[$j]->volumeInfo->categories[$i]);// making array of categories
				}				
			}			



			if( isset($res->items[$j]->volumeInfo->imageLinks->smallThumbnail) && isset($res->items[$j]->volumeInfo->imageLinks->thumbnail) )
			{
				$thumbnails = array("smallThumbnail"=>$res->items[$j]->volumeInfo->imageLinks->smallThumbnail,"thumbnail"=>$res->items[$j]->volumeInfo->imageLinks->thumbnail);
			}
			elseif(isset($res->items[$j]->volumeInfo->imageLinks->smallThumbnail) && !isset($res->items[$j]->volumeInfo->imageLinks->thumbnail))
			{
				$thumbnails = array("smallThumbnail"=>$res->items[$j]->volumeInfo->imageLinks->smallThumbnail);				
			}
			elseif(!isset($res->items[$j]->volumeInfo->imageLinks->smallThumbnail) && !isset($res->items[$j]->volumeInfo->imageLinks->thumbnail))
			{
				$thumbnails = array("thumbnail"=>$res->items[$j]->volumeInfo->imageLinks->thumbnail);				
			}
			else
			{
				$thumbnails = "";
			}

			if(isset($res->items[$j]->volumeInfo->previewLink))
			{
				$preview_link = $res->items[$j]->volumeInfo->previewLink; //book preview link
			}
			else
			{
				$preview_link = "";
			}

			if(isset($res->items[$j]->accessInfo->webReaderLink))
			{
				$web_reader_link = $res->items[$j]->accessInfo->webReaderLink; //book web reader link
			}
			else
			{
				$web_reader_link = "";
			}


				$book_info = array("title"=>$title,"authors"=>$authors,"publishedDate"=>$published_date,"description"=>$description,"rating"=>$rating,"pageCount"=>$page_count,"tags"=>$categories,"thumbnails"=>$thumbnails,"previewLink"=>$preview_link,"isbn"=>$isbn,"webReaderLink"=>$web_reader_link,"type"=>"book","no_of_files"=>0); // making array of whole book

				array_push($response,$book_info);

		}
	}

		return json_encode($response);

		//return view('api.googlebooks_search')->with(['res'=>$res]);
    }
}
