<?php

namespace CronLogger;


class Compats {
	public function __construct( Plugin $plugin ) {

		require_once dirname( __FILE__ ) . '/compats/wp-cron.php';
		$this->wp_cron = new WPCron( $plugin );

		require_once dirname( __FILE__ ) . '/compats/solr-plugin.php';
		$this->solr = new SolrPlugin( $plugin );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

	}

	function plugins_loaded() {
		do_action( Plugin::ACTION_INIT, Plugin::instance() );
	}

}