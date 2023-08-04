<?php
/**
 * Customer Credit Note.
 *
 * An email sent to the customer via admin.
 *
 * @class       BEWPI_WC_Email_Customer_Credit_Note
 * @version     0.0.1
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Classes/Emails
 * @author      Bas Elbers
 * @extends     WC_Email
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPI_WC_Email_Customer_Credit_Note' ) ) {

	/**
	 * Class BEWPI_WC_Email_Customer_Credit_Note.
	 */
	class BEWPI_WC_Email_Customer_Credit_Note extends WC_Email {

		/**
		 * Strings to find in subjects/headings.
		 *
		 * @var array
		 */
		public $find;

		/**
		 * Strings to replace in subjects/headings.
		 *
		 * @var array
		 */
		public $replace;

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id          = 'bewpi_customer_credit_note';
			$this->title       = __( 'Customer credit note', 'woocommerce-pdf-invoices' );
			$this->description = __( 'Customer credit note emails can be sent to customers containing their PDF Credit Note.', 'woocommerce-pdf-invoices' );

			$this->template_base  = trailingslashit( BEWPIP_DIR . '/includes/templates' );
			$this->template_html  = 'emails/customer-invoice.php';
			$this->template_plain = 'emails/plain/customer-invoice.php';

			$this->subject = __( 'Credit note for order {order_number} from {order_date}', 'woocommerce-pdf-invoices' );
			$this->heading = __( 'Credit note for order {order_number}', 'woocommerce-pdf-invoices' );

			parent::__construct();

			$this->customer_email = true;
			$this->manual         = true;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $order_id The order ID.
		 * @param WC_Order $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {
			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object    = $order;
				$this->recipient = $this->object->get_billing_email();

				$this->find['order-date']   = '{order_date}';
				$this->find['order-number'] = '{order_number}';

				$this->replace['order-date']   = wc_format_datetime( $this->object->get_date_created() );
				$this->replace['order-number'] = $this->object->get_order_number();
			}

			if ( ! $this->get_recipient() ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get email subject.
		 *
		 * @access public
		 * @return string
		 */
		public function get_subject() {
			return apply_filters( 'bewpip_woocommerce_email_subject_customer_credit_note', $this->format_string( $this->subject ), $this->object );
		}

		/**
		 * Get email heading.
		 *
		 * @access public
		 * @return string
		 */
		public function get_heading() {
			return apply_filters( 'bewpip_woocommerce_email_heading_customer_credit_note', $this->format_string( $this->heading ), $this->object );
		}

		/**
		 * Get content html.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html( $this->template_html, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			) );
		}

		/**
		 * Get content plain.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html( $this->template_plain, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			) );
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'subject'    => array(
					'title'       => __( 'Email subject', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: default subject */
					'description' => sprintf( __( 'Defaults to %s', 'woocommerce' ), '<code>' . $this->subject . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: default heading */
					'description' => sprintf( __( 'Defaults to %s', 'woocommerce' ), '<code>' . $this->heading . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}
}
