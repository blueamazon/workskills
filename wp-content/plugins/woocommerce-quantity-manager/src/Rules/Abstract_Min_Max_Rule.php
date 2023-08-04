<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Rules;

use Barn2\Plugin\WC_Quantity_Manager\Cart_Validation,
    Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Min Max Rules Abstract 
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Abstract_Min_Max_Rule extends Abstract_Rule {

    protected $min = null;
    protected $max = null;

    public function __construct( WC_Product $product ) {
        $this->sanitize_cb  = 'wc_clean';
        parent::__construct( $product );
    }

    /**
     * Calculate and set the rule value
     */
    public function calculate_rules() {
        parent::calculate_rules();
    }

    /**
     * Rule should define the qualifying total for the rule based on the cart_item_key
     * 
     * @param WC_Cart $cart 
     * @param string $cart_item_key 
     * @return mixed
     */
    abstract protected function get_qualifying_cart_total( $cart, $cart_item_key );

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

        if ( ! in_array( $this->get_level(), [ 'product-simple', 'product-variable', 'product-variation', 'category', 'global' ]) ) {
            return false; // 'no_rule'
        }

        $qualifying_total = $this->get_qualifying_cart_total( $cart, $cart_item_key );

        if ( is_null( $qualifying_total ) ) {
            return false; // 'invalid_rule_level'
        }

        if ( ! $this->get_max() && ! $this->get_min() ) {
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
        $passed = false;

        if ( $this->get_max() && $this->get_min() ) {
            $passed = $qualifying_total <= $this->get_max() && $qualifying_total >= $this->get_min();
        } 
        elseif ( $this->get_max() && ! $this->get_min() ) {
            $passed = $qualifying_total <= $this->get_max();
        }
        elseif ( $this->get_min() && ! $this->get_max() ) {
            $passed = $qualifying_total >= $this->get_min();
        }

        return $passed;
    }

    /**
     * Checks the array based meta value exists
     * 
     * @param mixed $value 
     * @return bool 
     */
    protected function has_meta_value( $value ) {
        if ( ! is_array( $value ) || $value === '' || $value === false ) {
            return false;
        }

        if ( ! isset( $value['min'] ) || ! isset( $value['max'] ) ) {
            return false;
        }

        if ( ! Field_Util::has_meta_value( $value['min'] ) && ! Field_Util::has_meta_value( $value['max'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the min value
     * 
     * @return mixed 
     */
    public function get_min() {
        return $this->min;
    }

    /**
     * Returns the max value
     * 
     * @return mixed 
     */
    public function get_max() {
        return $this->max;
    }
}