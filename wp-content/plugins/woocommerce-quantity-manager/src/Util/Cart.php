<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Util;

use WC_Product,
    Barn2\Plugin\WC_Quantity_Manager\Cart_Validation,
    Barn2\Plugin\WC_Quantity_Manager\Rules,
    Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util;

defined( 'ABSPATH' ) || exit;

/**
 * Cart Utilities
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Cart {

    /**
     * Retrieves all validations for a given cart.
     *
     * @param WC_Cart $cart
     * @return Cart_Validation[]
     */
    public static function get_cart_validations( $cart ) {
        $rules = [];
        $validations = [];

        if ( ! self::check_prerequisities( $cart ) ) {
            return $validations;
        }

        $shared_quantity_step_value = Field_Util::shared_quantity_step_calulation();

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $product_id = $cart_item['variation_id'] !== 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
            $product = wc_get_product( $product_id );


            $allowed_product_types = apply_filters( 'wc_quantity_manager_allowed_product_types', [ 'simple', 'variable', 'variation', 'subscription', 'variable-subscription', 'subscription_variation' ] );

            if ( ! in_array( $product->get_type(), $allowed_product_types ) ) {
                continue;
            }

            $rules[ $cart_item_key ] = [
                'quantity_step'     => $shared_quantity_step_value ? new Rules\Quantity_Step_Shared( $product ) : new Rules\Quantity_Step( $product ),
                'value_rules'       => new Rules\Min_Max_Value( $product ),
                'quantity_rules'    => new Rules\Min_Max_Quantity( $product ),
            ];

            foreach( $rules[ $cart_item_key ] as $rule ) {
                $cart_validation = $rule->get_cart_validation( $cart, $cart_item_key );

                if ( $cart_validation instanceof Cart_Validation ) {
                    $validations = array_merge( $validations, [ $cart_validation ] );
                }
            }
        }

        return $validations;
    }

    /**
     * Gets the cart validations applicable to a specific cart item
     *
     * @param Cart_Validation[] $cart_validations
     * @param string $cart_item_key
     * @param int $product_id
     * @return Cart_Validation[]
     */
    public static function get_add_to_cart_validations( $cart_validations, $cart_item_key, $product_id ) {
        // check for intersections
        $validations = array_filter( $cart_validations, function( Cart_Validation $validation ) use ( $cart_item_key, $product_id ) {
            // skip if passed
            if ( $validation->has_passed() ) {
                return false;
            }

            // don't include quantity step if calculation is shared
            if (
                    Field_Util::shared_quantity_step_calulation()
                    && $validation->rule instanceof Rules\Quantity_Step_Shared
                    && ! in_array( $validation->get_type(), [ 'product-simple', 'product-variation' ] )
                ) {
                return false;
            }

            // only look at max values for quantity and value rules
            if ( in_array( $validation->get_type(), [ 'quantity_rules', 'value_rules' ] ) && ! $validation->failed_max() ) {
                return false;
            }

            // direct validation
            if ( $validation->get_cart_item_key() === $cart_item_key ) {
                return true;
            }

            // any categories which match the added product
            if ( $validation->get_rule_level() === 'category' && $validation->rule->product->get_id() === $product_id ) {
                return true;
            }

            return false;
        } );

        return $validations;
    }

    /**
     * Retrieves the quantity of a cart item in the cart
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return int|false
     */
    public static function get_quantity_in_cart( $cart, $cart_item_key ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        if ( ! isset( $cart->cart_contents[ $cart_item_key ] ) ) {
            return false;
        }

        return $cart->cart_contents[ $cart_item_key ]['quantity'];
    }

    /**
     * Retrieves the cart item key based on the product and quantity to be added.
     *
     * @param WC_Cart $cart
     * @param WC_Product $product
     * @param int $quantity
     * @return string|false
     */
    public static function get_cart_item_key( $cart, WC_Product $product, $quantity ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $product_id = $product->get_id();
        $variation_id = 0;
        $variation = [];

        // If this is a variation get data to create cart_id
        if ( $product->is_type( 'variation' ) ) {
            $variation_id = $product->get_id();
            $variation = $product;
            $product_id = $product->get_parent_id();
        }

        // Load cart item data - may be added by other plugins.
        $cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', [], $product_id, $variation_id, $quantity );

        // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
        $cart_id = $cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

        // Find the cart item key in the existing cart.
        $cart_item_key = $cart->find_product_in_cart( $cart_id );

        return $cart_item_key;
    }

    /**
     * Gets the total in cart
     *
     * @param WC_Cart $cart
     * @return mixed
     */
    public static function get_global_total( $cart ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $cart_total = wc_prices_include_tax() ? $cart->get_cart_contents_total() + $cart->get_cart_contents_tax() : $cart->get_cart_contents_total();

        return $cart_total;
    }

    /**
     * Gets the total for a category in the cart
     *
     * @param WC_Cart $cart
     * @param int $category_id
     * @return mixed
     */
    public static function get_category_total( $cart, $category_id ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $category_cart_total = 0;

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $categories = get_the_terms( $cart_item['product_id'], 'product_cat' );

            foreach ( $categories as $category ) {
                if ( $category->term_id === $category_id ) {
                    $category_cart_total += $cart_item['line_total'];
                    break;
                }

                while ( $category->parent !== 0 && $category->term_id !== $category_id ) {
                    $category = get_term( $category->parent );

                    if ( $category->term_id === $category_id ) {
                        $category_cart_total += $cart_item['line_total'];
                        break 2;
                    }
                }
            }
        }

        return $category_cart_total;
    }

    /**
     * Gets the total for a product in the cart
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return mixed
     */
    public static function get_product_total( $cart, $cart_item_key ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        if ( ! isset( $cart->cart_contents[ $cart_item_key ]['line_total'] ) ) {
            return false;
        }

        $line_total = $cart->cart_contents[ $cart_item_key ]['line_total'];

        return $line_total;
    }

    /**
     * Gets the total for all variations in the cart associated to a variable product.
     *
     * @param WC_Cart $cart
     * @param int $variable_id
     * @return mixed
     */
    public static function get_variable_product_total( $cart, $variable_id ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $matching_variations = array_filter( $cart->cart_contents, function( $cart_item ) use ( $variable_id ) {
            return ( $cart_item['product_id'] === $variable_id );
        } );

        if ( empty( $matching_variations ) ) {
            return 0;
        }

        $variations_total = array_sum( array_column( $matching_variations, 'line_total' ) );

        return $variations_total;
    }

    /**
     * Gets the total quantity of items in the cart
     *
     * @param WC_Cart $cart
     * @return mixed
     */
    public static function get_global_quantity( $cart ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        return $cart->get_cart_contents_count();
    }

    /**
     * Gets the total quantity associated to a category
     *
     * @param WC_Cart $cart
     * @param int $category_id
     * @return mixed
     */
    public static function get_category_quantity( $cart, $category_id ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $quantity_in_cart = 0;

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $categories = get_the_terms( $cart_item['product_id'], 'product_cat' );

            foreach ( $categories as $category ) {
                if ( $category->term_id === $category_id ) {
                    $quantity_in_cart += $cart_item['quantity'];
                    break;
                }

                while ( $category->parent !== 0 && $category->term_id !== $category_id ) {
                    $category = get_term( $category->parent );

                    if ( $category->term_id === $category_id ) {
                        $quantity_in_cart += $cart_item['quantity'];
                        break 2;
                    }
                }
            }
        }

        return $quantity_in_cart;
    }

    /**
     * Gets the total quantity of a product in the cart.
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return mixed
     */
    public static function get_product_quantity( $cart, $cart_item_key ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        if ( ! isset( $cart->cart_contents[ $cart_item_key ]['quantity'] ) ) {
            return false;
        }

        $quantity_in_cart = $cart->cart_contents[ $cart_item_key ]['quantity'];

        return $quantity_in_cart;
    }

    /**
     * Gets the total quantity of variations in the cart associated to a variable product.
     *
     * @param WC_Cart $cart
     * @param int $variable_id
     * @return mixed
     */
    public static function get_variable_product_quantity( $cart, $variable_id ) {
        if ( ! self::check_prerequisities( $cart ) ) {
            return false;
        }

        $matching_variations = array_filter( $cart->cart_contents, function( $cart_item ) use ( $variable_id ) {
            return ( $cart_item['product_id'] === $variable_id );
        } );

        if ( empty( $matching_variations ) ) {
            return 0;
        }

        $quantity_in_cart = array_sum( array_column( $matching_variations, 'quantity' ) );

        return $quantity_in_cart;
    }

    /**
     * Checks that the cart exists
     *
     * @param WC_Cart $cart
     * @return bool
     */
    public static function check_prerequisities( $cart ) {
        if ( ! isset( $cart ) || $cart === '' ) {
            return false;
        }

        return true;
    }
}