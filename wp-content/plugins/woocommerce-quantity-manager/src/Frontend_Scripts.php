<?php

namespace Barn2\Plugin\WC_Quantity_Manager;

use Barn2\WQM_Lib\Util as Lib_Util,
    Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Conditional,
    Barn2\WQM_Lib\Service;

/**
 * Handles the registering of the front-end scripts.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Frontend_Scripts implements Service, Registerable, Conditional {

    private $plugin;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    public function is_required() {
        return Lib_Util::is_front_end();
    }

    public function register() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 15 );
    }

    public function register_scripts() {
        wp_enqueue_script( 'wqm-frontend', plugins_url( 'assets/js/wqm-frontend.min.js', $this->plugin->get_file() ), [ 'jquery' ], $this->plugin->get_version(), false );
    }
}
