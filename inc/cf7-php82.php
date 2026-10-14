<?php
/**
 * Lady Mom – Contact Form 7 bez ažuriranja dok je PHP 8.2
 * (Ranije Code Snippets #28, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Contact Form 7 ostaje na 6.1.7 dok sajt radi na PHP 8.2 (6.2+ traži PHP 8.3).
// Kad se pređe na PHP 8.3, ovaj snippet se isključi i CF7 normalno ažurira.
if ( version_compare( PHP_VERSION, '8.3', '<' ) ) {
	add_filter( 'site_transient_update_plugins', function ( $t ) {
		if ( is_object( $t ) && isset( $t->response['contact-form-7/wp-contact-form-7.php'] ) ) {
			unset( $t->response['contact-form-7/wp-contact-form-7.php'] );
		}
		return $t;
	} );
	add_filter( 'auto_update_plugin', function ( $u, $item ) {
		return ( isset( $item->plugin ) && 'contact-form-7/wp-contact-form-7.php' === $item->plugin ) ? false : $u;
	}, 10, 2 );
}
