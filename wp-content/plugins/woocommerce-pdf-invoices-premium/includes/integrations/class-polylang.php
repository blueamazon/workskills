<?php
/**
 * Polylang Integration Class for translation compatibility.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BEWPIP_Polylang_Integration' ) ) {

	/**
	 * Class BEWPIP_Polylang_Integration.
	 */
	class BEWPIP_Polylang_Integration {
		/**
		 * Post language.
		 *
		 * @var PLL_Language Polylang language.
		 */
		private static $language;

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			add_action( 'bewpi_before_invoice_content', array( __CLASS__, 'switch_language' ), 10, 1 );
			add_action( 'bewpi_after_invoice_content', array( __CLASS__, 'reset_language' ), 10, 1 );
		}

		/**
		 * Initialize String Translations.
		 */
		private static function init_translations() {
			$template_options = get_option( 'bewpi_template_settings' );

			foreach ( $template_options as $key => $name ) {
				add_filter( $key, array( __CLASS__, 'translate_admin_texts' ), 10, 1 );
			}

			// WooCommerce.
			add_filter( 'woocommerce_gateway_title', array( __CLASS__, 'translate_admin_texts' ), 10, 1 );
		}

		/**
		 * Translate String Translation.
		 *
		 * @param string $value original translation value.
		 *
		 * @return string translated string.
		 */
		public static function translate_admin_texts( $value ) {
			return pll_translate_string( $value, self::$language->locale );
		}

		/**
		 * Change locale.
		 *
		 * @param string $locale current locale.
		 *
		 * @return string locale.
		 */
		public static function locale( $locale ) {
			return self::$language->locale;
		}

		/**
		 * Switch language to post language.
		 *
		 * @param int $order_id WC_Order ID.
		 */
		public static function switch_language( $order_id ) {
			$post_locale = pll_get_post_language( $order_id, 'locale' );
			self::$language = PLL()->model->get_language( $post_locale );

			add_filter( 'locale', array( __CLASS__, 'locale' ) );
			add_filter( 'plugin_locale', array( __CLASS__, 'locale' ) );

			unload_textdomain( 'default' );
			unload_textdomain( 'woocommerce' );
			unload_textdomain( 'woocommerce-pdf-invoices' );

			load_default_textdomain();
			WC()->load_plugin_textdomain();
			WPI()->load_plugin_textdomain();

			unset( $GLOBALS['wp_locale'] );
			$GLOBALS['text_direction'] = self::$language->is_rtl ? 'rtl' : 'ltr';
			$GLOBALS['wp_locale'] = new WP_Locale();

			// Since WP 4.7, don't use PLL()->load_strings_translations() which rely on get_locale().
			$mo = new PLL_MO();
			$mo->import_from_db( self::$language );
			$GLOBALS['l10n']['pll_string'] = &$mo;

			self::init_translations();
		}

		/**
		 * Reset textdomains.
		 *
		 * @param int $order_id WC_Order ID.
		 */
		public static function reset_language( $order_id ) {
			remove_filter( 'locale', array( __CLASS__, 'locale' ) );
			remove_filter( 'plugin_locale', array( __CLASS__, 'locale' ) );

			unload_textdomain( 'default' );
			unload_textdomain( 'woocommerce' );
			unload_textdomain( 'woocommerce-pdf-invoices' );
		}
	}
}
