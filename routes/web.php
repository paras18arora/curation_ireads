<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/searcharticle/{id}/{page}', [
   'uses' => 'ArticleController@search',
   'as' => 'Article'
]);
Route::post('/filter', [
   'uses' => 'filterController@search',
   'as' => 'filter'
]);
Route::get('/', [
	'uses' => 'HomeController@display',
	'as' => 'home'
]);

Route::get('/home', [
	'uses' => 'HomeController@display',
	'as' => 'home'
]);

Route::get('/category', [
	'uses' => 'CategoryController@display',
	'as' => 'category'
]);

Route::get('/rooms', [
	'uses' => 'RoomsController@display',
	'as' => 'rooms'
]);
Route::get('/savetutorial', [
	'uses' => 'saveController@save',
	'as' => 'save'
]);
Route::post('/fileupload/{id}', [
	'uses' => 'uploadController@store',
	'as' => 'uploads'
]);
Route::get('/createcourse', [
	'uses' => 'CourseController@create',
	'as' => 'createcourse'
]);
Route::post('/search', [
	'uses' => 'SearchController@display',
	'as' => 'search'
]);

Route::post('/course/{type}/{name}', [
	'uses' => 'DetailsController@display',
	'as' => 'course'
]);

Route::get('/player/{type}/{name}', [
	'uses' => 'DetailsController@play',
	'as' => 'play'
]);

Route::get('/googlebooks_search/{id}/{start_index}', [
	'uses' => 'GoogleBooksController@search',
	'as' => 'googlebooks_search'	
]);
Route::get('/news', [
	'uses' => 'DetailsController@news',
	'as' => 'news'	
]);
Route::get('/youtube/{id}/{token}', [
   'uses' => 'YoutubeController@display',
   'as' => 'Youtube'
]);
Route::get('/tutorials/{id}/{token}', [
   'uses' => 'tutorialController@display',
   'as' => 'tutorial'
]);



Auth::routes();
