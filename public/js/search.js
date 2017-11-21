$(document).ready(function(){
	$(".item-img").css('min-height',$('.item-content').height());
	$('.filter-tags-url span').on('click',function(){
		$('.filter-tags-url').attr('href',document.URL.replace($(this).text().replace(' ','-').toLowerCase(),''));
	});
});
function selected_page(val,x)
{
    document.getElementById("nexttoken").value=val-1;
    document.getElementById("paginatevalue").value=val;
	$(x).closest('form').submit();

}

function clearfilter(val)
{
    document.getElementById("videoid").value="";
    document.getElementById("articleid").value="";
    document.getElementById("bookid").value="";
    document.getElementById("createdarticleid").value="";
	val.closest('form').submit();

}
function toggleCheckbox(element)
{
	$(element).closest('form').submit();
}
function toggleCheckbox1(element)
{
	$(element).closest('form').submit();
}
function toggleCheckbox2(element)
{
	$(element).closest('form').submit();
}