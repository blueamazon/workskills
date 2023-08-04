<?php
/**
 * Final WooCommerce PDF Invoices Premium Class.
 *
 * Processes several hooks and filter callbacks.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Class
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BE_WooCommerce_PDF_Invoices_Premium.
 */
final class BE_WooCommerce_PDF_Invoices_Premium {

	/**
	 * Main BE_WooCommerce_PDF_Invoices_Premium instance.
	 *
	 * @var BE_WooCommerce_PDF_Invoices_Premium
	 * @since 1.6.0
	 */
	protected static $_instance = null;

	/**
	 * Main BE_WooCommerce_PDF_Invoices_Premium instance.
	 *
	 * @return BE_WooCommerce_PDF_Invoices_Premium
	 * @since 1.6.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * BE_WooCommerce_PDF_Invoices_Premium constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize.
	 */
	public function init_hooks() {
		if ( is_admin() ) {
			$this->admin_init_hooks();
		}

		$this->load_integrations();

		BEWPIP_Premium_Settings::init_hooks();
		BEWPIP_License_Settings::init_hooks();
		BEWPIP_Font::init_hooks();
		BEWPIP_Invoice_Global::init_hooks();
		BEWPIP_Invoice::init_hooks();
		BEWPIP_Credit_Note::init_hooks();
		BEWPIP_Invoice_Reminder::init_hooks();
	}

	/**
	 * Admin initialization hooks.
	 */
	public function admin_init_hooks() {
		add_filter( 'plugin_action_links_woocommerce-pdf-invoices-premium/bootstrap.php', array(
			$this,
			'add_plugin_action_links',
		) );

		BEWPIP_Bulk_Print::init_hooks();
		BEWPIP_Bulk_Generate::init_hooks();
		BEWPIP_Bulk_Export::init_hooks();
		//BEWPIP_Invoice_UBL::init_hooks();
	}

	/**
	 * Load integrations.
	 */
	private function load_integrations() {
		if ( class_exists( 'SitePress' ) ) {
			BEWPIP_WPML_Integration::init_hooks();
		}

		if ( class_exists( 'Polylang' ) ) {
			BEWPIP_Polylang_Integration::init_hooks();
		}
	}

	/**
	 * Add plugin links.
	 *
	 * @param array $links Plugin page links.
	 *
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$links = array_merge( array(
			sprintf( '<a href="%1$s">%2$s</a>', add_query_arg( array( 'page' => WPI()->get_plugin_slug() ), admin_url( 'admin.php' ) ), __( 'Settings', 'woocommerce-pdf-invoices' ) ),
			sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://wcpdfinvoices.com/contact', __( 'Support', 'woocommerce-pdf-invoices' ) ),
		), $links );

		return $links;
	}

	/**
	 * Check if email is enabled.
	 *
	 * @param string $email Email ID.
	 *
	 * @return bool
	 */
	public function is_credit_note_email_enabled( $email ) {
		return in_array( $email, (array) WPI()->get_option( 'premium', 'credit_note_email_types' ), true );
	}

	/**
	 * Change invoice date.
	 *
	 * @param string                 $formatted_date formatted current mysql date.
	 * @param BEWPI_Abstract_Invoice $invoice        invoice object.
	 *
	 * @return string
	 */
	public function get_date_by_type( $formatted_date, $invoice ) {
		$date_type = WPI()->get_option( 'premium', 'date_type' );
		$date      = null;

		switch ( $date_type ) {
			case 'order_paid':
				$date = $invoice->order->get_date_paid();
				break;
			case 'order_created':
				$date = $invoice->order->get_date_created();
				break;
			default:
				return $formatted_date;
		}

		if ( null !== $date ) {
			$formatted_date = $date->format( 'Y-m-d H:i:s' );
		}

		return $formatted_date;
	}
}
