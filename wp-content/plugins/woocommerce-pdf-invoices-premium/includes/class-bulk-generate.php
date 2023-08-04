<?php
/**
 * Bulk Generate class.
 *
 * Adds a PDF invoices bulk generation action to the WooCommerce actions selectlist.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

if ( ! class_exists( 'BEWPIP_Bulk_Generate' ) ) {
	/**
	 * Class BEWPIP_Bulk_Generate.
	 */
	class BEWPIP_Bulk_Generate {

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			add_action( 'admin_footer-edit.php', array( __CLASS__, 'add_bulk_generate_actions' ) );
			add_action( 'load-edit.php', array( __CLASS__, 'bulk_generate_action' ) );
		}

		/**
		 * Adds a bulk export action to export all invoices to zip.
		 */
		public static function add_bulk_generate_actions() {
			global $post_type;

			if ( 'shop_order' === $post_type ) {
				?>
				<script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery('<option>').val('bulk_generate_pdf_invoices').text('<?php _e( 'Bulk Generate PDF Invoices', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
                        jQuery('<option>').val('bulk_generate_pdf_packing_slips').text('<?php _e( 'Bulk Generate PDF Packing Slips', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
                        jQuery('<option>').val('bulk_generate_pdf_credit_notes').text('<?php _e( 'Bulk Generate PDF Credit Notes', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
                    });
				</script>
				<?php
			}
		}

		/**
		 * Callback to bulk export all invoices to zip.
		 */
		public static function bulk_generate_action() {
			global $typenow;
			$post_type = $typenow;

			// Are we on order page?
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			// Get the action.
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();
			if ( ! in_array( $action, array(
				'bulk_generate_pdf_invoices',
				'bulk_generate_pdf_packing_slips',
				'bulk_generate_pdf_credit_notes',
			), true )
			) {
				return;
			}

			// Security check.
			check_admin_referer( 'bulk-posts' );

			$post_ids = array();
			if ( isset( $_REQUEST['post'] ) ) {
				$post_ids = array_map( 'intval', $_REQUEST['post'] );
			}

			if ( count( $post_ids ) === 0 ) {
				wp_die( __( 'No order selected.', 'woocommerce-pdf-invoices' ), '', array(
					'response'  => 200,
					'back_link' => true
				) );
			}

			sort( $post_ids );

			switch ( $action ) {
				case 'bulk_generate_pdf_invoices':
					self::bulk_generate_pdf_invoices( $post_ids );
					break;
				case 'bulk_generate_pdf_packing_slips':
					self::bulk_generate_pdf_packing_slips( $post_ids );
					break;
				case 'bulk_generate_pdf_credit_notes':
					self::bulk_generate_pdf_credit_notes( $post_ids );
					break;
			}
		}

		/**
		 * Bulk generate selected invoices.
		 *
		 * @param array $order_ids WooCommerce Order ID's.
		 */
		public static function bulk_generate_pdf_invoices( $order_ids ) {
			foreach ( $order_ids as $order_id ) {
				$invoice = new BEWPI_Invoice( $order_id );

				if ( ! $invoice->get_full_path() ) {
					$invoice->generate();
				} elseif ( ! $invoice->is_sent() ) {
					$invoice->update();
				}
			}

			do_action( 'bewpip_bulk_generate_pdf_invoices_end', $order_ids );
		}

		/**
		 * Bulk generate selected credit notes.
		 *
		 * @param array $order_ids WooCommerce Order ID's.
		 */
		public static function bulk_generate_pdf_credit_notes( $order_ids ) {
			foreach ( $order_ids as $order_id ) {
				$order   = wc_get_order( $order_id );
				$refunds = $order->get_refunds();

				if ( count( $refunds ) === 0 ) {
					continue;
				}

				/** @var WC_Order_Refund $refund */
				$refund  = $refunds[0];
				$invoice = new BEWPIP_Credit_Note( $refund->get_id() );
				if ( ! $invoice->get_full_path() ) {
					$invoice->generate();
				} else {
					$invoice->update();
				}
			}

			do_action( 'bewpip_bulk_generate_pdf_credit_notes_end', $order_ids );
		}

		/**
		 * Bulk generate selected PDF Packing Slips.
		 *
		 * @param array $order_ids WooCommerce Order ID's.
		 */
		public static function bulk_generate_pdf_packing_slips( $order_ids ) {

			foreach ( $order_ids as $order_id ) {
				$packing_slip = new BEWPI_Packing_Slip( $order_id );
				$packing_slip->generate();
			}

			do_action( 'bewpip_bulk_generate_pdf_packing_slips_end', $order_ids );
		}
	}
}
