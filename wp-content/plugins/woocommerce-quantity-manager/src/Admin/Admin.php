<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin;

use Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Setup_Wizard;
use Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Service,
    Barn2\WQM_Lib\Conditional,
    Barn2\WQM_Lib\Service_Container,
    Barn2\WQM_Lib\Plugin\Licensed_Plugin,
    Barn2\WQM_Lib\Util as Util,
    Barn2\WQM_Lib\Plugin\Admin\Admin_Links,
    Barn2\WQM_Lib\WooCommerce\Admin\Navigation;

defined( 'ABSPATH' ) || exit;

/**
 * General Admin Functions
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Admin implements Registerable, Service, Conditional {

    use Service_Container;

    private $plugin;
    private $license;

    public function __construct( Licensed_Plugin $plugin ) {
        $this->plugin = $plugin;
        $this->license = $this->plugin->get_license();
    }

    public function is_required() {
        return Util::is_admin();
    }

    public function register() {
        $this->register_services();

        // Load admin scripts.
        add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
    }

    public function get_services() {
        $services = [
            'admin_links'   => new Admin_Links( $this->plugin ),
            'wc_navigation' => new Navigation( $this->plugin, 'products&section=quantity-manager', __( 'Quantity Manager', 'woocommerce-quantity-manager' ) ),
            'settings_page' => new Settings_Page( $this->plugin ),
            'setup_wizard'  => new Setup_Wizard( $this->plugin ),
        ];

        if ( $this->license->is_valid() ) {
            $services = array_merge( $services, [
                'category_edit' => new Category_Edit(),
                'product_edit'  => new Product_Edit(),
            ] );
        }

        return $services;
    }

    public function load_scripts( $hook ) {
        global $post;

        // Min Max Field
        wp_register_script( 'wqm-min-max-field', plugins_url( 'assets/js/admin/wqm-min-max-field.min.js', $this->plugin->get_file() ), [ 'jquery' ], $this->plugin->get_version(), true );
        wp_localize_script( 'wqm-min-max-field', 'wqm_field_validation', [
            'min_max' => __( 'You cannot have a minimum value greater than your maximum value.', 'woocommerce-quantity-manager' ),
            'max_min' => __( 'You cannot have a maximum value less than your minimum value.', 'woocommerce-quantity-manager' ),
        ] );

        // Settings
        if ( 'woocommerce_page_wc-settings' === $hook && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'products' && isset( $_REQUEST['section'] ) && $_REQUEST['section'] === 'quantity-manager' ) {
            wp_enqueue_style( 'wqm-admin-settings', plugins_url( 'assets/css/admin/wqm-admin-settings.min.css', $this->plugin->get_file() ), [], $this->plugin->get_version() );
            wp_enqueue_script( 'wqm-admin-settings', plugins_url( 'assets/js/admin/wqm-admin-settings.min.js', $this->plugin->get_file() ), [ 'jquery', 'wqm-min-max-field', 'selectWoo' ], $this->plugin->get_version(), true );
        }

        // Category
        $screen = get_current_screen();
        if ( $screen && 'product_cat' === $screen->taxonomy ) {
            wp_enqueue_style( 'wqm-admin-categories', plugins_url( 'assets/css/admin/wqm-admin-categories.min.css', $this->plugin->get_file() ), [], $this->plugin->get_version() );
            wp_enqueue_script( 'wqm-admin-categories', plugins_url( 'assets/js/admin/wqm-admin-categories.min.js', $this->plugin->get_file() ), [ 'jquery', 'wqm-min-max-field' ], $this->plugin->get_version(), true );
        }

        // Product
        if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'product' === $post->post_type ) {
            wp_enqueue_style( 'wqm-admin-products', plugins_url( 'assets/css/admin/wqm-admin-products.min.css', $this->plugin->get_file() ), [], $this->plugin->get_version() );
            wp_enqueue_script( 'wqm-admin-products', plugins_url( 'assets/js/admin/wqm-admin-products.min.js', $this->plugin->get_file() ), [ 'jquery', 'wqm-min-max-field' ], $this->plugin->get_version(), true );
        }
    }
}