<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

// Get settings from wp-config.php
class Config {
	public static function referral_url() {
		return defined( 'SYMPLAST_WEBHOOK_REFERRAL_URL' ) ? SYMPLAST_WEBHOOK_REFERRAL_URL : '';
	}

	public static function recording_url() {
		return defined( 'SYMPLAST_WEBHOOK_RECORDING_URL' ) ? SYMPLAST_WEBHOOK_RECORDING_URL : '';
	}

	public static function signing_key() {
		return defined( 'SYMPLAST_WEBHOOK_SIGNING_KEY' ) ? SYMPLAST_WEBHOOK_SIGNING_KEY : '';
	}

	public static function enforce_signature() {
		return defined( 'SYMPLAST_WEBHOOK_ENFORCE_SIGNATURE' ) && SYMPLAST_WEBHOOK_ENFORCE_SIGNATURE;
	}
}
