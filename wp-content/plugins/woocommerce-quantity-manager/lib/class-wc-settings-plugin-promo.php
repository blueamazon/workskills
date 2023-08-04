<?php

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Util;

if ( ! class_exists( 'WC_Barn2_Plugin_Promo' ) ) {

	/**
	 * Provides functions to add the plugin promo to the plugin settings page in the WordPress admin.
	 *
	 * @package   Barn2\barn2-lib
	 * @author    Barn2 Plugins <support@barn2.com>
	 * @license   GPL-3.0
	 * @copyright Barn2 Media Ltd
	 * @version   1.3.5
	 * @deprecated Replaced by Barn2\WQM_Lib\WooCommerce\Admin\Plugin_Promo;
	 */
	class WC_Barn2_Plugin_Promo implements Registerable {

		private $plugin_id;
		private $plugin_file;
		private $section_slug;

		public function __construct( $plugin_id, $plugin_file, $section_slug ) {
			$this->plugin_id    = $plugin_id;
			$this->plugin_file  = $plugin_file;
			$this->section_slug = $section_slug;
		}

		public function register() {
			add_filter( 'woocommerce_get_settings_' . $this->section_slug, [ $this, 'add_plugin_promo_field' ], 11 );
			add_filter( 'woocommerce_get_settings_products', [ $this, 'add_plugin_promo_field' ], 11, 2 );
			add_action( 'admin_enqueue_scripts', [ $this, 'load_styles' ] );
		}

		public function should_add_promo() {
			global $current_tab, $current_section;

			// The current tab is 'Products' and the current section is this plugin
			if ( 'products' === $current_tab && $this->section_slug === $current_section ) {
				return true;
			}

			// The current tab is this plugin
			if ( $this->section_slug === $current_tab ) {
				return true;
			}

			return false;

		}

		public function add_plugin_promo_field( $settings, $current_section = '' ) {
			global $current_tab;

			// Return the input settings if the section or the tab are not related to this plugin
			if ( ! $this->should_add_promo() ) {
				return $settings;
			}

			$promo_fields = array_filter(
				$settings,
				function( $field ) {
					return isset( $field['id'] ) && 'plugin_promo' === $field['id'];
				}
			);

			// Return the input settings if a 'plugin_promo' field
			// is already present for this plugin
			if ( ! empty( $promo_fields ) ) {
				return $settings;
			}

			$settings_end = array_filter(
				$settings,
				function( $setting ) {
					return isset( $setting['type'] ) && $setting['type'] === 'settings_end';
				}
			);

			if ( empty( $settings_end ) ) {
				return $settings;
			}

			// find the array index of the last `settings_end` field
			$keys  = array_keys( $settings_end );
			$index = end( $keys );

			// extract the array before that field...
			$head  = array_slice( $settings, 0, $index );
			// ...and the array after that field
			$tail  = array_slice( $settings, $index + 1 );

			$settings_end              = end( $settings_end );
			$settings_end['has_promo'] = true;

			// join the arrays again, adding the Barn2 Promo
			// right before the Settings End
			return array_merge(
				$head,
				[
					[
						'type'    => 'plugin_promo',
						'id'      => 'plugin_promo',
						'content' => $this->render_promo(),
					],
					$settings_end,
				],
				$tail
			);
		}

		public function get_promo_content() {
			if ( ( $promo_content = get_transient( 'barn2_plugin_promo_' . $this->plugin_id ) ) === false ) {
				$promo_response = wp_remote_get( Util::barn2_url( '/wp-json/barn2/v2/pluginpromo/' . $this->plugin_id . '?_=' . date( 'mdY' ) ) );

				if ( wp_remote_retrieve_response_code( $promo_response ) != 200 ) {
					return;
				}

				$promo_content = json_decode( wp_remote_retrieve_body( $promo_response ), true );

				set_transient( 'barn2_plugin_promo_' . $this->plugin_id, $promo_content, DAY_IN_SECONDS );
			}

			if ( empty( $promo_content ) || is_array( $promo_content ) ) {
				return;
			}

			return $promo_content;
		}

		public function load_styles() {
			if ( $this->should_add_promo() ) {
				wp_enqueue_style( 'barn2-plugins-promo', plugins_url( 'lib/assets/css/admin/plugin-promo.min.css', $this->plugin_file ) );
			}
		}

		public function render_promo() {
			$promo         = '';
			$promo_content = $this->get_promo_content();

			if ( ! empty( $promo_content ) ) {
				$GLOBALS['hide_save_button'] = true;

				ob_start();
				?>

					<p class="submit">
						<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
					</p>
				</div>
				<?php // end of 'barn2-settings-inner' container ?>

				<div id="barn2_plugins_promo" class="barn2-plugins-promo">
					<div id="barn2_plugin_promo_content">
						<?php echo $promo_content; ?>
					</div>
				</div>

				<?php
				$promo = ob_get_clean();
			}

			return $promo;
		}

	}

}
