<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoomsController extends Controller
{
    public function display(){
    	return view('pages.rooms');
    }
}
