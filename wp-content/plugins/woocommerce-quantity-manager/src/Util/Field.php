<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Util;

use Barn2\WQM_Lib\WooCommerce\Admin\Settings_Util,
    WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Field (Data) Utilities
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Field {

    private static $keys = [
        'quantity_rules'    => [
            'global'    => 'quantity_manager_global_quantity',
            'category'  => 'quantity_manager_category_quantity',
            'product'   => '_wqm_product_quantity',
            'variation' => '_wqm_variation_product_quantity',
        ],
        'value_rules'       => [
            'global'    => 'quantity_manager_global_value',
            'category'  => 'quantity_manager_category_value',
            'product'   => '_wqm_product_value',
            'variation' => '_wqm_variation_product_value',
        ],
        'default_quantity'  => [
            'global'    => 'default-quantity__default_quantity', // legacy (WDQ)
            'category'  => '_default_product_quantity', // legacy (WDQ)
            'product'   => '_product_default_quantity', // legacy (WDQ)
            'variation' => '_wqm_variation_default_quantity',
        ],
        'quantity_step'     => [
            'global'    => 'quantity_manager_global_step',
            'category'  => 'quantity_manager_category_step',
            'product'   => '_wqm_product_step',
            'variation' => '_wqm_variation_product_step',
        ],
    ];

    /**
     * Outputs a min max input
     *
     * @param array $value
     * @param array $data
     */
    public static function min_max_input_field( $value, $data ) {
        $currency           = isset( $data['currency'] ) && $data['currency'] ? true : false;
        $min_value          = isset( $value['min'] ) ? wc_format_localized_price( $value['min'] ) : '';
        $max_value          = isset( $value['max'] ) ? wc_format_localized_price( $value['max'] ) : '';
        $custom_attributes  = Settings_Util::get_custom_attributes( $data ); // atts are escaped
        $input_classes      = $currency ? [ 'wqm-quantity-input', 'wc_input_price' ] : [ 'wqm-quantity-input' ];
        $input_classes      = isset( $data['classes'] ) && is_array( $data['classes'] ) ? array_merge( $data['classes'], $input_classes ) : $input_classes;
        $input_type         = $currency ? 'text' : 'number';
        $labels             = [
            'min' =>  __( 'Minimum', 'woocommerce-quantity-manager' ),
            'max' =>  __( 'Maximum', 'woocommerce-quantity-manager' ),
        ];

        ?>
        <span id="<?php echo esc_attr( $data['id'] ); ?>" class="wqm-min-max-input-container">
            <label class="wqm-min-max-label" for="<?php echo esc_attr( $data['id'] . "[min]" ); ?>">
                <span><?php echo $labels['min']; ?></span>
                <input
                    name="<?php echo esc_attr( $data['id'] . "[min]" ); ?>"
                    id="<?php echo esc_attr( $data['id'] . "[min]" ); ?>"
                    type="<?php echo esc_attr( $input_type ); ?>"
                    value="<?php echo esc_attr( $min_value ); ?>"
                    class="<?php echo self::class_names( $input_classes, 'min' ); ?>"
                    placeholder="<?php _e( 'Minimum', 'woocommerce-quantity-manager' ); ?>"
                    <?php echo $custom_attributes; ?>
                />
            </label>

            <label class="wqm-min-max-label" for="<?php echo esc_attr( $data['id'] . "[max]" ); ?>">
                <span><?php echo $labels['max']; ?></span>
                <input
                    name="<?php echo esc_attr( $data['id'] . "[max]" ); ?>"
                    id="<?php echo esc_attr( $data['id'] . "[max]" ); ?>"
                    type="<?php echo esc_attr( $input_type ); ?>"
                    value="<?php echo esc_attr( $max_value ); ?>"
                    class="<?php echo self::class_names( $input_classes, 'max' ); ?>"
                    placeholder="<?php _e( 'Maximum', 'woocommerce-quantity-manager' ); ?>"
                    <?php echo $custom_attributes; ?>
                />
            </label>
        </span>
        <?php
    }

    /**
     * Sanitize the min max quantity field
     *
     * @param mixed $value
     * @return array
     */
    public static function sanitize_min_max_quantity( $value ) {
        if ( ! is_array( $value ) ) {
            return [
                'min' => '',
                'max' => '',
            ];
        }

        if ( ! isset( $value['min'] ) || ! Util::is_integer( $value['min'] ) ) {
            $value['min'] = '';
        }

        if ( ! isset( $value['max'] ) || ! Util::is_integer( $value['max'] ) ) {
            $value['max'] = '';
        }

        if ( $value['max'] < $value['min'] ) {
            $value['max'] = '';
        }

        return $value;
    }

    /**
     * Sanitize the min max value field
     *
     * @param mixed $value
     * @return array
     */
    public static function sanitize_min_max_value( $value ) {
        if ( ! is_array( $value ) ) {
            return [
                'min' => '',
                'max' => '',
            ];
        }

        if ( ! isset( $value['min'] ) ) {
            $value['min'] = '';
        }

        if ( ! isset( $value['max'] ) ) {
            $value['max'] = '';
        }

        $value = array_map( 'wc_format_decimal', $value );

        if ( $value['max'] < $value['min'] ) {
            $value['max'] = '';
        }

        return $value;

    }

    /**
     * Gets the data key to access the meta on various object types
     *
     * @param string $type
     * @param string $context
     * @return WP_Error|string
     */
    public static function get_data_key( $type, $context ) {
        if ( ! in_array( $type, [ 'quantity_rules', 'value_rules', 'default_quantity', 'quantity_step' ] ) ) {
            return new WP_Error( 'quantity_field_request_invalid_type', __( 'Invalid type provided for quantity key request.', 'woocommerce-quantity-manager' ) );
        }

        if ( ! in_array( $context, [ 'global', 'category', 'product', 'variation' ] ) ) {
            return new WP_Error( 'quantity_field_request_invalid_context', __( 'Invalid context provided for quantity key request.', 'woocommerce-quantity-manager' ) );
        }

        return self::$keys[ $type ][ $context ];
    }

    /**
     * Determines whether shared quantity step calculation is active
     *
     * @return bool
     */
    public static function shared_quantity_step_calulation() {
        return get_option( 'quantity_manager_step_value_calc', 'individual' ) === 'shared';
    }

    /**
     * Check if meta contains data
     *
     * @param mixed $value
     * @return bool
     */
    public static function has_meta_value( $value ) {
        return $value !== false && $value !== '';
    }

    /**
     * Utility function to merge and flatten input class names
     *
     * @param array|string $existing_items
     * @param array|string $new_items
     * @return string
     */
    public static function class_names( $existing_items, $new_items ) {
        if ( ! is_array( $existing_items ) ) {
            $existing_items = [ $existing_items ];
        }

        if ( ! is_array( $new_items ) ) {
            $new_items = [ $new_items ];
        }

        $combined_items = array_merge( $existing_items, $new_items );

        return esc_attr( implode( ' ', $combined_items ) );
    }
}