<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin;

use Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Starter,
	Barn2\WQM_Lib\Plugin\Licensed_Plugin,
	Barn2\WQM_Lib\Plugin\Plugin_Activation_Listener,
	Barn2\WQM_Lib\Registerable;

/**
 * Plugin Setup
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin_Setup implements Plugin_Activation_Listener, Registerable {
	/**
	 * Plugin's entry file
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Wizard starter.
	 *
	 * @var Starter
	 */
	private $starter;

	/**
	 * Plugin instance
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param mixed $file
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( $file, Licensed_Plugin $plugin ) {
		$this->file    = $file;
		$this->plugin  = $plugin;
		$this->starter = new Starter( $this->plugin );
	}

	/**
	 * Register the service
	 *
	 * @return void
	 */
	public function register() {
		register_activation_hook( $this->file, [ $this, 'on_activate' ] );
		add_action( 'admin_init', [ $this, 'after_plugin_activation' ] );
	}

	/**
	 * On plugin activation determine if the setup wizard should run.
	 *
	 * @return void
	 */
	public function on_activate() {
		if ( $this->starter->should_start() ) {
			$this->starter->create_transient();
		}
	}

	/**
	 * Do nothing.
	 *
	 * @return void
	 */
	public function on_deactivate() {}

	/**
	 * Detect the transient and redirect to wizard.
	 *
	 * @return void
	 */
	public function after_plugin_activation() {

		if ( ! $this->starter->detected() ) {
			return;
		}

		$this->starter->delete_transient();
		$this->starter->redirect();
	}
}
