@extends('master')
@section('meta')
	<title>IndiaReads - Category</title>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('css/category.css') }}">
@endpush
@section('header')
	@include('includes.header')
@endsection
@section('content')
	<div class="jumbotron clearfix">
		<p class="col-md-12">{{ $courses->total() }} courses found @if(!empty($req['cat'])) in {{ title_case(str_replace('-',' ',$req['cat'])) }} @endif @if(!empty($req['topic'])) @if(!empty($req['cat'])) - @endif {{ title_case(str_replace('-',' ',$req['topic'])) }} @endif</p>
	</div>
	<div class="">
		<div class="col-md-2 side">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				@foreach($category as $cat)
			  	<div class="panel panel-default">
			  	    <div class="panel-heading" role="tab" id="{{ str_slug($cat->category_name) }}">
			  	      	<h4 class="panel-title">
		  	        	<a class="@if(!empty($req['cat']) && $req['cat'] == str_slug($cat->category_name)) active @endif" role="button" data-toggle="collapse" data-parent="#accordion" href="#{{ str_slug($cat->category_name) }}-{{ $cat->categories_id }}" aria-expanded="true" aria-controls="{{ str_slug($cat->category_name) }}-{{ $cat->categories_id }}">
		  	          		{{ ucwords($cat->category_name) }}
		  	        	</a>
			  	      	</h4>
			  	    </div>
			  	    <div id="{{ str_slug($cat->category_name) }}-{{ $cat->categories_id }}" class="panel-collapse collapse @if(!empty($req['cat']) && $req['cat'] == str_slug($cat->category_name)) in @endif" role="tabpanel" aria-labelledby="{{ str_slug($cat->category_name) }}">
			  	      	<div class="panel-body">
			  	      		@foreach($sub_category as $sub_cat)
			  	      		    @if($sub_cat->categories_id == $cat->categories_id)
			  	      		        <p class="col-md-12"><a class="subcat-name @if(!empty($req['topic']) && $req['topic'] == str_slug($sub_cat->sub_category_name)) active @endif" href="{{ route('category',['cat' => str_slug($cat->category_name), 'topic' => str_slug($sub_cat->sub_category_name)]) }}">
			  	      		           	{{ ucwords($sub_cat->sub_category_name) }}
			  	      		        </a></p>
			  	      		    @endif
			  	      		@endforeach
			  	      	</div>
			  	    </div>
		  	  	</div>
		  	  	@endforeach
			</div>
		</div>
		<div class="col-md-7 content">
			@if(empty($courses->items()))
				<div class="row">
					<h3 class="col-md-12" align="center">No Courses Found</h3>
				</div>
			@else
				@foreach($courses as $course)
					<a href="{{ route('course',['type' => $course['course_type'],'name' => str_slug($course['course_name'])]) }}">
						<div class="course-items row">
							<div class="col-md-3 item-img">
							</div>
							<div class="col-md-9 item-content">
								<h3>{{ ucwords($course['course_name']) }}</h3>
								<h4>{{ $course['author'] }}</h4>
								<p>{{ $course['description'] }}</p>
							</div>
						</div>
					</a>
				@endforeach
				<div align="center">
					{{ $courses->appends(\Request::except('page'))->links() }}
				</div>
			@endif
		</div>
		<div class="col-md-2 filters">
			<h4>Refine your search</h4><hr style="margin: 0px">
			@if(!empty($req['cat']) || !empty($req['topic']) || !empty($req['course_type']) || !empty($req['price']) || !empty($req['level']))
			<h5 class="clearfix"><span class="col-md-6 no-padd-leftright text-left">Filters</span><span class="col-md-6 no-padd-leftright text-right" style="font-size: 12px"><a href="{{ route('category') }}">Clear All</a></span></h5>
			@endif
			<p class="col-md-12 filter-tags text-left">
				@foreach($req as $key => $value)
					@if($key != 'page' && !empty($value))
						<a href="" class="filter-tags-url"><span class="label label-default">{{ title_case(str_replace('-',' ',$value)) }}</span></a>
					@endif
				@endforeach
			</p>
			@if(!empty($req['course_type']))
				<span style="float: right"><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'],'course_type' => '','price' => $req['price'],'level' => $req['level']]) }}">Clear</a></span>
			@endif
			<hr style="margin: 0px">
			<h6>Refine by Type</h6>
			<div class="cat-filters">
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => 'video','price' => $req['price'],'level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['course_type']) && $req['course_type'] == 'video') fa-check-square-o @else fa-square-o @endif"></i> Video</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => 'text','price' => $req['price'],'level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['course_type']) && $req['course_type'] == 'text') fa-check-square-o @else fa-square-o @endif"></i> Text</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => 'image','price' => $req['price'],'level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['course_type']) && $req['course_type'] == 'image') fa-check-square-o @else fa-square-o @endif"></i> Image</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => 'audio','price' => $req['price'],'level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['course_type']) && $req['course_type'] == 'audio') fa-check-square-o @else fa-square-o @endif"></i> Audio</a></p>
			</div>
			@if(!empty($req['price']))
			<span style="float: right"><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'],'course_type' => $req['course_type'] ,'price' => '','level' => $req['level']]) }}">Clear</a></span>
			@endif
			<hr style="margin: 0px">
			<h6>Refine by Price</h6>
			<div class="cat-filters">
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => $req['course_type'],'price' => 'free','level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['price']) && $req['price'] == 'free') fa-check-square-o @else fa-square-o @endif"></i> Free</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => $req['course_type'],'price' => 'paid','level' => $req['level']]) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['price']) && $req['price'] == 'paid') fa-check-square-o @else fa-square-o @endif"></i> Paid</a></p>
			</div>
			@if(!empty($req['level']))
			<span style="float: right"><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'],'course_type' => $req['course_type'] ,'price' => $req['price'],'level' => '']) }}">Clear</a></span>
			@endif
			<hr style="margin: 0px">
			<h6>Refine by Level</h6>
			<div class="cat-filters">
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => $req['course_type'],'price' => $req['price'],'level' => 'beginner']) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['level']) && $req['level'] == 'beginner') fa-check-square-o @else fa-square-o @endif"></i> Beginner</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => $req['course_type'],'price' => $req['price'],'level' => 'intermediate']) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['level']) && $req['level'] == 'intermediate') fa-check-square-o @else fa-square-o @endif"></i> Intermediate</a></p>
				<p class=""><a href="{{ route('category',['cat' => $req['cat'], 'topic' => $req['topic'], 'course_type' => $req['course_type'],'price' => $req['price'],'level' => 'advanced']) }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa @if(!empty($req['level']) && $req['level'] == 'advanced') fa-check-square-o @else fa-square-o @endif"></i> Advanced</a></p>
			</div>
		</div>
	</div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
	<script src="js/category.js"></script>
@endpush