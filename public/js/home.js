$(document).ready(function(){
    $('.latest').owlCarousel({
        loop:true,
        margin:10,
        nav:true,
        navText:["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
        dots:false,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:5
            }
        }
    });

    $('.trending').owlCarousel({
        loop:true,
        margin:10,
        nav:true,
        navText:["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
        dots:false,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:5
            }
        }
    });

    $('.popular').owlCarousel({
        loop:true,
        margin:10,
        nav:true,
        navText:["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
        dots:false,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:5
            }
        }
    });

    $(".newspaper").css('max-height',$('.carousel').height());
});