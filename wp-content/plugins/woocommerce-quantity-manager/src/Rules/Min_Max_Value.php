<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Rules;

use Barn2\Plugin\WC_Quantity_Manager\Util\Cart as Cart_Util,
    Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Min Max Value Rules
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Min_Max_Value extends Abstract_Min_Max_Rule {

    public function __construct( WC_Product $product ) {
        $this->type         = 'value_rules';
        $this->data_key     = 'value_rules';
        $this->sanitize_cb  = [ '\\Barn2\\Plugin\\WC_Quantity_Manager\\Util\\Field', 'sanitize_min_max_value' ];

        parent::__construct( $product );
    }

    /**
     * Calculate and set the rule value
     */
    public function calculate_rules() {
        parent::calculate_rules();

        if ( is_null( $this->rule_value ) || $this->rule_value === false || ! is_array( $this->rule_value ) ) {
            return;
        }

        if ( isset( $this->rule_value['min'] ) ) {
            $this->min = Field_Util::has_meta_value( $this->rule_value['min'] ) ? (float) $this->rule_value['min'] : false;
        }

        if ( isset( $this->rule_value['max'] ) ) {
            $this->max = Field_Util::has_meta_value( $this->rule_value['max'] ) ? (float) $this->rule_value['max'] : false;
        }
    }


    /**
     * Gets the qualifying cart total based on the rule level
     *
     * @param WC_Cart $cart
     * @param string $cart_item_key
     * @return mixed
     */
    public function get_qualifying_cart_total( $cart, $cart_item_key ) {
        switch ( $this->get_level() ) {
            case 'product-simple':
            case 'product-variation':
                $qualifying_total = Cart_Util::get_product_total( $cart, $cart_item_key );
                break;
            case 'product-variable':
                $qualifying_total = Cart_Util::get_variable_product_total( $cart, $this->product->get_parent_id() );
                break;
            case 'category':
                $qualifying_total = Cart_Util::get_category_total( $cart, $this->get_category_id() );
                break;
            case 'global':
                $qualifying_total = Cart_Util::get_global_total( $cart );
                break;
            default:
                $qualifying_total = null;
                break;
        }

        return $qualifying_total;
    }
}