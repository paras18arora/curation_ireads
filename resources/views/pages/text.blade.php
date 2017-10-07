
 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("books", "0");
      function initialize() {
      	var isbn="<?php echo $isbn; ?>";
        var viewer = new google.books.DefaultViewer(document.getElementById('viewerCanvas'));
        viewer.load('ISBN:'+isbn);
      }
      google.setOnLoadCallback(initialize);
    </script>
<div class="container jumbotron reader">
	<h3>{{$title}}</h3>
	<div id="viewerCanvas" style="width: 100%; height: 900px; clear:both;"></div>
</div>