<?php
/**
 * License settings class.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPIP_Premium_Settings.
 */
class BEWPIP_License_Settings extends BEWPI_Abstract_Settings {

	/**
	 * Endpoint My Account page.
	 */
	const MY_ACCOUNT_ENDPOINT = 'https://wcpdfinvoices.com/my-account/';

	/**
	 * BEWPIP_Premium_Settings constructor.
	 */
	public function __construct() {
		$this->id           = 'license';
		$this->settings_key = 'bewpi_license_settings';
		$this->settings_tab = __( 'License', 'woocommerce-pdf-invoices' );
		$this->fields       = $this->get_fields();
		$this->sections     = $this->get_sections();
		$this->defaults     = $this->get_defaults();

		if ( false === BEWPIP_License::is_activated() ) {
			$this->set_submit_button_text( __( 'Activate License', 'woocommerce-pdf-invoices' ) );
		} else {
			$this->set_submit_button_text( __( 'Deactivate License', 'woocommerce-pdf-invoices' ) );
		}

		parent::__construct();
	}

	/**
	 * Initialize hooks.
	 */
	public static function init_hooks() {
		if ( is_admin() ) {
			self::admin_init_hooks();
		}
	}

	/**
	 * Admin init hooks.
	 */
	public static function admin_init_hooks() {
		add_filter( 'wpi_setting_tabs', array( __CLASS__, 'add_setting_tab' ) );
	}

	/**
	 * Add setting to WooCommerce PDF Invoices' settings.
	 *
	 * @param array $settings This class.
	 *
	 * @return array $settings.
	 */
	public static function add_setting_tab( $settings ) {
		$settings['license'] = array(
			'class' => get_class(),
			'label' => __( 'License', 'woocommerce-pdf-invoices' ),
		);

		return $settings;
	}

	/**
	 * Adds all the different settings sections.
	 */
	private function get_sections() {
		$sections = array(
			'license' => array(
				'title'       => __( 'License Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'An active license will give you the ability to always receive the latest updates with the latest features and premium support.', 'woocommerce-pdf-invoices' ),
			),
		);

		return $sections;
	}

	/**
	 * The settings array.
	 *
	 * @return array
	 */
	private function get_fields() {
		$settings = array(
			array(
				'id'       => 'bewpi-license-email',
				'name'     => 'bewpi_license_email',
				'title'    => __( 'Activation email', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'license',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Enter your activation email from %s to receive updates and premium support.', 'woocommerce-pdf-invoices' ), '<a href="' . self::MY_ACCOUNT_ENDPOINT . '" target="_blank">wcpdfinvoices.com</a>' ),
				'default'  => (string) WPI()->get_option( 'premium', 'license_email' ),
			),
			array(
				'id'       => 'bewpi-license-key',
				'name'     => 'bewpi_license_key',
				'title'    => __( 'License', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'license_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'license',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Enter your license key from %s to receive updates and premium support.', 'woocommerce-pdf-invoices' ), '<a href="https://wcpdfinvoices.com/my-account/" target="_blank">wcpdfinvoices.com</a>' ),
				'default'  => (string) WPI()->get_option( 'premium', 'license_key' ),
			),
		);

		return $settings;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $posted settings.
	 *
	 * @return mixed
	 */
	public function sanitize( $posted ) {
		if ( ! isset( $posted['bewpi_license_key'] ) || empty( $posted['bewpi_license_key'] ) ) {
			$error          = new stdClass();
			$error->message = __( 'License key cannot be empty.', 'woocommerce-pdf-invoices' );
			$error->type    = 'error';
			$this->add_error( $error );
		} else {
			$posted['bewpi_license_key'] = sanitize_text_field( $posted['bewpi_license_key'] );
		}

		if ( ! isset( $posted['bewpi_license_email'] ) || empty( $posted['bewpi_license_email'] ) ) {
			$error          = new stdClass();
			$error->message = __( 'Activation email cannot be empty.', 'woocommerce-pdf-invoices' );
			$error->type    = 'error';
			$this->add_error( $error );
		} else {
			$posted['bewpi_license_email'] = sanitize_text_field( $posted['bewpi_license_email'] );
		}

		if ( count( $this->get_errors() ) > 0 ) {
			BEWPIP_License::set_activated( false );

			return $posted;
		}

		// Activate or deactivate.
		if ( false === BEWPIP_License::is_activated() ) {
			$response = json_decode( BEWPIP_License::activate( array(
				'email'          => $posted['bewpi_license_email'],
				'license_key'    => $posted['bewpi_license_key'],
				'api_product_id' => 'woocommerce-pdf-invoices-premium',
			) ), true );
		} else {
			$response = json_decode( BEWPIP_License::deactivate( array(
				'email'          => $posted['bewpi_license_email'],
				'license_key'    => $posted['bewpi_license_key'],
				'api_product_id' => 'woocommerce-pdf-invoices-premium',
			) ), true );
		}

		if ( true === $response['success'] && true === $response['activated'] ) {
			BEWPIP_License::set_activated( true );
		} else {
			BEWPIP_License::set_activated( false );
		}

		return $posted;
	}

	/**
	 * License option callback.
	 *
	 * @param array $args Field arguments.
	 */
	public function license_callback( $args ) {
		$license_key = WPI()->get_option( 'license', 'license_key' );

		printf( '<input id="%1$s" title="%2$s" name="%3$s" type="text" value="%4$s" />',
			esc_attr( $args['id'] ),
			esc_attr__( 'License', 'woocommerce-pdf-invoices' ),
			esc_attr( $args['page'] . '[' . $args['name'] . ']' ),
			esc_attr( $license_key )
		);

		if ( false === BEWPIP_License::is_activated() ) {
			printf( '<div class="license-btn inactive">%1$s</div>', esc_attr__( 'Inactive', 'woocommerce-pdf-invoices' ) );
		} else {
			printf( '<div class="license-btn active">%1$s</div>', esc_attr__( 'Active', 'woocommerce-pdf-invoices' ) );
		}

		echo '<div class="bewpi-notes">' . $args['desc'] . '</div>';
	}
}
