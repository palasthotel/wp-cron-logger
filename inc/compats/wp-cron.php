<?php

namespace CronLogger;


class WPCron {

	var $times = array();

	public function __construct(Plugin $plugin) {

		$this->log = $plugin->log;
		$this->timer = $plugin->timer;

		if ( defined('DOING_CRON') && DOING_CRON ) {
			add_action("plugins_loaded", array($this, "start"));
			add_action("shutdown", array($this, "shutdown"));
		}
	}

	function start() {
		do_action(Plugin::ACTION_WP_CRON_START);
		$this->log->start("wp-cron.php");
		$this->addCronActions();
	}

	function shutdown() {
		$this->log->update($this->timer->getDuration(), 'Done wp-cron.php 🎉 ');
		do_action(Plugin::ACTION_WP_CRON_FINISH);
	}

	function addCronActions(){
		$crons = _get_cron_array();
		$registered = array();

		foreach ( $crons as $timestamp => $cronhooks ) {
			foreach ($cronhooks as $hook => $keys) {
				add_action($hook, array($this, "before_execute_cron_hook"),0);
				add_action($hook, array($this, "after_execute_cron_hook"),999);
				$registered[] = $hook;
			}
		}

		$msg = __("No registered hooks? Something went wrong. There should be at least WordPress core cron hooks.", Plugin::DOMAIN);
		if(count($registered) > 0){
			$msg = sprintf(__("Register Hooks: %s", Plugin::DOMAIN), implode(', ', $registered));
		}
		$this->log->addInfo($msg);
	}

	function before_execute_cron_hook(){
		$this->times[current_filter()] = time();
		$this->log->addInfo("Starts ".current_filter());
	}
	function after_execute_cron_hook(){
		$this->log->addInfo("Finished ".current_filter(), time() - $this->times[current_filter()]);
	}

}

