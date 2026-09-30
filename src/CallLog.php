<?php

namespace SymplastWebhook;

defined( 'ABSPATH' ) || exit;

// Save and read the call logs and cron logs
class CallLog {
	public static function write_call( $call_id, array $data ) {
		return self::write( 'calls', $call_id, wp_json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	public static function write_job( $call_id, array $data ) {
		return self::write( 'cron', $call_id, wp_json_encode( $data ) );
	}

	public static function read( $file ) {
		$data = is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : null;

		return is_array( $data ) ? $data : null;
	}

	private static function write( $type, $call_id, $contents ) {
		$dir = ABSPATH . 'callrail-logs/' . $type;
		wp_mkdir_p( $dir );

		// Remove characters that could be used in a file path
		$call_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $call_id ) ?: 'unknown';
		$file = sprintf( '%s/%s.%s.log', $dir, wp_date( 'Ymd-His' ), $call_id );

		return file_put_contents( $file, $contents ) !== false ? $file : null;
	}
}
