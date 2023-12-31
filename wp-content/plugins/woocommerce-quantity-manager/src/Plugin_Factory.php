<?php

namespace Barn2\Plugin\WC_Quantity_Manager;

/**
 * Factory to return the shared plugin instance.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin_Factory {

    private static $plugin = null;

    /**
     * Create/return the shared plugin instance.
     *
     * @param string $file The main plugin __FILE__
     * @param string $version The current plugin version
     * @return Plugin The plugin instance
     */
    public static function create( $file, $version ) {
        if ( null === self::$plugin ) {
            self::$plugin = new Plugin( $file, $version );
        }
        return self::$plugin;
    }

}
