jQuery(document).ready(function($) {
	'use strict'; // satisfy code inspectors

	$('body').addClass('js-active');
	if ($('body').hasClass('js-active')) {
		$('.sub-menu').css('display','none');
	}

	$('#primary-menu li a:contains(About)').append('<span class="screen-reader-text">Click to be taken to a page with further information about ClassicPress</span>');

	/*if (window.matchMedia("screen and (max-width: 900px)").matches) {
		$('#primary-menu li').last().after(MENU_ITEM.searchform);
		$('#menu-toggle').click(function() {
			$(this).hide();
			$('#menu-toggle-close, .main-navigation, .sub-menu, .get-started').show();
			$('#menu-toggle-close').focus();
		});
		$('#menu-toggle-close').click(function() {
			$('#menu-toggle-close, .main-navigation, .sub-menu, .get-started').hide();
			$('#menu-toggle').show().focus();
		});
	}*/
	if (window.matchMedia("screen and (min-width: 900px)").matches) {
		/*$('#primary-menu li').first().find('li').last().after(MENU_ITEM.searchform);*/
		$('.menu li a').on('mouseenter focus', function() {
			$(this).next('.sub-menu').show();
			$(this).parent().siblings().children('.sub-menu').hide();
			$('.ui-menu-item').hide();
		});
		$('#content, .logo a, .get-started').on('mouseenter focus', function() {
			$('.sub-menu, .ui-menu-item').hide();
		});
	}

	// toggle
	//jQuery(".toggle").click(function () {
    //   jQuery(this).next().slideToggle();
    //}).next().hide();
	
	jQuery(function($) { 
    $(document).ready(function() { // this tells jquery to run the function below once the DOM is ready
    var showText="show"; // choose text for the show/hide link
    var hideText="hide";
    
    $(".toggle").prev().append(' <a href="#" class="toggleLink">'+showText+'</a>'); // append show/hide links to the element directly preceding the element with a class of "toggle"
    
    $('.toggle').hide(); // hide all of the elements with a class of 'toggle'
    $('a.toggleLink').click(function() { // capture clicks on the toggle links

    if ($(this).text()==showText) { // change the link text depending on whether the element is shown or hidden
    $(this).text(hideText);
    } else {
    $(this).text(showText);
    }
   
    $(this).parent().next('.toggle').toggle('fast'); // toggle the display
    return false; // return false so any link destination is not followed

    });
});
});

});
