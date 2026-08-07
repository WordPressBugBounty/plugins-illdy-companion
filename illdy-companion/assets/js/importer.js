/**
 * Demo content importer.
 *
 * Replaces the old flow, which posted a [class, method] pair to the Illdy theme's generic
 * welcome-screen AJAX dispatcher. This talks to the plugin's own endpoint with its own
 * nonce, and asks for confirmation first because the import overwrites Customizer
 * settings and front page widgets.
 */
( function( $ ) {
	'use strict';

	$( function() {
		var $button = $( '#illdy-run-import' );

		if ( ! $button.length || 'undefined' === typeof illdyCompanionImporter ) {
			return;
		}

		var i18n      = illdyCompanionImporter.i18n || {};
		var $spinner  = $button.siblings( '.spinner' );
		var $result   = $( '#illdy-import-result' );

		function notice( type, message ) {
			$result.html(
				$( '<div/>', { 'class': 'notice notice-' + type + ' inline', css: { margin: '1em 0', maxWidth: '46em' } } )
					.append( $( '<p/>' ).text( message ) )
			);
		}

		function busy( isBusy ) {
			$button.prop( 'disabled', isBusy ).text( isBusy ? i18n.running : i18n.button );
			$spinner.toggleClass( 'is-active', isBusy );
		}

		$button.on( 'click', function( e ) {
			e.preventDefault();

			var steps = $( '.illdy-import-step:checked' ).map( function() {
				return this.value;
			} ).get();

			if ( ! steps.length ) {
				notice( 'error', i18n.noneFound );
				return;
			}

			if ( ! window.confirm( i18n.confirm ) ) {
				return;
			}

			$result.empty();
			busy( true );

			$.ajax( {
				type: 'POST',
				url: illdyCompanionImporter.ajaxurl,
				dataType: 'json',
				data: {
					action: 'illdy_companion_import_demo',
					nonce: illdyCompanionImporter.nonce,
					steps: steps
				}
			} ).done( function( response ) {
				if ( response && response.success ) {
					notice( 'success', ( response.data && response.data.message ) || i18n.success );

					// The Customizer settings and widgets just changed underneath the page,
					// so reload rather than leave stale state on screen.
					window.setTimeout( function() {
						window.location.reload();
					}, 1200 );
					return;
				}

				notice( 'error', ( response && response.data && response.data.message ) || i18n.failure );
				busy( false );
			} ).fail( function( jqXHR ) {
				var message = i18n.failure;

				if ( jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
					message = jqXHR.responseJSON.data.message;
				}

				notice( 'error', message );
				busy( false );
			} );
		} );
	} );
}( jQuery ) );
