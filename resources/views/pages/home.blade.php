@extends('master')
@section('meta')
	<title>IndiaReads - Home</title>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('css/home.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('lib/owl.carousel/assets/owl.carousel.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('lib/owl.carousel/assets/owl.theme.default.min.css') }}">
@endpush
@section('header')
@endsection
@section('content')
	<div class="jumbotron text-center">
	  	<h3>What course will your life take?</h3>
	  	<h5>Own your future by learning new skills online</h5>
	  	<br>
	  	<form class="col-md-6 col-md-offset-3" method="post" action="{{ route('search') }}">
	  	<input type="hidden" name='nexttoken' value="0">
	  	<input type="hidden" name='paginatevalue' value="1">
	  	<input type='hidden' name='filter1' value='video1'>
           <input type='hidden' name='filter2' value='book1'>
           <input type='hidden' name='filter3' value='article1'>
	  	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	  	<input type='hidden' name='data' value=''>
	  		        <input type="hidden" name="nextYoutubetoken" value="0">
	  		        
	  		<div class="form-group{{ $errors->has('keywords') ? ' has-error' : '' }}" style="margin-bottom: 0px">
	  		    <div class="input-group">
	  		        <input id="keywords" type="search" class="form-control home-search" name="q" value="{{ old('q') }}" required autofocus placeholder="Search..">
	  		        
  		          	<span class="input-group-btn">
	  		            <button type="submit" class="btn btn-default home-search-btn" type="button"><i class="fa fa-search"></i></button>
  		          	</span>
	  		        @if ($errors->has('keywords'))
	  		            <span class="help-block">
	  		                <strong>{{ $errors->first('keywords') }}</strong>
	  		            </span>
	  		        @endif
	  		    </div>
	  		</div>
	  	</form>
	  	<div class="clearfix"></div>
	  	<br><br>
	  	<p>
	  		<!--<a class="btn home-btn" href="{{ route('category') }}" role="button">Browse Rooms</a>
	  		<span>&nbsp;&nbsp;&nbsp;Or&nbsp;&nbsp;&nbsp;</span> -->
	  		<a class="btn home-btn" href="{{ route('createcourse') }}" role="button">Create Course</a>
	  	</p>
	</div>
	<div class="row nomargin-leftright carousel">
		<!--<div class="col-md-8" style="border-right:1px solid #ddd">
			<h3 class="text-center">Latest Rooms<span class="pull-right"><button class="btn btn-default btn-show-all">Show All</button></span></h3>
			<hr class="margin-hr">
			<div class="latest">
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			</div>
			<br>
			<h3 class="text-center">Trending Rooms <span class="pull-right"><button class="btn btn-default btn-show-all">Show All</button></span></h3>
			<hr class="margin-hr">
			<div class="trending">
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			</div>
			<br>
			<h3 class="text-center">Popular Rooms<span class="pull-right"><button class="btn btn-default btn-show-all">Show All</button></span></h3>
			<hr class="margin-hr">
			<div class="popular">
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			    <div class="item"><h2>Name</h2><h4>Author</h4><p>Hello helloooooo</p></div>
			</div>
		</div>
		-->
		<div class="col-md-12 newspaper">
			<h3 class="text-center">Latest News</h3>
			<hr class="margin-hr">
			<ul class="list-group">
			@foreach($news as $key)
				
				<a href="{{ route('news',['type1' => 'news','link' => $key->url,'title' => $key->title,'imagesrc' =>$key->urlToImage]) }}"
					<li class="list-group-item">
                     
						<h4>{{$key->title}}</h4>
						<p>{{$key->description}}</p>
					</li>
				</a>
			@endforeach
			</ul>
		</div>
	</div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
	<script src="{{ URL::asset('lib/owl.carousel/owl.carousel.min.js') }}"></script>
	<script src="{{ URL::asset('js/home.js') }}"></script>
@endpush