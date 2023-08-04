<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Step,
	Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
	Barn2\Plugin\WC_Quantity_Manager\Util\Util;

/**
 * Layout Step.
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Step_Rules extends Step {


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_id( 'step-rules' );
		$this->set_name( __( 'Defaults & Step Values', 'woocommerce-quantity-manager' ) );
		$this->set_description( esc_html__( 'You can also set a default quantity value and force customers to buy in specific quantity increments.', 'woocommerce-quantity-manager' ) );
		$this->set_title( __( 'Quantity Defaults & Step Values', 'woocommerce-quantity-manager' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {

		$fields = [
			'default_quantity' => [
				'label'       => __( 'Global default quantity', 'woocommerce-quantity-manager' ),
				'description' => __( 'Choose a default quantity for all your products. You can override this for individual categories/products.', 'woocommerce-quantity-manager' ),
				'type'        => 'number',
				'min'         => 1,
				'step'        => 1,
				'value'       => get_option( Field_Util::get_data_key( 'default_quantity', 'global' ), 1 ),
			],

			'quantity_step'    => [
				'label'       => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
				'description' => __( 'Enter a whole number to force customers to purchase in specific quantity increments. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
				'type'        => 'number',
				'min'         => 1,
				'step'        => 1,
				'value'       => get_option( Field_Util::get_data_key( 'quantity_step', 'global' ), 1 ),
			],

			'step_calculation' => [
				'label'       => __( 'Step value calculation', 'woocommerce-quantity-manager' ),
				'description' => __( 'Choose whether to enforce the quantity step value individually for each product/variation, or to share it so that customers can combine multiple products to meet the required step value.', 'woocommerce-quantity-manager' ),
				'type'        => 'select',
				'options'     => [
					[
						'key'   => 'individual',
						'label' => __( 'Individual products/variations', 'woocommerce-quantity-manager' ),
					],
					[
						'key'   => 'shared',
						'label' => __( 'Share across multiple products', 'woocommerce-quantity-manager' ),
					]
				],
				'value'       => get_option( 'quantity_manager_step_value_calc', 'individual' ),
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

		$default_quantity = isset( $values['default_quantity'] ) && ! empty( $values['default_quantity'] ) ? $values['default_quantity'] : 1;
		$quantity_step    = isset( $values['quantity_step'] ) && ! empty( $values['quantity_step'] ) ? $values['quantity_step'] : 1;
		$step_calculation = isset( $values['step_calculation'] ) && ! empty( $values['step_calculation'] ) ? $values['step_calculation'] : 'individual';

		if ( ! Util::is_integer( $default_quantity ) ) {
			$this->send_error( esc_html__( 'Please enter a whole number for the default quantity.', 'woocommerce-quantity-manager' ) );
		}

		if ( ! Util::is_integer( $quantity_step ) ) {
			$this->send_error( esc_html__( 'Please enter a whole number for the quantity step', 'woocommerce-quantity-manager' ) );
		}

		update_option( Field_Util::get_data_key( 'default_quantity', 'global' ), $default_quantity );
		update_option( Field_Util::get_data_key( 'quantity_step', 'global' ), $quantity_step );
		update_option( 'quantity_manager_step_value_calc', $step_calculation );

		wp_send_json_success();

	}
}
