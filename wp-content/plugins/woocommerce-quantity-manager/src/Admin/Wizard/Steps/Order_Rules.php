<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Step,
	Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
	Barn2\WQM_Lib\Util as Lib_Util;

/**
 * Layout Step.
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Order_Rules extends Step {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_id( 'order-rules' );
		$this->set_name( esc_html__( 'Cart Rules', 'woocommerce-quantity-manager' ) );
		$this->set_description( esc_html__( 'Do you want to set minimum or maximum quantity rules for the entire cart?', 'woocommerce-quantity-manager' ) );
		$this->set_title( esc_html__( 'Order Quantity Rules', 'woocommerce-quantity-manager' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {

		$fields = [
			'quantity_rules' => [
				'title'       => __( 'Order quantity rules', 'woocommerce-quantity-manager' ),
				'description' => __( 'Enter a whole number to set a minimum and/or maximum number of items that can be purchased in the entire cart. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
				'type'        => 'min_max',
				'min'         => [
					'label'     => __( 'Minimum', 'woocommerce-quantity-manager' ),
					'min_value' => 0,
					'value'     => 5,
				],
				'max'         => [
					'label'     => __( 'Maximum', 'woocommerce-quantity-manager' ),
					'min_value' => 0,
					'value'     => 10
				],
				'step'        => 1,
				'value'       => get_option( Field_Util::get_data_key( 'quantity_rules', 'global' ) )
			],

			'value_rules'    => [
				/* translators: %s: WooCommerce Currency Symbol */
				'title'       => sprintf( __( 'Order value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ),
				'description' => __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value for the entire cart. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
				'type'        => 'min_max',
				'min'         => [
					/* translators: %s: WooCommerce Currency Symbol */
					'label'     => sprintf( __( 'Minimum (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ),
					'min_value' => 0,
					'value'     => 5,
				],
				'max'         => [
					/* translators: %s: WooCommerce Currency Symbol */
					'label'     => sprintf( __( 'Maximum (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ),
					'min_value' => 0,
					'value'     => 10
				],
				'step'        => 0.01,
				'value'       => get_option( Field_Util::get_data_key( 'value_rules', 'global' ) )
			],
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit() {

		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( esc_html__( 'You are not authorized.', 'woocommerce-quantity-manager' ) );
		}

		$values = $this->get_submitted_values();

		$quantity_rules = isset( $values['quantity_rules'] ) && is_array( $values['quantity_rules'] ) ? $values['quantity_rules'] : false;
		$value_rules    = isset( $values['value_rules'] ) && is_array( $values['value_rules'] ) ? $values['value_rules'] : false;

		if ( ! $quantity_rules ) {
			$this->send_error( esc_html__( 'Invalid quantity rules provided.', 'woocommerce-quantity-manager' ) );
		}

		if ( ! $value_rules ) {
			$this->send_error( esc_html__( 'Invalid value rules provided.', 'woocommerce-quantity-manager' ) );
		}

		$quantity_rules = Field_Util::sanitize_min_max_quantity( $quantity_rules );
		$value_rules    = Field_Util::sanitize_min_max_value( $value_rules );

		update_option( Field_Util::get_data_key( 'quantity_rules', 'global' ), $quantity_rules );
		update_option( Field_Util::get_data_key( 'value_rules', 'global' ), $value_rules );

		wp_send_json_success();

	}
}
