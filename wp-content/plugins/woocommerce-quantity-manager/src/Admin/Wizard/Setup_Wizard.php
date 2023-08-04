<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard;

use Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;
use Barn2\WQM_Lib\Plugin\License\EDD_Licensing;
use Barn2\WQM_Lib\Plugin\License\Plugin_License;
use Barn2\WQM_Lib\Plugin\Licensed_Plugin;
use Barn2\WQM_Lib\Registerable;
use Barn2\WQM_Lib\Util as Lib_Util;

/**
 * Main Setup Wizard Loader
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Setup_Wizard implements Registerable {

	private $plugin;
	private $wizard;

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {

		$this->plugin = $plugin;

		$steps = [
			new Steps\License_Verification(),
			new Steps\Order_Rules(),
			new Steps\Step_Rules(),
			new Steps\Upsell(),
			new Steps\Completed(),
		];

		$wizard = new Wizard( $this->plugin, $steps );

		$wizard->configure(
			[
				'skip_url'        => admin_url( 'admin.php?page=wc-settings&tab=products&section=quantity-manager' ),
				'license_tooltip' => esc_html__( 'The licence key is contained in your order confirmation email.', 'woocommerce-quantity-manager' ),
			]
		);

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );
		$wizard->add_restart_link( 'quantity-manager', 'quantity_manager_options' );

		$wizard->add_custom_asset(
			$plugin->get_dir_url() . 'assets/js/admin/wqm-wizard.min.js',
			Lib_Util::get_script_dependencies( $this->plugin, 'admin/wqm-wizard.min.js' )
		);

		$this->wizard = $wizard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->wizard->boot();
	}

}
