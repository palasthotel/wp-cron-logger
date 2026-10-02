<?php
/**
 * Plugin Name: Cron Logger - DEV
 * Description: Development wrapper, loads public/plugin.php. Never deployed.
 * Version: X.X.X
 * Author: Palasthotel <webmaster@palasthotel.de>
 * Author URI: https://palasthotel.de
 * Text Domain: cron-logger
 * Domain Path: /public/languages
 * Requires PHP: 8.1
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/public/plugin.php';

use CronLogger\Plugin;

// public/plugin.php registers its activation hooks for its own file, which is not
// the one WordPress activates while the repository itself is the plugin.
register_activation_hook( __FILE__, function ( $network_wide ) {
	Plugin::instance()->onActivation( $network_wide );
} );

register_deactivation_hook( __FILE__, function ( $network_wide ) {
	Plugin::instance()->onDeactivation( $network_wide );
} );
