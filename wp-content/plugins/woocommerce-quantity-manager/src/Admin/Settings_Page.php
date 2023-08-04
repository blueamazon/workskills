<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Admin;

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Service,
	Barn2\WQM_Lib\Conditional,
	Barn2\WQM_Lib\Util as Lib_Util,
	Barn2\WQM_Lib\Plugin\Licensed_Plugin,
	Barn2\WQM_Lib\WooCommerce\Admin\Custom_Settings_Fields,
	Barn2\WQM_Lib\WooCommerce\Admin\Plugin_Promo,
	Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util,
	Barn2\Plugin\WC_Quantity_Manager\Util\Util,
	WC_Admin_Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the WooCommerce settings page.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_Page implements Registerable, Conditional, Service {

	private $id;
	private $prefix;
	private $label;
	private $plugin;
	private $license;

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->id      = 'quantity-manager';
		$this->prefix  = 'quantity_manager_';
		$this->label   = __( 'Quantity manager', 'woocommerce-quantity-manager' );
		$this->plugin  = $plugin;
		$this->license = $plugin->get_license_setting();

		// Add plugin promo.
		$plugin_promo = new Plugin_Promo( $this->plugin->get_id(), $this->plugin->get_file(), $this->id );
		$plugin_promo->register();
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_required() {
		return Lib_Util::is_admin();
	}

	/**
	 * Register hooks and filters
	 */
	public function register() {
		$settings_fields = new Custom_Settings_Fields();
		$settings_fields->register();

		// Min / Max custom field
		add_action( 'woocommerce_admin_field_min_max_number', [ $this, 'min_max_number_field' ] );
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . Field_Util::get_data_key( 'quantity_rules', 'global' ), [ Field_Util::class, 'sanitize_min_max_quantity' ] );
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . Field_Util::get_data_key( 'value_rules', 'global' ), [ Field_Util::class, 'sanitize_min_max_value' ] );

		// Register settings and section
		add_filter( 'woocommerce_get_sections_products', [ $this, 'register_settings_section' ], 10, 1 );
		add_filter( 'woocommerce_get_settings_products', [ $this, 'get_settings' ], 10, 2 );

		// Sanitize and save license data
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . $this->license->get_license_setting_name(), [ $this->license, 'save_license_key' ] );
	}

	/**
	 * Outputs a Min / Max field formatted for WC Settings
	 *
	 * @param array $value
	 */
	public function min_max_number_field( $value ) {
		$option_value      = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		$field_description = WC_Admin_Settings::get_field_description( $value );

		// Redo the description as WC runs wp_kes_post() on it which messes up any inline CSS
		if ( ! empty( $value['desc'] ) ) {
			$field_description['description'] = '<span class="description">' . $value['desc'] . '</span>';
		}

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>">
					<?php echo esc_html( $value['title'] ); ?>
					<?php echo $field_description['tooltip_html']; ?>
				</label>

			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?> min-max-field">
				<?php Field_Util::min_max_input_field( $option_value, $value ); ?>
				<?php echo $field_description['description']; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Register the settings section.
	 *
	 * @param array $sections
	 * @return array $sections
	 */
	public function register_settings_section( $sections ) {
		$sections[ $this->id ] = $this->label;

		return $sections;
	}

	/**
	 * Register section settings
	 *
	 * @param array $settings
	 * @param string $current_section
	 * @return array $settings
	 */
	public function get_settings( $settings, $current_section ) {

		if ( $this->id !== $current_section ) {
			return $settings;
		}

		$plugin_settings = [];

		$plugin_settings[] = [
			'id'    => $this->prefix . 'settings_start',
			'type'  => 'settings_start',
			'class' => 'quantity-manager-settings barn2-plugins-settings'
		];

		$plugin_settings[] = [
			'title' => __( 'Quantity Manager', 'woocommerce-quantity-manager' ),
			'type'  => 'title',
			'desc'  => '<p>' . __( 'The following options control the WooCommerce Quantity Manager extension.', 'woocommerce-quantity-manager' ) . '</p>'
			. '<p>'
			. Lib_Util::format_link( $this->plugin->get_documentation_url(), __( 'Documentation', 'woocommerce-quantity-manager' ) )
			. ' | '
			. Lib_Util::format_link( $this->plugin->get_support_url(), __( 'Support', 'woocommerce-quantity-manager' ) )
			. '</p>',
			'id'    => $this->prefix . 'options'
		];

		$plugin_settings[] = $this->license->get_license_key_setting();
		$plugin_settings[] = $this->license->get_license_override_setting();

		$plugin_settings[] = [
			'title'             => __( 'Order quantity rules', 'woocommerce-quantity-manager' ),
			'type'              => 'min_max_number',
			'id'                => Field_Util::get_data_key( 'quantity_rules', 'global' ),
			'custom_attributes' => [
				'min'  => 1,
				'step' => 1,
			],
			'desc_tip'          => __( 'Enter a whole number to set a minimum and/or maximum number of items that can be purchased in the entire cart. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
		];

		$plugin_settings[] = [
			/* translators: %s: WooCommerce Currency Symbol */
			'title'    => sprintf( __( 'Order value rules (%s)', 'woocommerce-quantity-manager' ), get_woocommerce_currency_symbol() ),
			'type'     => 'min_max_number',
			'currency' => true,
			'id'       => Field_Util::get_data_key( 'value_rules', 'global' ),
			'desc_tip' => __( 'Enter a price without the currency symbol (e.g. 10 or 15.99) to set a minimum and/or maximum value for the entire cart. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
		];

		$plugin_settings[] = [
			'name'      => __( 'Global default quantity', 'woocommerce-quantity-manager' ),
			'id'        => Field_Util::get_data_key( 'default_quantity', 'global' ),
			'type'      => 'number',
			'data_type' => 'stock',
			'default'   => 1,
			'desc_tip'  => __( 'Choose a default quantity for all your products. You can override this for individual categories/products.', 'woocommerce-quantity-manager' ),
			'css'       => 'width: 100px;',
		];

		$plugin_settings[] = [
			'title'             => __( 'Quantity step values', 'woocommerce-quantity-manager' ),
			'type'              => 'number',
			'id'                => Field_Util::get_data_key( 'quantity_step', 'global' ),
			'default'           => 1,
			'custom_attributes' => [
				'min' => 1,
			],
			'desc_tip'          => Field_Util::shared_quantity_step_calulation() ?
				__( 'Enter a whole number to force customers to purchase in specific quantity increments. For example, if you enter 5 then this will force quantity groupings of 5, 10, 15, 20, etc. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' )
				: __( 'Enter a whole number to force customers to purchase in specific quantity increments. For example, if you enter 5 then each product can only be bought in a quantity of 5, 10, 15, 20, etc. This can be overridden for specific categories, products or variations.', 'woocommerce-quantity-manager' ),
			'css'               => 'width: 100px;',
		];

		$plugin_settings[] = [
			'title'    => __( 'Step value calculation', 'woocommerce-quantity-manager' ),
			'type'     => 'select',
			'id'       => $this->prefix . 'step_value_calc',
			'default'  => 'individual',
			'options'  => [
				'individual' => __( 'Individual products/variations', 'woocommerce-quantity-manager' ),
				'shared'     => __( 'Share across multiple products', 'woocommerce-quantity-manager' ),
			],
			'desc_tip' => __( 'Choose whether to enforce the quantity step value individually for each product/variation, or to share it so that customers can combine multiple products to meet the required step value.', 'woocommerce-quantity-manager' ),
		];

		$plugin_settings[] = [
			'title'             => __( 'User roles', 'woocommerce-quantity-manager' ),
			'type'              => 'multiselect',
			'custom_attributes' => [
				'aria-label'       => __( 'User roles', 'woocommerce-quantity-manager' ),
				'data-placeholder' => __( 'Select roles&hellip;', 'woocommerce-quantity-manager' )
			],
			'id'                => $this->prefix . 'roles',
			'default'           => array_keys( Util::get_roles() ),
			'options'           => Util::get_roles(),
			'desc_tip'          => __( 'Select which roles the quantity rules will apply to. This will affect all your quantity rules, including those for individual categories, products and variations.', 'woocommerce-quantity-manager' ),
		];

		$plugin_settings[] = [
			'type' => 'sectionend',
			'id'   => $this->prefix . 'options',
		];

		$plugin_settings[] = [
			'id'   => $this->prefix . 'settings_end',
			'type' => 'settings_end'
		];

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $plugin_settings );
	}
}
