<style>
	.my-account.bewpi-generate-global-invoice select {
		padding: 6px 17.5px;
		margin: 0 3px 0 0;
	}
	.my-account.bewpi-generate-global-invoice input[type="submit"] {
		margin: 0 0 0 3px;
	}
</style>
<h2><?php _e( 'Generate Global Invoice', 'woocommerce-pdf-invoices' ); ?></h2>
<div class="my-account bewpi-generate-global-invoice">
	<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
		<?php
		$current_user_id    = get_current_user_id();
		$premium_options    = get_option( 'bewpipremium_settings' );
		$period             = $premium_options[ 'bewpi_customer_generation_period' ];
		if ( $period === 'month' ) { ?>

			<select name="month" required>
				<option value="" disabled selected="selected"> <?php echo __( 'Select a month', 'woocommerce-pdf-invoices' ); ?></option>

				<?php
				$months = get_order_months( $current_user_id, 'F' );
				foreach ( $months as $month ) { ?>
					<option value="<?php echo $month[ 'num' ]; ?>"><?php echo $month[ 'name' ]; ?></option>
				<?php } ?>

			</select>

		<?php } else { ?>

			<select name="year" required>
				<option value="" disabled selected="selected"> <?php echo __( 'Select a year', 'woocommerce-pdf-invoices' ); ?></option>

				<?php
				$years = get_order_years( $current_user_id );
				foreach ( $years as $year ) { ?>
					<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
				<?php } ?>

			</select>

		<?php } ?>

		<input type="hidden" name="action" value="create_global_invoice_by_<?php echo $period; ?>"/>
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'create_global_invoice_by_' . $period ); ?>"/>
		<input type="submit" value="<?php _e( 'Generate invoice', 'woocommerce-pdf-invoices' ); ?>"/>
	</form>
</div>