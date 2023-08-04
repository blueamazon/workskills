<?php
/**
 * PDF Credit Note template body.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/credit-note/simple/yourtemplatename/body.php.
 *
 * HOWEVER, on occasion WooCommerce PDF Invoices will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Bas Elbers
 * @package WooCommerce_PDF_Invoices_Premium/Templates
 * @version 0.0.1
 */

$templater = WPI()->templater();
/** @var BEWPIP_Credit_Note $credit_note */
$credit_note = $templater->invoice;
/** @var WC_Order_Refund $order */
$order                      = $credit_note->order;
$parent_order               = $credit_note->invoice->order;
$formatted_shipping_address = $parent_order->get_formatted_shipping_address();
$formatted_billing_address  = $parent_order->get_formatted_billing_address();

// Refund order has no line items when fully refunded.
$is_fully_refunded = $credit_note->order_is_fully_refunded();
$line_items        = $is_fully_refunded ? $parent_order->get_items() : $credit_note->get_refunded_items();
$columns           = $is_fully_refunded ? $credit_note->invoice->get_columns() : $credit_note->get_columns();
$columns_data      = $is_fully_refunded ? $credit_note->invoice->get_columns_data( $line_items ) : $credit_note->get_columns_data( $line_items );
$order_item_totals = $is_fully_refunded ? $credit_note->invoice->get_order_item_totals() : $credit_note->get_order_item_totals();
?>

<table cellpadding="0" cellspacing="0">
    <tr class="title">
        <td colspan="3">
            <h2><?php _e( 'Credit Note', 'woocommerce-pdf-invoices' ); ?></h2>
        </td>
    </tr>
    <tr class="information">
        <td width="50%">
			<?php
			/**
			 * Invoice object.
			 *
			 * @var BEWPI_Invoice $invoice .
			 */
			foreach ( $credit_note->get_details() as $id => $info ) {
				printf( '<span class="%1$s">%2$s %3$s</span>', esc_attr( $id ), esc_html( $info['title'] ), esc_html( $info['value'] ) );
				echo '<br>';
			}
			?>
        </td>

        <td>
            <strong><?php _e( 'Bill to:', 'woocommerce-pdf-invoices' ); ?></strong><br>
			<?php echo $formatted_billing_address; ?>
        </td>

        <td>
			<?php
			if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $parent_order ) ) {
				echo '<strong>' . __( 'Ship to:', 'woocommerce-pdf-invoices' ) . '</strong><br>';
				echo $formatted_shipping_address;
			}
			?>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0">
    <thead>
    <tr class="heading" bgcolor="<?php echo WPI()->get_option( 'template', 'color_theme' ); ?>;">
		<?php
		foreach ( $columns as $key => $value ) {
			$templater->display_header_recursive( $key, $value );
		}
		?>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ( $columns_data as $index => $row ) {
		echo '<tr class="item">';

		// Display row data.
		foreach ( $row as $column_key => $data ) {
			BEWPIP_Credit_Note::display_data_recursive( $column_key, $data );
		}

		echo '</tr>';
	}
	?>

    <tr class="spacer">
        <td></td>
    </tr>

    </tbody>
</table>

<table cellpadding="0" cellspacing="0">
    <tbody>

	<?php
	foreach ( $order_item_totals as $key => $total ) {
		$class = str_replace( '_', '-', $key );
		?>

        <tr class="total">
            <td width="50%"></td>

            <td width="25%" align="left" class="border <?php echo esc_attr( $class ); ?>">
				<?php echo $total['label']; ?>
            </td>

            <td width="25%" align="right" class="border <?php echo esc_attr( $class ); ?>">
				<?php
				$total['value'] = $credit_note->maybe_make_negative( $total['value'] );
				echo str_replace( '&nbsp;', '', $total['value'] );
				?>
            </td>
        </tr>

	<?php } ?>
    </tbody>
</table>

<table class="notes" cellpadding="0" cellspacing="0">
    <tr>
        <td>
			<?php
			// Customer notes.
			if ( WPI()->get_option( 'template', 'show_customer_notes' ) ) {
				// Note added by customer.
				$customer_note = BEWPI_WC_Order_Compatibility::get_customer_note( $parent_order );
				if ( $customer_note ) {
					printf( '<strong>' . __( 'Note from customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br />', nl2br( $customer_note ) );
				}

				// Notes added by administrator on 'Edit Order' page.
				foreach ( $parent_order->get_customer_order_notes() as $custom_order_note ) {
					printf( '<strong>' . __( 'Note to customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br />', nl2br( $custom_order_note->comment_content ) );
				}
			}
			?>
        </td>
    </tr>

    <tr>
        <td>
			<?php
			// Zero Rated VAT message.
			if ( $credit_note->invoice->is_vat_exempt() ) {
				_e( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' );
				echo '<br>';
			}
			?>
        </td>
    </tr>
</table>

<?php
$terms = WPI()->get_option( 'template', 'terms' );
if ( $terms ) {
	?>
    <div class="terms">
        <table>
            <tr>
                <td style="border: 1px solid #000;">
					<?php echo nl2br( $terms ); ?>
                </td>
            </tr>
        </table>
    </div>
<?php } ?>
