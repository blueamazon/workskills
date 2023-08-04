<?php
function get_customer_orders( $user_id ) {
	$orders = get_posts( array(
		'numberposts'   => -1,
		'meta_key'      => '_customer_user',
		'meta_value'    => $user_id,
		'post_type'     => wc_get_order_types(),
		'post_status'   => array_keys( wc_get_order_statuses() ),
		'orderby'       => 'post_date',
		'order'         => 'ASC'
	) );

	return $orders;
}

function get_customer_orders_by_year( $user_id, $year ) {
	$orders = get_posts( array(
		'numberposts'   => -1,
		'meta_key'      => '_customer_user',
		'meta_value'    => $user_id,
		'post_type'     => wc_get_order_types(),
		'post_status'   => array_keys( wc_get_order_statuses() ),
		'year'          => $year,
		'orderby'       => 'post_date',
		'order'         => 'ASC',
		'fields'        => 'ids',
	) );

	return $orders;
}

function get_customer_orders_by_month( $user_id, $monthnum ) {
	$orders = get_posts( array(
		'numberposts'   => -1,
		'meta_key'      => '_customer_user',
		'meta_value'    => $user_id,
		'post_type'     => wc_get_order_types(),
		'post_status'   => array_keys( wc_get_order_statuses() ),
		'monthnum'      => $monthnum,
		'orderby'       => 'post_date',
		'order'         => 'ASC',
		'fields'        => 'ids',
	) );

	return $orders;
}

/**
 * @param $user_id
 * @param $date_format
 * Get order years by using date format 'Y' or months by 'm' or 'F'
 * @return array
 */
function get_order_months( $user_id, $month_format = 'F' ) {
	$orders = get_customer_orders( $user_id );

	$months = array();
	foreach ( $orders as $order ) {
		$month_name     = date_i18n( $month_format, strtotime( $order->post_date ) );
		$month_number   = date_i18n( 'm', strtotime( $order->post_date ) );

		$month = array(
			"name"  => $month_name,
			"num"   => $month_number
		);
		if ( ! in_array( $month, $months ) )
			$months[] = $month;
	}

	return $months;
}

function get_order_years( $user_id, $date_format = 'Y' ) {
	$orders = get_customer_orders( $user_id );

	$years = array();
	foreach ( $orders as $order ) {
		$year = date( $date_format, strtotime( $order->post_date ) );

		if ( ! in_array( $year, $years ) )
			$years[] = $year;
	}

	return $years;
}
