<?php

namespace CronLogger;

use CronLogger\Components\Component;

class Schedule extends Component {
	public function onCreate(): void {
		parent::onCreate();
		add_action('admin_init', [$this, 'init']);
		add_action(Plugin::SCHEDULE, [$this, 'execute']);
	}

	public function init(): void {
		if (!wp_next_scheduled(Plugin::SCHEDULE)) {
			wp_schedule_event(time(), 'daily', Plugin::SCHEDULE);
		}
	}

	public function execute(): void {
		$this->plugin->log->clean();
	}
}
