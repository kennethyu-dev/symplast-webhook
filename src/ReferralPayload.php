<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

// Data for the referral source webhook
class ReferralPayload {
	public static function from_callrail( array $call ) {
		$first_touch = $call['milestones']['first_touch'] ?? null;

		return [
			'external_call_id' => $call['id'] ?? $call['resource_id'] ?? null,

			'customer_name' => trim( $call['customer_name'] ?? '' ),
			'phone_number' => $call['customer_phone_number'] ?? null,
			'city' => $call['customer_city'] ?? null,
			'state' => $call['customer_state'] ?? null,
			'country' => $call['customer_country'] ?? null,

			'marketing_source' => $call['source'] ?? $first_touch['source'] ?? null,
			'campaign_name' => $call['campaign'] ?? $first_touch['campaign'] ?? null,
			'keyword' => $call['keywords'] ?? $first_touch['keywords'] ?? null,

			// Empty when there is no web session
			'utm_source' => $first_touch['landing_page_url_params']['utm_source'] ?? null,
			'utm_medium' => $first_touch['landing_page_url_params']['utm_medium'] ?? null,
			'utm_campaign' => $first_touch['landing_page_url_params']['utm_campaign'] ?? null,
			'utm_content' => $first_touch['landing_page_url_params']['utm_content'] ?? null,
			'utm_term' => $first_touch['landing_page_url_params']['utm_keyword'] ?? null,

			'landing_page_url' => $call['landing_page_url'] ?? $first_touch['landing'] ?? null,
			'referrer_url' => $first_touch['referrer'] ?? null,
			'device_type' => $first_touch['device'] ?? null,

			'call_answered' => $call['answered'] ?? null,
			'call_duration_seconds' => $call['duration'] ?? null,
			'call_start_time' => $call['start_time'] ?? null,
			'call_recording_url' => RecordingPayload::recording_url( $call ),

			'tracking_number' => $call['tracking_phone_number'] ?? null,
			'business_number' => $call['business_phone_number'] ?? null,
			'call_direction' => $call['direction'] ?? null,
			'voicemail' => $call['voicemail'] ?? null
		];
	}
}
