<?php

namespace CronLogger;

use CronLogger\Components\Component;

class Ajax extends Component {

	const CLEANUP_NONCE_ACTION = 'cron_logger_cleanup';

	public function onCreate(): void {
		parent::onCreate();
		add_action('wp_ajax_cron_logger_cleanup', [$this, 'cleanup']);
	}

	/**
	 * Delete expired logs.
	 *
	 * Reachable only for users who may also see the log page, which is
	 * registered with manage_options. There is deliberately no nopriv variant.
	 */
	function cleanup(): void {
		check_ajax_referer( self::CLEANUP_NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to clean cron logs.', 'cron-logger' ) ),
				403
			);
		}

		$this->plugin->log->clean();

		wp_send_json_success();
	}

}
