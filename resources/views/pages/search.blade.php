@extends('master')
@section('meta')
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<meta name="csrf-token3" content="{!! Session::token() !!}">
	<title>IndiaReads - Search</title>
	<script type="text/javascript">
	$( document ).ready(function() {
    var video='<?php echo $filter1; ?>';
    var book='<?php echo $filter2; ?>';
    var article='<?php echo $filter3; ?>';
    $("li").removeClass("active");
    var paginatevalue='<?php echo $paginatevalue; ?>';
    if (paginatevalue==1)
       $(".a7").addClass("disabled");
   if (paginatevalue==5)
       $(".a6").addClass("disabled");
    var nexttoken='<?php echo $nexttoken; ?>'
   
    $('.a'+paginatevalue).addClass('active');

    if(video=="video")
    $('#videoid').attr('checked', true);
    if(book=="book")
    $('#bookid').attr('checked', true);
    if(article=="article")
    $('#articleid').attr('checked', true);
});
	</script>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('css/search.css') }}">
@endpush
@section('header')
	@include('includes.header')
@endsection
@section('content')
	<div class="jumbotron clearfix">
		<p class="col-md-12"> results found @if(isset($req['q'])) for keyword '{{ $req['q'] }}' @endif</p>
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
			  	      		        <p class="col-md-12"><a class="subcat-name @if(!empty($req['topic']) && $req['topic'] == str_slug($sub_cat->sub_category_name)) active @endif" href="{{ route('search',['q' => $req['q'],'cat' => str_slug($cat->category_name), 'topic' => str_slug($sub_cat->sub_category_name)]) }}">
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
		@if(empty($data))
			<h3 class="col-md-7 content" align="center">No Courses Found</h3>
		@else
		<div class="col-md-7 content">
		 @if($filter_value==0)
			@foreach($data as $key)
            
			<form id="detail" method="post" action="{{ route('course',['type' => $key['type'],'name' => str_slug($key['title'])]) }}">
				<a onclick="$(this).closest('form').submit();">
				<input type='hidden' name='_token' value='{!! Session::token() !!}'>
				<input type='hidden' name='isbn' value="@if($key['type']=='book') {{$key['isbn']}} @else @endif">
				<input type='hidden' name='videoid' value="@if($key['type']=='video') {{$key['VideoId']}} @else @endif">
				<input type='hidden' name='no_of_files' value="{{$key['no_of_files']}}">
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
									{{$key['authors'][0]}} @else  
								@endif">

					<div class="course-items row">
						<div class="col-md-3 item-img"><img class="item-size" src="@if($key['type']=='video') {{$key['thumbnail1']}}  @elseif($key['type']=='article') @if($key['imagesrc']!='')https://cdn-images-1.medium.com/fit/t/400/400/{{$key['imagesrc']}} @else {{ URL::asset('img/article.jpg') }} @endif @elseif($key['type']=='book') {{$key['thumbnails']['thumbnail']}} @else {{ URL::asset('img/article.jpg') }} @endif" /></div>
						<div class="col-md-9 item-content">
							<h3>{{ $key['title'] }}</h3>
							<h4>
								@if(isset($key['authors'][0])) 
									{{$key['authors'][0]}} 
								@endif
							</h4>
							<p>{{ substr($key['description'],0,200) }}...</p>
						</div>
					</div>
				</a>
				</form>
			@endforeach
			@else
			@foreach($data1 as $key)
            
			<form id="detail" method="post" action="{{ route('course',['type' => $key['type'],'name' => str_slug($key['title'])]) }}">
				<a onclick="$(this).closest('form').submit();">
				<input type='hidden' name='_token' value='{!! Session::token() !!}'>
				<input type='hidden' id="dataid" name='dataa' value='{{serialize($data)}}'>
				<input type='hidden' name='videoid' value="@if($key['type']=='video') {{$key['VideoId']}} @else @endif">
				<input type='hidden' name='isbn' value="@if($key['type']=='book') {{$key['isbn']}} @else @endif">
				<input type="hidden" name="title" value="{{$key['title']}}">
				<input type="hidden" name="source" value="@if(isset($key['source'])){{$key['source']}} @else '' @endif">
				<input type="hidden" name="id" value="@if(isset($key['id'])){{$key['id']}} @else '' @endif">
				<input type="hidden" name="type" value="{{$key['type']}}">
				<input type="hidden" name="link" value="@if(isset($key['link'])){{$key['link']}} @else '' @endif">
				<input type="hidden" name="description" value="{{$key['description']}}">
				<input type="hidden" name="rating" value="{{$key['rating']}}">
				<input type="hidden" name="imagesrc" value="@if($key['type']=='video') {{$key['thumbnail1']}}  @elseif($key['type']=='article') @if($key['imagesrc']!='')https://cdn-images-1.medium.com/fit/t/400/400/{{$key['imagesrc']}} @else {{ URL::asset('img/article.jpg') }} @endif @elseif($key['type']=='book') {{$key['thumbnails']['thumbnail']}} @else {{ URL::asset('img/article.jpg') }} @endif">
				<input type="hidden" name="author" value="@if(isset($key['authors'][0])) 
									{{$key['authors'][0]}} @else  
								@endif">

					<div class="course-items row">
						<div class="col-md-3 item-img"><img class="item-size" src="@if($key['type']=='video') {{$key['thumbnail1']}}  @elseif($key['type']=='article') @if($key['imagesrc']!='')https://cdn-images-1.medium.com/fit/t/400/400/{{$key['imagesrc']}} @else {{ URL::asset('img/article.jpg') }} @endif @elseif($key['type']=='book') {{$key['thumbnails']['thumbnail']}} @else {{ URL::asset('img/article.jpg') }} @endif" /></div>
						<div class="col-md-9 item-content">
							<h3>{{ $key['title'] }}</h3>
							<h4>
								@if(isset($key['authors'][0])) 
									{{$key['authors'][0]}} 
								@endif
							</h4>
							<p>{{ substr($key['description'],0,200) }}...</p>
						</div>
					</div>
				</a>
				</form>
			@endforeach
			@endif
			<center>
			<nav aria-label="...">
			<?php $paginateaddress="/search?q=".$req['q']."&nextYoutubetoken=".$youtube_token."&course_type=".$course_type."&nexttoken="; ?>
			<form method="post" action="{{ route('search',['q' => $req['q'],'filtervalue' => '1','nextYoutubetoken' => $youtube_token]) }}">
			<input type='hidden' name='_token' value='{!! Session::token() !!}'>
			<input type='hidden' name='filter1' value='{{$filter1}}'>
			<input type='hidden' id="paginatevalue" name='paginatevalue' value=''>
           <input type='hidden' name='filter2' value='{{$filter2}}'>
           <input type='hidden' name='filter3' value='{{$filter3}}'>
			<input type='hidden' id="nexttoken" name='nexttoken' value=''>
  <ul class="pagination">
    <li class="page-item a7">
      
      <a class="page-link" onclick="selected_page('<?php echo $paginatevalue-1; ?>',this);" href="#">&laquo; Previous</a>
    </li>
    <li class="page-item a1"><a class="page-link" onclick="selected_page(1,this);" href="#" >1</a></li>
    <li class="page-item a2">
      <a class="page-link " onclick="selected_page(2,this);" href="#">
        2
      </a>
    </li>
    <li class="page-item a3"><a class="page-link" onclick="selected_page(3,this);" href="#">3</a></li>
    <li class="page-item a4"><a class="page-link" onclick="selected_page(4,this);" href="#">4</a></li>
    <li class="page-item a5"><a class="page-link" onclick="selected_page(5,this);" href="#">5</a></li>
    <li class="page-item a6">
      <a class="page-link" onclick="selected_page('<?php echo $paginatevalue+1; ?>',this);" href="#">Next &raquo;</a>
    </li>
  </ul>
  </form>
