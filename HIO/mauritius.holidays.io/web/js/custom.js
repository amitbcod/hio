
$(document).ready(function(){
  $(".footer_main_menu").click(function(){
    $("#travel-info-sub").slideToggle("slow");
  });
});

$(document).ready(function(){
  $(".responsible_travel").click(function(){
    $("#sust-travel-sub").slideToggle("slow");
  });
});

$(document).ready(function(){
  $(".accommodation_by_region").click(function(){
	// $('.sust-travel-sub_region').slideToggle();
      $(this).children(".sust-travel-sub_region").slideToggle("slow");
  });
});


$(document).ready(function() {
  
  $(window).scroll(function () {
    if ($(window).scrollTop() > 130) {
      $('.main-nav').addClass('navbar-fixed');
    }
    if ($(window).scrollTop() < 131) {
      $('.main-nav').removeClass('navbar-fixed');
    }
  });
});



