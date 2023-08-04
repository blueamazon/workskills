<?php
/**
 * Bulk Print class.
 *
 * Adds a bulk action to the WooCommerce actions selectlist.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

/**
 * Class BEWPIP_Bulk_Export.
 */
class BEWPIP_Bulk_Print {
	/**
	 * Initialize hooks.
	 */
	public static function init_hooks() {
		add_filter( 'bewpi_mpdf_after_write', array( __CLASS__, 'merge_pdf_packing_slips' ), 10, 2 );
		add_action( 'admin_footer-edit.php', array( __CLASS__, 'add_bulk_print_actions' ) );
		add_action( 'load-edit.php', array( __CLASS__, 'bulk_print_action' ) );
	}

	/**
	 * Adds bulk export actions to export PDF documents to zip file.
	 */
	public static function add_bulk_print_actions() {
		global $post_type;

		if ( 'shop_order' === $post_type ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('<option>').val('bulk_print_pdf_packing_slips').text('<?php _e( 'Bulk Print PDF Packing Slips', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
				});
			</script>
			<?php
		}
	}

	/**
	 * Is action request.
	 *
	 * @return bool
	 */
	private static function is_bulk_action() {
		global $typenow;

		if ( 'shop_order' !== $typenow ) {
			return false;
		}

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		return in_array( $action, array( 'bulk_print_pdf_packing_slips' ), true );
	}

	/**
	 * Callback to bulk print selected pdf packing slips.
	 */
	public static function bulk_print_action() {
		if ( ! self::is_bulk_action() ) {
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
				'back_link' => true,
			) );
		}

		$order_id     = $post_ids[0];
		$packing_slip = new BEWPI_Packing_Slip( $order_id );
		$full_path    = $packing_slip->generate();

		$packing_slip::view( $full_path );
	}

	/**
	 * Merge multiple PDF packing slips.
	 *
	 * @param mPDF                    $mpdf mpdf object.
	 * @param BEWPI_Abstract_Document $document document object.
	 *
	 * @return mPDF.
	 */
	public static function merge_pdf_packing_slips( $mpdf, $document ) {
		if ( ! self::is_bulk_action() ) {
			return $mpdf;
		}

		// Only add invoices to the first generated pdf invoice.
		remove_filter( 'bewpi_mpdf_after_write', array( __CLASS__, 'merge_pdf_packing_slips' ) );

		$post_ids = array_map( 'intval', $_REQUEST['post'] );

		// Remove the first pdf invoice since we use it to add the other invoice to.
		array_shift( $post_ids );

		$mpdf->SetImportUse();

		foreach ( $post_ids as $order_id ) {
			$packing_slip = new BEWPI_Packing_Slip( $order_id );
			$full_path    = $packing_slip->generate();

			$page_count = $mpdf->setSourceFile( $full_path );
			for ( $i = 1; $i <= $page_count; $i ++ ) {
				$mpdf->AddPage( '', '', 1, '', '', '', '', '', '', '', '', '', '', '', '', - 1, 0, - 1, 0 );
				$template_id = $mpdf->importPage( $i );
				$mpdf->useTemplate( $template_id );
			}
		}

		return $mpdf;
	}
}
