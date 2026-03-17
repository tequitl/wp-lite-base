<?php
/**
 * Plugin Name: FreeRadio
 * Description: Live transmit + records.
 * Version: 0.1.0
 * Author: WP Lite
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FREERADIO_VERSION', '0.1.0' );
define( 'FREERADIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'FREERADIO_URL', plugin_dir_url( __FILE__ ) );
define( 'FREERADIO_VIEWS', FREERADIO_PATH . 'views/' );

require_once FREERADIO_PATH . 'includes/class-freeradio.php';

FreeRadio::init();
