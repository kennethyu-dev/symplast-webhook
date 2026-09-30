<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

// Data for the recording webhook
class RecordingPayload {
	public static function from_callrail( array $call ) {
		return [
			'external_call_id' => $call['id'] ?? $call['resource_id'] ?? null,

			'customer_name' => trim( $call['customer_name'] ?? '' ),
			'phone_number' => self::format_e164( $call['customer_phone_number'] ?? '' ),

			'call_start_time' => $call['start_time'] ?? null,
			'call_duration_seconds' => isset( $call['duration'] ) ? (int) $call['duration'] : null,
			'call_direction' => $call['direction'] ?? null,
			'call_answered' => $call['answered'] ?? null,
			'voicemail' => $call['voicemail'] ?? null,

			'call_recording_url' => self::recording_url( $call ),
			'call_recording_player_url' => $call['recording_player'] ?? null,
			'recording_duration_seconds' => isset( $call['recording_duration'] ) ? (int) $call['recording_duration'] : null,

			'tracking_number' => $call['tracking_phone_number'] ?? null,
			'business_number' => $call['business_phone_number'] ?? null,

			'marketing_source' => $call['source'] ?? $call['milestones']['first_touch']['source'] ?? null
		];
	}

	// Use the direct audio link first, the API link needs a key so it goes last
	public static function recording_url( array $call ) {
		return $call['recording_redirect'] ?? $call['recording_player'] ?? $call['recording'] ?? null;
	}

	private static function format_e164( $phone_number ) {
		if ( empty( $phone_number ) ) {
			return $phone_number;
		}

		$digits = preg_replace( '/\D/', '', $phone_number );

		return strlen( $digits ) === 10 ? '+1' . $digits : '+' . $digits;
	}
}
