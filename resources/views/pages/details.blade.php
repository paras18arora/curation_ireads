@extends('master')
@section('meta')
	<title>IndiaReads - Details</title>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('css/details.css') }}">
@endpush
@section('header')
	@include('includes.header')
@endsection
@section('content')
	<div id="cover">
		<div class="parallax cover <?php if($type1=='book') echo 'book'; else if($type1=='video') echo 'video'; else echo 'article'; ?>">

			<div class="bottom course-name">
				<div class="col-md-10">
					<h4 class="name">{{$title}}</h4>
					
					<h5 class="author">{{$author}}</h5>
					
				</div>
				<div class="col-md-2 text-right">
					<h3 class="rating"><span class="label label-success">{{$rating}}</span></h3>
					<a href="#"><h5 class="reviews">23 Reviews <i class="fa fa-pencil"></i></h5></a>
				</div>
			</div>
		</div>
		<div class="clearfix text-center course-details">
			<div class="col-md-3"><h4>Category</h4></div>
			<div class="col-md-3"><h4>Course Type</h4></div>
			<div class="col-md-2"><h4>Level</h4></div>
			<div class="col-md-2"><h4>Free</h4></div>
			<div class="col-md-2 goto no-padd-leftright"><a href="{{ route('play',['type' => 'article','id' => $id,'source' => $source,'type1' => $type1,'isbn' => $isbn,'videoid' => $videoid,'name' => str_slug('Course Name'),'link' => $link,'title' => $title,'imagesrc' => $imagesrc,'no_of_files'=> $no_of_files]) }}" class="btn btn-danger">Start course <i class="fa fa-angle-double-right"></i></a></div>
		</div>
	</div>
	<div class="col-md-8 about-div">
		<ul class="navbar nav nav-pills nav-justified no-padd-leftright" role="tablist">
		  	<li role="presentation" class="active"><a href="#about-course" aria-controls="about-course" role="tab" data-toggle="tab">About Course</a></li>
			<li role="presentation"><a href="#course-content" aria-controls="course-content" role="tab" data-toggle="tab">Course Content</a></li>
		  <!-- 	<li role="presentation"><a href="#course-review" aria-controls="course-review" role="tab" data-toggle="tab">Course Review</a></li> -->
		  	<li role="presentation"><a href="#write-review" aria-controls="write-review" role="tab" data-toggle="tab">Write a Review</a></li>
		  	<li role="presentation"><a href="#discuss" aria-controls="discuss" role="tab" data-toggle="tab">Discussion Forum</a></li>
		</ul>
		<hr style="margin:0px;margin-bottom: 20px">
		<div class="tab-content col-md-12">
		  	<div role="tabpanel" class="tab-pane active" id="about-course">
		  		<h4 class="col-md-6">About this course</h4>
		  		<h4 class="text-right col-md-6">{{$rating}}/5</h4>
		  		<p class="clearfix" style="margin-bottom:0px"></p>
		  		<hr style="margin-top: 0px">
		  		<div class="col-md-12">
		  			<h4>Description</h4>
		  			<p>
		  				{{$description}}
		  			</p>
		  			<br>
		  		
		  		</div>
		  	</div>
		  	<div role="tabpanel" class="tab-pane" id="course-content">
		  		<h3 style="margin-bottom: 0px">Modules</h3>
		  		<div class="row" style="margin-left: 50px">
		  			<ul class="col-md-3">
		  				<h4>Module 1</h4>
		  				<li>Episode 1</li>
		  				<li>Episode 2</li>
		  				<li>Episode 3</li>
		  				<li>Episode 4</li>
		  				<li>Episode 5</li>
		  			</ul>
		  			<ul class="col-md-3">
		  				<h4>Module 2</h4>
		  				<li>Episode 1</li>
		  				<li>Episode 2</li>
		  				<li>Episode 3</li>
		  				<li>Episode 4</li>
		  				<li>Episode 5</li>
		  			</ul>
		  			<ul class="col-md-3">
		  				<h4>Module 3</h4>
		  				<li>Episode 1</li>
		  				<li>Episode 2</li>
		  				<li>Episode 3</li>
		  				<li>Episode 4</li>
		  				<li>Episode 5</li>
		  			</ul>
		  			<ul class="col-md-3">
		  				<h4>Module 4</h4>
		  				<li>Episode 1</li>
		  				<li>Episode 2</li>
		  				<li>Episode 3</li>
		  				<li>Episode 4</li>
		  				<li>Episode 5</li>
		  			</ul>
		  		</div>
		  		<hr class="margin-hr">
		  		<h4>Key Points</h4>
	  			<ul>
	  				<li>C++ Syntax</li>
	  				<li>C++ Language Fundamentals</li>
	  				<li>How to Create Functions in C++</li>
	  				<li>Prepare yourself for intermediate and advanced C++ topics in follow-up courses taught by Microsoft</li>
	  			</ul>
	  			<hr class="margin-hr">
		  		<h4>Target Audience</h4>
	  			<ul>
	  				<li>People with limited time.</li>
					<li>Anyone with a desire to learn about Linux.</li>
					<li>People that have Linux experience, but would like to learn about the Linux command line interface.</li>
					<li>Existing Linux users that want to become power users.</li>
	  				<li>C++ Syntax</li>
	  				<li>C++ Language Fundamentals</li>
	  				<li>How to Create Functions in C++</li>
	  				<li>People that need Linux knowledge for a personal or business project like hosting a website on a Linux server.</li>
	  				<li>Professionals that need to learn Linux to become more effective at work. Helpdesk staff, application support engineers, and application developers that are required to use the Linux operating system.</li>
	  				<li>Researchers, college professors, and college students that will be using Linux servers to conduct research or complete course work.</li>
	  			</ul>
		  	</div>
		  	<div role="tabpanel" class="tab-pane" id="course-review">
		  		<h4 class="col-md-6">User Reviews</h4>
		  		<h4 class="text-right col-md-6">23 reviews(4.8/5)</h4>
		  		<p class="clearfix" style="margin-bottom:0px"></p>
		  		<hr style="margin-top: 0px">
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  		<div>
		  			<h5 class="col-md-6"><b>Akash Kumar Singh</b> (3 days ago)</h5>
		  			<h5 class="text-right col-md-6">4.8/5</h5>
		  			<p class="col-md-12 review-text">C++ is a general purpose programming language that supports various computer programming models such as object-oriented programming and generic programming. <br><a href="#">Full Review</a></p>
		  		</div>
		  	</div>
		  	<div role="tabpanel" class="tab-pane" id="write-review">
		  		<div class="text-center">
		  			<h2>Write a Review</h2>
		  		</div>
		  	</div>
		  	<div role="tabpanel" class="tab-pane" id="discuss">
		  		<div class="text-center">
		  			<h2>Discuss Forum</h2>
		  		</div>
		  	</div>
		</div>
	</div>
	<h4 class="text-center related-heading">Some related courses</h4>
	<div class="col-md-4 related-div">
		@foreach($recommend_data as $key)

		<form id="detail" method="post" action="{{ route('course',['type' => $key['type'],'name' => str_slug($key['title'])]) }}">
				<a onclick="$(this).closest('form').submit();">
				<input type='hidden' name='_token' value='{!! Session::token() !!}'>
				<input type='hidden' name='isbn' value="@if($key['type']=='book') {{$key['isbn']}} @else @endif">
				<input type='hidden' name='videoid' value="@if($key['type']=='video') {{$key['VideoId']}} @else @endif">
				<input type='hidden' id="dataid" name='dataa' value='{{json_encode($data)}}'>
				<input type="hidden" name="title" value="{{$key['title']}}">
				<input type="hidden" name="source" value="@if(isset($key['source'])){{$key['source']}} @else '' @endif">
				<input type="hidden" name="id" value="@if(isset($key['id'])){{$key['id']}} @else '' @endif">
				<input type="hidden" name="type" value="{{$key['type']}}">
				<input type="hidden" name="link" value="@if(isset($key['link'])){{$key['link']}} @else '' @endif">
				<input type="hidden" name="description" value="{{$key['description']}}">
				<input type="hidden" name="rating" value="{{$key['rating']}}">
				<input type="hidden" name="imagesrc" value="@if($key['type']=='video') {{$key['thumbnail1']}}  @elseif($key['type']=='article') @if($key['imagesrc']!='')https://cdn-images-1.medium.com/fit/t/400/400/{{$key['imagesrc']}} @else {{ URL::asset('img/article.jpg') }} @endif @elseif($key['type']=='book') {{$key['thumbnails']['thumbnail']}} @else {{ URL::asset('img/article.jpg') }} @endif">
				<input type="hidden" name="author" value="@if(isset($key['authors'][0])) 
									{{$key['authors'][0]}} @else '' 
								@endif">
			<div class="course-items row">
				<div class="col-md-3"><img class="item-size" src="@if($key['type']=='video') {{$key['thumbnail1']}}  @elseif($key['type']=='article') @if($key['imagesrc']!='')https://cdn-images-1.medium.com/fit/t/400/400/{{$key['imagesrc']}} @else {{ URL::asset('img/article.jpg') }} @endif @elseif($key['type']=='book') {{$key['thumbnails']['thumbnail']}} @else {{ URL::asset('img/article.jpg') }} @endif" />
				</div>
				<div class="col-md-9 item-content">
					<h5>{{ $key['title'] }}</h5>
					<h6>@if(isset($key['authors'][0])) 
									{{$key['authors'][0]}} 
								@endif</h6>
					<h6>@if($key['type']=='book' || $key['type']=='video'){{$key['type']}} @else article @endif | Free</h6>
				</div>
			</div>
		</a>
		</form>
		@endforeach
	</div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
	<script src="{{ URL::asset('js/details.js') }}"></script>
@endpush