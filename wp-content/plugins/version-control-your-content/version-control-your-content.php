<?php 
/**
 * Plugin Name: Version Control Your Content
 * Description: Never lose the work your are doing in your WordPress backend or wp-admin area. All the changes you make on any backend page will be version controlled securely using Git services.
 * Author: Haris Amjed
 * Version: 1.0.0
 * Text Domain: version-control-your-content
 * Domain Path: /i18n/languages/
 * Requires at least: 5.6
 * Requires PHP: 7.2
 *
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit; // No script kiddies please.
use VCYC\Controllers\Plugin;
/**
 * Auto load all the classes using composer
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Initialize the plugin
 * @param string pass plugin main file
 * @param string pass plugin main directory
 */
new Plugin(__FILE__, __DIR__);