<?php
/**
 * Plugin Name: Cron Logger
 * Description: Logs for wp-cron.php runs.
 * Version: 1.1.0
 * Requires at least: 5.3
 * Tested up to: 5.8
 * Author: Palasthotel <rezeption@palasthotel.de> (Edward Bock)
 * Author URI: https://palasthotel.de
 * Domain Path: /languages
 * Text Domain: cron-logger
 * @copyright Copyright (c) 2017, Palasthotel
 * @package Palasthotel\CronLogger
 */

namespace CronLogger;

require_once dirname( __FILE__ ) . "/vendor/autoload.php";


/**
 * @property Timer timer
 * @property Log log
 * @property Services services
 * @property Page page
 */
class Plugin extends Components\Plugin {

	/**
	 * ---------------------------------------------
	 * constants
	 * ---------------------------------------------
	 */
	const DOMAIN = "cron-logger";

	const ACTION_INIT = "cron_logger_init";

	const ACTION_WP_CRON_START = "cron_logger_wp_cron_start";
	const ACTION_WP_CRON_FINISH = "cron_logger_wp_cron_shutdown";

	const FILTER_EXPIRE = "cron_logger_expire";

	/**
	 * Plugin constructor
	 */
	function onCreate() {

		$this->loadTextdomain(
			Plugin::DOMAIN,
			"languages"
		);

		$this->timer    = new Timer();
		$this->log      = new Log( $this );
		$this->services = new Services( $this );
		$this->page     = new Page( $this );
	}

	/**
	 * on activation
	 */
	function onSiteActivation() {
		$this->log->createTable();
	}
}

Plugin::instance();
