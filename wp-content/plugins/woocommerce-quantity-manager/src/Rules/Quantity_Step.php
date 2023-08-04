<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Rules;

use Barn2\Plugin\WC_Quantity_Manager\Util\Cart as Cart_Util,
    Barn2\Plugin\WC_Quantity_Manager\Cart_Validation,
    WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Quantity Step
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Quantity_Step extends Abstract_Rule {

    public function __construct( WC_Product $product ) {
        $this->type         = 'quantity_step';
        $this->data_key     = 'quantity_step';
        $this->sanitize_cb  = 'absint';
        parent::__construct( $product );
    }

    /**
     * Returns a cart validation for the rule based on the cart item key
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return Cart_Validation|false
     */
    public function get_cart_validation( $cart, $cart_item_key ) {
        if ( ! isset( $cart ) || $cart === '' ) {
            return false; // 'no_cart'
        }

        if ( ! in_array( $this->get_level(), [ 'product-simple', 'product-variable', 'product-variation', 'category', 'global' ] ) ) {
            return false; // 'no_rule'
        }

        $qualifying_total = Cart_Util::get_product_quantity( $cart, $cart_item_key );

        if ( is_null( $qualifying_total ) ) {
            return false; // 'invalid_rule_level'
        }

        if ( ! $this->get_value() ) {
            return false;
        }

        return new Cart_Validation( $cart_item_key, $qualifying_total, $this );
    }

    /**
     * Checks if the qualifying total meets the rule conditions
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return mixed
     */
    public function check_cart_validation( $qualifying_total ) {
        return $qualifying_total % $this->get_value() === 0;
    }
}