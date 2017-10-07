@if(count($errors) > 0)
	<div class="row">
		<div class="col-md-12">
			<ul class="notify fail">
				@foreach($errors->all() as $error)
					<li class="list-unstyled">{{$error}}</li>
				@endforeach
			</ul>
		</div>
	</div>
@endif
@if(Session::has('fail'))
	<section class="notify fail">
		{{ Session::get('fail') }}
	</section>
@endif
@if(Session::has('success'))
	<section class="notify success">
		{{ Session::get('success') }}
	</section>
@endif