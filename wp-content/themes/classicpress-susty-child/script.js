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
} );
