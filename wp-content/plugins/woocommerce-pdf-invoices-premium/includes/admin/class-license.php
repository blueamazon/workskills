<?php
/**
 * License class.
 *
 * Self host license class.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Class
 * @version     0.1
 */

/**
 * Class BEWPIP_License.
 */
class BEWPIP_License {

	/**
	 * Endpoint to activate or deactivate license.
	 */
	const ENDPOINT = 'https://wcpdfinvoices.com/?wc-api=license_wp_api_activation';

	/**
	 * License activated constant.
	 */
	const OPTION_ACTIVATED = 'bewpi_license_activated';

	/**
	 * Activation email.
	 *
	 * @var string
	 */
	private $email;

	/**
	 * License key.
	 *
	 * @var string
	 */
	private $key;

	/**
	 * BEWPIP_License constructor.
	 *
	 * @param string $email License activation email.
	 * @param string $key   License key.
	 */
	public function __construct( $email, $key ) {
		$this->email = $email;
		$this->key   = $key;
	}

	/**
	 * Set license as active.
	 *
	 * @param bool $activated Activated license.
	 */
	public static function set_activated( $activated ) {
		if ( true === $activated ) {
			update_option( self::OPTION_ACTIVATED, true );
		} else {
			delete_option( self::OPTION_ACTIVATED );
		}
	}

	/**
	 * Check if license is activated
	 *
	 * @return bool
	 */
	public static function is_activated() {
		return get_option( self::OPTION_ACTIVATED, false );
	}

	/**
	 * Attempt to activate a plugin license.
	 *
	 * @param array $args params.
	 *
	 * @return bool|string
	 */
	public static function activate( $args ) {
		$defaults = array(
			'request'   => 'activate',
			'instance'  => site_url(),
			'sslverify' => false,
		);

		$args = wp_parse_args( $defaults, $args );

		return self::do_request( $args );
	}

	/**
	 * Attempt t deactivate a license.
	 *
	 * @param array $args params.
	 *
	 * @return bool|string
	 */
	public static function deactivate( $args ) {
		$defaults = array(
			'request'   => 'deactivate',
			'instance'  => site_url(),
			'sslverify' => false,
		);

		$args = wp_parse_args( $defaults, $args );

		return self::do_request( $args );
	}

	/**
	 * Do request.
	 *
	 * @param array $args params.
	 *
	 * @return bool|string
	 */
	private static function do_request( $args ) {
		$request_url = add_query_arg( $args, self::ENDPOINT );
		$request     = wp_remote_get( $request_url );

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $request );
	}
}
