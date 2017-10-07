@extends('master')
@section('meta')
	<title>IndiaReads - Rooms</title>
@endsection
@push('css')
	<link rel="stylesheet" href="{{ URL::asset('css/rooms.css') }}">
@endpush
@section('header')
	@include('includes.header')
@endsection
@section('content')
 	<div class="container">
	 	<h1>Rooms</h1>
	 	<div class="room-card col-md-3">
			<a href="#">
				<div class="col-md-12 outerdiv">
					<div class="col-md-12 imagediv">
						<div class="image" style="background-image: url('http://lorempixel.com/1920/1080/');">
						</div>
					</div>
					<div class="col-md-12 detailsdiv">
						<h4 class="col-md-12 name">Center Name</h4>
					</div>
				</div>
			</a>
	 	</div>
	 	<div class="room-card col-md-3"></div>
	 	<div class="room-card col-md-3"></div>
	 	<div class="room-card col-md-3"></div>
 	</div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
@endpush