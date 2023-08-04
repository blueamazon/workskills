<?php
/**
 * Updater.
 *
 * Self host updater class.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Class
 * @version     0.1
 */

/**
 * Class BEWPIP_Updater.
 */
class BEWPIP_Updater {

	/**
	 * Endpoint.
	 */
	const ENDPOINT = 'https://wcpdfinvoices.com';

	/**
	 * Plugin file.
	 *
	 * @var string.
	 */
	private $plugin_file;

	/**
	 * Plugin name.
	 *
	 * @var string.
	 */
	private $plugin_name;

	/**
	 * Plugin slug.
	 *
	 * @var string.
	 */
	private $plugin_slug;

	/**
	 * License key.
	 *
	 * @var string.
	 */
	private $license_key;

	/**
	 * License email.
	 *
	 * @var string.
	 */
	private $license_email;

	/**
	 * Errors.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * BEWPIP_Updater constructor.
	 *
	 * @param string $plugin_file plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file   = $plugin_file;
		$this->plugin_name   = plugin_basename( $this->plugin_file );
		$this->plugin_slug   = 'woocommerce-pdf-invoices-premium';
		$this->license_key   = WPI()->get_option( 'license', 'license_key' );
		$this->license_email = WPI()->get_option( 'license', 'license_email' );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Admin init.
	 */
	public function admin_init() {
		add_action( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ), 21, 1 );
		add_action( 'admin_notices', array( $this, 'error_notices' ) );
	}

	/**
	 * Output errors
	 */
	public function error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				?>
				<div class="error">
					<p><?php echo wp_kses_post( $error ); ?></p>
				</div>
				<?php
				unset( $this->errors[ $key ] );
			}
		}
	}

	/**
	 * Sends and receives data to and from the server API.
	 *
	 * @param array $args request arguments.
	 *
	 * @return object $response
	 */
	public function do_license_request( $args ) {
		$request_url = add_query_arg( $args, 'https://www.wcpdfinvoices.com/?wc-api=license_wp_api_update' );

		$request = wp_remote_get( $request_url );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			return null;
		}
		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
		if ( is_object( $response ) ) {
			return $response;
		} else {
			return null;
		}
	}

	/**
	 * Add an error message
	 *
	 * @param string $message Your error message.
	 * @param string $type    Type of error message.
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Handle errors from the API
	 *
	 * @param array $errors errors.
	 */
	public function handle_errors( $errors ) {
		foreach ( $errors as $error_key => $error ) {
			// add error to WP.
			$this->add_error( $error, $error_key );
			if ( 'no_activation' === $error_key ) {
				// Deactivate license on server.
				BEWPIP_License::deactivate( array(
					'api_product_id' => $this->plugin_slug,
					'license_key'    => $this->license_key,
				) );

				// Set local activation status to false.
				BEWPIP_License::set_activated( false );

				// Renew notice.
				$args          = array(
					'renew_license'    => $this->license_key,
					'activation_email' => $this->license_email,
				);
				$renew_url     = add_query_arg( $args, self::ENDPOINT );
				$renew_message = sprintf( __( 'Whoops, your license for %1$s has expired. To keep receiving updates and premium support, we offer you a 30%% discount when you <a href="%2$s" target="_blank">renew your license</a>.', 'woocommerce-pdf-invoices' ), '<strong>WooCommerce PDF Invoices Premium</strong>', esc_url( $renew_url ) );

				$this->add_error( $renew_message, 'error' );
			}
		}
	}

	/**
	 * Check for updates.
	 *
	 * @param object $transient transient object.
	 *
	 * @return object $transient.
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $this->license_key ) || empty( $this->license_email ) ) {
			return $transient;
		}

		// Only check for data if license is activated.
		if ( false === BEWPIP_License::is_activated() ) {
			return $transient;
		}

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$current_version = isset( $transient->checked[ $this->plugin_name ] ) ? $transient->checked[ $this->plugin_name ] : '';
		// only continue when the plugin version is set.
		if ( '' === $current_version ) {
			return $transient;
		}

		$args = array(
			'request'        => 'pluginupdatecheck',
			'plugin_name'    => $this->plugin_slug,
			'version'        => $current_version,
			'api_product_id' => $this->plugin_slug,
			'license_key'    => $this->license_key,
			'email'          => $this->license_email,
			'instance'       => site_url(),
			'sslverify'      => false,
		);

		// Check for a plugin update.
		$response = $this->do_license_request( $args );
		if ( isset( $response->errors ) ) {
			$this->handle_errors( $response->errors );
		}

		// Set version variables.
		if ( isset( $response ) && is_object( $response ) && false !== $response ) {
			// New plugin version from the API.
			$new_version = (string) $response->new_version;
		}

		// If there is a new version, modify the transient to reflect an update is available.
		if ( isset( $new_version ) ) {
			if ( false !== $response && version_compare( $new_version, $current_version, '>' ) ) {
				$transient->response[ $this->plugin_name ] = $response;
			}
		}

		return $transient;
	}
}
