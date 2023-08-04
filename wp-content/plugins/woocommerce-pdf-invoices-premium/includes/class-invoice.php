<?php
/**
 * Invoice class.
 *
 * Handling invoice specific functionality.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BEWPIP_Invoice' ) ) {
	/**
	 * Class BEWPIP_Invoice.
	 */
	class BEWPIP_Invoice {

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			self::add_settings();

			add_filter( 'wpi_delete_invoice_confirm_message', array(
				__CLASS__,
				'change_delete_invoice_confirm_message',
			) );
			add_action( 'wpi_watermark_end', array( __CLASS__, 'add_watermark' ), 10, 2 );
			add_filter( 'woocommerce_email_headers', array( __CLASS__, 'add_recipients' ), 10, 2 );
			add_filter( 'bewpi_mpdf_after_write', array( __CLASS__, 'add_pdf_to_invoice' ), 10, 2 );

			// Request Invoice.
			add_action( 'woocommerce_after_order_notes', array( __CLASS__, 'add_request_invoice_checkout_field' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array(
				__CLASS__,
				'process_request_invoice_checkout_field',
			) );
			add_filter( 'bewpi_skip_invoice_generation', array( __CLASS__, 'skip_invoice_generation' ), 10, 3 );

			// Advanced Table Content.
			add_action( 'bewpi_before_invoice_content', array( __CLASS__, 'load_advanced_table_content' ), 10, 1 );

			// Display used coupons on invoice.
			add_action( 'wpi_order_item_totals_left', array( __CLASS__, 'display_used_coupons' ), 10, 2 );

			// Display additional billing fields.
			add_action( 'wpi_after_formatted_billing_address', array(
				__CLASS__,
				'display_additional_billing_fields'
			), 10, 1 );

			// Display additional billing fields.
			add_action( 'wpi_after_formatted_shipping_address', array(
				__CLASS__,
				'display_additional_shipping_fields'
			), 10, 1 );

			add_filter( 'woocommerce_order_hide_zero_taxes', function () {
				return false;
			}, 10, 1 );

			add_filter( 'wpi_invoice_date', array( __CLASS__, 'change_date_by_type' ), 10, 2 );
		}

		/**
		 * Change invoice date.
		 *
		 * @param string                 $formatted_date formatted current mysql date.
		 * @param BEWPI_Abstract_Invoice $invoice        invoice object.
		 *
		 * @return string
		 */
		public static function change_date_by_type( $formatted_date, $invoice ) {
			return WPIP()->get_date_by_type( $formatted_date, $invoice );
		}

		/**
		 * Load advanced table content on pdf generation if enabled.
		 *
		 * @param int $order_id Order id.
		 */
		public static function load_advanced_table_content( $order_id ) {
			if ( ! WPI()->templater()->has_advanced_table_content() ) {
				return;
			}

			add_action( 'wpi_order_item_meta_start', array( __CLASS__, 'display_sku_as_meta_data' ), 10, 2 );
			add_filter( 'wpi_get_invoice_columns', array( __CLASS__, 'get_columns' ), 10, 2 );
			add_filter( 'wpi_get_invoice_columns_data_row', array( __CLASS__, 'get_columns_data' ), 10, 4 );
			add_filter( 'wpi_get_invoice_total_rows', array( __CLASS__, 'get_total_rows' ), 10, 2 );
		}

		/**
		 * Change WooCommerce PDF Invoices confirm message when deleting invoice.
		 *
		 * @param string $message free version confirm message.
		 *
		 * @return string $message
		 */
		public static function change_delete_invoice_confirm_message( $message ) {
			$message = __( 'Instead consider creating a Cancelled PDF invoice by changing the order status to Cancelled.', 'woocommerce-pdf-invoices' );

			return $message;
		}

		/**
		 * Add invoice settings.
		 */
		private static function add_settings() {
			add_filter( 'wpi_template_sections', array( __CLASS__, 'add_template_sections' ) );
			add_filter( 'wpi_template_settings', array( __CLASS__, 'add_template_settings' ), 10, 2 );
		}

		/**
		 * Add Cancelled watermark for cancelled orders.
		 *
		 * @param WC_Order               $order   WC order object.
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 */
		public static function add_watermark( $order, $invoice ) {
			if ( 'cancelled' === $order->get_status( 'edit' ) ) {
				printf( '<h2 class="red">%s</h2>', esc_html__( 'Cancelled', 'woocommerce-pdf-invoices' ) );
			}
		}

		/**
		 * Add advanced table content section.
		 *
		 * @param array $sections Sections.
		 *
		 * @return array.
		 */
		public static function add_template_sections( $sections ) {
			$sections['advanced_table_content'] = array(
				'title'       => __( 'Advanced Table Content', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Enable Advanced Table Content settings to fully customize line item columns and total rows. When enabled the standard Table Content settings will be ignored. When using a custom template, make sure to update it! Micro template is not supported.', 'woocommerce-pdf-invoices' ),
			);

			return $sections;
		}

		/**
		 * Add advanced table content checkbox to enable it.
		 *
		 * @param array                   $settings          Settings fields.
		 * @param BEWPI_Template_Settings $template_settings object template settings.
		 *
		 * @return array
		 */
		public static function add_template_settings( $settings, $template_settings ) {
			$ex_tax_or_vat  = WC()->countries->ex_tax_or_vat();
			$inc_tax_or_vat = WC()->countries->inc_tax_or_vat();

			$advanced_settings = array(
				array(
					'id'       => 'bewpi-enable-advanced-table-content',
					'name'     => 'bewpi_enable_advanced_table_content',
					'title'    => '',
					'callback' => array( $template_settings, 'input_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable Advanced Table Content', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-show-sku-meta',
					'name'     => 'bewpi_show_sku_meta',
					'title'    => '',
					'callback' => array( $template_settings, 'input_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'checkbox',
					'desc'     => __( 'Show SKU as meta data', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-tax-total-display',
					'name'     => 'bewpi_tax_total_display',
					'title'    => __( 'Display tax totals', 'woocommerce-pdf-invoices' ),
					'callback' => array( $template_settings, 'select_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'select',
					'desc'     => '',
					'default'  => get_option( 'woocommerce_tax_total_display' ),
					'options'  => array(
						'itemized' => __( 'Itemized', 'woocommerce-pdf-invoices' ),
						'single'   => __( 'As a single total', 'woocommerce-pdf-invoices' ),
					),
				),
				array(
					'id'       => 'bewpi-columns',
					'name'     => 'bewpi_columns',
					'title'    => __( 'Line item columns', 'woocommerce-pdf-invoices' ),
					'callback' => array( $template_settings, 'multi_select_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'multiple_select',
					'desc'     => '',
					'class'    => 'bewpi-columns',
					'options'  => array(
						'description'             => array(
							'name'    => __( 'Description', 'woocommerce-pdf-invoices' ),
							'value'   => 'description',
							'default' => 1,
						),
						'cost_ex_vat'             => array(
							'name'    => __( 'Cost', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'cost_ex_vat',
							'default' => 1,
						),
						'discount_ex_vat'         => array(
							'name'    => __( 'Discount', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'discount_ex_vat',
							'default' => 0,
						),
						'cost_incl_vat'           => array(
							'name'    => __( 'Cost', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'cost_incl_vat',
							'default' => 0,
						),
						'discount_incl_vat'       => array(
							'name'    => __( 'Discount', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'discount_incl_vat',
							'default' => 0,
						),
						'quantity'                => array(
							'name'    => __( 'Quantity', 'woocommerce-pdf-invoices' ),
							'value'   => 'quantity',
							'default' => 1,
						),
						'vat'                     => array(
							'name'    => WC()->countries->tax_or_vat(),
							'value'   => 'vat',
							'default' => 1,
						),
						'total_discount_ex_vat'   => array(
							'name'    => __( 'Total Discount', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'total_discount_ex_vat',
							'default' => 0,
						),
						'total_ex_vat'            => array(
							'name'    => __( 'Total', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'total_ex_vat',
							'default' => 1,
						),
						'total_discount_incl_vat' => array(
							'name'    => __( 'Total Discount', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'total_discount_incl_vat',
							'default' => 0,
						),
						'total_incl_vat'          => array(
							'name'    => __( 'Total', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'total_incl_vat',
							'default' => 0,
						),
					),
				),
				array(
					'id'       => 'bewpi-show-discounted-amounts',
					'name'     => 'bewpi_show_discounted_amounts',
					'title'    => '',
					'callback' => array( $template_settings, 'input_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'checkbox',
					'desc'     => __( 'Show discounted amounts', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-totals',
					'name'     => 'bewpi_totals',
					'title'    => __( 'Total rows', 'woocommerce-pdf-invoices' ),
					'callback' => array( $template_settings, 'multi_select_callback' ),
					'page'     => 'bewpi_template_settings',
					'section'  => 'advanced_table_content',
					'type'     => 'multiple_select',
					'desc'     => '',
					'class'    => 'bewpi-totals',
					'options'  => array(
						'discount_ex_vat'   => array(
							'name'    => __( 'Discount', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'discount_ex_vat',
							'default' => 1,
						),
						'shipping_ex_vat'   => array(
							'name'    => __( 'Shipping', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'shipping_ex_vat',
							'default' => 1,
						),
						'fee_ex_vat'        => array(
							'name'    => __( 'Fee', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'fee_ex_vat',
							'default' => 1,
						),
						'subtotal_ex_vat'   => array(
							'name'    => __( 'Subtotal', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'subtotal_ex_vat',
							'default' => 1,
						),
						'subtotal_incl_vat' => array(
							'name'    => __( 'Subtotal', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'subtotal_incl_vat',
							'default' => 0,
						),
						'discount_incl_vat' => array(
							'name'    => __( 'Discount', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'discount_incl_vat',
							'default' => 0,
						),
						'shipping_incl_vat' => array(
							'name'    => __( 'Shipping', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'shipping_incl_vat',
							'default' => 0,
						),
						'vat'               => array(
							'name'    => WC()->countries->tax_or_vat(),
							'value'   => 'vat',
							'default' => 1,
						),
						'fee_incl_vat'      => array(
							'name'    => __( 'Fee', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'fee_incl_vat',
							'default' => 0,
						),
						'total_ex_vat'      => array(
							'name'    => __( 'Total', 'woocommerce-pdf-invoices' ) . ' ' . $ex_tax_or_vat,
							'value'   => 'total_ex_vat',
							'default' => 0,
						),
						'total_incl_vat'    => array(
							'name'    => __( 'Total', 'woocommerce-pdf-invoices' ) . ' ' . $inc_tax_or_vat,
							'value'   => 'total_incl_vat',
							'default' => 1,
						),
					),
				),
				array(
					'id'       => 'bewpi-show-tax-labels',
					'name'     => 'bewpi_show_tax_labels',
					'title'    => '',
					'callback' => array( $template_settings, 'input_callback' ),
					'page'     => $template_settings->settings_key,
					'section'  => 'advanced_table_content',
					'type'     => 'checkbox',
					'desc'     => __( 'Show tax labels', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
			);

			return array_merge( $settings, $advanced_settings );
		}

		/**
		 * Add line item tax headers to the invoice.
		 *
		 * @param BEWPI_Invoice $invoice Invoice object.
		 */
		public static function display_line_item_tax_headers( $invoice ) {
			$template_options = get_option( 'bewpi_template_settings' );

			if ( wc_tax_enabled() && $template_options['bewpi_show_tax'] && $invoice->order->get_taxes() > 0 ) {
				foreach ( $invoice->order->get_taxes() as $tax_item ) {
					printf( '<th>%s</th>', $tax_item['label'] );
				}
			}
		}

		/**
		 * Add line item tax to the invoice.
		 *
		 * @param int               $item_id Tax item ID.
		 * @param WC_Order_Item_Tax $item    Tax item.
		 * @param BEWPI_Invoice     $invoice Invoice object.
		 */
		public static function display_line_item_tax( $item_id, $item, $invoice ) {
			$template_options = get_option( 'bewpi_template_settings' );

			if ( wc_tax_enabled() && $template_options['bewpi_show_tax'] && $invoice->order->get_taxes() > 0 ) {
				foreach ( self::get_line_item_tax_data( $invoice->order, $item ) as $tax_total ) {
					printf( '<td>%s</td>', $tax_total );
				}

				$colspan = count( $invoice->order->get_taxes() ) + 1;
				WPI()->templater()->invoice->set_colspan( $colspan );
			}
		}

		/**
		 * Display SKU as item meta.
		 *
		 * @param WC_Order_Item_Product $item  order item object.
		 * @param WC_Order              $order order object.
		 */
		public static function display_sku_as_meta_data( $item, $order ) {
			if ( ! WPI()->templater()->has_sku_as_meta_data() ) {
				return;
			}

			$product = BEWPI_WC_Order_Compatibility::get_product( $order, $item );
			$sku     = $product && BEWPI_WC_Product_Compatibility::get_prop( $product, 'sku' ) ? BEWPI_WC_Product_Compatibility::get_prop( $product, 'sku' ) : '-';
			?>
			<br>
			<ul>
				<li>
					<strong><?php esc_html_e( 'SKU:', 'woocommerce-pdf-invoices' ); ?></strong> <?php echo esc_html( $sku ); ?>
				</li>
			</ul>
			<?php
		}

		/**
		 * Add VAT to column headers.
		 *
		 * @param array                  $data    column headers data.
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 *
		 * @return array $data.
		 */
		public static function add_vat_column( $data, $invoice ) {
			$data['vat'] = array();

			if ( $invoice->is_vat_exempt() ) {
				$data['vat']['vat_exempt'] = sprintf( __( 'VAT %s', 'woocommerce-pdf-invoices' ), '0%' );
			} else {
				foreach ( $invoice->order->get_taxes() as $code => $tax ) {
					$tax_label                              = sprintf( __( 'VAT %s', 'woocommerce-pdf-invoices' ), WC_Tax::get_rate_percent( $tax['rate_id'] ) );
					$data['vat'][ sanitize_title( $code ) ] = $tax_label;
				}
			}

			return $data;
		}

		/**
		 * Add line item column headers.
		 *
		 * @param array                  $data    Column header data.
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 *
		 * @return array $data.
		 */
		public static function get_columns( $data, $invoice ) {
			$data             = array();
			$selected_columns = (array) WPI()->get_option( 'template', 'columns' );
			$show_tax_labels  = (bool) WPI()->get_option( 'template', 'show_tax_labels' );

			foreach ( $selected_columns as $column ) {
				switch ( $column ) {
					case 'description':
						$invoice->add_column( $data, $column, __( 'Description', 'woocommerce-pdf-invoices' ) );
						break;

					case 'quantity':
						$invoice->add_column( $data, $column, __( 'Qty', 'woocommerce-pdf-invoices' ) );
						break;

					case 'cost_ex_vat':
						$tax_display = $show_tax_labels ? 'excl' : '';
						$invoice->add_column( $data, $column, __( 'Cost', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'discount_ex_vat':
						$tax_display = $show_tax_labels ? 'excl' : '';
						$invoice->add_column( $data, $column, __( 'Discount', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'cost_incl_vat':
						$tax_display = $show_tax_labels ? 'incl' : '';
						$invoice->add_column( $data, $column, __( 'Cost', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'discount_incl_vat':
						$tax_display = $show_tax_labels ? 'incl' : '';
						$invoice->add_column( $data, $column, __( 'Discount', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'vat':
						$data = self::add_vat_column( $data, $invoice );
						break;

					case 'total_discount_ex_vat':
						$tax_display = $show_tax_labels ? 'excl' : '';
						$invoice->add_column( $data, $column, __( 'Total Discount', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'total_ex_vat':
						$tax_display = $show_tax_labels ? 'excl' : '';
						$invoice->add_column( $data, $column, __( 'Total', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'total_discount_incl_vat':
						$tax_display = $show_tax_labels ? 'excl' : '';
						$invoice->add_column( $data, $column, __( 'Total Discount', 'woocommerce-pdf-invoices' ), $tax_display );
						break;

					case 'total_incl_vat':
						$tax_display = $show_tax_labels ? 'incl' : '';
						$invoice->add_column( $data, $column, __( 'Total', 'woocommerce-pdf-invoices' ), $tax_display );
						break;
				}
			}

			// Sort by setting.
			$data = array_merge( array_flip( $selected_columns ), $data );

			// Remove VAT key when there is non.
			if ( isset( $data['vat'] ) && count( $data['vat'] ) === 0 ) {
				unset( $data['vat'] );
			}

			return $data;
		}

		/**
		 * Add VAT column data.
		 *
		 * @param array                  $row     Column data.
		 * @param int                    $item_id Item ID.
		 * @param WC_Order_Item          $item    Item object.
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 */
		private static function add_vat_column_data( &$row, $item_id, $item, $invoice ) {
			$row['vat'] = array();

			if ( $invoice->is_vat_exempt() ) {
				$row['vat']['vat_exempt'] = wc_price( 0, array( 'currency' => WPI()->get_currency( $invoice->order ) ) );
			} else {
				foreach ( self::get_line_item_tax_data( $invoice->order, $item ) as $code => $tax ) {
					$row['vat'][ sanitize_title( $code ) ] = $tax;
				}
			}
		}

		/**
		 * Get line item tax data.
		 *
		 * @param WC_Order      $order Order object.
		 * @param WC_Order_Item $item  Order item object.
		 *
		 * @return array.
		 */
		private static function get_line_item_tax_data( $order, $item ) {
			$line_item_tax_data = array();
			$line_tax_data      = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
			$tax_data           = maybe_unserialize( $line_tax_data );

			foreach ( $order->get_taxes() as $code => $tax ) {
				$tax_item_id    = $tax['rate_id'];
				$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';

				if ( ! empty( $tax_item_total ) ) {
					$line_item_tax_data[ sanitize_title( $code ) ] = wc_price( wc_round_tax_total( $tax_item_total ), array(
							'currency' => WPI()->get_currency( $order ),
						)
					);
				} else {
					$line_item_tax_data[ sanitize_title( $code ) ] = '&ndash;';
				}
			}

			return $line_item_tax_data;
		}

		/**
		 * Add Cost column data.
		 *
		 * @param array                  $row      Column data.
		 * @param int                    $item_id  Item ID.
		 * @param object                 $item     Item object.
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param bool                   $incl_tax Including tax.
		 */
		private static function add_cost_column_data( &$row, $item_id, $item, $invoice, $incl_tax = false ) {
			$key   = 'cost_' . ( $incl_tax ? 'incl' : 'ex' ) . '_vat';
			$price = (float) $invoice->order->get_item_subtotal( $item, $incl_tax );

			// Show cost after discount?
			$show_discounted_amounts = WPI()->get_option( 'template', 'show_discounted_amounts' );
			if ( true === (bool) $show_discounted_amounts ) {
				$price -= (float) self::get_item_discount( $invoice, $item, $incl_tax );
			}

			$row[ $key ] = wc_price( $price, array( 'currency' => WPI()->get_currency( $invoice->order ) ) );
		}

		/**
		 * Get item discount
		 *
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param object                 $item     Item object.
		 * @param bool                   $incl_tax Including tax.
		 *
		 * @return float
		 */
		public static function get_item_discount( $invoice, $item, $incl_tax = false ) {
			$discount = 0.00;
			if ( $item->get_subtotal() !== $item->get_total() ) {
				$discount = $invoice->order->get_item_subtotal( $item, $incl_tax, false ) - $invoice->order->get_item_total( $item, $incl_tax, false );
			}

			return $discount;
		}

		/**
		 * Get line discount
		 *
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param object                 $item     Item object.
		 * @param bool                   $incl_tax Including tax.
		 *
		 * @return float
		 */
		public static function get_line_discount( $invoice, $item, $incl_tax = false ) {
			$discount = 0.00;
			if ( $item->get_subtotal() !== $item->get_total() ) {
				$discount = $invoice->order->get_line_subtotal( $item, $incl_tax, false ) - $invoice->order->get_line_total( $item, $incl_tax, false );
			}

			return $discount;
		}

		/**
		 * Add Discount column data.
		 *
		 * @param array                  $row      Column data.
		 * @param int                    $item_id  Item ID.
		 * @param object                 $item     Item object.
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param bool                   $incl_tax Including tax.
		 */
		private static function add_discount_column_data( &$row, $item_id, $item, $invoice, $incl_tax = false ) {
			$key = 'discount_' . ( $incl_tax ? 'incl' : 'ex' ) . '_vat';

			$discount = 0.00;
			if ( $item->get_subtotal() !== $item->get_total() ) {
				$discount = $invoice->order->get_item_subtotal( $item, $incl_tax, false ) - $invoice->order->get_item_total( $item, $incl_tax, false );
			}

			$row[ $key ] = wc_price( wc_format_decimal( $discount, '' ), array( 'currency' => $invoice->order->get_currency() ) );
		}

		/**
		 * Adds line item discount total to columns data array.
		 *
		 * @param array                  $row      Column data.
		 * @param int                    $item_id  Item ID.
		 * @param object                 $item     Item object.
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param bool                   $incl_tax Including or excluding tax.
		 */
		private static function add_total_discount_column_data( &$row, $item_id, $item, $invoice, $incl_tax = false ) {
			$key           = 'total_discount_' . ( $incl_tax ? 'incl' : 'ex' ) . '_vat';
			$line_discount = 0.00;

			if ( $item->get_subtotal() !== $item->get_total() ) {
				$line_discount = self::get_line_discount( $invoice, $item, $incl_tax );
			}

			$row[ $key ] = wc_price( wc_format_decimal( $line_discount, '' ), array( 'currency' => $invoice->order->get_currency() ) );
		}

		/**
		 * Adds line item total incl. tax to columns data array.
		 *
		 * @param array                  $row      Column data.
		 * @param int                    $item_id  Item ID.
		 * @param object                 $item     Item object.
		 * @param BEWPI_Abstract_Invoice $invoice  Invoice object.
		 * @param bool                   $incl_tax Including tax.
		 */
		private static function add_total_column_data( &$row, $item_id, $item, $invoice, $incl_tax = false ) {
			$key   = 'total_' . ( $incl_tax ? 'incl' : 'ex' ) . '_vat';
			$price = (float) $invoice->order->get_line_subtotal( $item, $incl_tax );

			// Show total after discount?
			$show_discounted_amounts = WPI()->get_option( 'template', 'show_discounted_amounts' );
			if ( true === (bool) $show_discounted_amounts ) {
				$price -= (float) self::get_line_discount( $invoice, $item, $incl_tax );
			}

			$row[ $key ] = wc_price( $price, array( 'currency' => WPI()->get_currency( $invoice->order ) ) );
		}

		/**
		 * Add column data to rows.
		 *
		 * @param array                  $row     Column data.
		 * @param int                    $item_id Item ID.
		 * @param object                 $item    Item object.
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 *
		 * @return array.
		 */
		public static function get_columns_data( $row, $item_id, $item, $invoice ) {
			$row              = array();
			$selected_columns = (array) WPI()->get_option( 'template', 'columns' );

			foreach ( $selected_columns as $column ) {
				switch ( $column ) {
					case 'description':
						$invoice->add_description_column_data( $row, $item_id, $item );
						break;
					case 'quantity':
						$invoice->add_quantity_column_data( $row, $item_id, $item );
						break;

					case 'cost_ex_vat':
						self::add_cost_column_data( $row, $item_id, $item, $invoice );
						break;

					case 'discount_ex_vat':
						self::add_discount_column_data( $row, $item_id, $item, $invoice );
						break;

					case 'vat':
						self::add_vat_column_data( $row, $item_id, $item, $invoice );
						break;

					case 'cost_incl_vat':
						self::add_cost_column_data( $row, $item_id, $item, $invoice, true );
						break;

					case 'discount_incl_vat':
						self::add_discount_column_data( $row, $item_id, $item, $invoice, true );
						break;

					case 'total_discount_ex_vat':
						self::add_total_discount_column_data( $row, $item_id, $item, $invoice );
						break;

					case 'total_ex_vat':
						self::add_total_column_data( $row, $item_id, $item, $invoice );
						break;

					case 'total_discount_incl_vat':
						self::add_total_discount_column_data( $row, $item_id, $item, $invoice, true );
						break;

					case 'total_incl_vat':
						self::add_total_column_data( $row, $item_id, $item, $invoice, true );
						break;
				}
			}

			// Sort by setting.
			$row = array_merge( array_flip( $selected_columns ), $row );

			// Remove VAT key when there is non.
			if ( isset( $row['vat'] ) && count( $row['vat'] ) === 0 ) {
				unset( $row['vat'] );
			}

			return $row;
		}

		/**
		 * Calculate subtotal based on the Advanced Tabel Content settings.
		 *
		 * @param WC_Order $order    order object.
		 * @param bool     $incl_tax including tax.
		 *
		 * @return float
		 */
		public static function calculate_subtotal( $order, $incl_tax = false ) {
			if ( $incl_tax ) {
				$subtotal = 0;
				/**
				 * Annotation.
				 *
				 * @var WC_Order_Item_Product $item
				 */
				foreach ( $order->get_items() as $item ) {
					$subtotal += (float) $item->get_subtotal() + (float) $item->get_subtotal_tax();
				}

				return $subtotal;
			}

			$subtotal = (float) $order->get_subtotal();

			foreach ( WPI()->get_totals_before_subtotal() as $total ) {
				switch ( $total ) {
					case 'discount_ex_vat':
						$subtotal -= (float) $order->get_total_discount();
						break;

					case 'shipping_ex_vat':
						$subtotal += (float) WPI()->get_prop( $order, 'shipping_total' );
						break;

					case 'fee_ex_vat':
						/**
						 * Fee Annotation.
						 *
						 * @var WC_Order_Item_Fee $fee
						 */
						foreach ( $order->get_items( 'fee' ) as $fee ) {
							$subtotal += (float) $fee['line_total'];
						}
						break;
				}
			}

			return (float) $subtotal;
		}

		/**
		 * Add total row for subtotal.
		 *
		 * @param array                  $total_rows  totals.
		 * @param bool                   $incl_tax    including or excluding tax.
		 * @param bool                   $tax_display display tax label.
		 * @param BEWPI_Abstract_Invoice $invoice     Invoice object.
		 */
		private static function add_subtotal_total_row( &$total_rows, $invoice, $incl_tax, $tax_display = true ) {
			$subtotal           = self::calculate_subtotal( $invoice->order, $incl_tax );
			$formatted_subtotal = wc_price( $subtotal, array( 'currency' => WPI()->get_currency( $invoice->order ) ) );

			$label = __( 'Subtotal', 'woocommerce-pdf-invoices' );
			if ( $tax_display ) {
				$label .= ' ' . WPI()->tax_or_vat_label( $incl_tax );
			}

			$key                = 'cart_subtotal_' . ( $incl_tax ? 'incl_vat' : 'ex_vat' );
			$total_rows[ $key ] = array(
				/* translators: tax or vat label */
				'label' => $label,
				'value' => $formatted_subtotal,
			);
		}

		/**
		 * Add total row for discounts.
		 *
		 * @param array                  $total_rows  totals.
		 * @param BEWPI_Abstract_Invoice $invoice     Invoice object.
		 * @param bool                   $incl_tax    including or excluding tax.
		 * @param bool                   $tax_display display tax label.
		 *
		 * @return array
		 */
		private static function add_discount_total_row( &$total_rows, $invoice, $incl_tax, $tax_display = true ) {
			$label = __( 'Discount', 'woocommerce-pdf-invoices' );
			if ( $tax_display ) {
				$label .= ' ' . WPI()->tax_or_vat_label( $incl_tax );
			}

			$key                = 'discount_' . ( $incl_tax ? 'incl_vat' : 'ex_vat' );
			$total_rows[ $key ] = array(
				/* translators: tax or vat label */
				'label' => $label,
				'value' => '-' . wc_price( $invoice->order->get_total_discount( ! $incl_tax ), array( 'currency' => WPI()->get_currency( $invoice->order ) ) ),
			);
		}

		/**
		 * Add total row for shipping.
		 *
		 * @param array                  $total_rows  totals.
		 * @param BEWPI_Abstract_Invoice $invoice     Invoice object.
		 * @param bool                   $incl_tax    including or excluding tax.
		 * @param bool                   $tax_display display tax label.
		 */
		private static function add_shipping_total_row( &$total_rows, $invoice, $incl_tax, $tax_display = true ) {
			$shipping_total = WPI()->get_prop( $invoice->order, 'shipping_total', 'edit' );

			if ( $incl_tax ) {
				$shipping_total += (float) WPI()->get_prop( $invoice->order, 'shipping_tax', 'edit' );
			}

			$label = __( 'Shipping', 'woocommerce-pdf-invoices' );
			if ( $tax_display ) {
				$label .= ' ' . WPI()->tax_or_vat_label( $incl_tax );
			}

			$key                = 'shipping_' . ( $incl_tax ? 'incl_vat' : 'ex_vat' );
			$total_rows[ $key ] = array(
				/* translators: tax or vat label */
				'label' => $label,
				'value' => wc_price( $shipping_total, array(
						'currency' => WPI()->get_currency( $invoice->order ),
					)
				),
			);
		}

		/**
		 * Add total row for fees.
		 *
		 * @param array                  $total_rows  totals.
		 * @param BEWPI_Abstract_Invoice $invoice     Invoice object.
		 * @param bool                   $incl_tax    including or excluding tax.
		 * @param bool                   $tax_display display tax label.
		 */
		private static function add_fee_total_row( &$total_rows, $invoice, $incl_tax, $tax_display = true ) {
			/**
			 * Fee annotations.
			 *
			 * @var string            $id  WooCommerce ID.
			 * @var WC_Order_Item_Fee $fee WooCommerce Fee.
			 */
			foreach ( $invoice->order->get_fees() as $id => $fee ) {
				if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
					continue;
				}

				$label = $fee['name'];
				if ( $tax_display ) {
					$label .= ' ' . WPI()->tax_or_vat_label( $incl_tax );
				}

				$key                                     = 'fee_' . ( $incl_tax ? 'incl_vat' : 'ex_vat' );
				$total_rows[ 'fee_' . $key . '_' . $id ] = array(
					/* translators: Fee name and tax or vat label */
					'label' => $label,
					'value' => wc_price( $incl_tax ? (float) $fee['line_total'] + (float) $fee['line_tax'] : $fee['line_total'], array( 'currency' => WPI()->get_currency( $invoice->order ) ) ),
				);
			}
		}

		/**
		 * Get taxes, merged by code, formatted ready for output.
		 *
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 *
		 * @return array
		 */
		private static function get_tax_totals( $invoice ) {
			$tax_totals = array();

			/**
			 * Tax annotation.
			 *
			 * @var WC_Order_Item_Tax $tax Tax object.
			 */
			foreach ( $invoice->order->get_taxes() as $key => $tax ) {
				$code = $tax->get_rate_code();

				if ( ! isset( $tax_totals[ $code ] ) ) {
					$tax_totals[ $code ]         = new stdClass();
					$tax_totals[ $code ]->amount = 0;
				}

				$tax_totals[ $code ]->id          = $key;
				$tax_totals[ $code ]->rate_id     = $tax->get_rate_id();
				$tax_totals[ $code ]->is_compound = $tax->is_compound();
				$tax_totals[ $code ]->label       = $tax->get_label();
				$tax_totals[ $code ]->percent     = WC_Tax::get_rate_percent( $tax->get_rate_id() );
				$tax_totals[ $code ]->amount      += (float) $tax->get_tax_total();

				if ( WPI()->templater()->has_advanced_table_content() ) {
					$rate_id = $tax->get_rate_id( 'edit' );

					foreach ( WPI()->get_option( 'template', 'totals' ) as $total ) {
						switch ( $total ) {
							case 'shipping_ex_vat':
								// Make sure the total tax per rate includes shipping tax.
								$tax_totals[ $code ]->amount += (float) $tax->get_shipping_tax_total();
								break;
							case 'fee_incl_vat':
								$tax_totals[ $code ]->amount -= self::get_fee_tax_by_rate_id( $invoice, $rate_id );
								break;
						}
					}
				} else {
					$tax_totals[ $code ]->amount += (float) $tax->get_shipping_tax_total();
				}

				$tax_totals[ $code ]->formatted_amount = wc_price( wc_round_tax_total( $tax_totals[ $code ]->amount ), array( 'currency' => WPI()->get_currency( $invoice->order ) ) );
			} // End foreach.

			return $tax_totals;
		}

		/**
		 * Get discount tax amount per tax class/percentage.
		 *
		 * @param WC_Order $order   order.
		 * @param int      $rate_id Tax rate id.
		 *
		 * @return float
		 */
		public static function get_discount_tax_by_rate_id( $order, $rate_id ) {
			$discount_tax = 0.0;

			foreach ( $order->get_items() as $item_id => $item ) {
				$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
				$tax_data      = maybe_unserialize( $line_tax_data );

				$tax_item_total    = (float) isset( $tax_data['total'][ $rate_id ] ) ? $tax_data['total'][ $rate_id ] : 0;
				$tax_item_subtotal = (float) isset( $tax_data['subtotal'][ $rate_id ] ) ? $tax_data['subtotal'][ $rate_id ] : 0;

				if ( $tax_item_total !== $tax_item_subtotal ) {
					$discount_tax += $tax_item_subtotal - $tax_item_total;
				}
			}

			return $discount_tax;
		}

		/**
		 * Get discount tax amount per tax class/percentage.
		 *
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 * @param int                    $rate_id Tax rate id.
		 *
		 * @return float
		 */
		private static function get_fee_tax_by_rate_id( $invoice, $rate_id ) {
			$tax_item_total = 0;

			foreach ( $invoice->order->get_items( 'fee' ) as $item_id => $item ) {
				$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
				$tax_data      = maybe_unserialize( $line_tax_data );

				$tax_item_id    = $rate_id;
				$tax_item_total += (float) isset( $tax_data['total'][ $tax_item_id ] ) ? (float) $tax_data['total'][ $tax_item_id ] : 0;
			}

			return (float) $tax_item_total;
		}

		/**
		 * Add total row for taxes.
		 *
		 * @param array                  $total_rows totals.
		 * @param bool                   $incl_tax   including or excluding tax.
		 * @param BEWPI_Abstract_Invoice $invoice    Invoice object.
		 *
		 * @return array
		 */
		private static function add_tax_total_row( &$total_rows, $invoice, $incl_tax ) {
			// Check if vat is exempt.
			if ( $invoice->is_vat_exempt() ) {
				$total_rows['vat_exempt'] = array(
					'label' => sprintf( __( 'VAT %s', 'woocommerce-pdf-invoices' ), '0%' ),
					'value' => wc_price( 0, array( 'currency' => WPI()->get_currency( $invoice->order ) ) ),
				);
			} elseif ( false === $incl_tax ) {
				if ( 'itemized' === WPI()->get_option( 'template', 'tax_total_display' ) ) {
					foreach ( self::get_tax_totals( $invoice ) as $code => $tax ) {
						$total_rows[ sanitize_title( $code ) ] = array(
							'label' => sprintf( __( 'VAT %s', 'woocommerce-pdf-invoices' ), WC_Tax::get_rate_percent( $tax->rate_id ) ),
							'value' => $tax->formatted_amount,
						);
					}
				} else {
					$total_rows['tax'] = array(
						'label' => sprintf( __( 'VAT', 'woocommerce-pdf-invoices' ) ),
						'value' => wc_price( $invoice->order->get_total_tax(), array( 'currency' => WPI()->get_currency( $invoice->order ) ) ),
					);
				}
			}
		}


		/**
		 * Gets order total - formatted for display.
		 *
		 * @param BEWPI_Abstract_Invoice $invoice Invoice object.
		 *
		 * @return string
		 * @deprecated Use $order->get_total() instead.
		 *
		 */
		private static function get_formatted_order_total( $invoice ) {
			$total          = $invoice->order->get_total();
			$total_refunded = $invoice->order->get_total_refunded();

			if ( $total_refunded ) {
				$total -= $total_refunded;
			}

			return wc_price( $total, array(
					'currency' => WPI()->get_currency( $invoice->order ),
				)
			);
		}

		/**
		 * Add total row for grand total.
		 *
		 * @param array                  $total_rows  totals.
		 * @param BEWPI_Abstract_Invoice $invoice     Invoice object.
		 * @param bool                   $incl_tax    including or excluding tax.
		 * @param bool                   $tax_display display tax label.
		 */
		private static function add_total_total_row( &$total_rows, $invoice, $incl_tax, $tax_display = true ) {
			$label = __( 'Total', 'woocommerce-pdf-invoices' );
			if ( $tax_display ) {
				$label .= ' ' . WPI()->tax_or_vat_label( $incl_tax );
			}

			$key                = 'order_total_' . ( $incl_tax ? 'incl_vat' : 'ex_vat' );
			$total_rows[ $key ] = array(
				/* translators: tax or vat label */
				'label' => $label,
				'value' => wc_price( $invoice->order->get_total(), array( 'currency' => WPI()->get_currency( $invoice->order ) ) ),
			);
		}

		/**
		 * Get order item totals.
		 *
		 * @param array                  $total_rows Order item totals.
		 * @param BEWPI_Abstract_Invoice $invoice    Invoice object.
		 *
		 * @return array
		 */
		public static function get_total_rows( $total_rows, $invoice ) {
			$total_rows      = array();
			$show_tax_labels = WPI()->get_option( 'template', 'show_tax_labels' );

			foreach ( (array) WPI()->get_option( 'template', 'totals' ) as $total_row ) {
				switch ( $total_row ) {
					case 'discount_ex_vat':
						if ( $invoice->order->get_total_discount() > 0 ) {
							self::add_discount_total_row( $total_rows, $invoice, false, $show_tax_labels );
						}
						break;
					case 'shipping_ex_vat':
						if ( $invoice->order->get_shipping_method() ) {
							self::add_shipping_total_row( $total_rows, $invoice, false, $show_tax_labels );
						}
						break;
					case 'fee_ex_vat':
						if ( ! empty( $invoice->order->get_fees() ) ) {
							self::add_fee_total_row( $total_rows, $invoice, false, $show_tax_labels );
						}
						break;
					case 'subtotal_ex_vat':
						self::add_subtotal_total_row( $total_rows, $invoice, false, $show_tax_labels );
						break;
					case 'subtotal_incl_vat':
						self::add_subtotal_total_row( $total_rows, $invoice, true, $show_tax_labels );
						break;
					case 'discount_incl_vat':
						if ( $invoice->order->get_total_discount() > 0 ) {
							self::add_discount_total_row( $total_rows, $invoice, true, $show_tax_labels );
						}
						break;
					case 'shipping_incl_vat':
						if ( $invoice->order->get_shipping_method() ) {
							self::add_shipping_total_row( $total_rows, $invoice, true, $show_tax_labels );
						}
						break;
					case 'fee_incl_vat':
						if ( ! empty( $invoice->order->get_fees() ) ) {
							self::add_fee_total_row( $total_rows, $invoice, true, $show_tax_labels );
						}
						break;
					case 'total_ex_vat':
						self::add_total_total_row( $total_rows, $invoice, false, $show_tax_labels );
						break;
					case 'vat':
						self::add_tax_total_row( $total_rows, $invoice, false );
						break;
					case 'total_incl_vat':
						self::add_total_total_row( $total_rows, $invoice, true, $show_tax_labels );
						break;
				}
			}

			return apply_filters( 'wpip_total_rows', $total_rows, $invoice );
		}

		/**
		 * Add multiple recipients enabled emails.
		 *
		 * @param string $headers WooCommerce email headers.
		 * @param string $status  WooCommerce email type.
		 *
		 * @return string
		 */
		public static function add_recipients( $headers, $status ) {
			// Check if current email type is enabled.
			if ( ! WPI()->is_email_enabled( $status ) ) {
				return $headers;
			}

			// comma separated suppliers email addresses.
			$recipients = WPI()->get_option( 'premium', 'suppliers' );
			if ( ! $recipients ) {
				return $headers;
			}

			$recipients = explode( ',', $recipients );
			foreach ( $recipients as $recipient ) {
				$headers .= 'BCC: <' . $recipient . '>' . "\r\n";
			}

			return $headers;
		}

		/**
		 * Add additional PDF file to invoice.
		 *
		 * @param mPDF                    $mpdf     Library object.
		 * @param BEWPI_Abstract_Document $document PDF document class.
		 *
		 * @return mixed
		 */
		public static function add_pdf_to_invoice( $mpdf, $document ) {
			// Only add to invoice.
			if ( 'invoice/simple' !== $document->get_type() ) {
				return $mpdf;
			}

			// As of 3.0.9 we've improved the media upload settings feature.
			$attachment = WPI()->get_option( 'premium', 'pdf_attachment' );
			if ( version_compare( WPI_VERSION, '3.0.9' ) >= 0 && ! $attachment ) {
				$attachment_id = WPI()->get_option( 'premium', 'pdf_attachment_id' );
				$attachment    = get_attached_file( $attachment_id );
			}

			if ( empty( $attachment ) ) {
				return $mpdf;
			}

			$mpdf->SetImportUse();

			$page_count = $mpdf->SetSourceFile( $attachment );
			for ( $i = 1; $i <= $page_count; $i ++ ) {
				$mpdf->AddPage( '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', - 1, 0, - 1, 0 );
				$mpdf->showWatermarkText = false;
				$template_id             = $mpdf->ImportPage( $i );
				$mpdf->UseTemplate( $template_id );
			}

			return $mpdf;
		}

		/**
		 * Add Request Invoice checkout field.
		 *
		 * @param WC_Checkout $checkout Checkout object.
		 */
		public static function add_request_invoice_checkout_field( $checkout ) {
			if ( ! WPI()->get_option( 'premium', 'request_invoice' ) ) {
				return;
			}

			woocommerce_form_field( '_bewpi_request_invoice', array(
				'type'  => 'checkbox',
				'class' => array( 'bewpi_request_invoice form-row-wide' ),
				'label' => __( 'Request invoice', 'woocommerce-pdf-invoices' ),
			), apply_filters( 'wpi_bewpi_request_invoice_default_value', 0 ) );
		}

		/**
		 * Process Request Invoice checkout field.
		 *
		 * @param int $order_id WC Order ID.
		 */
		public static function process_request_invoice_checkout_field( $order_id ) {
			if ( isset( $_POST['_bewpi_request_invoice'] ) ) {
				update_post_meta( $order_id, '_bewpi_request_invoice', sanitize_text_field( $_POST['_bewpi_request_invoice'] ) );
			}
		}

		/**
		 * Skip invoice generation.
		 *
		 * @param bool     $skip   To skip.
		 * @param string   $status WC Email status.
		 * @param WC_Order $order  Order object.
		 *
		 * @return bool true to skip.
		 */
		public static function skip_invoice_generation( $skip, $status, $order ) {
			if ( ! WPI()->get_option( 'premium', 'request_invoice' ) ) {
				return $skip;
			}

			if ( WPI()->get_meta( $order, '_bewpi_request_invoice' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Display used coupon codes.
		 *
		 * @param array                  $row_key row.
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 */
		public static function display_used_coupons( $row_key, $invoice ) {
			if ( ! WPI()->get_option( 'template', 'show_used_coupons' ) ) {
				return;
			}

			if ( 'discount' === $row_key && count( $invoice->order->get_used_coupons() ) > 0 ) {
				printf( __( 'Coupon(s): %s', 'woocommerce-pdf-invoices' ), implode( ', ', $invoice->order->get_used_coupons() ) );
			}
		}

		/**
		 * Output additional address fields.
		 *
		 * @param WC_Order $order order object.
		 * @param string   $type  type of address.
		 */
		private static function formatted_additional_address_fields( $order, $type = 'billing' ) {
			$formatted_fields = array();
			$fields           = explode( "\r\n", WPI()->get_option( 'premium', 'customer_' . $type . '_fields' ) );

			foreach ( $fields as $field ) {
				preg_match( '/{(.*?)}/', $field, $match );

				if ( ! isset( $match[1] ) ) {
					continue;
				}

				$meta_value = get_post_meta( $order->get_id(), '_' . $match[1], true );
				if ( empty( $meta_value ) ) {
					$meta_value = get_user_meta( $order->get_customer_id(), $match[1], true );

					if ( empty( $meta_value ) ) {
						continue;
					}
				}

				$meta_value         = wp_kses( $meta_value, '' );
				$formatted_fields[] = str_replace( '{' . $match[1] . '}', $meta_value, $field );
			}

			echo '<br>' . join( '<br>', $formatted_fields );
		}

		/**
		 * Display additional billing fields.
		 *
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 */
		public static function display_additional_billing_fields( $invoice ) {
			self::formatted_additional_address_fields( $invoice->order );
		}

		/**
		 * Display additional billing fields.
		 *
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 */
		public static function display_additional_shipping_fields( $invoice ) {
			self::formatted_additional_address_fields( $invoice->order, 'shipping' );
		}
	}
}
