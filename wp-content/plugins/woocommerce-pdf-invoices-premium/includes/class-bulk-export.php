<?php
/**
 * Bulk Export class.
 *
 * Adds a bulk action to the WooCommerce actions selectlist.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

if ( ! class_exists( 'BEWPIP_Bulk_Export' ) ) {
	/**
	 * Class BEWPIP_Bulk_Export.
	 */
	class BEWPIP_Bulk_Export {

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			add_action( 'admin_footer-edit.php', array( __CLASS__, 'add_bulk_export_actions' ) );
			add_action( 'load-edit.php', array( __CLASS__, 'bulk_export_action' ) );
		}

		/**
		 * Adds bulk export actions to export PDF documents to zip file.
		 */
		public static function add_bulk_export_actions() {
			global $post_type;

			if ( 'shop_order' === $post_type ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery('<option>').val('bulk_export_pdf_invoices').text('<?php _e( 'Bulk Export PDF Invoices', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
						jQuery('<option>').val('bulk_export_pdf_packing_slips').text('<?php _e( 'Bulk Export PDF Packing Slips', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
					});
				</script>
				<?php
			}
		}

		/**
		 * Callback to bulk export all invoices to zip.
		 */
		public static function bulk_export_action() {
			global $typenow;
			$post_type = $typenow;

			// Are we on order page?
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			// Get the action.
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();
			if ( ! in_array( $action, array( 'bulk_export_pdf_invoices', 'bulk_export_pdf_packing_slips' ), true ) ) {
				return;
			}

			// Security check.
			check_admin_referer( 'bulk-posts' );

			$post_ids = array();
			if ( isset( $_REQUEST['post'] ) ) {
				$post_ids = array_map( 'intval', $_REQUEST['post'] );
			}

			switch ( $action ) {
				case 'bulk_export_pdf_invoices':

					if ( count( $post_ids ) === 0 ) {
						self::bulk_export_all_pdf_invoices();
					} else {
						self::bulk_export_pdf_invoices( $post_ids );
					}

					break;
				case 'bulk_export_pdf_packing_slips':

					if ( count( $post_ids ) === 0 ) {
						wp_die( __( 'No order selected.', 'woocommerce-pdf-invoices' ), '', array(
							'response'  => 200,
							'back_link' => true,
						) );
					}

					self::bulk_export_pdf_packing_slips( $post_ids );

					break;
			}

		}

		/**
		 * Bulk export PDF invoices to ZIP.
		 *
		 * @param array $order_ids WooCommerce Order ID's.
		 */
		public static function bulk_export_pdf_invoices( $order_ids ) {
			$zip           = new ZipArchive;
			$zipname       = apply_filters( 'bewpi_bulk_export_pdf_invoices_zipname', date_i18n( 'Y-m-d-His' ) . '.zip' );
			$zip_full_path = WPI_ATTACHMENTS_DIR . '/' . $zipname;
			$zip->open( $zip_full_path, ZipArchive::CREATE );

			foreach ( $order_ids as $order_id ) {
				$pdf_path = get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
				if ( ! $pdf_path ) {
					continue;
				}

				$pdf_full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;
				if ( ! BEWPI_Abstract_Document::exists( $pdf_full_path ) ) {
					continue;
				}

				// use $pdf_path and create year folder to prevent filename collisions.
				$zip->addFile( $pdf_full_path, $pdf_path );
			}

			$zip->close();

			header( 'Content-Type: application/zip' );
			header( 'Content-Disposition: attachment; filename="' . basename( $zip_full_path ) . '"' );
			header( 'Content-Length: ' . filesize( $zip_full_path ) );
			readfile( $zip_full_path );
			exit;
		}

		/**
		 * Create zip, add pdf invoices and download zip.
		 */
		public static function bulk_export_all_pdf_invoices() {
			$zip           = new ZipArchive;
			$zipname       = apply_filters( 'bewpi_bulk_export_pdf_invoices_zipname', date_i18n( 'Y-m-d-His' ) . '.zip' );
			$zip_full_path = WPI_ATTACHMENTS_DIR . '/' . $zipname;
			$zip->open( $zip_full_path, ZipArchive::CREATE );

			foreach ( glob( WPI_ATTACHMENTS_DIR . '*' ) as $file ) {
				// Add pdf file.
				if ( is_file( $file ) && 'pdf' === pathinfo( $file, PATHINFO_EXTENSION ) ) {
					$zip->addFile( $file, basename( $file ) );
					continue;
				}

				// iterate over directory and add pdf files.
				if ( is_dir( $file ) ) {
					foreach ( glob( $file . '/*.pdf' ) as $pdf_file ) {
						$zip->addFile( $pdf_file, basename( dirname( $pdf_file ) ) . '/' . basename( $pdf_file ) );
					}
				}
			}

			$zip->close();

			header( 'Content-Type: application/zip' );
			header( 'Content-Disposition: attachment; filename="' . basename( $zip_full_path ) . '"' );
			header( 'Content-Length: ' . filesize( $zip_full_path ) );
			readfile( $zip_full_path );
			exit;
		}

		/**
		 * Bulk export Packing slips to ZIP.
		 *
		 * @param array $order_ids WooCommerce Order ID's.
		 */
		public static function bulk_export_pdf_packing_slips( $order_ids ) {
			$zip           = new ZipArchive;
			$zipname       = apply_filters( 'bewpi_bulk_export_packing_slips_zipname', date_i18n( 'Y-m-d-His' ) . '.zip' );
			$zip_full_path = WPI_ATTACHMENTS_DIR . '/' . $zipname;
			$zip->open( $zip_full_path, ZipArchive::CREATE );

			foreach ( $order_ids as $order_id ) {
				$packing_slip = new BEWPI_Packing_Slip( $order_id );
				$pdf_path     = $packing_slip->get_pdf_path();

				$pdf_full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;
				if ( ! BEWPI_Abstract_Document::exists( $pdf_full_path ) ) {
					$packing_slip  = new BEWPI_Packing_Slip( $order_id );
					$pdf_full_path = $packing_slip->generate();

					if ( ! file_exists( $pdf_full_path ) ) {
						continue;
					}
				}

				// Use $pdf_path and create year folder to prevent filename collisions.
				$zip->addFile( $pdf_full_path, $pdf_path );
			}

			$zip->close();

			if ( file_exists( $zip_full_path ) ) {
				header( 'Content-Type: application/zip' );
				header( 'Content-Disposition: attachment; filename="' . basename( $zip_full_path ) . '"' );
				header( 'Content-Length: ' . filesize( $zip_full_path ) );
				readfile( $zip_full_path );
				exit;
			}
		}
	}
}
