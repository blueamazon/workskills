<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Steps\Welcome;

/**
 * Welcome / License Step.
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class License_Verification extends Welcome {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_id( 'license_activation' );
		$this->set_name( esc_html__( 'Welcome', 'woocommerce-quantity-manager' ) );
		$this->set_description( esc_html__( 'Start adding quantity rules in no time.', 'woocommerce-quantity-manager' ) );
		$this->set_tooltip( esc_html__( 'Use this setup wizard to add some global quantity rules to your store. You can change these options later on the plugin settings page or by relaunching the setup wizard. You can also set quantity rules for specific categories and products.', 'woocommerce-quantity-manager' ) );
	}

}
