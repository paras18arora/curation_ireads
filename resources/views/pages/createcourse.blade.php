@extends('master')
@section('meta')

@endsection
@push('css')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<link rel="stylesheet" href="{{ URL::asset('css/course.css') }}">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/codemirror.min.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap.tagsinput/0.4.2/bootstrap-tagsinput.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.6.0/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.6.0/css/froala_style.min.css" rel="stylesheet" type="text/css" />
@endpush
@section('header')
	@include('includes.header')
@endsection
@section('content')
    <div class="modal fade" id="myModal1" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tutorial Saved successfully</h4>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
	<div class="jumbotron clearfix">
		<p class="col-md-10 col-md-offset-2"> Create Course  </p>
	</div>
	<div class="container">
	<div id="course">
	<textarea id="article1"></textarea>
    </div>
    <div class="row">
    <br>
    <button class="btn btn-info jumbo" onclick="addtutorial()">Add new tutorial</button>
    <button class="btn btn-info jumbo1" onclick="removetutorial()">Remove</button>
    <button type="button" class="btn btn-info jumbo1" data-toggle="modal" onclick= "submittutorial('{{ csrf_token() }}')">Save the tutorial</button>
    </div>
    </div>
    <div class="modal fade" id="myModal" data-backdrop="static" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Course</h4>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="title" id="title" placeholder="Enter title" />
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="author" id="author" placeholder="Author name..." />
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" data-role="tagsinput" name="tags" id="tags" placeholder="Enter tags" />
        </div>
        <div class="modal-body">
          <textarea class="form-control" rows="4"  name="description" id="description" placeholder="description..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Continue</button>
        </div>
      </div>
      
    </div>
  </div>
@endsection
@section('footer')
	@include('includes.footer')
@endsection
@push('js')
    <!-- Include external JS libs. -->
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/codemirror.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/mode/xml/xml.min.js"></script>
    <script src="//cdn.jsdelivr.net/bootstrap.tagsinput/0.4.2/bootstrap-tagsinput.min.js"></script>
 
    <!-- Include Editor JS files. -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.6.0/js/froala_editor.pkgd.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/createcourse.js') }}"></script>
    <!-- Initialize the editor. -->
    <script> 
    $(window).on('load',function(){
        $('#myModal').modal('show');
    });
    $(function() { 
    	
    	$('#article1').froalaEditor({
            heightMin: 300,
            imageMove: true,
            imageUploadParam: 'image',
            imageUploadMethod: 'post',
            // Set the image upload URL.
            imageUploadURL: '/fileupload/1',
            imageUploadParams: {
                location: 'img', // This allows us to distinguish between Froala or a regular file upload.
                _token: "{{ csrf_token() }}" // This passes the laravel token with the ajax request.
            },
            // URL to get all department images from
            imageManagerLoadURL: '/fileupload',
            // Set the delete image request URL.
            imageManagerDeleteURL: "/filedelete",
            // Set the delete image request type.
            imageManagerDeleteMethod: "DELETE",
            imageManagerDeleteParams: {
                _token: "{{ csrf_token() }}"
            }
            });
            }); 
    function addtutorial()
    {
       var textarea = document.createElement("textarea");
       textarea.setAttribute("id", "article"+number);
       number++;
       document.getElementById('course').appendChild(textarea);

       $(textarea).froalaEditor({
            heightMin: 300,
            imageMove: true,
            imageUploadParam: 'image',
            imageUploadMethod: 'post',
            // Set the image upload URL.
            imageUploadURL: '/fileupload/'+number,
            imageUploadParams: {
                location: 'img', // This allows us to distinguish between Froala or a regular file upload.
                _token: "{{ csrf_token() }}" // This passes the laravel token with the ajax request.
            },
            // URL to get all department images from
            imageManagerLoadURL: '/fileuploads',
            // Set the delete image request URL.
            imageManagerDeleteURL: "/filedelete",
            // Set the delete image request type.
            imageManagerDeleteMethod: "DELETE",
            imageManagerDeleteParams: {
                _token: "{{ csrf_token() }}"
            }
            });
    }
            </script>
    @endpush