$(document).ready(function() {
	$(window).scroll(function (event) {
	    var scroll = $(window).scrollTop();
	    if(scroll > 190) {
	    	if(!$('.header-v5').hasClass('header-fixed-shrink')) {
	    		$('.header-v5').addClass('header-fixed-shrink');
	    	}
	    } else {
	    	if($('.header-v5').hasClass('header-fixed-shrink')) {
	    		$('.header-v5').removeClass('header-fixed-shrink');
	    	}
	    }
	});
	$('.miniCartTable').mCustomScrollbar({ 
        theme:"minimal-dark"        
  	});

  	$('li.dropdown').click(function() {
  		$(this).siblings('.active').removeClass('active');
  		$(this).addClass('active');
  		if($(this).hasClass('open')) {
  			$(this).removeClass('open');
  		} else {
  			$(this).addClass('open');
  		}
  	});
});