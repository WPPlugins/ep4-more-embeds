jQuery( document ).ready(function($) {
	
	// Generic On load events.
	$('div[data-setting-id]').each(function() {
		$(this).parents('tr').attr( 'data-setting-id', $(this).attr('data-setting-id') );
	});
	
	$('input[name*="[layout]"][checked]').parents('.form-table').attr( 'data-layout', $('input[name*="[layout]"][checked]').attr('value') );
	$('input[name*="[artwork]"][checked]').parents('.form-table').attr( 'data-artwork', $('input[name*="[artwork]"][checked]').attr('value') );
	$('input[name*="[tracklist]"]').parents('.form-table').attr( 'data-tracklist', $('input[name*="[tracklist]"]').attr('checked') ? 'true' : 'false' );

	// Generic On click events.
	$('input[name*="[layout]"]').click(function() {
		$(this).parents('.form-table').attr( 'data-layout', $(this).attr('value') );
	});
	
	$('input[name*="[artwork]"]').click(function() {
		$(this).parents('.form-table').attr( 'data-artwork', $(this).attr('value') );
	});
	
	$('input[name*="[tracklist]"]').click(function() {
		$(this).parents('.form-table').attr( 'data-tracklist', $(this).attr('checked') ? 'true' : 'false' );
	});
	
	
	// Specific On click events.
	$('#layout_slim').click(function() {
		if( $('#artwork_big').attr('checked') ) {
			$('#artwork_small').click();
		}
	});
	
	$('#layout_standard').click(function() {
		if( $('#artwork_small').attr('checked') ) {
			$('#artwork_big').click();
		}
	});
	
});