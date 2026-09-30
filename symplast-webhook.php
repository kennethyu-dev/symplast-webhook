<?php
/**
 * Plugin Name:       Symplast Webhook
 * Description:       Receives CallRail post-call webhooks and forwards them to Symplast.
 * Version:           2.0.0
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * Author:            Kenneth Yu
 * Text Domain:       symplast-webhook
 */

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

spl_autoload_register( function ( $class ) {
	$prefix = __NAMESPACE__ . '\\';

	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}

	$file = __DIR__ . '/src/' . substr( $class, strlen( $prefix ) ) . '.php';

	if ( is_file( $file ) ) {
		require $file;
	}
} );

add_action( 'plugins_loaded', [ new Plugin(), 'register' ] );
