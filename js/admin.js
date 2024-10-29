(function($) {

	var item_template = '' +
	'<tr class="row">' +
		'<td class="order"></td>' +
		'<td class="content">' +
			'<div>' +
				'<label>' + BFAQ.labels.question + '<br><input type="text" name="" value=""></label>' +
			'</div>' +
			'<div>' +
				'<label>' + BFAQ.labels.answer + '<br><textarea name="" rows="10"></textarea></label>' +
			'</div>' +
		'</td>' +
		'<td class="operation">' +
			'<p><a href="#" class="up-row button">' + BFAQ.labels.up + '</a></p>' +
			'<p><a href="#" class="down-row button">' + BFAQ.labels.down + '</a></p>' +
			'<p><a href="#" class="delete-row button">' + BFAQ.labels.remove + '</a></p>' +
		'</td>' +
	'</tr>';

	/**
	 * Document ready.
	 */
	$(function() {

		/**
		 * Setup sort UI
		 */
		var $sortable = $('.sortable tbody');
		$sortable.sortable({
			helper: sortHelper
		});
		// $sortable.disableSelection();
		//
		$sortable.on('sortupdate', function(event) {
			numbering();
		})

		/**
		 * Initial items
		 */
		if ( BFAQ.faqs ) {
			jQuery.each( BFAQ.faqs, function( i, obj ) {
				var $item = $( item_template );
				$item.find( '.content input' ).val( obj.q );
				$item.find( '.content textarea' ).html( obj.a );
				$( '.faq-table tbody' ).append( $item );
			});

			numbering();
		}

		/**
		 * Delete row
		 */
		$(document).on('click', '.delete-row', function(event) {
			event.preventDefault();
			$( this ).parent().parent().parent().remove();

			numbering();
		});

		/**
		 * Up row
		 */
		$(document).on( 'click', '.up-row', function(event) {
			event.preventDefault();
			var $row = $( this ).parent().parent().parent();
			var $prev = $row.prev();
			if ( $prev.length ) {
				$row.insertBefore( $prev );
				numbering();
			}
		});

		/**
		 * Down row
		 */
		$(document).on( 'click', '.down-row', function(event) {
			event.preventDefault();
			var $row = $( this ).parent().parent().parent();
			var $next = $row.next();
			if ( $next.length ) {
				$row.insertAfter( $next );
				numbering();
			}
		});

		/**
		 * Add row
		 */
		$( '.add-row' ).on( 'click', function( event ) {
			event.preventDefault();
			$( '.faq-table tbody' ).append( item_template );
			numbering();
			$( this ).trigger( 'blur' );
		});

		if ( ! $( '.row' ).length ) {
			$( '.add-row' ).trigger( 'click' );
		}

		/**
		 * Sort helper function
		 */
		function sortHelper( e, tr ) {
			var $originals = tr.children();
			var $row = tr.clone();
			$row.children().each( function( index ) {
				var $td = $originals.eq( index );
				$( this ).width( $td.width() );

			}); // each

			var bgColor = tr.css( 'background-color' );
			$row.css( { 'background-color': bgColor } );

			return $row;
		}

		/**
		 * Refresh row number
		 */
		function numbering() {
			$('.row', $sortable).each(function(i) {
				var number = i + 1;
				var $row = $( this );
				$row.find( '.order' ).text( number );
				$row.find( 'input[type="text"]' ).attr( 'name', 'bfaq_faq[' + i + '][q]' );
				$row.find( 'textarea' ).attr( 'name', 'bfaq_faq[' + i + '][a]' );
			});
		}

	}); // Document ready

})(jQuery);
