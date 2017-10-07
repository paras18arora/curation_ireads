$(document).ready(function(){
	$(".item-img").css('min-height',$('.item-content').height());
	$('.filter-tags-url span').on('click',function(){
		$('.filter-tags-url').attr('href',document.URL.replace($(this).text().replace(' ','-').toLowerCase(),''));
	});
});