<?php
/**
 * WPML Integration Class that temporary switches the current language to the order language.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Classes
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BEWPIP_WPML_Integration' ) ) {

	/**
	 * Class BEWPIP_WPML_Integration.
	 */
	class BEWPIP_WPML_Integration {

		/**
		 * Order.
		 *
		 * @var WC_Order WooCommerce Order.
		 */
		private static $order;

		/**
		 * Order language code.
		 *
		 * @var string.
		 */
		private static $order_language;

		/**
		 * Current language code.
		 *
		 * @var string.
		 */
		private static $language;

		/**
		 * Current admin language.
		 *
		 * @var string.
		 */
		private static $admin_language;

		/**
		 * Order locale code.
		 *
		 * @var string.
		 */
		private static $locale;

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

			add_filter( 'woocommerce_gateway_title', array( __CLASS__, 'translate_gateway_title' ), 10, 2 );
		}

		/**
		 * Translate admin texts.
		 *
		 * @param string $value Default translation.
		 *
		 * @return string The translated string.
		 */
		public static function translate_admin_texts( $value ) {
			// When empty default language is selected.
			if ( empty( self::$order_language ) ) {
				return $value;
			}

			$name = '[bewpi_template_settings]' . current_filter();
			$translated_value = apply_filters( 'wpml_translate_single_string', $value, 'admin_texts_bewpi_template_settings', $name, self::$order_language );

			return $translated_value;
		}

		/**
		 * Translate payment gateway.
		 *
		 * @param string $title Gateway title.
		 * @param string $gateway_id Gateway ID.
		 *
		 * @return string
		 */
		public static function translate_gateway_title( $title, $gateway_id ) {
			// When empty default language is selected.
			if ( empty( self::$order_language ) ) {
				return $title;
			}

			$translated_title = apply_filters( 'wpml_translate_single_string', $title, 'woocommerce', $gateway_id . '_gateway_title', self::$order_language );

			return $translated_title;
		}

		/**
		 * Change locale to order language.
		 *
		 * @param string $locale Current locale.
		 *
		 * @return string Order locale.
		 */
		public static function locale( $locale ) {
			return self::$locale;
		}

		/**
		 * Switch language before PDF invoice generation.
		 *
		 * @param int $order_id WC_Order ID.
		 */
		public static function switch_language( $order_id ) {
			global $sitepress;

			self::$order = wc_get_order( $order_id );

			self::$order_language = get_post_meta( $order_id, 'wpml_language', true );
			if ( empty( self::$order_language ) ) {
				return;
			}

			self::$language = $sitepress->get_current_language();
			self::$admin_language = $sitepress->get_admin_language();

			// Switch language to order language.
			$sitepress->switch_lang( self::$order_language, true );

			// Switch admin language to order language.
			$sitepress->set_admin_language( self::$order_language );
			$sitepress->set_admin_language_cookie( self::$order_language );

			// Get order language from active languages.
			self::$locale = $sitepress->get_locale( self::$order_language );
			add_filter( 'locale', array( __CLASS__, 'locale' ) );
			add_filter( 'plugin_locale', array( __CLASS__, 'locale' ) );

			unload_textdomain( 'default' );
			unload_textdomain( 'woocommerce' );
			unload_textdomain( 'woocommerce-pdf-invoices' );

			load_default_textdomain();
			WC()->load_plugin_textdomain();
			WPI()->load_plugin_textdomain();

			global $wp_locale;
			$wp_locale = new WP_Locale();

			self::init_translations();
		}

		/**
		 * Restore language after PDF invoice generation.
		 */
		public static function reset_language() {
			global $sitepress;

			remove_filter( 'locale', array( __CLASS__, 'locale' ) );
			remove_filter( 'plugin_locale', array( __CLASS__, 'locale' ) );

			unload_textdomain( 'default' );
			unload_textdomain( 'woocommerce' );
			unload_textdomain( 'woocommerce-pdf-invoices' );

			$sitepress->switch_lang( self::$language, true );
			$sitepress->set_admin_language( self::$admin_language );
			$sitepress->reset_admin_language_cookie();
		}
	}
}
