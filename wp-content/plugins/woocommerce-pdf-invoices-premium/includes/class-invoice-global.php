<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BEWPIP_Global_Invoice' ) ) {

	/**
	 * Class BEWPIP_Invoice_Global.
	 */
	class BEWPIP_Invoice_Global extends BEWPI_Abstract_Invoice {

		public $orders, $order_notes, $taxes, $fees = array();

		private $subtotal, $subtotal_incl_tax, $item_line_total_tax, $total_discount, $total_shipping, $total_shipping_tax, $total, $total_refunded;

		private $currency;

		public $billing_email;

		public $billing_phone;

		/**
		 * BEWPIP_Invoice_Global constructor.
		 *
		 * @param array $orders_ids multiple WooCommerce Order IDs.
		 */
		public function __construct( $orders_ids ) {
			$this->order = wc_create_order();
			$order_id    = BEWPI_WC_Order_Compatibility::get_id( $this->order );
			$this->type  = 'invoice/global';

			$taxes = array();
			foreach ( $orders_ids as $i => $order_id ) {
				$order          = wc_get_order( $order_id );
				$this->orders[] = $order;

				$this->total_discount     += (double) $order->get_total_discount();
				$this->total_shipping     += (double) BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_total' );
				$this->total_shipping_tax += (double) BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_tax' );
				$this->total              += (double) $order->get_total();
				$this->total_refunded     += (double) $order->get_total_refunded();

				// Add products to order.
				/**
				 * @var string $item_id
				 * @var WC_Order_Item $item
				 */
				foreach ( $order->get_items( 'line_item' ) as $item_id => $item ) {
					$this->subtotal            += (double) $item['line_subtotal'];
					$this->item_line_total_tax += (double) $item['line_tax'];

					if ( BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ) {
						$product = $item->get_product();
					} else {
						$product = $order->get_product_from_item( $item );
					}

					if ( $product ) {
						$this->order->add_product( $product, $item['qty'] );
					}
				}

				// Add fees to order.
				foreach ( $order->get_fees() as $key => $f ) {

					/** @var WC_Order_Item_Fee $fee */
					$fee = $this->get_fee_by_name( $f['name'] );
					if ( is_null( $fee ) ) {
						// Add fee.
						$fee            = new stdClass();
						$fee->name      = $f['name'];
						$fee->tax_class = $f['tax_class'];
						$fee->taxable   = '0' !== $fee->tax_class;
						$fee->amount    = $f['line_total'];
						$fee->tax       = $f['line_tax'];
						$fee->tax_data  = array();
						$this->order->add_fee( $fee );

						continue;
					}

					// Update fee.
					$line_total = (double) $f['line_total'] + (double) $fee['line_total'];
					$line_tax = (double) $f['line_tax'] + (double) $fee['line_tax'];

					if ( BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ) {
						$fee->set_total( (string) $line_total );
						$fee->set_total_tax( (string) $line_tax );
					} else {
						$fee['line_total'] = (string) $line_total;
						$fee['line_tax'] = (string) $line_tax;
					}
				}

				// Tax.
				foreach ( $order->get_tax_totals() as $code => $tax ) {

					// Only add tax when unique.
					if ( ! in_array( $tax->rate_id, $taxes ) ) {
						$this->taxes[] = $tax;
					} else {
						$this->update_tax( $tax );
					}

					$taxes[] = $tax->rate_id;
				}

				// Sort taxes by tax rate id.
				if ( count( $this->taxes ) > 0 ) {
					usort( $this->taxes, array( $this, "sort_taxes" ) );
				}

				// Only need to set once from the first order.
				if ( $i === 0 ) {
					$this->billing_email = get_post_meta( $order_id, '_billing_email', true );
					$this->billing_phone = get_post_meta( $order_id, '_billing_phone', true );
					$this->currency      = BEWPI_WC_Order_Compatibility::get_currency( $order );

					$this->order->set_address( array(
						'first_name' => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_first_name' ),
						'last_name'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_last_name' ),
						'company'    => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_company' ),
						'address_1'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_address_1' ),
						'address_2'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_address_2' ),
						'city'       => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_city' ),
						'state'      => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_state' ),
						'postcode'   => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_postcode' ),
						'country'    => BEWPI_WC_Order_Compatibility::get_prop( $order, 'shipping_country' ),
					), 'shipping' );

					$this->order->set_address( array(
						'first_name' => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ),
						'last_name'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ),
						'company'    => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_company' ),
						'address_1'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' ),
						'address_2'  => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' ),
						'city'       => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_city' ),
						'state'      => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_state' ),
						'postcode'   => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ),
						'country'    => BEWPI_WC_Order_Compatibility::get_prop( $order, 'billing_country' ),
					), 'billing' );
				}
			}

			$this->subtotal_incl_tax += $this->item_line_total_tax;

			// Update order values.
			update_post_meta( $order_id, '_cart_discount', $this->total_discount );
			update_post_meta( $order_id, '_order_shipping_tax', $this->total_shipping_tax );
			$this->order->set_total( $this->total );

			parent::__construct( $order_id );
		}

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			if ( is_admin() ) {
				self::admin_init_hooks();
			} else {
				self::frontend_init_hooks();
			}

			add_action( 'wp_ajax_create_global_invoice_by_month', array( __CLASS__, 'frontend_pdf_callback' ) );
			add_action( 'wp_ajax_create_global_invoice_by_year', array( __CLASS__, 'frontend_pdf_callback' ) );

			add_filter( 'bewpi_template_directories', array( __CLASS__, 'add_template_directories' ) );
			add_action( 'bewpi_before_document_generation', array( __CLASS__, 'delete_order' ), 10, 2 );
			add_action( 'bewpi_after_post_meta_deletion', array( __CLASS__, 'delete_postmeta' ), 10, 1 );
		}

		/**
		 * Initialize admin hooks.
		 */
		private static function admin_init_hooks() {
			add_action( 'admin_footer-edit.php', array( __CLASS__, 'add_bulk_action' ) );
			add_action( 'load-edit.php', array( __CLASS__, 'generate_global_invoice' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice_template' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice_global' ) );
			add_action( 'bewpi_admin_pdf_callback_end', array( __CLASS__, 'view_global_invoice' ), 10, 2 );
		}

		/**
		 * Initialize frontend-only hooks.
		 */
		private static function frontend_init_hooks() {
			add_action( 'init', array( __CLASS__, 'frontend_pdf_callback' ) );
			add_action( 'woocommerce_before_my_account', array( __CLASS__, 'customer_generation_html' ) );
			add_shortcode( 'bewpi-create-global-invoice', array( __CLASS__, 'customer_generation_html' ) );
		}

		/**
		 * Add template directory.
		 *
		 * @param array $directories Absolute template directory paths.
		 *
		 * @return array
		 */
		public static function add_template_directories( $directories ) {
			$directories[] = BEWPIP_DIR . '/includes/templates';

			return $directories;
		}

		/**
		 * Frontend PDF callback.
		 */
		public static function frontend_pdf_callback() {
			if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) ) {
				return;
			}

			$action          = sanitize_key( $_POST['action'] );
			$allowed_actions = array( 'create_global_invoice_by_month', 'create_global_invoice_by_year' );
			if ( ! in_array( $action, $allowed_actions, true ) ) {
				return;
			}

			$premium_options = get_option( 'bewpipremium_settings' );
			if ( ! $premium_options['bewpi_customer_generation'] ) {
				return;
			}

			$action = sanitize_key( $_POST['action'] );
			if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), $action ) ) {
				wp_die( __( 'Invalid request', 'woocommerce-pdf-invoices' ) );
			}

			$order_ids = array();

			switch ( $action ) {
				case 'create_global_invoice_by_month':
					if ( isset( $_POST['month'] ) ) {
						$order_ids = get_customer_orders_by_month( get_current_user_id(), intval( $_POST['month'] ) );
					}
					break;

				case 'create_global_invoice_by_year':
					if ( isset( $_POST['year'] ) ) {
						$order_ids = get_customer_orders_by_year( get_current_user_id(), intval( $_POST['year'] ) );
					}
					break;
			}

			if ( count( $order_ids ) === 0 ) {
				wp_die( __( 'No orders found.', 'woocommerce-pdf-invoices' ),
					'Generate Global Invoice Orders Error',
					array(
						'response' => 200,
						'back_link' => true,
					)
				);
			}

			$invoice = new BEWPIP_Invoice_Global( $order_ids );
			$full_path = $invoice->generate();
			BEWPI_Abstract_Document::view( $full_path );
			exit;
		}

		/**
		 * Admin callback hook that executes on every valid PDF action.
		 *
		 * @param string $action PDF action.
		 * @param int    $order_id WC_Order ID.
		 */
		public static function view_global_invoice( $action, $order_id ) {
			if ( 'view_global_invoice' === $action ) {
				$pdf_path = get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
				if ( ! $pdf_path ) {
					return;
				}

				$full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;
				if ( ! BEWPI_Abstract_Document::exists( $full_path ) ) {
					return;
				}

				self::view( $full_path );
			}
		}

		/**
		 * Add bulk action to shop order page.
		 */
		public static function add_bulk_action() {
			global $post_type;

			if ( 'shop_order' === $post_type ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery('<option>').val('generate_global_invoice').text('<?php _e( 'Generate global invoice', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action']");
					});
				</script>
				<?php
			}
		}

		/**
		 * Generate global invoice callback from bulk actions list.
		 */
		public static function generate_global_invoice() {
			global $typenow;
			$post_type = $typenow;

			// Are we on order page?
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			// Get the action.
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();
			if ( 'generate_global_invoice' !== $action ) {
				return;
			}

			// Using micro template?
			if ( strpos( WPI()->get_option( 'template', 'template_name' ), 'micro' ) === false ) {
				set_transient( 'bewpi_global_invoice_template_error', __( 'Could not generate the invoice. Global invoices are only supported when using the micro template.', 'woocommerce-pdf-invoices' ), 10 );
				return;
			}

			// Security check.
			check_admin_referer( 'bulk-posts' );
			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			// Make sure ids are submitted. Depending on the resource type, this may be 'media' or 'ids'.
			$post_ids = array_map( 'intval', $_REQUEST['post'] );
			if ( count( $post_ids ) < 2 ) {
				wp_die( __( 'Select more then one order to generate a global invoice.', 'woocommerce-pdf-invoices' ), '', array( 'response' => 200, 'back_link' => true ) );
			}

			// this is based on wp-admin/edit.php.
			$sendback = remove_query_arg( array( 'generated', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
			if ( ! $sendback ) {
				$sendback = admin_url( 'edit.php' );
			}

			// generate global invoice.
			$invoice   = new BEWPIP_Invoice_Global( $post_ids );
			$full_path = $invoice->generate();
			if ( file_exists( $full_path ) ) {
				$invoice->send();
			}

			$sendback = add_query_arg( array(
				'post_type'     => $post_type,
				'paged'         => $wp_list_table->get_pagenum(),
				'ids'           => join( ',', $post_ids ),
				'order_id'      => BEWPI_WC_Order_Compatibility::get_id( $invoice->order ),
				'bewpi_notice'  => 'global',
			), $sendback );

			$sendback = remove_query_arg( array(
				'action',
				'action2',
				'tags_input',
				'post_author',
				'comment_status',
				'ping_status',
				'_status',
				'post',
				'bulk_edit',
				'post_view',
			), $sendback );

			wp_safe_redirect( $sendback );
			exit();
		}

		/**
		 * Display wrong template notice.
		 */
		public static function admin_notice_template() {
			$message = get_transient( 'bewpi_global_invoice_template_error' );
			if ( ! $message ) {
				return;
			}

			printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message );
		}

		/**
		 * Admin notice for administrator to rate plugin on wordpress.org.
		 */
		public static function admin_notice_global() {
			if ( ! isset( $_GET['bewpi_notice'] ) || ! isset( $_GET['order_id'] ) ) {
				return;
			}

			// Notice needs to be inactive.
			if ( ! BEWPI_Admin_Notices::is_admin_notice_active( 'global-forever' ) ) {
				return;
			}

			include BEWPIP_DIR . '/includes/admin/views/html-global-notice.php';
		}

		/**
		 * Let a customer generate a global invoice from his/her account.
		 */
		public static function customer_generation_html() {
			$premium_options = get_option( 'bewpipremium_settings' );
			if ( ! (bool) $premium_options['bewpi_customer_generation'] ) {
				return;
			}

			include BEWPIP_DIR . '/includes/admin/views/html-customer-generation.php';
		}

		/**
		 * Delete WC_Order.
		 *
		 * @param string $type PDF type.
		 * @param int    $order_id WC_Order ID.
		 */
		public static function delete_order( $type, $order_id ) {
			if ( 'invoice/global' === $type ) {
				global $wpdb;
				// Do not delete postmeta like invoice number etc.
				$wpdb->delete( $wpdb->posts, array( 'ID' => $order_id ) );
			}
		}

		/**
		 * Delete order meta.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public static function delete_postmeta( $order_id ) {
			delete_post_meta( $order_id, 'bewpi_custom_date' );
			delete_post_meta( $order_id, '_bewpi_reminder_sent' );
			delete_post_meta( $order_id, '_bewpi_is_reminder_email' );
		}

		protected function sort_taxes( $a, $b ) {
			return $a->rate_id > $b->rate_id;
		}

		/**
		 * Send global invoice email.
		 */
		public function send() {
			$admin_email = get_option( 'admin_email' );
			if ( empty( $admin_email ) ) {
				return;
			}

			$premium_options = get_option( 'bewpipremium_settings' );
			$subject         = $premium_options['bewpi_global_invoice_email_subject'];
			$message         = $premium_options['bewpi_global_invoice_email_message'];

			$mailer  = WC()->mailer();
			$headers = 'From: ' . $mailer->get_from_name() . ' <' . $mailer->get_from_address() . '>' . "\r\n";

			$recipients = $this->get_recipients();
			foreach ( $recipients as $recipient ) {
				$headers .= 'BCC: <' . $recipient . '>' . "\r\n";
			}

			wp_mail( $admin_email, $subject, $message, $headers, $this->full_path );
		}

		/**
		 * Global invoice recipients.
		 *
		 * @return array $recipients
		 */
		public function get_recipients() {
			$recipients      = array();
			$general_options = get_option( 'bewpi_general_settings' );
			$premium_options = get_option( 'bewpipremium_settings' );

			// Customer.
			if ( $premium_options['bewpi_global_invoice_to_customer'] && ! empty( $this->billing_email ) ) {
				$recipients[] = $this->billing_email;
			}

			// Email It In.
			if ( $premium_options['bewpi_global_invoice_to_email_it_in'] && ! empty( $general_options['bewpi_email_it_in_account'] ) ) {
				$recipients[] = $general_options['bewpi_email_it_in_account'];
			}

			// Suppliers.
			if ( $premium_options['bewpi_global_invoice_to_suppliers'] ) {
				$recipients = array_merge( $recipients, explode( ',', $premium_options['bewpi_suppliers'] ) );
			}

			return apply_filters( 'bewpi_recipients', $recipients );
		}

		public function get_subtotal( $shipping_taxable = false ) {
			if ( $shipping_taxable ) {
				return $this->subtotal + $this->get_total_shipping( false );
			}

			return $this->subtotal;
		}

		public function get_currency() {
			return $this->currency;
		}

		/**
		 * Get the total amount with or without refunds
		 * @return string
		 */
		public function get_formatted_total() {
			return wc_price( $this->total, array( 'currency' => BEWPI_WC_Order_Compatibility::get_currency( $this->order ) ) );
		}

		public function get_total_after_refunded() {
			if ( $this->total_refunded > 0 ) {
				return $this->total - $this->total_refunded;
			} else {
				return $this->total;
			}
		}

		public function get_total_discount() {
			return $this->total_discount;
		}

		public function get_total_shipping( $incl_tax = true ) {
			return ( $incl_tax ) ? $this->total_shipping : $this->total_shipping - $this->total_shipping_tax;
		}

		public function get_taxes() {
			return $this->taxes;
		}

		public function get_order_currency() {
			return $this->currency;
		}

		public function get_fees() {
			return $this->fees;
		}

		public function get_total_refunded() {
			return $this->total_refunded;
		}

		private function update_tax( $tax ) {
			foreach ( $this->taxes as $i => $t ) {
				if ( $t->rate_id === $tax->rate_id ) {
					$this->taxes[ $i ]->amount += $tax->amount;
				}
			}
		}

		private function get_fee_by_name( $name ) {
			foreach ( $this->order->get_fees() as $fee ) {
				if ( $name === $fee['name'] ) {
					return $fee;
				}
			}

			return null;
		}
	}
}
