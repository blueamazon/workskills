<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Admin;

use Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Service,
    Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
    WC_Product;

defined( 'ABSPATH' ) || exit;
/**
 * Handles the quantity settings on the Edit Product screen, in the Product Data metabox.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Edit implements Registerable, Service {

    /**
     * Register hooks and filters.
     */
    public function register() {
        add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'add_quantity_rules_field' ] );
        add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'add_value_rules_field' ] );
        add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'add_default_quantity_field' ] );
        add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'add_quantity_step_field' ] );

        add_action( 'woocommerce_process_product_meta', [ $this, 'save_fields' ] );

        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_quantity_rules_field' ], 10, 3 );
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_value_rules_field' ], 10, 3 );
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_default_quantity_field' ], 10, 3 );
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_quantity_step_field' ], 10, 3 );

        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_fields' ], 10, 2 );
    }

    /**
     * Add the quantity rules (min/max) field to Product Data > Inventory.
     */
    public function add_quantity_rules_field() {
        global $product_object;

        $data_key = Field_Util::get_data_key( 'quantity_rules', 'product' );
        $value = $product_object->get_meta( $data_key );

        ?>
        <p class="form-field _quantity_rules_field show_if_simple show_if_variable hide_if_bundle hide_if_composite">
            <label for="<?php echo esc_attr( $data_key ); ?>"><?php _e( 'Quantity rules', 'woocommerce-quantity-manager' ); ?></label>

            <?php
            $tooltip_content = $this->maybe_add_variation_message(
                $product_object,
                __( 'Enter a whole number to set a minimum and/or maximum number of the product that can be purchased.', 'woocommerce-quantity-manager' )
            );

            echo wc_help_tip( $tooltip_content );

            Field_Util::min_max_input_field( $value, [
                'id'                => $data_key,
                'classes'           => [ 'wqm-product-field' ],
                'custom_attributes' => array_merge( [
                    'min'       => 1,
                    'step'      => 1,
                ], $this->maybe_add_disabled_attribute( $product_object ) )
            ] );
            ?>
        </p>
        <?php
    }

    /**
     * Add the value rules (min/max) field to Product Data > Inventory.
     */
    public function add_value_rules_field() {
        global $product_object;

        $data_key = Field_Util::get_data_key( 'value_rules', 'product' );
        $value = $product_object->get_meta( $data_key );

        ?>
        <p class="form-field _value_rules_field show_if_simple show_if_variable hide_if_bundle hide_if_composite">
            <label for="<?php echo esc_attr( $data_key ); ?>"><?php printf( __( 'Value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ); ?></label>

            <?php
            $tooltip_content = $this->maybe_add_variation_message(
                $product_object,
                __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value of the product that can be purchased.', 'woocommerce-quantity-manager' )
            );

            echo wc_help_tip( $tooltip_content );

            Field_Util::min_max_input_field( $value, [
                'id'            => $data_key,
                'currency'      => true,
                'classes'       => [ 'wqm-product-field' ],
                'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
            ] );
            ?>
        </p>
        <?php
    }

    /**
     * Add the default quantiy field to Product Data > Inventory.
     */
    public function add_default_quantity_field() {
        global $product_object;

        $data_key = Field_Util::get_data_key( 'default_quantity', 'product' );
        $value = $product_object->get_meta( $data_key );

        woocommerce_wp_text_input( [
            'id'            => $data_key,
            'name'          => $data_key,
            'value'         => $value,
            'class'         => 'short wqm-product-field',
            'wrapper_class' => 'show_if_simple show_if_variable hide_if_bundle hide_if_composite',
            'label'         => __( 'Default quantity', 'woocommerce-quantity-manager' ),
            'type'          => 'number',
            'placeholder'   => __( 'Default quantity', 'woocommerce-quantity-manager' ),
            'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
        ] );
    }

    /**
     * Add the quantity step field to Product Data > Inventory.
     */
    public function add_quantity_step_field() {
        global $product_object;

        $data_key = Field_Util::get_data_key( 'quantity_step', 'product' );
        $value = $product_object->get_meta( $data_key );

        $tooltip_text = Field_Util::shared_quantity_step_calulation() ?
        __( 'Enter a whole number to force customers to purchase in specific increments. For example, if you enter 5 then customers must select multiples of 5, 10, 15, etc. before they can complete their order.', 'woocommerce-quantity-manager' )
        : __( 'Enter a whole number to force customers to purchase in specific increments. For example, if you enter 5 then customers must add the product in multiples of 5, 10, 15, etc. before they can complete their order', 'woocommerce-quantity-manager' );

        $tooltip_content = $this->maybe_add_variation_message( $product_object, $tooltip_text );

        woocommerce_wp_text_input( [
            'id'            => $data_key,
            'name'          => $data_key,
            'value'         => $value,
            'class'         => 'short wqm-product-field',
            'wrapper_class' => 'show_if_simple show_if_variable hide_if_bundle hide_if_composite',
            'label'         => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
            'type'          => 'number',
            'placeholder'   => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
            'desc_tip'      => true,
            'description'   => $tooltip_content,
            'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
        ] );
    }

    /**
     * Add the quantity rules (min/max) field to product variations.
     *
     * @param   int         $index
     * @param   array       $variation_data
     * @param   WC_Product  $variation
     */
    public function add_variation_quantity_rules_field( $index, $variation_data, $variation ) {
        $product_object = wc_get_product( $variation->ID );
        $data_key = Field_Util::get_data_key( 'quantity_rules', 'variation' );
        $value = $product_object->get_meta( $data_key );

        ?>
        <p class="form-field form-row form-row-first">
            <label for="<?php echo esc_attr( sprintf( '%s[%d]', $data_key, $index ) ); ?>"><?php _e( 'Quantity rules', 'woocommerce-quantity-manager' ); ?></label>

            <?php
            echo wc_help_tip( __( 'Enter a whole number to set a minimum and/or maximum number of the variation that can be purchased.', 'woocommerce-quantity-manager' ) );

            Field_Util::min_max_input_field( $value, [
                'id'                => "{$data_key}[{$index}]",
                'classes'           => [ 'wqm-product-field' ],
                'custom_attributes' => [
                    'min'       => 1,
                    'step'      => 1,
                    'disabled'  => $product_object->is_sold_individually(),
                ]
            ] );
            ?>

        </p>
        <?php
    }

    /**
     * Add the quantity field to product variations.
     *
     * @param   int         $index
     * @param   array       $variation_data
     * @param   WC_Product  $variation
     */
    public function add_variation_value_rules_field( $index, $variation_data, $variation ) {
        $product_object = wc_get_product( $variation->ID );
        $data_key = Field_Util::get_data_key( 'value_rules', 'variation' );
        $value = $product_object->get_meta( $data_key );

        ?>
        <p class="form-field form-row form-row-last">
            <label for="<?php echo esc_attr( sprintf( '%s[%d]', $data_key, $index ) ); ?>"><?php printf( __( 'Value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ); ?></label>

            <?php
            echo wc_help_tip( __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value of the variation that can be purchased.', 'woocommerce-quantity-manager' ) );

            Field_Util::min_max_input_field( $value, [
                'id'                => "{$data_key}[{$index}]",
                'classes'           => [ 'wqm-product-field' ],
                'currency'          => true,
                'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
            ] );
            ?>

        </p>
        <?php
    }

    /**
     * Add the default quantity field to product variations.
     *
     * @param   int         $index
     * @param   array       $variation_data
     * @param   WC_Product  $variation
     */
    public function add_variation_default_quantity_field( $index, $variation_data, $variation ) {
        $product_object = wc_get_product( $variation->ID );
        $data_key = Field_Util::get_data_key( 'default_quantity', 'variation' );
        $value = $product_object->get_meta( $data_key );

        woocommerce_wp_text_input( [
                'id'            => "{$data_key}[{$index}]",
                'name'          => "{$data_key}[{$index}]",
                'value'         => $value,
                'label'         => __( 'Default quantity', 'woocommerce-quantity-manager' ),
                'type'          => 'number',
                'class'         => 'short wqm-product-field',
                'wrapper_class' => 'form-row form-row-first',
                'placeholder'   => __( 'Default quantity', 'woocommerce-quantity-manager' ),
                'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
        ] );
    }

    /**
     * Add the default quantity field to product variations.
     *
     * @param   int         $index
     * @param   array       $variation_data
     * @param   WC_Product  $variation
     */
    public function add_variation_quantity_step_field( $index, $variation_data, $variation ) {
        $product_object = wc_get_product( $variation->ID );
        $data_key = Field_Util::get_data_key( 'quantity_step', 'variation' );
        $value = $product_object->get_meta( $data_key );

        $tooltip_text = Field_Util::shared_quantity_step_calulation() ?
            __( 'Enter a whole number to force customers to purchase in specific increments. For example, if you enter 5 then customers must select multiples of 5, 10, 15, etc. before they can complete their order.', 'woocommerce-quantity-manager' )
            : __( 'Enter a whole number to force customers to purchase in specific increments. For example, if you enter 5 then customers must add the variation in multiples of 5, 10, 15, etc. before they can complete their order.', 'woocommerce-quantity-manager' );

        woocommerce_wp_text_input( [
            'id'            => "{$data_key}[{$index}]",
            'name'          => "{$data_key}[{$index}]",
            'value'         => $value,
            'label'         => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
            'type'          => 'number',
            'wrapper_class' => 'form-row form-row-last',
            'class'         => 'short wqm-product-field',
            'desc_tip'      => true,
            'description'   => $tooltip_text,
            'placeholder'   => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
            'custom_attributes' => $this->maybe_add_disabled_attribute( $product_object )
        ] );
    }

    /**
     * Save the variation quantity fields.
     *
     * @param int $variation_id
     * @param int $index
     */
    public function save_variation_fields( $variation_id, $index ) {
        $product = wc_get_product( $variation_id );

        if ( ! $product ) {
            return;
        }

        $keys = [
            'quantity_rules'    => Field_Util::get_data_key( 'quantity_rules', 'variation' ),
            'value_rules'       => Field_Util::get_data_key( 'value_rules', 'variation' ),
            'default_quantity'  => Field_Util::get_data_key( 'default_quantity', 'variation' ),
            'quantity_step'     => Field_Util::get_data_key( 'quantity_step', 'variation' ),
        ];

        if ( isset( $_POST[ $keys['quantity_rules'] ][$index] ) ) {
            $product->update_meta_data( $keys['quantity_rules'], wc_clean( wp_unslash( $_POST[ $keys['quantity_rules'] ][$index] ) ) );
        }

        if ( isset( $_POST[ $keys['value_rules'] ][$index] ) ) {
            $product->update_meta_data( $keys['value_rules'], wc_clean( wp_unslash( $_POST[ $keys['value_rules'] ][$index] ) ) );
        }

        if ( isset( $_POST[ $keys['default_quantity'] ][$index] ) ) {
            $product->update_meta_data( $keys['default_quantity'], wc_clean( wp_unslash( $_POST[$keys['default_quantity']][$index] ) ) );
        }

        if ( isset( $_POST[ $keys['quantity_step'] ][$index] ) ) {
            $product->update_meta_data( $keys['quantity_step'], wc_clean( wp_unslash( $_POST[ $keys['quantity_step'] ][$index] ) ) );
        }

        $product->save();
    }

    /**
     * Save the simple product lead time field.
     *
     * @param int $post_id
     */
    public function save_fields( $post_id ) {
        $product = wc_get_product( $post_id );

        if ( ! $product ) {
            return;
        }

        $keys = [
            'quantity_rules'    => Field_Util::get_data_key( 'quantity_rules', 'product' ),
            'value_rules'       => Field_Util::get_data_key( 'value_rules', 'product' ),
            'default_quantity'  => Field_Util::get_data_key( 'default_quantity', 'product' ),
            'quantity_step'     => Field_Util::get_data_key( 'quantity_step', 'product' ),
        ];

        // Quantity Rules
        $quantity_rules = Field_Util::sanitize_min_max_quantity( $_POST[ $keys['quantity_rules'] ] );
        $product->update_meta_data( $keys['quantity_rules'], $quantity_rules );

        // Value Rules
        $value_rules = Field_Util::sanitize_min_max_value( $_POST[ $keys['value_rules'] ] );
        $product->update_meta_data( $keys['value_rules'], $value_rules );

        // Default Quantity
        $default_quantity = filter_input( INPUT_POST, $keys['default_quantity'], FILTER_SANITIZE_STRING );
        $default_quantity = is_null( $default_quantity ) || $default_quantity === false || $default_quantity === '' ? false : absint( $default_quantity );
        $product->update_meta_data( $keys['default_quantity'], $default_quantity );

        // Quantity Step
        $quantity_step = filter_input( INPUT_POST, $keys['quantity_step'], FILTER_SANITIZE_STRING );
        $quantity_step = is_null( $quantity_step ) || $quantity_step === false || $quantity_step === '' ? false : absint( $quantity_step );
        $product->update_meta_data( $keys['quantity_step'], $quantity_step );

        // Save Product
        $product->save();
    }

    /**
     * Adds the disabled attribute if the product is sold individually
     *
     * @param WC_Product $product_object
     * @return string
     */
    private function maybe_add_disabled_attribute( $product_object ) {
        return $product_object->is_sold_individually() ? [ 'disabled' => 'true' ] : [];
    }

    /**
     * Adds extra instructions to the tooltip content for variable products
     *
     * @param WC_Product $product
     * @param string $tooltip_content
     * @return string $tooltip_content
     */
    private function maybe_add_variation_message( $product, $tooltip_content ) {
        if ( $product->is_type( 'variable' ) ) {
            $tooltip_content = sprintf( '%1$s %2$s', $tooltip_content, __( 'This can be overridden for specific variations.', 'woocommerce-quantity-manager' ) );
        }

        return $tooltip_content;
    }
}