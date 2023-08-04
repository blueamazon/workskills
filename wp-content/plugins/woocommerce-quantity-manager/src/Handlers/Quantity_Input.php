<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Handlers;

use Barn2\Plugin\WC_Quantity_Manager\Rules\Quantity_Step_Shared;
use Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Service,
    Barn2\Plugin\WC_Quantity_Manager\Rules,
    Barn2\Plugin\WC_Quantity_Manager\Util\Util,
    Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Quantity Input Handler
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Quantity_Input implements Registerable, Service {

    public function register() {
        add_filter( 'woocommerce_quantity_input_args', [ $this, 'quantity_input_args' ], 999, 2 );
        add_filter( 'woocommerce_available_variation', [ $this, 'available_variations' ], 20, 1 );

        add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'loop_input_args' ], 10, 2 );
    }

    /**
     * Filter the WC quantity input args
     *
     * @param   array       $args
     * @param   WC_Product  $product
     * @return  $args
     */
    public function quantity_input_args( $args, WC_Product $product ) {
        if ( ! Util::user_has_rules() ) {
            return $args;
        }

        $input_args = $this->include_quantity_step_calculation( $product ) ?
            $this->determine_input_args( $product ) : $this->determine_input_args_without_step( $product );

        if ( isset( $input_args['input_value'] ) ) {
            $args['input_value'] = $input_args['input_value'];
        }

        if ( isset( $input_args['step'] ) ) {
            $args['step'] = $input_args['step'];
        }

        if ( isset( $input_args['min'] ) ) {
            $args['min_value'] = $input_args['min'];
        }

        if ( isset( $input_args['max'] ) ) {
            $args['max_value'] = $input_args['max'];
        }

        return $args;
    }

    /**
     * Filters the variation input args.
     * These are applied to the variation qty input in wqm-frontend.js
     *
     * @param array $data
     * @return array $data
     */
    public function available_variations( $data ) {
        if ( ! Util::user_has_rules() ) {
            return $data;
        }

        $product = wc_get_product( $data['variation_id'] );

        if ( ! $product ) {
            return $data;
        }

        $input_args = $this->include_quantity_step_calculation( $product ) ?
            $this->determine_input_args( $product ) : $this->determine_input_args_without_step( $product );

        if ( isset( $input_args['input_value'] ) ) {
            $data['input_value'] = $input_args['input_value'];
        }

        if ( isset( $input_args['step'] ) ) {
            $data['step'] = $input_args['step'];
        }

        if ( isset( $input_args['min'] ) ) {
            $data['min_qty'] = $input_args['min'];
        }

        if ( isset( $input_args['max'] ) ) {
            $data['max_qty'] = $input_args['max'];
        }

        return $data;
    }

    /**
     * Filter the WC loop add to carts input args
     *
     * @param   array         $args
     * @param   WC_Product    $product
     *
     * @return  array         $args
     */
    public function loop_input_args( $args, WC_Product $product ) {
        if ( ! Util::user_has_rules() ) {
            return $args;
        }

        $args = $this->include_quantity_step_calculation( $product ) ?
            $this->determine_loop_input_args( $args, $product ) : $this->determine_loop_input_args_without_step( $args, $product );

        return $args;
    }

    /**
     * Filter the WC loop add to carts input args.
     *
     * @param   array         $args
     * @param   WC_Product    $product
     *
     * @return  array         $args
     */
    private function determine_loop_input_args( $args, WC_Product $product ) {
        $default_quantity_rule = new Rules\Default_Quantity( $product );
        $default_quantity_value = $default_quantity_rule->get_value();

        if ( $default_quantity_value !== false ) {
            $args['quantity'] = $default_quantity_value;
        }

        if ( $args['quantity'] === 0 ) {
            $args['quantity'] = 1;
        }


        // Quantity Step
        $quantity_step_rule = new Rules\Quantity_Step( $product );
        $quantity_step_value = $quantity_step_rule->get_value();

        if ( is_numeric( $quantity_step_value ) && $quantity_step_value > 0 ) {
            $args['quantity'] = $default_quantity_value % $quantity_step_value === 0 ? $default_quantity_value : $quantity_step_value;
        }

        $min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

        // Max Quantity
        if ( $min_max_quantity_rule->get_max() && $min_max_quantity_rule->get_max() < $args['quantity'] ) {
            $max_value = $min_max_quantity_rule->get_max();

            // If the max value is not a multiple of step get the closest valid multiple
            if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
                $max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
            }

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
                $max_value = $product->get_max_purchase_quantity();

                if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
                    $max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
                }
            }

            $args['quantity'] = $max_value;
        }

        // Min Quantity
        if (
            in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ] )
            && $min_max_quantity_rule->get_min()
            && $min_max_quantity_rule->get_min() > $args['quantity']
        ) {
            $min_value = $min_max_quantity_rule->get_min();

            // If the min value is not a multiple of step get the closest valid multiple
            if ( $quantity_step_value !== false && $min_value % $quantity_step_value !== 0 ) {
                $min_value = ceil( $min_value / $quantity_step_value ) * $quantity_step_value;
            }

            $args['quantity'] = $min_value;
        }

        return $args;
    }

    /**
     * Filter the WC loop add to carts input args  (without quantity step).
     *
     * @param   array         $args
     * @param   WC_Product    $product
     *
     * @return  array         $args
     */
    private function determine_loop_input_args_without_step( $args, WC_Product $product ) {
        $default_quantity_rule = new Rules\Default_Quantity( $product );
        $default_quantity_value = $default_quantity_rule->get_value();

        if ( $default_quantity_value !== false ) {
            $args['quantity'] = $default_quantity_value;
        }

        if ( $args['quantity'] === 0 ) {
            $args['quantity'] = 1;
        }


        $min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

        // Max Quantity
        if ( $min_max_quantity_rule->get_max() && $min_max_quantity_rule->get_max() < $args['quantity'] ) {
            $max_value = $min_max_quantity_rule->get_max();

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
                $max_value = $product->get_max_purchase_quantity();
            }

            $args['quantity'] = $max_value;
        }

        // Min Quantity
        if (
            in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ] )
            && $min_max_quantity_rule->get_min()
            && $min_max_quantity_rule->get_min() > $args['quantity']
        ) {
            $min_value = $min_max_quantity_rule->get_min();

            $args['quantity'] = $min_value;
        }

        return $args;
    }

    /**
     * Determines the quantity input attributes based on the configured rules.
     *
     * @param WC_Product $product
     * @return array
     */
    private function determine_input_args( WC_Product $product ) {
        $args = [];

        // Default Quantity
        $default_quantity_rule = new Rules\Default_Quantity( $product );
        $default_quantity_value = $default_quantity_rule->get_value();

        if ( ! is_cart() && $default_quantity_value !== false ) {
            $args['input_value'] = $default_quantity_value;

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $default_quantity_value ) {
                $args['input_value'] = $product->get_max_purchase_quantity();
            }
        }

        // Quantity Step
        $quantity_step_rule = new Rules\Quantity_Step( $product );
        $quantity_step_value = $quantity_step_rule->get_value();

        if ( $quantity_step_value !== false ) {
            // Set step
            $args['step'] = $quantity_step_value;
        }

        $min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

        // Max Quantity
        if ( $min_max_quantity_rule->get_max() ) {
            $max_value = $min_max_quantity_rule->get_max();

            // If the max value is not a multiple of step get the closest valid multiple
            if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
                $max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
            }

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
                $max_value = $product->get_max_purchase_quantity();

                if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
                    $max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
                }
            }

            // Set Max
            $args['max'] = $max_value;
        }

        // Min Quantity
        if ( in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ] ) && $min_max_quantity_rule->get_min() ) {
            $min_value = $min_max_quantity_rule->get_min();

            // If the min value is not a multiple of step get the closest valid multiple
            if ( $quantity_step_value !== false && $min_value % $quantity_step_value !== 0 ) {
                $min_value = ceil( $min_value / $quantity_step_value ) * $quantity_step_value;
            }

            // Set Min
            $args['min'] = $min_value;
        }

        // Change min to 0 if we have default quantity of 0
        if ( $default_quantity_value === 0 ) {
            $args['min'] = 0;
        }

        // Don't allow min to be higher than max
        if ( isset( $args['min'] ) && isset( $args['max'] ) && $args['min'] > $args['max'] ) {
            $args['min'] = $args['max'];
        }

        // If we don't have a min and we have a step set min to step
        if ( ! isset( $args['min'] ) && isset( $args['step'] ) && ( ! isset( $args['input_value'] ) || $args['input_value'] !== 0 ) ) {
            $args['min'] = $args['step'];
        }

        if ( ! is_cart() )  {
            // Set input value to closest multiple if is not a multiple of step. (ignore default quantity 0)
            if ( isset( $args['input_value'] ) && isset( $args['step'] ) && $args['input_value'] !== 0 ) {
                $args['input_value'] = $args['input_value'] % $args['step'] === 0 && $args['input_value'] >= $args['step'] ? $args['input_value'] : ceil( $args['input_value'] / $args['step'] ) * $args['step'];
            }

            // If we still don't have an input value set it to the step if we have one
            if ( isset( $args['step'] ) && ! isset( $args['input_value'] ) ) {
                $args['input_value'] = $args['step'];
            }

            // If we still don't have an input value set it to the min if we have one
            if ( isset( $args['min'] ) && ! isset( $args['input_value'] ) ) {
                $args['input_value'] = $args['min'];
            }

            // If we still don't have an input value set it to 1
            if ( ! isset( $args['input_value'] ) ) {
                $args['input_value'] = 1;
            }

            // Don't allow max to be less than input value
            if ( isset( $args['max'] ) && isset( $args['input_value'] ) && $args['max'] < $args['input_value'] ) {
                $args['input_value'] = $args['max'];
            }

            // Don't allow min to be more than input value
            if ( isset( $args['min'] ) && isset( $args['input_value'] ) && $args['min'] > $args['input_value'] ) {
                $args['input_value'] = $args['min'];
            }
        }

        return $args;
    }

    /**
     * Determines the quantity input attributes based on the configured rules (without quantity step).
     *
     * @param WC_Product $product
     * @return array
     */
    private function determine_input_args_without_step( WC_Product $product ) {
        $args = [];

        // Default Quantity
        $default_quantity_rule = new Rules\Default_Quantity( $product );
        $default_quantity_value = $default_quantity_rule->get_value();

        if ( ! is_cart() && $default_quantity_value !== false ) {
            $args['input_value'] = $default_quantity_value;

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $default_quantity_value ) {
                $args['input_value'] = $product->get_max_purchase_quantity();
            }
        }

        $min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

        // Max Quantity
        if ( $min_max_quantity_rule->get_max() ) {
            $max_value = $min_max_quantity_rule->get_max();

            if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
                $max_value = $product->get_max_purchase_quantity();
            }

            // Set Max
            $args['max'] = $max_value;
        }

        // Min Quantity
        if ( in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ] ) && $min_max_quantity_rule->get_min() ) {
            $min_value = $min_max_quantity_rule->get_min();

            // Set Min
            $args['min'] = $min_value;
        }

        // Change min to 0 if we have default quantity of 0
        if ( $default_quantity_value === 0 ) {
            $args['min'] = 0;
        }

        // Don't allow min to be higher than max
        if ( isset( $args['min'] ) && isset( $args['max'] ) && $args['min'] > $args['max'] ) {
            $args['min'] = $args['max'];
        }

        if ( ! is_cart() )  {
            // If we still don't have an input value set it to the min if we have one
            if ( isset( $args['min'] ) && ! isset( $args['input_value'] ) ) {
                $args['input_value'] = $args['min'];
            }

            // If we still don't have an input value set it to 1
            if ( ! isset( $args['input_value'] ) ) {
                $args['input_value'] = 1;
            }

            // Don't allow max to be less than input value
            if ( isset( $args['max'] ) && isset( $args['input_value'] ) && $args['max'] < $args['input_value'] ) {
                $args['input_value'] = $args['max'];
            }

            // Don't allow min to be more than input value
            if ( isset( $args['min'] ) && isset( $args['input_value'] ) && $args['min'] > $args['input_value'] ) {
                $args['input_value'] = $args['min'];
            }
        }

        return $args;
    }

    /**
     * Determine if we should include quantity step into the calculation
     *
     * @param WC_Product $product
     * @return bool
     */
    private function include_quantity_step_calculation( $product ) {
        if ( ! Field_Util::shared_quantity_step_calulation() ) {
            return true;
        }

        $shared_rule = new Quantity_Step_Shared( $product );

        if ( in_array( $shared_rule->get_level(), [ 'product-simple', 'product-variation' ] ) ) {
            return true;
        }

        return false;
    }
}