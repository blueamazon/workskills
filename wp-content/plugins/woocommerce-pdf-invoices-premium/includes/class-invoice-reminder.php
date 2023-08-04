<?php
/**
 * Class that automatically loads fonts on runtime.
 *
 * The fonts need to be in uploads/woocommerce-pdf-invoices/fonts folder.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPIP_Invoice_Reminder' ) ) {

	/**
	 * Class BEWPIP_Invoice_Reminder.
	 */
	class BEWPIP_Invoice_Reminder {

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			if ( is_admin() ) {
				self::admin_init_hooks();
			}

			add_action( 'init', array( __CLASS__, 'init' ), 10, 1 );
			add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'add_order_meta' ), 10, 2 );
			add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_wc_reminder_email' ) );
			add_filter( 'woocommerce_email_attachments', array(
				__CLASS__,
				'attach_pdf_to_customer_invoice_reminder_email',
			), 99, 3 );
			add_filter( 'wpi_invoice_information_meta', array( __CLASS__, 'add_pdf_invoice_due_date' ), 10, 2 );
		}

		/**
		 * Initialization hook.
		 */
		public static function init() {
			add_action( 'wpi_scheduled_reminder', array( __CLASS__, 'trigger_reminder_emails' ) );
		}

		/**
		 * Initialize admin hooks.
		 */
		public static function admin_init_hooks() {
			add_filter( 'bewpi_order_page_pdf_invoice_meta_box_details', array(
				__CLASS__,
				'add_admin_order_reminder_meta_box_details',
			), 10, 2 );
		}

		/**
		 * Is reminder feature enabled?
		 *
		 * @return bool
		 */
		public static function reminder_enabled() {
			return (bool) WPI()->get_option( 'premium', 'enable_reminder' );
		}

		/**
		 * Add reminder order meta.
		 *
		 * @param int   $order_id Order ID.
		 * @param array $posted   Post data.
		 */
		public static function add_order_meta( $order_id, $posted ) {
			if ( ! self::reminder_enabled() ) {
				return;
			}

			$order = wc_get_order( $order_id );
			$order->update_meta_data( 'bewpi_days_until_reminder', WPI()->get_option( 'premium', 'days_until_reminder' ) );
			$order->update_meta_data( 'bewpi_days_until_due', WPI()->get_option( 'premium', 'days_until_due' ) );
			$order->save();
		}

		/**
		 * Add PDF Invoice Reminder details to meta box on order page.
		 *
		 * @param array         $details Invoice information to display.
		 * @param BEWPI_Invoice $invoice The invoice object.
		 *
		 * @return array.
		 */
		public static function add_admin_order_reminder_meta_box_details( $details, $invoice ) {
			if ( ! self::reminder_enabled() ) {
				return $details;
			}

			$reminder_date = self::get_reminder_date( $invoice->order );
			$sent          = WPI()->get_meta( $invoice->order, '_bewpi_reminder_sent' ) ? __( 'Yes', 'woocommerce-pdf-invoices' ) : __( 'No', 'woocommerce-pdf-invoices' );

			$details['reminder_scheduled_on'] = array(
				'title' => __( 'Reminder on:', 'woocommerce-pdf-invoices' ),
				'value' => $reminder_date->format( $invoice->get_date_format() ),
			);

			$details['reminder_sent'] = array(
				'title' => __( 'Reminder sent?', 'woocommerce-pdf-invoices' ),
				'value' => $sent,
			);

			return $details;
		}

		/**
		 * Daily event to send reminder email with PDF Invoice.
		 */
		public static function trigger_reminder_emails() {
			if ( ! self::reminder_enabled() ) {
				return;
			}

			$args = array(
				'numberposts' => - 1,
				'post_type'   => 'shop_order',
				'post_status' => apply_filters( 'wpip_reminder_order_statuses', array(
					'wc-on-hold',
				) ),
				'meta_query'  => array(
					array(
						'key'     => '_bewpi_reminder_sent',
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
					array(
						'key'     => '_payment_method',
						'compare' => 'IN',
						'value'   => apply_filters( 'wpip_reminder_payment_methods', array(
							'bacs',
							'cheque',
							'cod',
						) ),
					),
				),
				'fields'      => 'ids',
			);

			$query    = new WP_Query( $args );
			$post_ids = $query->get_posts();

			if ( count( $post_ids ) === 0 ) {
				return;
			}

			$emails = WC()->mailer()->get_emails();

			foreach ( $post_ids as $post_id ) {
				$order         = wc_get_order( $post_id );
				$reminder_date = self::get_reminder_date( $order );
				$today         = new DateTime();
				// Compare today's date with reminder date in order to get a negative number of days.
				$days_until_reminder = $today->diff( $reminder_date )->format( '%r%a' );
				if ( 0 >= $days_until_reminder ) {

					$email_class = apply_filters( 'bewpi_reminder_email_class', 'BEWPI_WC_Email_Customer_Invoice_Reminder', $order );
					/**
					 * Email annotation.
					 *
					 * @var BEWPI_WC_Email_Customer_Invoice_Reminder $email Customer invoice reminder email object.
					 */
					$email    = $emails[ $email_class ];
					$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
					$email->trigger( $order_id );
					update_post_meta( $order_id, '_bewpi_reminder_sent', true );
				}
			}
		}

		/**
		 * Get date to count from.
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @return DateTime/null
		 */
		private static function get_date( $order ) {
			if ( 'order_date' === WPI()->get_option( 'premium', 'reminder_date_type' ) ) {
				$date = BEWPI_WC_Order_Compatibility::get_date_created( $order );
			} else {
				$invoice_date = WPI()->get_meta( $order, '_bewpi_invoice_date' );

				if ( $invoice_date ) {
					$date = new DateTime( $invoice_date );
				} else {
					$date = null;
				}
			}

			return $date;
		}

		/**
		 * Get the due date.
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @return DateTime
		 */
		public static function get_due_date( $order ) {
			$date = self::get_date( $order );

			if ( null !== $date ) {
				$days_until_due = WPI()->get_meta( $order, 'bewpi_days_until_due' );

				// backporting.
				if ( ! $days_until_due ) {
					$days_until_due = WPI()->get_option( 'premium', 'days_until_due' );
				}

				$date->modify( sprintf( '+%d day', (int) $days_until_due ) );
			}

			return $date;
		}

		/**
		 * Get the order date incremented with the days until reminder date.
		 *
		 * @param WC_Order $order WooCommerce Order object.
		 *
		 * @return DateTime
		 */
		public static function get_reminder_date( $order ) {
			$date = self::get_date( $order );

			if ( null !== $date ) {
				$days_until_reminder = WPI()->get_meta( $order, 'bewpi_days_until_reminder' );

				// backporting.
				if ( ! $days_until_reminder ) {
					$days_until_reminder = WPI()->get_option( 'premium', 'days_until_reminder' );
				}

				$date->modify( sprintf( '+%d day', (int) $days_until_reminder ) );
			}

			return $date;
		}

		/**
		 * Attach PDF invoice to reminder email.
		 *
		 * @param array  $attachments Email attachments.
		 * @param string $status      Name of email.
		 * @param object $order       Product or WC_Order etc.
		 *
		 * @return array
		 */
		public static function attach_pdf_to_customer_invoice_reminder_email( $attachments, $status, $order ) {
			if ( 'bewpi_customer_invoice_reminder' !== $status ) {
				return $attachments;
			}

			if ( ! $order instanceof WC_Order ) {
				return $attachments;
			}

			$order_id  = BEWPI_WC_Order_Compatibility::get_id( $order );
			$invoice   = new BEWPI_Invoice( $order_id );
			$full_path = $invoice->get_full_path();
			if ( ! $full_path ) {
				$full_path = $invoice->generate();
			} elseif ( ! $invoice->is_sent() ) {
				// Only update PDF invoice when client doesn't got it already.
				$full_path = $invoice->update();
			}

			$attachments[] = $full_path;

			return $attachments;
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
		public static function add_wc_reminder_email( $email_classes ) {
			$email_classes['BEWPI_WC_Email_Customer_Invoice_Reminder'] = new BEWPI_WC_Email_Customer_Invoice_Reminder();

			return $email_classes;
		}

		/**
		 * Add Due Date to PDF invoice.
		 * Display after Invoice Date.
		 *
		 * @param array                  $info    Invoice info.
		 * @param BEWPI_Abstract_Invoice $invoice invoice object.
		 *
		 * @return array.
		 */
		public static function add_pdf_invoice_due_date( $info, $invoice ) {
			if ( ! self::reminder_enabled() ) {
				return $info;
			}

			$due_date = self::get_due_date( $invoice->order );

			if ( null === $due_date ) {
				return $info;
			}

			// Place invoice due date after invoice date.
			$offset = array_search( 'invoice_date', array_keys( $info ), true );
			$info   = array_merge(
				array_splice( $info, 0, $offset + 1 ),
				array(
					'invoice_due_date' => array(
						'title' => __( 'Due Date:', 'woocommerce-pdf-invoices' ),
						'value' => date_i18n( $invoice->get_date_format(), strtotime( $due_date->format( 'Y-m-d H:i:s' ) ) ),
					),
				),
				$info
			);

			return $info;
		}
	}
}
