<?php

namespace CronLogger;

use CronLogger\Components\Component;

class Ajax extends Component {
	public function onCreate(): void {
		parent::onCreate();
		add_action('wp_ajax_cron_logger_cleanup', [$this, 'cleanup']);
	}

	function cleanup(): void {
		$this->plugin->log->clean();
		echo "clean!";
	}
}
