<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Steps\Cross_Selling;

/**
 * Upsell Step.
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Upsell extends Cross_Selling {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'woocommerce-quantity-manager' ) );
		$this->set_description(
			sprintf(
				// translators: %1$s: URL to All Access Pass page %2$s: URL to the KB about the upgrading process
				__( 'Enhance your store with these fantastic plugins from Barn2, or get them all by upgrading to an <a href="%1$s" target="_blank">All Access Pass<a/>! <a href="%2$s" target="_blank">(learn how here)</a>', 'woocommerce-quantity-manager' ),
				'https://barn2.com/wordpress-plugins/bundles/',
				'https://barn2.com/kb/how-to-upgrade-license/'
			)
		);
		$this->set_title( esc_html__( 'Extra features', 'woocommerce-quantity-manager' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upsells() {
		$this->get_wizard()->set_as_completed();
		parent::get_upsells();
	}

}
