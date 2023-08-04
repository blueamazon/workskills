<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Starter as Setup_Wizard_Starter;

/**
 * Setup Wizard Starter
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Starter extends Setup_Wizard_Starter {

	/**
	 * Determine if the conditions to start the wizard are met.
	 *
	 * @return boolean
	 */
	public function should_start() {
		$setup_happened = get_option( 'woocommerce-quantity-manager-setup-wizard_completed' ) ?: false;
		return ! $setup_happened;
	}

}
