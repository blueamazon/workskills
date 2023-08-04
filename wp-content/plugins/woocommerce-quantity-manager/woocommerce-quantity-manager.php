<?php
/**
 * The main plugin file for WooCommerce Quantity Manager
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 *
 * @wordpress-plugin
 * Plugin Name:     WooCommerce Quantity Manager
 * Plugin URI:      https://barn2.com/wordpress-plugins/woocommerce-quantity-manager/
 * Description:     Control your product quantities by adding minimum and maximum quantity rules, step values, default quantities, and more.
 * Version:         2.2.1
 * Author:          Barn2 Plugins
 * Author URI:      https://barn2.com
 * Text Domain:     woocommerce-quantity-manager
 * Domain Path:     /languages
 *
 * WC requires at least: 3.7
 * WC tested up to: 6.2.0
 *
 * Copyright:       Barn2 Media Ltd
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Barn2\Plugin\WC_Quantity_Manager;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

const PLUGIN_FILE    = __FILE__;
const PLUGIN_VERSION = '2.2.1';

// Include autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Helper function to access the shared plugin instance.
 *
 * @return Plugin The plugin instance.
 */
function wqm() {
    return Plugin_Factory::create( PLUGIN_FILE, PLUGIN_VERSION );
}

// Load the plugin.
wqm()->register();
