<?php

namespace Barn2\Plugin\WC_Quantity_Manager;

use Barn2\Plugin\WC_Quantity_Manager\Rules\Quantity_Step_Global;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Quantity Step
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Notice_Helper {

    public static function get_cart_notice( Cart_Validation $validation ) {
        $messages = [
            'global' => [
                /* translators: 2: Global cart value rules comparison string. These are seperately translatable. e.g. between $1 and $5 */
                'value_rules'           => __( 'Your cart total must be %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Global cart quantity rules comparison string. These are seperately translatable. e.g. 'between 2 and 3 products' */
                'quantity_rules'        => __( 'Your cart must contain %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step */
                'quantity_step'         => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step */
                'quantity_step_shared'  => __( 'The quantity in your cart must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
            ],
            'category' => [
                /* translators: 1: Category name 2: Category cart value rules comparison string. These are seperately translatable. e.g. between $1 and $5 */
                'value_rules'           => __( 'Your total spend from the &quot;%1$s&quot; category must be %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Category name 2: Category cart quantity rules comparison string. These are seperately translatable. e.g. 'between 2 and 3 items' or 'more than 4 items' */
                'quantity_rules'        => __( 'Your cart must contain %2$s from the &quot;%1$s&quot; category before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step */
                'quantity_step'         => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Category name 2: Quantity step */
                'quantity_step_shared'  => __( 'The quantity of the &quot;%1$s&quot; category must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
            ],
            'product-simple' => [
                /* translators: 1: Product name 2: Product cart value rules comparison string. These are seperately translatable. e.g. 'between $100 and $500' or 'more than $100' */
                'value_rules'           => __( 'You must spend %2$s on &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Product cart quantity rules comparison string. These are seperately translatable. e.g. 'between 2 and 3 units' or 'less than 3 units' */
                'quantity_rules'        => __( 'Your cart must contain %2$s of &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: The quantity step */
                'quantity_step'         => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: The quantity step */
                'quantity_step_shared'  => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
            ],
            'product-variation' => [
                /* translators: 1: Product name 2: Product cart value rules comparison string. These are seperately translatable. e.g. 'between $100 and $500' or 'more than $100' */
                'value_rules'           => __( 'You must spend %2$s on &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Product cart quantity rules comparison string. These are seperately translatable. e.g. 'between 2 and 3 units' or 'less than 3 units' */
                'quantity_rules'        => __( 'Your cart must contain %2$s of &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step */
                'quantity_step'         => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step */
                'quantity_step_shared'  => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
            ],
            'product-variable' => [
                /* translators: 1: Product name 2: Product cart value rules comparison string. These are seperately translatable. e.g. 'between $100 and $500' or 'more than $100' */
                'value_rules'           => __( 'You must spend %2$s on &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Product cart quantity rules comparison string. These are seperately translatable. e.g. 'between 2 and 3 units' or 'less than 3 units' */
                'quantity_rules'        => __( 'Your cart must contain %2$s of &quot;%1$s&quot; before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step rule value */
                'quantity_step'         => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Product name 2: Quantity step rule value */
                'quantity_step_shared'  => __( 'The quantity of &quot;%1$s&quot; must be a multiple of %2$s before you can complete your order.', 'woocommerce-quantity-manager' ),
            ],
        ];

        return sprintf( $messages[ $validation->get_rule_level() ][ $validation->get_type() ], self::get_object_name( $validation ), self::get_comparison_string( $validation ) );
    }

    public static function get_add_to_cart_notice( Cart_Validation $validation, $quantity ) {
        $messages = [
            'global' => [
                /* translators: 2: Global cart max value rule 3: Product name */
                'value_rules'           => __( 'You cannot add &quot;%3$s&quot; because the cart value must be less than %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Global cart max quantity rule 3: Product name */
                'quantity_rules'        => __( 'You cannot add &quot;%3$s&quot; because the cart has a maximum quantity of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step'         => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
                'quantity_step_shared'  => '',
            ],
            'category' => [
                /* translators: 1: Category name 2: Category cart max value rule 3: Product name */
                'value_rules'           => __( 'You cannot add &quot;%3$s&quot; because the value of items from the %1$s category must be less than %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Category name 2: Category cart quantity rule 3: Product name */
                'quantity_rules'        => __( 'You cannot add &quot;%3$s&quot; because your cart cannot contain more than %2$s products from the %1$s category.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step'         => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
                'quantity_step_shared'  => '',
            ],
            'product-simple' => [
                /* translators: 2: Product cart max value rule 3: Product name */
                'value_rules'           => __( 'You cannot add &quot;%3$s&quot; because it has a maximum value of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Product cart quantity rule 3: Product name */
                'quantity_rules'        => __( 'You cannot add &quot;%3$s&quot; because it has a maximum quantity of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step'         => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step_shared'  => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
            ],
            'product-variation' => [
                /* translators: 2: Product cart max value rule 3: Product name */
                'value_rules'           => __( 'You cannot add &quot;%3$s&quot; because it has a maximum value of %2$s.', 'woocommerce-quantity-manager' ),
                 /* translators: 2: Product cart quantity rule 3: Product name */
                'quantity_rules'        => __( 'You cannot add &quot;%3$s&quot; because it has a maximum quantity of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step'         => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
                'quantity_step_shared'  => '',
            ],
            'product-variable' => [
                /* translators: 1: Variable product name 2: Product cart max value rule */
                'value_rules'           => __( 'You cannot add &quot;%1$s&quot; because it has a maximum value of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Variable product name 2: Product cart quantity rule */
                'quantity_rules'        => __( 'You cannot add &quot;%1$s&quot; because it has a maximum quantity of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 1: Variable product name 2: Quantity step rule value */
                'quantity_step'         => __( '&quot;%1$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
                /* translators: 2: Quantity step rule value 3: Product name */
                'quantity_step_shared'  => __( '&quot;%3$s&quot; must be added in multiples of %2$s.', 'woocommerce-quantity-manager' ),
            ],
        ];

        $notice = sprintf( $messages[ $validation->get_rule_level() ][ $validation->get_type() ], self::get_object_name( $validation ), self::get_add_to_cart_value_string( $validation ), $validation->get_product_name() );

        return $notice;
    }

    /**
     * Gets the appropriate object name for an error message
     *
     * @param Cart_Validation $validation
     * @return string
     */
    private static function get_object_name( Cart_Validation $validation ) {
        if ( $validation->get_type() === 'quantity_step' ) {
            return $validation->get_product_name();
        }

        switch ( $validation->get_rule_level() ) {
            case 'category':
                $term = get_term( $validation->get_category_id(), 'product_cat' );
                $object_name = ! is_wp_error( $term ) ? $term->name : '';
                break;
            case 'product-simple':
            case 'product-variation':
                $object_name = $validation->get_product_name();
                break;
            case 'product-variable':
                $variable_product = wc_get_product( $validation->rule->product->get_parent_id() );
                $object_name = $variable_product->get_name();
                break;
            default:
                $object_name = '';
                break;
        }

        return $object_name;
    }

    /**
     * Gets the add to cart message value string
     *
     * @param Cart_Validation $validation
     * @return string
     */
    private static function get_add_to_cart_value_string( Cart_Validation $validation ) {
        switch ( $validation->get_type() ) {
            case 'quantity_rules':
                $string = $validation->get_rule_value()['max'];
                break;
            case 'value_rules':
                $string = sprintf( '%1$s%2$s', get_woocommerce_currency_symbol(), $validation->get_rule_value()['max'] );
                break;
            case 'quantity_step':
            case 'quantity_step_shared':
                $string = $validation->get_rule_value();
                break;
            default:
                $string = '';
                break;
        }

        return $string;
    }

    /**
     * Gets the comparison string for the error message
     *
     * @param Cart_Validation $validation
     * @return string
     */
    private static function get_comparison_string( Cart_Validation $validation ) {
        switch ( $validation->get_type() ) {
            case 'quantity_rules':
                $string = self::get_quantity_comparison_string( $validation->get_rule_level(), $validation->get_rule_value() );
                break;
            case 'value_rules':
                $string = self::get_value_comparison_string( $validation->get_rule_value() );
                break;
            case 'quantity_step':
            case 'quantity_step_shared':
                $string = $validation->get_rule_value();
                break;
            default:
                $string = '';
                break;
        }

        return $string;
    }

    /**
     * Gets the value comparison string for value rules
     *
     * @param array $rule_value
     * @return string
     */
    private static function get_value_comparison_string( $rule_value ) {
        $min_value = $rule_value['min'];
        $max_value = $rule_value['max'];

        if ( $max_value === $min_value ) {
            /* translators: 1: WooCommerce Currrency Symbol 2: Amount */
            $string = sprintf( __( '%1$s%2$s', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol(), $max_value );
        }
        elseif ( $max_value && $min_value ) {
            /* translators: 1: WooCommerce Currrency Symbol 2: Min Amount 3: Max Amount */
            $string = sprintf( __( 'between %1$s%2$s and %1$s%3$s', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol(), $min_value, $max_value );
        }
        elseif ( $max_value && ! $min_value ) {
            /* translators: 1: WooCommerce Currrency Symbol 2: Max Amount */
            $string = sprintf( __( '%1$s%2$s or less', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol(), $max_value );
        }
        elseif ( $min_value && ! $max_value ) {
            /* translators: 1: WooCommerce Currrency Symbol 2: Min Amount */
            $string = sprintf( __( 'at least %1$s%2$s', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol(), $min_value );
        }

        return $string;
    }

    /**
     * Gets the quantity comparison string for quantity rules
     *
     * @param string $rule_level
     * @param mixed $rule_value
     * @return string
     */
    private static function get_quantity_comparison_string( $rule_level, $rule_value ) {
        $min_value = $rule_value['min'];
        $max_value = $rule_value['max'];


        if ( $max_value === $min_value ) {
            /* translators: 1: Amount 2: Unit string, seperately translatable */
            $string = sprintf( __( '%1$s %2$s', 'woocommerce-quantity-manager' ), $max_value, self::get_unit_string( $rule_level ) );
        }
        elseif ( $max_value && $min_value ) {
            /* translators: 1: Min Amount 2: Max Amount 3: Unit string, seperately translatable */
            $string = sprintf( __( 'between %1$s and %2$s %3$s', 'woocommerce-quantity-manager' ), $min_value, $max_value, self::get_unit_string( $rule_level ) );
        }
        elseif ( $max_value && ! $min_value ) {
            /* translators: 1: Max Amount 2: Unit string, seperately translatable */
            $string = sprintf( __( '%1$s %2$s or less', 'woocommerce-quantity-manager' ), $max_value, self::get_unit_string( $rule_level ) );
        }
        elseif ( $min_value && ! $max_value ) {
            /* translators: 1: Min Amount 2: Unit string, seperately translatable */
            $string = sprintf( __( 'at least %1$s %2$s', 'woocommerce-quantity-manager' ), $min_value, self::get_unit_string( $rule_level ) );
        }

        return $string;
    }

    /**
     * Gets the unit string for the rule level
     *
     * @param string $rule_level
     * @return string
     */
    private static function get_unit_string( $rule_level ) {
        $unit_strings = [
            /* translators: Unit string for global rules */
            'global'            => __( 'products', 'woocommerce-quantity-manager' ),
            /* translators: Unit string for category rules */
            'category'          => __( 'items', 'woocommerce-quantity-manager' ),
            /* translators: Unit string for product simple rules */
            'product-simple'    => __( 'units', 'woocommerce-quantity-manager' ),
            /* translators: Unit string for product variable rules */
            'product-variable'  => __( 'units', 'woocommerce-quantity-manager' ),
            /* translators: Unit string for product variation rules */
            'product-variation' => __( 'units', 'woocommerce-quantity-manager' ),
        ];

        return $unit_strings[ $rule_level ];
    }
}