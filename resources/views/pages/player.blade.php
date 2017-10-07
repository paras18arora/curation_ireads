@extends('master')
@section('meta')
	<title>IndiaReads - Reader</title>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('lib/owl.carousel/assets/owl.carousel.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('lib/owl.carousel/assets/owl.theme.default.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/player.css') }}">
@endpush
@section('header')
@endsection
@section('content')
	@php $post_type = $type1; @endphp
	<div class="top player-bar">
		<nav>
		  	<ul class="pager pager-top">
		    	<li class="previous menu"><a href="javascript:void(0)" onclick="openNav()"><i class="fa fa-bars fa-2x"></i></li>
		    	<li class="next go-to-course"><a href="{{ route('course',['type' => $post_type,'name' => 'course-name']) }}"><span aria-hidden="true">&larr;</span> Go To Course Page</a></li>
		  	</ul>
		</nav>
	</div>
	<div class="player wrapper">
		@for($i = 0; $i < $totalfiles; $i++)
	    <div class="item">
	    	@if($post_type == 'video')
	    		@include('pages.video')
	    	@elseif($post_type == 'article' && $no_of_files!=0)
	    		@include('pages.tutorials')
	    	@elseif($post_type == 'article' || $post_type == 'database_article' || $post_type == 'news')
	    		@include('pages.image')
            @elseif($post_type == 'book')
	    		@include('pages.text')
	    	@endif
	    	
	    </div>
	    @endfor
	</div>
	<div class="bottom player-bar">
		<nav>
		  	<ul class="pager pager-bottom">
		    	<li class="previous"><a href="javascript:void(0)"><span aria-hidden="true">&larr;</span> Previous</a></li>
		    	<!-- <li class="center"><h3 class="controls"><i class="fa fa-angle-left"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-play"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right"></i></h3></li> -->
		    	<li class="next"><a href="javascript:void(0)">Next <span aria-hidden="true">&rarr;</span></a></li>
		  	</ul>
		</nav>
	</div>
	<div id="mySidenav" class="sidenav">
	  	<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
	  	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	  		@for($i = 1; $i <= 5; $i++)
	  	  	<div class="panel panel-default">
		  	    <div class="panel-heading" role="tab" id="heading-{{ $i }}">
		  	      	<h4 class="panel-title">
		  	        	<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $i }}" aria-expanded="true" aria-controls="collapse-{{ $i }}">
		  	          		Chapter {{ $i }}
		  	        	</a>
		  	      	</h4>
  	    		</div>
	  	    	<div id="collapse-{{ $i }}" class="panel-collapse collapse @if( $i == 2) in @endif" role="tabpanel" aria-labelledby="heading-{{ $i }}">
		  	      	<div class="panel-body">
		  	      		@for($j = 1; $j <= 5; $j++)
		  	        	<a href="javascript:void(0)">Topic {{ $j }}</a>
		  	        	@endfor
		  	      	</div>
		  	    </div>
	  	  	</div>
	  	  	@endfor
	  	</div>
	</div>
@endsection
@section('footer')
@endsection
@push('js')
	<script src="{{ URL::asset('lib/owl.carousel/owl.carousel.min.js') }}"></script>
	<script src="{{ URL::asset('js/player.js') }}"></script>
@endpush