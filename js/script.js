(function( $ ) {

	$( function() {
		var $tab = $( '.bfaq-tab' );
		var $faqs = $( '.bfaq-faqs' );
		var $q = $( '.bfaq-q' );
		var $a = $( '.bfaq-a' );

		$tab.first().addClass( 'active' );
		$faqs.first().show();

		$tab.on( 'click', function( event ) {
			event.preventDefault();
			$tab.filter( '.active' ).removeClass( 'active' );
			$( this ).addClass( 'active' );

			var index = $tab.index( this );
			$faqs.filter( ':visible' ).hide();
			$faqs.eq( index ).show();
		});

		$q.on( 'click', function( event ) {
			$( this ).toggleClass( 'close' );
			var index = $q.index( this );
			$a.eq( index ).slideToggle();
		});

	});

})( jQuery );
