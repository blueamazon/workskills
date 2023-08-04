<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Integration;

use Barn2\WQM_Lib\Registerable;

/**
 * WooCommerce Product Table integration.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Table implements Registerable {

    public function register() {
        add_filter( 'wc_product_table_show_all_cart_errors', '__return_true' );
    }
}
