jQuery( document ).ready( function( $ ) {
	var showText = 'show'; // choose text for the show/hide link
	var hideText = 'hide';

	// append show/hide links to the element directly preceding the element
	// with a class of "toggle"
	$( '.toggle' ).prev().append(
		' <a href="#" class="toggleLink">' + hideText + '</a>'
	);

	$( 'a.toggleLink' ).click( function() {
		if ( $( this ).text() === showText ) {
			$( this ).text( hideText );
		} else {
			$( this ).text( showText );
		}

		// toggle the display
		$( this ).parent().next( '.toggle' ).toggle( 'fast' );

		return false; // do not follow link destination
	} );

	// Fix main menu hover and keyboard navigation on IE11 and older Edge
	// Note, also using lack of :focus-within as a proxy for whatever issue
	// causes submenus to not expand normally (broken selector specificity?)
	// and whatever issue causes the sidebar to display incorrectly (width too
	// narrow)
	try {
		document.querySelector( ':focus-within' );
	} catch ( e ) {
		var sel = '.nav--toggle-sub li';
		function submenuExpand() {
			$( this ).children( 'ul.sub-menu' ).addClass( 'open' );
		}
		function submenuCollapse() {
			$( this ).children( 'ul.sub-menu' ).removeClass( 'open' );
		}
		$( document )
			.on( 'mouseover', sel, submenuExpand )
			.on( 'mouseout', sel, submenuCollapse )
			.on( 'focusin', sel, submenuExpand )
			.on( 'focusout', sel, submenuCollapse );
		$( document.body ).addClass( 'ie11' );
	}
} );
