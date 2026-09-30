<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

class Plugin {
	public function register() {
		add_action( 'rest_api_init', [ new WebhookController(), 'register_routes' ] );
		add_action( ForwardCallJob::HOOK, [ new ForwardCallJob(), 'handle' ], 10, 3 );
	}
}
