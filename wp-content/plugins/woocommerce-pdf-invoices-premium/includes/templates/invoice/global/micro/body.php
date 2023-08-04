<?php
$columns_count = 4;
echo $this->outlining_columns_html( count( $this->taxes ) );
?>
<table class="two-column customer">
	<tbody>
	<tr>
		<td class="address small-font">
			<b><?php _e( 'Invoice to', 'woocommerce-pdf-invoices' ); ?></b><br/>
			<?php
			echo $this->order->get_formatted_billing_address() . '<br/>';
			// Billing phone.
			$billing_phone = method_exists( 'WC_Order', 'get_billing_phone' ) ? $this->order->get_billing_phone() : $this->order->billing_phone;
			echo $billing_phone ? sprintf( __( 'Phone: %s', 'woocommerce-pdf-invoices' ), $billing_phone ) : '';
			?>
		</td>
		<td class="address small-font">
			<?php if ( $this->order->get_formatted_shipping_address() != "" ) { ?>
				<b><?php _e( 'Ship to:', 'woocommerce-pdf-invoices' ); ?></b><br/>
				<?php echo $this->order->get_formatted_shipping_address(); ?>
			<?php } ?>
		</td>
	</tr>
	</tbody>
</table>
<table class="invoice-head">
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php _e( 'Global Invoice', 'woocommerce-pdf-invoices' ); ?></h1>
			<span class="number"
			      style="color: <?php echo $this->template_options['bewpi_color_theme']; ?>;"><?php echo $this->get_formatted_number(); ?></span><br/>
			<span class="small-font"><?php echo $this->get_formatted_invoice_date(); ?></span><br/><br/>
		</td>
		<td class="total-amount" bgcolor="<?php echo $this->template_options['bewpi_color_theme']; ?>">
				<span>
					<h1 class="amount"><?php echo $this->get_formatted_total(); ?></h1>
					<p class="small-font"><?php echo $this->template_options['bewpi_intro_text']; ?></p>
				</span>
		</td>
	</tr>
	</tbody>
