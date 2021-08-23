<?php
/**
 * Plugin Name: Cron Logger
 * Description: Logs for wp-cron.php runs.
 * Version: 1.0.5
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


class Plugin {

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
	 * ---------------------------------------------
	 * object instance
	 * ---------------------------------------------
	 */

	/** @var Plugin */
	private static $instance;

	/** @return Plugin */
	public static function instance() {
		if ( Plugin::$instance == null ) {
			Plugin::$instance = new Plugin();
		}

		return Plugin::$instance;
	}

	/**
	 * ---------------------------------------------
	 * properties
	 * ---------------------------------------------
	 */

	/**
	 * @var bool
	 */
	var $isDebug = false;

	/**
	 * @var string
	 */
	var $url, $dir;


	/**
	 * Plugin constructor
	 */
	private function __construct() {

		/**
		 * Base paths
		 */
		$this->dir = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );

		/**
		 * load translations
		 */
		load_plugin_textdomain(
			Plugin::DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		require_once dirname( __FILE__ ) . '/inc/timer.php';
		$this->timer = new Timer();

		require_once dirname( __FILE__ ) . '/inc/log.php';
		$this->log = new Log( $this );

		// compatible processes
		require_once dirname( __FILE__ ) . '/inc/compats.php';
		$this->compats = new Compats( $this );

		require_once dirname( __FILE__ ) . '/inc/page.php';
		$this->page = new Page( $this );

		register_activation_hook( __FILE__, array( $this, 'on_activate' ) );

		if ( $this->isDebug ) {
			// in debug always try to create table
			$this->log->createTable();
		}
	}

	/**
	 * on activation
	 */
	function on_activate( $network_wide ) {
		// check if this is a multisite installation
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// check if it is a network activation
			if ( $network_wide ) {
				$network_site = get_network()->site_id;
				$args         = array( 'fields' => 'ids' );
				$site_ids     = get_sites( $args );

				// run the activation function for each blog id
				foreach ( $site_ids as $site_id ) {
					switch_to_blog( $site_id );
					$this->log->createTable();
				}

				// switch back to the network site
				switch_to_blog( $network_site );

				return;
			}
		}
		$this->log->createTable();
	}
}

Plugin::instance();
