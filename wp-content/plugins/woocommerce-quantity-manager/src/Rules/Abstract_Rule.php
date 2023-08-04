<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Rules;

use Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    Barn2\Plugin\WC_Quantity_Manager\Cart_Validation,
    WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Rule Class
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Abstract_Rule {

    public $product = null;

    protected $type = null;
    protected $data_key = null;
    protected $sanitize_cb = '';

    protected $product_value = null;
    protected $variable_value = null;
    protected $variation_value = null;
    protected $category_value = null;
    protected $global_value = null;

    protected $category_id = null;

    protected $rule_value = null;
    protected $rule_level = null;

    public function __construct( WC_Product $product ) {
        $this->product = $product;
        $this->calculate_rules();
    }

    /**
     * Calculate and set the rule value and rule level.
     */
    protected function calculate_rules() {
        if ( ! $this->product || $this->product->is_sold_individually() ) {
            return false;
        }

        $rule_value = false;

        // 1. Variation
        $rule_value = $this->get_variation_value();

        if ( $this->has_meta_value( $rule_value ) ) {
            $this->rule_level = 'product-variation';
        }

        // 1. Variable (Variation Parent Product)
        if ( ! $this->has_meta_value( $rule_value ) ) {
            $rule_value = $this->get_variable_value();

            if ( $this->has_meta_value( $rule_value ) ) {
                $this->rule_level = 'product-variable';
            }
        }

        // 3. Product
        if ( ! $this->has_meta_value( $rule_value ) ) {
            $rule_value = $this->get_product_value();

            if ( $this->has_meta_value( $rule_value ) ) {
                $this->rule_level = 'product-simple';
            }
        }

        // 4. Category
        if ( ! $this->has_meta_value( $rule_value ) ) {
            $rule_value = $this->get_category_value();

            if ( $this->has_meta_value( $rule_value ) ) {
                $this->rule_level = 'category';
            }
        }

        // 5. Global
        if ( ! $this->has_meta_value( $rule_value ) ) {
            $rule_value = $this->get_global_value();

            if ( $this->has_meta_value( $rule_value ) ) {
                $this->rule_level = 'global';
            }
        }

        $this->rule_value = $rule_value;
    }

    /**
     * Determines if a give meta value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function has_meta_value( $value ) {
        return $value !== false && $value !== '';
    }

    /**
     * Checks if the qualifying total meets the rule conditions
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return mixed
     */
    abstract public function check_cart_validation( $qualifying_total );

    /**
     * Returns a cart validation for the rule based on the cart item key
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return Cart_Validation|false
     */
    abstract public function get_cart_validation( $cart, $cart_item_key );

    /**
     * Retrieves and sets the meta value at the variation level
     *
     * @return mixed
     */
    public function get_variation_value() {
        if ( ! is_null( $this->variation_value ) ) {
            return $this->variation_value;
        }

        $this->variation_value = false;

        if ( ! in_array( $this->product->get_type(), [ 'variation', 'subscription_variation' ] ) ) {
            return $this->variation_value;
        }

        $variation_value = $this->product->get_meta( Field_Util::get_data_key( $this->data_key, 'variation' ) );

        if ( $this->has_meta_value( $variation_value )  ) {
            $this->variation_value = call_user_func( $this->sanitize_cb, $variation_value );
        }

        return $this->variation_value;
    }

    /**
     * Retrieves and sets the meta value at the variable level
     *
     * @return mixed
     */
    public function get_variable_value() {
        if ( ! is_null( $this->variable_value ) ) {
            return $this->variable_value;
        }

        $this->variable_value = false;

        if ( ! in_array( $this->product->get_type(), [ 'variation', 'subscription_variation' ] ) && ! in_array( $this->product->get_type(), [ 'variable', 'variable-subscription' ] ) ) {
            return $this->variable_value;
        }

        $product = in_array( $this->product->get_type(), [ 'variation', 'subscription_variation' ] ) ? wc_get_product( $this->product->get_parent_id() ) : $this->product;

        $variable_value = $product->get_meta( Field_Util::get_data_key( $this->data_key, 'product' ) );

        if ( $this->has_meta_value( $variable_value ) ) {
            $this->variable_value = call_user_func( $this->sanitize_cb, $variable_value );
        }

        return $this->variable_value;
    }

    /**
     * Retrieves and sets the meta value at the product (simple) level
     *
     * @return mixed
     */
    public function get_product_value() {
        if ( ! is_null( $this->product_value ) ) {
            return $this->product_value;
        }

        $this->product_value = false;

        if ( ! in_array( $this->product->get_type(), [ 'simple', 'subscription' ] ) ) {
            return $this->product_value;
        }

        $product_value = $this->product->get_meta( Field_Util::get_data_key( $this->data_key, 'product' ) );

        if ( $this->has_meta_value( $product_value ) ) {
            $this->product_value = call_user_func( $this->sanitize_cb, $product_value );
        }

        return $this->product_value;
    }

    /**
     * Retrieves and sets the meta value at the category level
     *
     * @return mixed
     */
    public function get_category_value() {
        if ( ! is_null( $this->category_value ) ) {
            return $this->category_value;
        }

        $this->category_value = false;
        $category_value = false;
        $category_id = false;

        $id = in_array( $this->product->get_type(), [ 'variation', 'subscription_variation' ] ) ? $this->product->get_parent_id() : $this->product->get_id();
        $categories = get_the_terms( $id, 'product_cat' );

        if ( ! $categories || empty( $categories ) ) {
            return $this->category_value;
        }

        // Grab our values
        foreach ( $categories as $category ) {
            $category_value = get_term_meta( $category->term_id, Field_Util::get_data_key( $this->data_key, 'category' ), true );

            // Check parents
            while ( ! $this->has_meta_value( $category_value ) && $category->parent !== 0 ) {
                $category = get_term( $category->parent );
                $category_value = get_term_meta( $category->term_id , Field_Util::get_data_key( $this->data_key, 'category' ), true );
            }

            if ( ! $this->has_meta_value( $category_value ) ) {
                continue;
            }

            $category_value = call_user_func( $this->sanitize_cb, $category_value );
            $category_id = $category->term_id;
            break;
        }

        if ( $category_value !== false && is_int( $category_id ) ) {
            $this->category_value = $category_value;
            $this->category_id = $category_id;
        }

        return $this->category_value;
    }

    /**
     * Retrieves and sets the meta value at the global level
     *
     * @return mixed
     */
    public function get_global_value() {
        if ( ! is_null( $this->global_value ) ) {
            return $this->global_value;
        }

        $global_value = get_option( Field_Util::get_data_key( $this->data_key, 'global' ) );

        if ( ! $this->has_meta_value( $global_value ) ) {
            return false;
        }

        $this->global_value = call_user_func( $this->sanitize_cb, $global_value );

        return $this->global_value;
    }

    /**
     * Returns the calculated rule value based on the rule level
     *
     * @return mixed
     */
    public function get_value() {
        return apply_filters( 'wc_quantity_manager_' . $this->data_key . '_rule_value', $this->rule_value, $this );
    }

    /**
     * Returns the rule level
     *
     * @return string|null
     */
    public function get_level() {
        return $this->rule_level;
    }

    /**
     * Get the category id
     * @return int|null
     */
    public function get_category_id() {
        return $this->category_id;
    }

    /**
     * Get the data key
     *
     * @return string
     */
    public function get_data_key() {
        return $this->data_key;
    }

    /**
     * Get the type ID
     *
     * @return string
     */
    public function get_type() {
        return $this->type;
    }
}