$(document).ready(function(){
	$(".notify").animate({bottom:'10px !important'},400);
	$('#browse-cat').on('click', function (event) {
	    $('.category-navbar').toggle();
	});
});
