import * as jQuery from 'jQuery';

jQuery( function( $ ) {
    $( '' ).on( '', function( e ) {
        $.ajax( {

        } );
    } );
} );

/*

( function( $ ) {
			$( '.dws-framework-notice-<?php echo esc_js( $this->plugin->get_plugin_slug() ); ?>' ).on( 'click.wp-dismiss-notice', '.notice-dismiss', function( e ) {
				var notice = $( this ).closest( '.dws-framework-notice' );
				$.ajax( {
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'dws_framework_utilities_<?php echo esc_js( $this->plugin->get_plugin_safe_slug() ); ?>_dismiss_notice',
						notice_id: $( notice ).data( 'notice-id' ),
						is_global: $( notice ).data( 'notice-global' ),
						_wpnonce: '<?php echo esc_js( wp_create_nonce( 'dws-dismiss-notice' ) ); ?>'
					}
				} );
			} );
		} ) ( jQuery );

 */