</nav>
	</center>
		</div>
		@endif

		<div class="col-md-2 filters">
			
			<form id="filterform" method="post" action="{{ route('search',['q' => $req['q'],'filter_value' => '1','nextYoutubetoken' => $youtube_token, 'course_type' => 'book','nexttoken' => $nexttoken]) }}">
			@if(!empty($req['course_type']))
				<span style="float: right"><a onClick="clearfilter(this);" >Clear</a></span>
			@endif
			<hr style="margin: 0px">
			<h6>Refine by Type</h6>
			<div class="cat-filters">
			<div class="checkbox">
			
			
			    
			    <input type='hidden' id="dataid" name='data' value='{{serialize($data)}}'>
			    <input type='hidden' name='_token' value='{!! Session::token() !!}'>
                <label><input id="videoid" type="checkbox" onChange="this.form.submit();"  name="filter1" value="video">Video</label>
                </div>
                <div class="checkbox">
                <label><input id="bookid" type="checkbox" onChange="this.form.submit();"  name="filter2" value="book">Book</label>
                </div>
                <div class="checkbox">
                <label><input id="articleid" type="checkbox" onChange="this.form.submit();"  name="filter3" value="article">Article</label>
                </div>
             
			
				
			</div>
			
		
			
			</form>
		</div>
	</div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
	<script src="{{ URL::asset('js/search.js') }}"></script>
@endpush