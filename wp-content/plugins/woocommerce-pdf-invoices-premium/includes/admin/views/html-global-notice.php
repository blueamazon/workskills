<?php
/**
 * View global invoice admin notice
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Admin
 * @version     1.0.0
 */

$order_id   = intval( $_GET['order_id'] );
$url        = admin_url() . 'edit.php?post_type=shop_order&bewpi_action=view_global_invoice&nonce=' . wp_create_nonce( 'view_global_invoice' ) . '&post=' . $order_id;
?>
<div class="updated notice notice-success" data-dismissible="global-forever">
	<p>
		<?php echo __( 'Global invoice successfully generated!', 'woocommerce-pdf-invoices' ); ?>
		<a href="<?php echo esc_url( $url ); ?>" class="bewpi-admin-notice-view-global-invoice-btn invoice-btn button grant_access" title="<?php _e( 'View invoice', 'woocommerce-pdf-invoices' ); ?>"><?php _e( 'View', 'woocommerce-pdf-invoices' ); ?></a>
	</p>
</div>
