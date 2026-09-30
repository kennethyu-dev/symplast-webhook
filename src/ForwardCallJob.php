<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

class ForwardCallJob {
	const HOOK = 'symplast_schedule_cron_event';

	const MAX_ATTEMPTS = 3;

	// Answered calls go to referral, calls with a recording go to recording
	public static function targets( $call ) {
		$targets = [];

		if ( ! empty( $call['callrail_data']['answered'] ) && ! empty( $call['payload'] ) && Config::referral_url() ) {
			$targets['referral'] = [ Config::referral_url(), $call['payload'] ];
		}

		if ( ! empty( $call['recording_payload']['call_recording_url'] ) && Config::recording_url() ) {
			$targets['recording'] = [ Config::recording_url(), $call['recording_payload'] ];
		}

		return $targets;
	}

	public static function schedule( $log_file, $attempt = 1, $target = '' ) {
		$delay = $attempt === 1 ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS;
		$args = [ $log_file, $attempt, $target ];

		// Return true if the same job is already scheduled
		return wp_schedule_single_event( time() + $delay, self::HOOK, $args ) || wp_next_scheduled( self::HOOK, $args );
	}

	public function handle( $log_file, $attempt = 1, $target = '' ) {
		$call = CallLog::read( $log_file );
		$result = [ 'attempt' => $attempt ];

		foreach ( self::targets( $call ) as $name => list( $url, $payload ) ) {
			// On retry, only send the one that failed
			if ( $target && $target !== $name ) {
				continue;
			}

			list( $status, $body, $retry ) = $this->send( $url, $payload );

			$result[ $name . '_status' ] = $status;
			$result[ $name . '_response' ] = $body;

			if ( $retry && $attempt < self::MAX_ATTEMPTS ) {
				self::schedule( $log_file, $attempt + 1, $name );
			}
		}

		if ( count( $result ) > 1 ) {
			CallLog::write_job( $call['recording_payload']['external_call_id'] ?? $call['payload']['external_call_id'] ?? null, $result );
		}
	}

	// Retry on timeout, 429 or 5xx error
	private function send( $url, $payload ) {
		$response = wp_remote_post( $url, [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
			],
			'body' => wp_json_encode( $payload ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ $response->get_error_message(), null, true ];
		}

		$status = wp_remote_retrieve_response_code( $response );

		return [ $status, json_decode( wp_remote_retrieve_body( $response ) ), $status === 429 || $status >= 500 ];
	}
}
