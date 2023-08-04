<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Handlers;

use Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Service,
    Barn2\Plugin\WC_Quantity_Manager\Util\Cart as Cart_Util,
    Barn2\Plugin\WC_Quantity_Manager\Util\Util,
    Barn2\Plugin\WC_Quantity_Manager\Notice_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Cart Handler
 * 
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cart implements Registerable, Service {

    public function register() {
        add_action( 'woocommerce_check_cart_items', [ $this, 'cart_validation' ], 10 );
    }

    /**
     * Runs a cart validation with the configured rules
     */
    public function cart_validation() {
        if ( ! apply_filters( 'wc_quantity_manager_handle_cart_validation', true ) ) {
            return;
        }

        if ( ! Util::user_has_rules() ) {
            return;
        }
        
        $validations = Cart_Util::get_cart_validations( WC()->cart );

        foreach ( $validations as $validation ) {
            if ( $validation->has_passed() ) {
                continue;
            }
   
            if ( ! wc_has_notice( Notice_Helper::get_cart_notice( $validation ), 'error' ) ) {
                wc_add_notice( Notice_Helper::get_cart_notice( $validation ), 'error' );
            }
        }
    }
}