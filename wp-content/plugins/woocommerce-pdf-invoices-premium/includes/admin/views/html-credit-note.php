<?php
/**
 * View credit note button html.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Admin
 * @version     1.0.0
 */

$action     = 'view_credit_note';
$title      = sprintf( __( 'PDF Credit Note %1$s', 'woocommerce-pdf-invoices' ), $credit_note->get_formatted_number() );
$attributes = array( 'class="button grant_access order-page invoice wpi"', 'target="_blank"' );

$url = wp_nonce_url( add_query_arg( array(
	'post' => $order_id,
	'action' => 'edit',
	'bewpi_action' => $action,
), admin_url( 'post.php' ) ), $action, 'nonce' );

$url = apply_filters( 'bewpi_pdf_credit_note_url', $url, $order_id, $action );

printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $title, join( ' ', $attributes ), $title );
