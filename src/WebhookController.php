<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

class WebhookController {
	public function register_routes() {
		register_rest_route( 'symplast/webhook', '/postcall', [
			'methods' => 'POST',
			'callback' => [ $this, 'handle' ],
			'permission_callback' => [ $this, 'check_signature' ],
		] );
	}

	// Only block requests when SYMPLAST_WEBHOOK_ENFORCE_SIGNATURE is true
	public function check_signature( \WP_REST_Request $request ) {
		if ( ! Config::enforce_signature() ) {
			return true;
		}

		$signature = $this->signature_status( $request );

		if ( $signature === 'valid' ) {
			return true;
		}

		error_log( "Symplast Webhook: rejected request, signature $signature" );

		return new \WP_Error( 'invalid_signature', 'Invalid signature.', [ 'status' => 401 ] );
	}

	public function handle( \WP_REST_Request $request ) {
		$call = $request->get_json_params();

		if ( empty( $call ) || ! is_array( $call ) ) {
			return new \WP_REST_Response( [ 'status' => 'error', 'result' => 'invalid request' ], 400 );
		}

		$entry = [
			'signature_check' => $this->signature_status( $request ),
			'callrail_data' => $call,
			'payload' => ReferralPayload::from_callrail( $call ),
			'recording_payload' => RecordingPayload::from_callrail( $call ),
		];

		$log_file = CallLog::write_call( $entry['recording_payload']['external_call_id'], $entry );

		if ( ! $log_file ) {
			return new \WP_REST_Response( [ 'status' => 'error', 'result' => 'could not write call log' ], 500 );
		}

		// Skip missed calls with no recording
		if ( empty( $call['answered'] ) && empty( $entry['recording_payload']['call_recording_url'] ) ) {
			return new \WP_REST_Response( [ 'status' => 'skipped', 'result' => 'nothing to send' ], 200 );
		}

		if ( ! ForwardCallJob::schedule( $log_file ) ) {
			return new \WP_REST_Response( [ 'status' => 'error', 'result' => 'could not schedule' ], 500 );
		}

		return new \WP_REST_Response( [ 'status' => 'scheduled' ], 200 );
	}

	// Check the CallRail signature (base64 HMAC-SHA1 of the body)
	private function signature_status( \WP_REST_Request $request ) {
		$key = Config::signing_key();
		$signature = $request->get_header( 'signature' );

		if ( ! $key ) {
			return 'unconfigured';
		}

		if ( ! $signature ) {
			return 'missing';
		}

		$expected = base64_encode( hash_hmac( 'sha1', (string) $request->get_body(), $key, true ) );

		return hash_equals( $expected, $signature ) ? 'valid' : 'invalid';
	}
}
