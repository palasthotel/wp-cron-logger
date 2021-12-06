<?php

namespace CronLogger;

use CronLogger\Components\Update;

/**
 * @property Plugin $plugin
 */
class Updates extends Update {

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('admin_init', function(){
			$this->checkUpdates();
		});
	}

	function getVersion(): int {
		return 1;
	}

	function getCurrentVersion(): int {
		return get_option('_cron_logger_version', 0);
	}

	function setCurrentVersion( int $version ) {
		update_option('_cron_logger_version', $version);
	}

	public function update_1(){
		$table = $this->plugin->log->table;
		$this->plugin->log->wpdb->query(
			"ALTER TABLE $table ADD KEY (parent_id)"
		);
	}
}