</table>
<table class="products small-font">
	<thead>
	<tr class="table-headers">
		<!-- Description -->
		<th class="align-left"><?php _e( 'Description', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- SKU -->
		<?php
		if ( $this->template_options['bewpi_show_sku'] ) {
			$columns_count ++;
			echo '<th class="align-left">' . __( "SKU", 'woocommerce-pdf-invoices' ) . '</th>';
		}
		?>
		<!-- Cost -->
		<th class="align-left"><?php _e( 'Cost', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- Qty -->
		<th class="align-left"><?php _e( 'Qty', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- Tax -->
		<?php
		$order_taxes = $this->get_taxes();
		if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) && ! empty( $order_taxes ) ) :
			foreach ( $order_taxes as $tax_id => $tax_item ) :
				$columns_count ++;
				$tax_label    = __( 'VAT', 'woocommerce-pdf-invoices' );
				$column_label = ! empty( $tax_item->label ) ? $tax_item->label : $tax_label;
				?>
				<th class="align-left">
					<?php echo $column_label; ?>
				</th>
				<?php
			endforeach;
		endif;
		?>
		<!-- Total -->
		<th class="align-right"><?php _e( 'Total', 'woocommerce-pdf-invoices' ); ?></th>
	</tr>
	</thead>
	<!-- Products -->
	<tbody>
	<?php foreach ( $this->orders as $order ) :
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
		$order = wc_get_order( $order_id );
		$order_date = method_exists( 'WC_Order', 'get_date_created' ) ? $order->get_date_created() : $order->order_date;
		?>
		<tr>
			<td>
				<strong><?php printf( __( 'Order #%d - %s', 'woocommerce-pdf-invoices' ), $order->get_order_number(), date_i18n( $this->get_date_format(), strtotime( $order_date ) ) ); ?></strong>
			</td>
		</tr>
		<?php
		foreach ( $order->get_items( 'line_item' ) as $item_id => $item ) :
		$product = $order->get_product_from_item( $item ); ?>
		<tr class="product-row">
			<td>
				<!-- Title -->
				<?php
				echo esc_html( $item['name'] );

				WPI()->templater()->wc_display_item_meta( $item, true );
				WPI()->templater()->wc_display_item_downloads( $item, true );

				?>
			</td>
			<!-- SKU -->
			<?php if ( $this->template_options['bewpi_show_sku'] ) { ?>
				<td><?php echo ( $product && $product->get_sku() ) ? $product->get_sku() : '-'; ?></td>
			<?php } ?>
			<td>
				<!-- Line total -->
				<?php
				if ( isset( $item['line_total'] ) ) {
					echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $this->get_currency() ) );
				}
				?>
			</td>
			<td>
				<!-- Qty -->
				<?php echo $item['qty']; ?>
			</td>
			<?php
			if ( empty( $legacy_order ) && wc_tax_enabled() && $this->template_options['bewpi_show_tax'] ) :
				$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
				$tax_data = maybe_unserialize( $line_tax_data );

				foreach ( $this->get_taxes() as $tax_item ) :
					$tax_item_id = $tax_item->rate_id;
					$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
					$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
					?>

					<td class="item-tax">
						<!-- Tax -->
						<?php
						if ( '' != $tax_item_total ) {
							echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $this->get_currency() ) );
						} else {
							echo '&ndash;';
						}
						?>
					</td>

					<?php
				endforeach;
			endif;
			?>
			<td class="align-right item-total" width="">
				<!-- Item total -->
				<?php
				if ( isset( $item['line_total'] ) ) {
					$tax_display_cart = get_option( 'woocommerce_tax_display_cart' ) === 'incl';
					echo wc_price( $order->get_line_total( $item, $tax_display_cart, true ), array( 'currency' => $this->get_currency() ) );
				}
				?>
			</td>
		</tr>


	<?php endforeach; ?>
	<?php endforeach; ?>

	<!-- Space -->
	<tr class="space">
		<td colspan="<?php echo $columns_count; ?>"></td>
	</tr>
	<?php $colspan = $this->get_colspan( $columns_count ); ?>
	<!-- Table footers -->
	<!-- Discount -->
	<?php if ( $this->template_options['bewpi_show_discount'] && $this->get_total_discount() !== 0 ) { ?>
		<tr class="discount after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Discount', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>"
			    class="align-right"><?php echo wc_price( $this->get_total_discount(), array( 'currency' => $this->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping taxable -->
	<?php if ( $this->template_options['bewpi_show_shipping'] && (bool) $this->template_options["bewpi_shipping_taxable"] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Shipping', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>"
			    class="align-right"><?php echo wc_price( $this->get_total_shipping(), array( 'currency' => $this->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Subtotal -->
	<?php if ( $this->template_options['bewpi_show_subtotal'] ) { ?>
		<tr class="subtotal after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Subtotal', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>"
			    class="align-right"><?php echo wc_price( $this->get_subtotal(), array( 'currency' => $this->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping not taxable -->
	<?php if ( $this->template_options['bewpi_show_shipping'] && ! (bool) $this->template_options["bewpi_shipping_taxable"] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Shipping', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>"
			    class="align-right"><?php echo wc_price( $this->get_total_shipping(), array( 'currency' => $this->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Fees -->
	<?php
	$line_items_fee = $this->order->get_fees();
	foreach ( $line_items_fee as $item_id => $item ) :
		?>
		<tr class="after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php echo ! empty( $item['name'] ) ? esc_html( $item['name'] ) : __( 'Fee', 'woocommerce' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right">
				<?php
				echo ( isset( $item['line_total'] ) ) ? wc_price( wc_round_tax_total( $item['line_total'] ) ) : '';
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	<!-- Tax -->
	<?php if ( $this->template_options['bewpi_show_tax_total'] && wc_tax_enabled() ) :
		foreach ( $this->get_taxes() as $tax ) : ?>
			<tr class="after-products">
				<td colspan="<?php echo $colspan['left']; ?>"></td>
				<td colspan="<?php echo $colspan['right_left']; ?>"><?php echo $tax->label; ?></td>
				<td colspan="<?php echo $colspan['right_right']; ?>"
				    class="align-right"><?php echo wc_price( $tax->amount, array( 'currency' => $this->get_currency() ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<!-- Total -->
	<tr class="after-products">
		<td colspan="<?php echo $colspan['left']; ?>"></td>
		<td colspan="<?php echo $colspan['right_left']; ?>"
		    class="total"><?php _e( 'Total', 'woocommerce-pdf-invoices' ); ?></td>
		<td colspan="<?php echo $colspan['right_right']; ?>" class="grand-total align-right"
		    style="color: <?php echo $this->template_options['bewpi_color_theme']; ?>;"><?php echo $this->get_formatted_total(); ?></td>
	</tr>
	</tbody>
</table>
<table id="terms-notes">
	<!-- Notes & terms -->
	<tr>
		<td class="border" colspan="3">
			<?php echo nl2br( $this->template_options['bewpi_terms'] ); ?>
		</td>
	</tr>
</table>