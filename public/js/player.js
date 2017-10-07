$(document).ready(function(){
    $(window).resize(function(){
        $(".item").height($(document).height());
    });
    $('.player').owlCarousel({
        loop:false,
        items:1,
        nav:false,
        touchDrag:false,
        mouseDrag:false,
        pullDrag:false,
        freeDrag:false,
        navText:["<i class='fa fa-chevron-left fa-2x'></i>","<i class='fa fa-chevron-right fa-2x'></i>"],
        dots:false,
        onDragged: function(event){
            console.log(event.item.index);
        }
    });
    var owl = $('.owl-carousel');
    owl.owlCarousel();
    $('.pager.pager-bottom .previous').click(function() {
        owl.trigger('prev.owl.carousel');
    });
    $('.pager.pager-bottom .next').click(function() {
        owl.trigger('next.owl.carousel');
    });

    $('.player').mousemove(function(){
        $(".top,.bottom").slideDown(300);
    });

    var timeout = null;
    $('.player-bar').on('mouseover',function(){
        clearTimeout(timeout);
    });
    $('.player').on('mousemove', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            $(".top,.bottom").slideUp(300);
        }, 1500);
    });
});
/* Set the width of the side navigation to 250px */
function openNav() {
    document.getElementById("mySidenav").style.width = "350px";
}

/* Set the width of the side navigation to 0 */
function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}