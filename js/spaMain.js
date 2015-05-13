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


function showActionDialog(dialog) {
    // Prepare variable
    var title   = '';
    var message = '';
    if(typeof(dialog.title) != 'undefined') {
        title = dialog.title;
    }
    if(typeof(dialog.message) != 'undefined') {
        message = dialog.message;
    }

    var actionDialogHTML    = '<div class="action-dialog-container">'
                            + '     <div class="action-dialog">'
                            + '         <h2>' + title + '</h2>'
                            + '         <span class="message">' + message + '</span>'
                            + '     </div>'
                            + '</div>';
    $('body').prepend(actionDialogHTML);
    $('.action-dialog-container').css('visibility', 'hidden');

    // Set width
    if(typeof(dialog.boxWidth) != 'undefined') {
        $('.action-dialog').css('width', dialog.boxWidth + 'px');
    }

    for (i in dialog.actionList) {
        // Create action button

        var actionItem = dialog.actionList[i];
        var desc = '';
        if(typeof(actionItem.desc) != 'undefined') {
            desc = actionItem.desc;
        }

        var actionBtn = '<div id="action-btn-' + actionItem.id + '" class="action-btn">'
                      + '<h1>' + actionItem.name + '</h1>'
                      + desc
                      + '</div>';
        $('.action-dialog').append(actionBtn);

        // Add event when click button
        $('#action-btn-' + actionItem.id).click(actionItem.func);
    }

    // Set position
    $('.action-dialog').css('margin-top', -Math.abs($('.action-dialog').outerHeight() / 2));
    $('.action-dialog').css('margin-left', -Math.abs($('.action-dialog').outerWidth() / 2));
    
    // Display
    $('.action-dialog-container').css('visibility', 'visible');

    if(typeof(dialog.success) == 'function') {
        dialog.success();
    }
}

function hideActionDialog() {
    $('.action-dialog-container').remove();
}