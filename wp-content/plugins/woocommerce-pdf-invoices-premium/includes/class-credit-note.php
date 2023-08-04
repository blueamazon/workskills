<?php
/**
 * Credit Note invoice class.
 *
 * Handling Credit Note invoice specific functionality.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BEWPIP_WooCommerce_PDF_Invoices/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPIP_Credit_Note' ) ) {
	/**
	 * Class BEWPIP_Credit_Note.
	 */
	class BEWPIP_Credit_Note extends BEWPI_Abstract_Invoice {

		/**
		 * Invoice object.
		 *
		 * @var BEWPI_Abstract_Invoice.
		 */
		public $invoice;

		/**
		 * BEWPIP_Credit_Note constructor.
		 *
		 * @param int $refund_id refund object ID.
		 */
		public function __construct( $refund_id ) {
			$this->id    = 'credit_note';
			$this->type  = 'credit-note/simple';
			$this->order = wc_get_order( $refund_id );

			$parent_order  = wc_get_order( $this->order->get_parent_id() );
			$this->invoice = WPI()->get_invoice( BEWPI_WC_Order_Compatibility::get_id( $parent_order ) );

			$templater = WPI()->templater();
			$templater->add_directory( BEWPIP_DIR . '/includes/templates' );
			$templater->set_invoice( $this );

			parent::__construct( $refund_id );
		}

		/**
		 * Always use minimal template for credit notes.
		 *
		 * @param string $name     template name.
		 * @param string $type     template type.
		 * @param int    $order_id order ID.
		 *
		 * @return string
		 */
		public static function set_template_name( $name, $type, $order_id ) {
			// Use minimal credit note template when using micro template for invoice.
			if ( 'credit-note/simple' === $type && strpos( $name, 'micro' ) !== false ) {
				return 'minimal';
			}

			return $name;
		}

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			add_filter( 'wpi_template_name', array( __CLASS__, 'set_template_name' ), 10, 3 );

			if ( is_admin() ) {
				self::admin_init_hooks();
			}
		}

		/**
		 * Initialize admin hooks.
		 */
		private static function admin_init_hooks() {
			add_action( 'admin_init', array( __CLASS__, 'send_credit_note' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
			add_action( 'admin_post_wpip_view_credit_note', array( __CLASS__, 'view_credit_note' ) );
			add_action( 'wp_ajax_wpip_delete_credit_note', array( __CLASS__, 'delete_credit_note' ) );
			add_action( 'wp_ajax_wpip_reload_credit_notes_meta_box', array(
				__CLASS__,
				'reload_credit_notes_meta_box',
			) );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_scripts' ), 99, 1 );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_order_meta_boxes' ), 30 );
			add_action( 'woocommerce_order_refunded', array( __CLASS__, 'generate_credit_note_for_refund' ), 10, 2 );
			add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_customer_credit_note_email' ) );

			add_filter( 'woocommerce_resend_order_emails_available', array(
				__CLASS__,
				'add_customer_credit_note_email_resend',
			), 90, 1 );
			add_filter( 'woocommerce_order_actions', array( __CLASS__, 'add_order_action' ) );

			add_filter( 'woocommerce_email_attachments', array( __CLASS__, 'attach_credit_note_to_email' ), 99, 3 );
		}

		/**
		 * Send Credit Note to customer.
		 */
		public static function send_credit_note() {
			if ( empty( $_POST['wc_order_action'] ) || empty( $_POST['post_ID'] ) ) { // @codingStandardsIgnoreLine
				return;
			}

			$action = wc_clean( wp_unslash( $_POST['wc_order_action'] ) ); // @codingStandardsIgnoreLine

			if ( 'send_customer_credit_note' === $action ) {
				$post_id = absint( $_POST['post_ID'] ); // @codingStandardsIgnoreLine
				$order   = wc_get_order( $post_id );
				WC()->mailer()->emails['BEWPI_WC_Email_Customer_Credit_Note']->trigger( $order->get_id(), $order );
			}
		}

		/**
		 * Display no document found error message.
		 */
		public static function admin_notices() {
			$message = get_transient( 'bewpi_no_document_error' );
			if ( $message ) {
				printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $message ) );
			}
		}

		/**
		 * Load admin scripts.
		 *
		 * @param string $hook To check current page.
		 */
		public static function load_admin_scripts( $hook ) {
			global $post;

			if ( is_null( $post ) || 'shop_order' !== $post->post_type || 'post.php' !== $hook ) {
				return;
			}

			// JS.
			wp_enqueue_script( 'wpip-admin-meta-boxes-order', BEWPIP_URL . '/assets/js/meta-boxes-order.js', array( 'wc-admin-order-meta-boxes' ), BEWPIP_VERSION, false );
			$params = array(
				'post'              => $post->ID,
				'post_url'          => admin_url( 'admin-post.php' ),
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'credit_note_nonce' => wp_create_nonce( 'credit-note' ),
			);
			wp_localize_script( 'wpip-admin-meta-boxes-order', 'wpip_admin_meta_boxes_order', $params );

			// CSS.
			wp_register_style( 'wpip-admin-meta-boxes-order-style', BEWPIP_URL . '/assets/css/meta-boxes-order.css', false, BEWPIP_VERSION );
			wp_enqueue_style( 'wpip-admin-meta-boxes-order-style' );
		}

		/**
		 * Add credit note meta box to order page.
		 */
		public static function add_order_meta_boxes() {
			add_meta_box( 'wpip-credit-notes', __( 'PDF Credit Notes', 'woocommerce-pdf-invoices' ), array(
				__CLASS__,
				'display_credit_notes_meta_box',
			), 'shop_order', 'side', 'high' );
		}

		/**
		 * GET callback for logged in users to view the credit note.
		 */
		public static function view_credit_note() {

			if ( empty( $_GET ) || false === check_admin_referer( 'credit-note', 'security' ) ) {
				wp_die( 'Invalid request.' );
			}

			if ( ! isset( $_GET['refund_id'] ) ) {
				wp_die( 'Refund ID not found.' );
			}

			// Validate allowed user roles.
			if ( ! array_intersect( WPI()->get_allowed_roles(), wp_get_current_user()->roles ) ) {
				wp_die( 'Access denied.' );
			}

			$refund_id   = absint( $_GET['refund_id'] );
			$credit_note = new BEWPIP_Credit_Note( $refund_id );
			$full_path   = $credit_note->update();

			if ( false === $full_path ) {
				wp_die( 'Something went wrong. PDF Credit Note not found.' );
			}

			self::view( $full_path );
		}

		/**
		 * GET callback for logged in users to delete the credit note.
		 */
		public static function delete_credit_note() {

			if ( empty( $_POST ) || false === check_ajax_referer( 'credit-note', 'security' ) ) {
				wp_die( 'Invalid request.' );
			}

			if ( ! isset( $_POST['refund_id'] ) ) {
				wp_die( 'Refund ID not found.' );
			}

			$refund_id = absint( $_POST['refund_id'] );

			// Validate allowed user roles.
			if ( ! array_intersect( WPI()->get_allowed_roles(), wp_get_current_user()->roles ) ) {
				wp_die( 'Access denied.' );
			}

			self::delete( $refund_id );

			wp_die();
		}

		/**
		 * AJAX callback to reload credit notes meta box.
		 */
		public static function reload_credit_notes_meta_box() {
			check_ajax_referer( 'credit-note', 'security' );

			if ( ! isset( $_POST['post'] ) ) {
				wp_die( - 1 );
			}

			$post = get_post( absint( $_POST['post'] ) );

			ob_start();

			self::display_credit_notes_meta_box( $post );

			$html = ob_get_clean();

			wp_die( $html );
		}

		/**
		 * Check if order has credit notes.
		 *
		 * @param int $order_id order id.
		 *
		 * @return bool
		 */
		public static function order_has_credit_notes( $order_id ) {
			$order   = wc_get_order( $order_id );
			$refunds = $order->get_refunds();

			/**
			 * Annotation.
			 *
			 * @var WC_Order_Refund $refund refund object.
			 */
			foreach ( $refunds as $refund ) {
				$refund_id = BEWPI_WC_Order_Compatibility::get_id( $refund );
				if ( self::exists( $refund_id ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Output the credit note meta box.
		 *
		 * @param WP_Post $post post object.
		 */
		public static function display_credit_notes_meta_box( $post ) {
			$order = wc_get_order( $post->ID );

			echo '<ul class="credit-notes">';

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			if ( self::order_has_credit_notes( $order_id ) ) {

				/**
				 * Annotation.
				 *
				 * @var WC_Order_Refund $refund refund object.
				 */
				foreach ( $order->get_refunds() as $refund ) {

					$refund_id = BEWPI_WC_Order_Compatibility::get_id( $refund );
					if ( ! self::exists( $refund_id ) ) {
						continue;
					}

					$credit_note = new BEWPIP_Credit_Note( $refund_id );
					?>
					<li data-order_refund_id="<?php echo absint( $refund_id ); ?>" class="system-note credit-note">
						<div class="note_content">
							<?php
							$url = add_query_arg( array(
								'action'    => 'wpip_view_credit_note',
								'security'  => wp_create_nonce( 'credit-note' ),
								'refund_id' => $refund_id,
							), admin_url( 'admin-post.php' ) );

							/* translators: credit note. */
							$text = sprintf( __( '<a href="%1$s" class="%2$s" target="%3$s">PDF Credit Note (%4$s)</a> for Refund #%5$s.', 'woocommerce-pdf-invoices' ), $url, 'link wpip', '_blank', $credit_note->get_formatted_number(), $refund_id );
							echo wpautop( wptexturize( wp_kses_post( $text ) ) );
							?>
						</div>
						<p class="meta">
							<abbr class="exact-date"
							      title="<?php echo $credit_note->date; ?>"><?php printf( __( 'created on %1$s at %2$s', 'woocommerce-pdf-invoices' ), date_i18n( wc_date_format(), strtotime( $credit_note->date ) ), date_i18n( wc_time_format(), strtotime( $credit_note->date ) ) ); ?></abbr>
							<a href="#" class="delete-credit-note"
							   role="button"><?php _e( 'Delete', 'woocommerce-pdf-invoices' ); ?></a>
						</p>
					</li>
					<?php
				}
			} else {
				echo '<li>' . __( 'There are no credit notes yet.', 'woocommerce-pdf-invoices ' ) . '</li>';
			}

			echo '</ul>';
		}

		/**
		 * Add bulk action to create credit note for selected refunds.
		 *
		 * @param WC_Order $order order object.
		 */
		public static function add_bulk_action( $order ) {
			printf( '<button type="button" class="button bulk-credit-note-items" style="display: none;">%1$s</button>', __( 'Create PDF Credit Notes for selected refund(s)', 'woocommerce-pdf-invoices' ) );
		}

		/**
		 * Generate credit note for refund.
		 *
		 * @param int $order_id  Order ID.
		 * @param int $refund_id Refund Order ID.
		 */
		public static function generate_credit_note_for_refund( $order_id, $refund_id ) {
			$credit_note = new BEWPIP_Credit_Note( $refund_id );

			if ( ! $credit_note->get_full_path() ) {
				$credit_note->generate();
			}
		}

		/**
		 * Add PDF credit note generate button to meta box on order page when order has refunds.
		 *
		 * @param int $order_id order ID.
		 */
		public static function add_admin_order_credit_note_meta_box( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( count( $order->get_refunds() ) === 0 ) {
				return;
			}

			$action     = 'view_credit_note';
			$title      = __( 'Credit note', 'woocommerce-pdf-invoices' );
			$attributes = array( 'class="button grant_access order-page invoice wpi"' );

			$url = wp_nonce_url( add_query_arg( array(
				'post'         => $order_id,
				'action'       => 'edit',
				'bewpi_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url = apply_filters( 'bewpi_pdf_credit_note_url', $url, $order_id, $action );

			printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $title, join( ' ', $attributes ), $title );
		}

		/**
		 * Delete all credit notes on invoice deletion.
		 *
		 * @param WC_Order $order_id order object.
		 */
		public static function delete_credit_notes( $order_id ) {
			$order = wc_get_order( $order_id );

			foreach ( $order->get_refunds() as $refund ) {
				$refund_id = BEWPI_WC_Order_Compatibility::get_id( $refund );
				parent::delete( $refund_id );
			}
		}

		/**
		 * Add a custom email to the list of emails WooCommerce should load.
		 *
		 * @since 1.6.0
		 *
		 * @param array $email_classes available email classes.
		 *
		 * @return array filtered available email classes
		 */
		public static function add_customer_credit_note_email( $email_classes ) {
			$email_classes['BEWPI_WC_Email_Customer_Credit_Note'] = new BEWPI_WC_Email_Customer_Credit_Note();

			return $email_classes;
		}

		/**
		 * Add credit note email to order actions list.
		 *
		 * @param array $order_actions Email to resend.
		 *
		 * @return array
		 */
		public static function add_order_action( $order_actions ) {
			$order_actions['send_customer_credit_note'] = __( 'Email credit note to customer', 'woocommerce-pdf-invoices' );

			return $order_actions;
		}

		/**
		 * Add credit note email to order actions list.
		 *
		 * @param array $available_emails Email to resend.
		 *
		 * @return array
		 */
		public static function add_customer_credit_note_email_resend( $available_emails ) {
			$available_emails[] = 'bewpi_customer_credit_note';

			return $available_emails;
		}

		/**
		 * Format invoice number with placeholders.
		 *
		 * @return string
		 */
		public function get_formatted_number() {
			// format number with the number of digits.
			$digits           = WPI()->get_option( 'template', 'invoice_number_digits' );
			$digitized_number = sprintf( '%0' . $digits . 's', $this->number );
			$formatted_number = str_replace(
				array( '[number]', '[order-date]', '[order-number]', '[Y]', '[y]', '[m]' ),
				array(
					$digitized_number,
					date_i18n( apply_filters( 'bewpi_formatted_credit_note_number_order_date_format', 'Y-m-d' ), strtotime( $this->date ) ),
					$this->invoice->order->get_order_number(),
					$this->year,
					date_i18n( 'y', strtotime( $this->date ) ),
					date_i18n( 'm', strtotime( $this->date ) ),
				),
				WPI()->get_option( 'premium', 'credit_note_number_format' )
			);

			// Add prefix and suffix to formatted invoice number.
			$prefix           = WPI()->get_option( 'premium', 'credit_note_number_prefix' );
			$suffix           = WPI()->get_option( 'premium', 'credit_note_number_suffix' );
			$formatted_number = $prefix . $formatted_number . $suffix;

			return apply_filters( 'bewpi_formatted_credit_note_number', $formatted_number, $this->type );
		}

		/**
		 * Attach the Credit Note to the Refunded or Cancelled email.
		 *
		 * @param array  $attachments attachments.
		 * @param string $status      name of email.
		 * @param object $order       order.
		 *
		 * @return array
		 */
		public static function attach_credit_note_to_email( $attachments, $status, $order ) {
			// Only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $attachments;
			}

			if ( ! WPIP()->is_credit_note_email_enabled( $status ) ) {
				return $attachments;
			}

			$refunds = $order->get_refunds();
			if ( count( $refunds ) === 0 ) {
				return $attachments;
			}

			// An order should have an invoice in order to generate a credit note.
			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			if ( ! BEWPI_Abstract_Invoice::exists( $order_id ) ) {
				return $attachments;
			}

			$refund_id   = BEWPI_WC_Order_Compatibility::get_id( $refunds[0] );
			$credit_note = new BEWPIP_Credit_Note( $refund_id );
			if ( ! $credit_note->get_full_path() ) {
				$full_path = $credit_note->generate();
			} else {
				$full_path = $credit_note->update();
			}

			$attachments[] = $full_path;

			return $attachments;
		}

		/**
		 * Save invoice.
		 *
		 * @param string $destination pdf generation mode.
		 *
		 * @return string
		 */
		public function generate( $destination = 'F' ) {
			if ( false !== $this->full_path ) {
				self::delete( $this->full_path );
			}

			$refund_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

			$this->date = WPIP()->get_date_by_type( current_time( 'mysql' ), $this->invoice );
			$this->year = date_i18n( 'Y', strtotime( $this->date ) );


			// Use WooCommerce order numbers as invoice numbers?
			if ( 'woocommerce_order_number' === WPI()->get_option( 'invoice_number_type' ) ) {
				$this->number = $refund_id;
			} else {
				$this->number = $this->get_next_invoice_number();
			}

			$pdf_path        = $this->get_rel_pdf_path() . '/' . $this->get_formatted_number() . '.pdf';
			$this->full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;
			$this->filename  = basename( $this->full_path );

			// update invoice data in db.
			update_post_meta( $refund_id, '_bewpi_invoice_date', $this->date );
			update_post_meta( $refund_id, '_bewpi_invoice_number', $this->number );
			update_post_meta( $refund_id, '_bewpi_invoice_pdf_path', $pdf_path );

			do_action( 'bewpi_before_credit_note_generation', $this->type, $refund_id );

			BEWPI_Abstract_Document::generate( $destination );

			return $this->full_path;
		}

		/**
		 * Get email types for credit notes.
		 *
		 * @return array
		 */
		public static function get_email_types() {
			$email_types = array();

			/**
			 * Email annotation.
			 *
			 * @var WC_Email $email
			 */
			foreach ( WC()->mailer()->get_emails() as $email ) {
				$email_types[ $email->id ] = array(
					'name'    => $email->get_title(),
					'value'   => $email->id,
					'default' => 'bewpi_customer_credit_note' === $email->id ? 1 : 0,
				);
			}

			$email_types['customer_partially_refunded_order'] = array(
				'name'    => __( 'Partially Refunded Order', 'woocommerce-pdf-invoices' ),
				'value'   => 'customer_partially_refunded_order',
				'default' => 0,
			);

			return $email_types;
		}

		/**
		 * Get credit note details.
		 */
		public function get_details() {
			$details = array();

			// Add credit note details.
			$details['number'] = array(
				'title' => __( 'Credit Note #:', 'woocommerce-pdf-invoices' ),
				'value' => $this->get_formatted_number(),
			);

			$details['date'] = array(
				'title' => __( 'Date:', 'woocommerce-pdf-invoices' ),
				'value' => $this->get_formatted_date(),
			);

			// Add invoice details.
			if ( $this->invoice ) {
				$details['invoice_number'] = array(
					'title' => __( 'Invoice #:', 'woocommerce-pdf-invoices' ),
					'value' => $this->invoice->get_formatted_number(),
				);

				$details['invoice_date'] = array(
					'title' => __( 'Invoice Date:', 'woocommerce-pdf-invoices' ),
					'value' => $this->invoice->get_formatted_date(),
				);
			}

			// Add order details.
			$details['order_number'] = array(
				'title' => __( 'Order #:', 'woocommerce-pdf-invoices' ),
				'value' => $this->invoice->order->get_order_number(),
			);

			$details['order_date'] = array(
				'title' => __( 'Order Date:', 'woocommerce-pdf-invoices' ),
				'value' => $this->invoice->get_formatted_order_date(),
			);

			// Add payment details.
			$details['payment_method'] = array(
				'title' => __( 'Payment Method:', 'woocommerce-pdf-invoices' ),
				'value' => $this->invoice->order->get_payment_method_title(),
			);

			return apply_filters( 'wpi_credit_note_details', $details, $this );
		}

		/**
		 * Check if order has been fully refunded.
		 *
		 * @return bool
		 */
		public function order_is_fully_refunded() {
			return 'refunded' === $this->invoice->order->get_status();
		}

		/**
		 * Get refunded items.
		 *
		 * @return array.
		 */
		public function get_refunded_items() {
			$refunds        = $this->invoice->order->get_refunds();
			$refunded_order = $refunds[0];

			/** @var WC_Order_Refund $refunded_order */
			return $refunded_order->get_items();
		}

		/**
		 * Make non negative values negative.
		 *
		 * @param string $value value.
		 *
		 * @return string
		 */
		public static function maybe_make_negative( $value ) {
			preg_match( "'<span class=\"woocommerce-Price-amount amount\">(.*?)<span class=\"woocommerce-Price-currencySymbol\">'", $value, $matches );

			if ( count( $matches ) > 0 && '-' !== $matches[1] ) {
				$value = '-' . $value;
			}

			return $value;
		}

		/**
		 * Helper function to display data recursively.
		 *
		 * @param string $key  Column ID.
		 * @param        array /string $data Column or column label.
		 */
		public static function display_data_recursive( $key, $data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $k => $d ) {
					self::display_data_recursive( $k, $d );
				}

				return;
			}

			if ( 'quantity' === $key && strpos( $data, '-', 0 ) === false ) {
				$column_value = '-' . $data;
			} else {
				$column_value = self::maybe_make_negative( $data );
			}

			printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $column_value );
		}
	}
}
