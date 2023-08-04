<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Integration;

use Barn2\WQM_Lib\Registerable;
/**
 * WooCommerce Restaurant Ordering integration.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Restaurant_Ordering implements Registerable {

    public function register() {
        add_filter( 'wc_restaurant_ordering_script_params', [ $this, 'increase_cart_error_notice_timeout' ], 10, 1 );
        add_filter( 'wc_restaurant_ordering_show_all_cart_errors', '__return_true' );
    }

    /**
     * Increases the cart notice timeout in WRO for errors.
     * 
     * @param array $script_params 
     * @return array $script_params 
     */
    public function increase_cart_error_notice_timeout( $script_params ) {
        $script_params['cart_notice_timeout'] = 5600;

        return $script_params;
    }
}
