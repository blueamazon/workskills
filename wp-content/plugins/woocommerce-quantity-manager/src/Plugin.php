<?php
namespace Barn2\Plugin\WC_Quantity_Manager;

use Barn2\WQM_Lib\Registerable,
    Barn2\WQM_Lib\Translatable,
    Barn2\WQM_Lib\Service_Provider,
    Barn2\WQM_Lib\Service_Container,
    Barn2\WQM_Lib\Plugin\Premium_Plugin,
    Barn2\WQM_Lib\Plugin\Licensed_Plugin,
    Barn2\WQM_Lib\Util,
    Barn2\WQM_Lib\Admin\Notices;
/**
 * The main plugin class. Responsible for setting up to core plugin services.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Registerable, Translatable, Service_Provider, Licensed_Plugin {

    use Service_Container;

    const NAME    = 'WooCommerce Quantity Manager';
    const ITEM_ID = 128233;

    /**
     * Constructs and initalizes the main plugin class.
     *
     * @param string $file The path to the main plugin file.
     * @param string $version The current plugin version.
     */
    public function __construct( $file = null, $version = '1.0' ) {
        parent::__construct( [
            'name'               => self::NAME,
            'item_id'            => self::ITEM_ID,
            'version'            => $version,
            'file'               => $file,
            'is_woocommerce'     => true,
            'settings_path'      => 'admin.php?page=wc-settings&tab=products&section=quantity-manager',
            'documentation_path' => 'kb-categories/quantity-manager-kb'
        ] );
    }

    /**
     * Registers the plugin with WordPress.
     */
    public function register() {
        parent::register();

        $plugin_setup = new Admin\Plugin_Setup( $this->get_file(), $this );
        $plugin_setup->register();

        add_action( 'plugins_loaded', [ $this, 'maybe_load_plugin' ] );
    }

    public function maybe_load_plugin() {
        // Don't load anything if WooCommerce not active.
        if ( ! Util::is_woocommerce_active() ) {
            $this->add_missing_woocommerce_notice();
            return;
        }

        add_action( 'init', [ $this, 'load_textdomain' ], 5 );
        add_action( 'init', [ $this, 'register_services' ] );
    }

    public function get_services() {
        $services = [
            'admin' => new Admin\Admin( $this ),
        ];

        if ( $this->has_valid_license() ) {
            $services = array_merge( $services, [
                'frontend_scripts'                  => new Frontend_Scripts( $this ),
                'handlers/quantity_input'           => new Handlers\Quantity_Input(),
                'handlers/add_to_cart'              => new Handlers\Add_To_Cart(),
                'handlers/cart'                     => new Handlers\Cart(),
                'integration/product_table'         => new Integration\Product_Table(),
                'integration/quick_view_pro'        => new Integration\Quick_View_Pro(),
                'integration/restaurant_ordering'   => new Integration\Restaurant_Ordering(),
            ] );
        }

        return $services;
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'woocommerce-quantity-manager', false, $this->get_slug() . '/languages' );
    }

    private function add_missing_woocommerce_notice() {
        if ( Util::is_admin() ) {
            $admin_notice = new Notices();
            $admin_notice->add(
                'wqm_woocommerce_missing',
                '',
                sprintf(
                    /* translators: %1$s: open link tag, %2$s: close link tag, %3$s: the plugin name */
                    __( 'Please %1$sinstall WooCommerce%2$s in order to use the %3$s extension.', 'woocommerce-quantity-manager' ),
                    Util::format_link_open( 'https://woocommerce.com/', true ),
                    '</a>',
                    $this->get_name() ),
                array(
                    'type'       => 'error',
                    'capability' => 'install_plugins'
            ) );
            $admin_notice->boot();
        }
    }

}
