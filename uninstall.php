<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Remove queued jobs, keep the call logs
wp_unschedule_hook( 'symplast_schedule_cron_event' );
