<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Froala\FileUpload;
class uploadController extends Controller
{
	/**
	 * This returns a json array of links to fill the image manager in Froala forms. DO NOT CHANGE.
	 * @return JSON Returns a json array of links.
	 */
	/**
	 * Stores images uploaded from a Froala enabled form. 
	 * @param  Request $request the POST request
	 * @return JSON     Returns a json link with a url (used to insert image into article/page). DO NOT CHANGE.
	 */
	public function store(Request $request){
	
        // $filename 			= $fileData->getClientOriginalName();
        $filename           = $_FILES['image']['name'];
        // $completePath 		= url('/' . $location . '/' . $filename);
        $destinationPath 	= 'img/';
        $request->file('image')->move($destinationPath, $filename);
		$completePath = url('/' . $destinationPath . $filename);
		
		// if($fileupload->save()){
			return stripslashes(response()->json(['link' => $completePath])->content());
		// }
	}
	/**
	 * Find and delete the deleted image.
	 * @param  Request  $request 	[description]
	 * @param  int  	$id      	Department ID
	 */
    public function destroy(Request $request){
    	$input = $request->all();
    	$url = parse_url($input['src']);
    	$splitPath = explode("/", $url["path"]);
    	$splitPathLength = count($splitPath);
    	FileUpload::where('path', 'LIKE', '%' . $splitPath[$splitPathLength-1] . '%')->delete();
    }
}
