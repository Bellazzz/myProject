$(document).ready(function() {
	$(window).scroll(function (event) {
	    var scroll = $(window).scrollTop();
	    if(scroll > 100) {
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
});