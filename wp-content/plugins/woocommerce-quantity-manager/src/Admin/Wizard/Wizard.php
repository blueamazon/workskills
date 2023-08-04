<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Interfaces\Restartable;
use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Setup_Wizard;

/**
 * Main Setup Wizard Loader
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Wizard extends Setup_Wizard implements Restartable {

	/**
	 * On wizard restart, detect which pages should be automatically unhidden.
	 *
	 * @return void
	 */
	public function on_restart() {
		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'error_message' => __( 'You are not authorized.', 'woocommerce-quantity-manager' ) ], 403 );
		}
	}

}
