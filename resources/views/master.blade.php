<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		@yield('meta')
		<link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">
		<link rel="stylesheet" href="{{ URL::asset('lib/font-awesome/css/font-awesome.min.css') }}">
		<link rel="stylesheet" href="{{ URL::asset('css/common.css') }}">
		@stack('css')
		<script src="{{ URL::asset('lib/jquery.min.js') }}"></script>
	</head>
	<body>
		@yield('header')
		<div class="main">
			@yield('content')
		</div>
		@yield('footer')
		<script src="{{ URL::asset('lib/bootstrap/js/bootstrap.min.js') }}"></script>
		
		<script src="{{ URL::asset('js/common.js') }}"></script>
		@stack('js')
	</body>
</html>