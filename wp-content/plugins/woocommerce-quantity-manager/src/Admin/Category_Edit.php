<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin;

use Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Handles fields and data on the Product Category Add/Edit screen
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Category_Edit implements Registerable, Service {

    public function register() {
        // Quantity Rules
        add_action( 'product_cat_add_form_fields', [ $this, 'add_min_max_quantity_field' ], 21 );
        add_action( 'product_cat_edit_form_fields', [ $this, 'edit_min_max_quantity_field' ], 21 );

        add_action( 'created_product_cat', [ $this, 'save_quantity_rules_field' ], 10, 2 );
        add_action( 'edit_product_cat', [ $this, 'save_quantity_rules_field' ], 10, 2 );

        // Value Rules
        add_action( 'product_cat_add_form_fields', [ $this, 'add_min_max_value_field' ], 21 );
        add_action( 'product_cat_edit_form_fields', [ $this, 'edit_min_max_value_field' ], 21 );

        add_action( 'created_product_cat', [ $this, 'save_value_rules_field' ], 10, 2 );
        add_action( 'edit_product_cat', [ $this, 'save_value_rules_field' ], 10, 2 );

        // Default Quantity
        add_action( 'product_cat_add_form_fields', [ $this, 'add_default_quantity_field' ], 21 );
        add_action( 'product_cat_edit_form_fields', [ $this, 'edit_default_quantity_field' ], 21 );

        add_action( 'created_product_cat', [ $this, 'save_default_quantity_field' ], 10, 2 );
        add_action( 'edit_product_cat', [ $this, 'save_default_quantity_field' ], 10, 2 );

        // Quantity Step
        add_action( 'product_cat_add_form_fields', [ $this, 'add_quantity_step_field' ], 21 );
        add_action( 'product_cat_edit_form_fields', [ $this, 'edit_quantity_step_field' ], 21 );

        add_action( 'created_product_cat', [ $this, 'save_quantity_step_field' ], 10, 2 );
        add_action( 'edit_product_cat', [ $this, 'save_quantity_step_field' ], 10, 2 );
    }

    /**
     * Inserts the Min Max quantity amount field to the 'Add New' product category fields
     */
    public function add_min_max_quantity_field() {
        $data_key = Field_Util::get_data_key( 'quantity_rules', 'category' );
        ?>
        <div class="form-field wqm-quantity-rules-wrap">
            <label for="<?php echo esc_attr( $data_key ); ?>">
                <?php _e( 'Quantity rules', 'woocommerce-quantity-manager' ) ?>
                <?php echo wc_help_tip( __( 'Enter a whole number to set a minimum and/or maximum number of items that can be purchased from the category. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' ) ); ?>
            </label>
            <?php Field_Util::min_max_input_field( '', [
                'id'                => $data_key,
                'custom_attributes' => [
                    'min'   => 1,
                    'step'  => 1,
                ]
            ] ); ?>
        </div>
        <?php
    }

    /**
     * Inserts the Min Max quantity amount field to the 'Edit' product category fields
     *
     * @param mixed $term The product category being edited
     */
    public function edit_min_max_quantity_field( $term ) {
        $data_key = Field_Util::get_data_key( 'quantity_rules', 'category' );
        $value = get_term_meta( $term->term_id, $data_key, true );

        ?>
        <tr class="form-field wqm-quantity-rules-wrap">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr( $data_key ); ?>"><?php _e( 'Quantity rules', 'woocommerce-quantity-manager' ); ?></label>
                <?php echo wc_help_tip( __( 'Enter a whole number to set a minimum and/or maximum number of items that can be purchased from the category. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' ) ); ?>
            </th>
            <td>
                <?php Field_Util::min_max_input_field( $value, [
                    'id'                => $data_key,
                    'custom_attributes' => [
                        'min'   => 1,
                        'step'  => 1,
                    ]
                ] ); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Inserts the Min Max value amount field to the 'Add New' product category fields
     */
    public function add_min_max_value_field() {
        $data_key = Field_Util::get_data_key( 'value_rules', 'category' );
        ?>
        <div class="form-field wqm-value-rules-wrap">
            <label for="<?php echo esc_attr( $data_key ); ?>">
                <?php printf( __( 'Value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ); ?>
                <?php echo wc_help_tip( __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value that can be purchased from the category. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' ) ); ?>
            </label>
            <?php Field_Util::min_max_input_field( '', [
                'id'                => $data_key,
                'currency'          => true,
            ] ); ?>
        </div>
        <?php
    }

    /**
     * Inserts the Min Max value amount field to the 'Edit' product category fields
     *
     * @param mixed $term The product category being edited
     */
    public function edit_min_max_value_field( $term ) {
        $data_key = Field_Util::get_data_key( 'value_rules', 'category' );
        $value = get_term_meta( $term->term_id, $data_key, true );

        ?>
        <tr class="form-field wqm-value-rules-wrap">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr( $data_key ); ?>"><?php printf( __( 'Value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ); ?></label>
                <?php echo wc_help_tip( __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value that can be purchased from the category. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' ) ); ?>
            </th>
            <td>
            <?php Field_Util::min_max_input_field( $value, [
                'id'                => $data_key,
                'currency'          => true,
            ] ); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Inserts the default quantity field to the 'Add New' product category fields
     */
    public function add_default_quantity_field() {
        $data_key = Field_Util::get_data_key( 'default_quantity', 'category' );
        ?>
        <div class="form-field wqm-value-rules-wrap">
            <label for="<?php echo esc_attr( $data_key ); ?>"><?php _e( 'Default quantity', 'woocommerce-quantity-manager' ) ?></label>
            <input
                name="_default_product_quantity"
                id="quantity_manager_default_quantity"
                type="number"
                class="wqm-quantity-field"
                min="0"
            />
        </div>
        <?php
    }

    /**
     * Inserts the default quantity field to the 'Edit' product category fields
     *
     * @param mixed $term The product category being edited
     */
    public function edit_default_quantity_field( $term ) {
        $data_key = Field_Util::get_data_key( 'default_quantity', 'category' );
        $value = get_term_meta( $term->term_id, $data_key, true ); // Use legacy WDQ key

        ?>
        <tr class="form-field wqm-value-rules-wrap">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr( $data_key ); ?>"><?php _e( 'Default quantity', 'woocommerce-quantity-manager' ); ?></label>
            </th>
            <td>
                <input
                    name="<?php echo esc_attr( $data_key ); ?>"
                    id="<?php echo esc_attr( $data_key ); ?>"
                    class="wqm-quantity-field"
                    type="number"
                    min="0"
                    value="<?php echo $value; ?>"
                />
            </td>
        </tr>
        <?php
    }

    /**
     * Inserts the quantity step field to the 'Add New' product category fields
     */
    public function add_quantity_step_field() {
        $data_key = Field_Util::get_data_key( 'quantity_step', 'category' );
        $tooltip_text = Field_Util::shared_quantity_step_calulation() ?
            __( 'Enter a whole number to force customers to purchase from the category in specific increments. For example, if you enter 5 then customers must select multiples of 5, 10, 15, etc. before they can complete their order. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' )
            : __( 'Enter a whole number to force customers to purchase from the category in specific increments. For example, if you enter 5 then customers must add items from the category in multiples of 5, 10, 15, etc. before they can complete their order. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' );
            ?>
        <div class="form-field wqm-default-quantity-wrap">
            <label for="<?php echo esc_attr( $data_key ); ?>">
                <?php _e( 'Quantity step values', 'woocommerce-quantity-manager' ); ?>
                <?php echo wc_help_tip( $tooltip_text ); ?>
            </label>
            <input
                name="<?php echo esc_attr( $data_key ); ?>"
                id="<?php echo esc_attr( $data_key ); ?>"
                type="number"
                class="wqm-quantity-field"
                min="0"
            />
        </div>
        <?php
    }

    /**
     * Inserts the quantity step field to the 'Edit' product category fields
     *
     * @param mixed $term The product category being edited
     */
    public function edit_quantity_step_field( $term ) {
        $data_key = Field_Util::get_data_key( 'quantity_step', 'category' );
        $value = get_term_meta( $term->term_id, $data_key, true );

        ?>
        <tr class="form-field wqm-quantity-step-wrap">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr( $data_key ); ?>"><?php _e( 'Quantity step values', 'woocommerce-quantity-manager' ); ?></label>
                <?php echo wc_help_tip( __( 'Enter a whole number to force customers to purchase from the category in specific increments. For example, if you enter 5 then customers must add items from the category in multiples of 5, 10, 15, etc. before they can complete their order. This can be overridden for specific products or variations.', 'woocommerce-quantity-manager' ) ); ?>
            </th>
            <td>
                <input
                    name="<?php echo esc_attr( $data_key ); ?>"
                    id="<?php echo esc_attr( $data_key ); ?>"
                    class="wqm-quantity-field"
                    type="number"
                    min="0"
                    value="<?php echo $value; ?>"
                />
            </td>
        </tr>
        <?php
    }

    /**
     * Save Min Max quantity field
     *
     * @param mixed $term_id Term ID being saved
     * @param mixed $tt_id The term taxonomy ID
     */
    public function save_quantity_rules_field( $term_id, $tt_id = '' ) {
        $data_key = Field_Util::get_data_key( 'quantity_rules', 'category' );
        $quantity_rules = Field_Util::sanitize_min_max_quantity( $_POST[ $data_key ] );

        // Bail if no quantity rules to save (e.g. on 'quick edit')
        if ( is_null( $quantity_rules ) || $quantity_rules === false ) {
            return;
        }

        update_term_meta( $term_id, $data_key, $quantity_rules );
    }

    /**
     * Save Min Max value field
     *
     * @param mixed $term_id Term ID being saved
     * @param mixed $tt_id The term taxonomy ID
     */
    public function save_value_rules_field( $term_id, $tt_id = '' ) {
        $data_key = Field_Util::get_data_key( 'value_rules', 'category' );
        $value_rules = Field_Util::sanitize_min_max_value( $_POST[ $data_key ] );

        // Bail if no quantity rules to save (e.g. on 'quick edit')
        if ( is_null( $value_rules ) || $value_rules === false ) {
            return;
        }

        update_term_meta( $term_id, $data_key, $value_rules );
    }


    /**
     * Save default quantity
     *
     * @param mixed $term_id Term ID being saved
     * @param mixed $tt_id The term taxonomy ID
     */
    public function save_default_quantity_field( $term_id, $tt_id = '' ) {
        $data_key = Field_Util::get_data_key( 'default_quantity', 'category' );
        $default_quantity = filter_input( INPUT_POST, $data_key, FILTER_SANITIZE_NUMBER_INT );

        if ( is_null( $default_quantity ) || $default_quantity === false ) {
            return;
        }

        update_term_meta( $term_id, $data_key, $default_quantity );
    }

    /**
     * Save quantity step
     *
     * @param mixed $term_id Term ID being saved
     * @param mixed $tt_id The term taxonomy ID
     */
    public function save_quantity_step_field( $term_id, $tt_id = '' ) {
        $data_key = Field_Util::get_data_key( 'quantity_step', 'category' );
        $quantity_step = filter_input( INPUT_POST, $data_key, FILTER_SANITIZE_NUMBER_INT );

        if ( is_null( $quantity_step ) || $quantity_step === false ) {
            return;
        }

        update_term_meta( $term_id, $data_key, $quantity_step );
    }
}
