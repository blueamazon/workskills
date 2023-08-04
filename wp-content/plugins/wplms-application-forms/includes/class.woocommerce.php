<?php
/**
 *
 * @class       WPLMS_Application_WooCommerce
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPLMS_Application_WooCommerce{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPLMS_Application_WooCommerce();
	    return self::$instance;
	}

	private function __construct(){
		$lms_settings = get_option('lms_settings');
				
		add_filter('lms_general_settings',array($this,'generate_waf_form'),99);
		
		if( !isset( $lms_settings['general']['sold_individually'] ) ||  !$lms_settings['general']['sold_individually'] )
			add_filter( 'woocommerce_is_sold_individually', array($this,'sold_individually'), 10, 2 );
		
		if( !isset( $lms_settings['general']['one_time_purchase'] ) ||  !$lms_settings['general']['one_time_purchase'] ){
			add_filter( 'woocommerce_is_purchasable', array($this,'disable_repeat_purchase'), 10, 2 );
			add_action( 'woocommerce_single_product_summary', array($this,'purchase_disabled_message'), 31 );
		}
		add_action( 'woocommerce_remove_cart_item_from_session', array($this,'remove_course_notice'), 10, 2);
		add_filter( 'woocommerce_add_error', array($this,'cant_purchase_notice') );
		
		add_filter( 'woocommerce_return_to_shop_redirect',  array($this, 'shop_url' ) );
		
		add_filter('wplms_course_product_id',array($this,'wplms_course_order_url'),10,2);
		add_filter('wplms_take_this_course_button_label',array($this,'wplms_course_payment_on_hold_label'),99,2);

	} // END public function __construct
	
    function generate_waf_form($settings){
    	if( isset($_GET['sub']) && !empty($_GET['sub']) && $_GET['sub'] != 'general')
    		return $settings;

    	$settings[] = array(
						'label'=>__('WooCommerce Settings','wplms-af' ),
						'type'=> 'title',
					);
		$settings[] = array(
						'label'=>__('Please note, below functionality do not affect directly on course. User buy prodcuct not actual course. So, One course must be linked with one product & vice versa.','wplms-af' ),
						'type'=> 'heading',
					);
		$settings[] = array(
						'label' => __('Disable One time course purchase mode', 'wplms-af'),
						'name' => 'one_time_purchase',
						'desc' => __('By Default, user is restricted to puchase course(product) one time. If you disable this, user can puchase same course(product) multiple times.', 'wplms-af'),
						'type' => 'checkbox',
					);
		$settings[] = array(
							'label' => __('Disable Sold course(product) individually.','wplms-af'),
							'name' => 'sold_individually',
							'type' => 'checkbox',
							'desc' => __('By Default, User is restricted to purchase course(product) one quantity per order. <br />Sold invdividually can be confirgured per product by editing same product from dashboard. If you enable this, settings per product will work & products not having sold invdividually setting on, can be added multiple times in cart. <br />This settings need to be disabled for <strong>One time course purchase</strong> to work properly.','wplms-af')
						);					
					
    	return $settings;
    }
	
	
	function sold_individually( $individually, $product ){
		return true;
	}
	
	/**
	 * Disables repeat purchase for products / variations
	 * 
	 * @param bool $purchasable true if product can be purchased
	 * @param \WC_Product $product the WooCommerce product
	 * @return bool $purchasable the updated is_purchasable check
	 */
	function disable_repeat_purchase( $purchasable, $product ) {
	
		// Don't run on parents of variations,
		// function will already check variations separately
		if ( $product->is_type( 'variable' ) ) {
			return $purchasable;
		}
		
		// Get the ID for the current product (passed in)
		$product_id = $product->is_type( 'variation' ) ? $product->variation_id : $product->id; 
		
		// return false if the customer has bought the product / variation
		if ( $this->wc_customer_have_product( wp_get_current_user()->user_email, get_current_user_id(), $product_id ) ) {
			$purchasable = false;
		}
		
		// Double-check for variations: if parent is not purchasable, then variation is not
		if ( $purchasable && $product->is_type( 'variation' ) ) {
			$purchasable = $product->parent->is_purchasable();
		}
		
		return $purchasable;
	}
	
	
	
	/**
	 * Shows a "purchase disabled" message to the customer
	 */
	function purchase_disabled_message() {
		
		// Get the current product to see if it has been purchased
		global $product;
			add_filter( 'woocommerce_order_is_paid_statuses', array($this, 'wc_get_is_have_statuses') );
		if ( $product->is_type( 'variable' ) ) {
			
			foreach ( $product->get_children() as $variation_id ) {
				// Render the purchase restricted message if it has been purchased
				if ( $this->wc_customer_have_product( wp_get_current_user()->user_email, get_current_user_id(), $variation_id ) ) {
					$this->render_variation_non_purchasable_message( $product, $variation_id );
				}
			}
			
		} else {
			if ( $this->wc_customer_have_product( wp_get_current_user()->user_email, get_current_user_id(), $product->id ) ) {
				echo '<div class="woocommerce"><div class="woocommerce-info wc-nonpurchasable-message">'._x('You\'ve already purchased this product! It can only be purchased once. Contact us for more details.','Notice: Product can be purchased one time.','wplms-af').'</div></div>';
			}
		}
	}
	
	
	/**
	 * Generates a "purchase disabled" message to the customer for specific variations
	 * 
	 * @param \WC_Product $product the WooCommerce product
	 * @param int $no_repeats_id the id of the non-purchasable product
	 */
	function render_variation_non_purchasable_message( $product, $no_repeats_id ) {
		
		// Double-check we're looking at a variable product
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			
			$variation_purchasable = true;
			
			foreach ( $product->get_available_variations() as $variation ) {
				
				// only show this message for non-purchasable variations matching our ID
				if ( $no_repeats_id === $variation['variation_id'] ) {
					$variation_purchasable = false;	
					echo '<div class="woocommerce"><div class="woocommerce-info wc-nonpurchasable-message js-variation-' . sanitize_html_class( $variation['variation_id'] ) . '">'._x('You\'ve already purchased this product! It can only be purchased once. Contact us for more details.','Show message for non-purchasable only variations matching our ID','wplms-af').'</div></div>';
				}
			}
		}
			
		if ( ! $variation_purchasable ) {
			wc_enqueue_js("
				jQuery('.variations_form')
					.on( 'woocommerce_variation_select_change', function( event ) {
						jQuery('.wc-nonpurchasable-message').hide();
					})
					.on( 'found_variation', function( event, variation ) {
						jQuery('.wc-nonpurchasable-message').hide();
						if ( ! variation.is_purchasable ) {
							jQuery( '.wc-nonpurchasable-message.js-variation-' + variation.variation_id ).show();
						}
					})
				.find( '.variations select' ).change();
			");
		}
	}
	
	function remove_course_notice( $key, $values ){
		$product = wc_get_product( $values['variation_id'] ? $values['variation_id'] : $values['product_id'] );
		if ( ! $product->is_purchasable() ) {
			$notices = WC()->session->get( 'wc_notices', array() );
			array_pop(  $notices[ 'error' ] );
			WC()->session->set( 'wc_notices', $notices );
			/* translators: %s: product name */
			wc_add_notice( sprintf( __( "Sorry, you've got this course(%s) earlier, contact administration for more details.", 'wplms-af' ), $product->get_name() ), 'error' );

		}
	}
	
	function cant_purchase_notice( $message ){
		if( $message === 'Sorry, this product cannot be purchased.' ){
			$message = __( "Sorry, you've got this course earlier, contact administration for more details",  'wplms-af');	
		}
		return $message;
	}
	
	function shop_url( $url ) {
		$bp_pages = get_option('bp-pages');
		if( !empty( $bp_pages['course'] ) ){
			return get_permalink( $bp_pages['course'] );
		}
		return $url;
	}

	function wc_get_is_have_product_statuses(){
		return  array('on-hold', 'pending', 'processing', 'completed');	
	}
	
	function wc_get_is_on_hold_statuses(){
		return  array('on-hold', 'pending', 'processing');	
	}
	
	function wc_customer_have_product( $customer_email, $user_id, $product_id ) {
		global $wpdb;
	
		$result = apply_filters( 'woocommerce_pre_customer_have_product', null, $customer_email, $user_id, $product_id );
	
		if ( null !== $result ) {
			return $result;
		}
	
		$transient_name    = 'wc_customer_have_product_' . md5( $customer_email . $user_id );
		$transient_version = WC_Cache_Helper::get_transient_version( 'orders' );
		$transient_value   = get_transient( $transient_name );
	
		if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
			$result = $transient_value['value'];
		} else {
			$customer_data = array( $user_id );
	
			if ( $user_id ) {
				$user = get_user_by( 'id', $user_id );
	
				if ( isset( $user->user_email ) ) {
					$customer_data[] = $user->user_email;
				}
			}
	
			if ( is_email( $customer_email ) ) {
				$customer_data[] = $customer_email;
			}
	
			$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );
			$statuses      = array_map( 'esc_sql', $this->wc_get_is_have_product_statuses() );
	
			if ( count( $customer_data ) === 0 ) {
				return false;
			}
	
			$result = $wpdb->get_col(
				"
				SELECT im.meta_value FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
				WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
				AND pm.meta_key IN ( '_billing_email', '_customer_user' )
				AND im.meta_key IN ( '_product_id', '_variation_id' )
				AND im.meta_value != 0
				AND pm.meta_value IN ( '" . implode( "','", $customer_data ) . "' )
			"
			); // WPCS: unprepared SQL ok.
			$result = array_map( 'absint', $result );
	
			$transient_value = array(
				'version' => $transient_version,
				'value'   => $result,
			);
	
			set_transient( $transient_name, $transient_value, DAY_IN_SECONDS * 30 );
		}
		return in_array( absint( $product_id ), $result, true );
	}

		
	
	function wc_customer_on_hold_product( $customer_email, $user_id, $product_id ) {
		global $wpdb;
	
		$result = apply_filters( 'woocommerce_pre_customer_on_hold_product', null, $customer_email, $user_id, $product_id );
	
		if ( null !== $result ) {
			return $result;
		}
	
		$transient_name    = 'wc_customer_on_hold_product_' . md5( $customer_email . $user_id );
		$transient_version = WC_Cache_Helper::get_transient_version( 'orders' );
		$transient_value   = get_transient( $transient_name );
	
	    if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
			$result = $transient_value['value'];
		} else {
			$customer_data = array( $user_id );
	
			if ( $user_id ) {
				$user = get_user_by( 'id', $user_id );
	
				if ( isset( $user->user_email ) ) {
					$customer_data[] = $user->user_email;
				}
			}
	
			if ( is_email( $customer_email ) ) {
				$customer_data[] = $customer_email;
			}
	
			$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );
			$statuses      = array_map( 'esc_sql', $this->wc_get_is_on_hold_statuses() );
	
			if ( count( $customer_data ) === 0 ) {
				return false;
			}
	
			$result = $wpdb->get_col(
				"
				SELECT im.meta_value FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
				WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
				AND pm.meta_key IN ( '_billing_email', '_customer_user' )
				AND im.meta_key IN ( '_product_id', '_variation_id' )
				AND im.meta_value != 0
				AND pm.meta_value IN ( '" . implode( "','", $customer_data ) . "' )
			"
			); // WPCS: unprepared SQL ok.
			$result = array_map( 'absint', $result );
	
			$transient_value = array(
				'version' => $transient_version,
				'value'   => $result,
			);
	
		    set_transient( $transient_name, $transient_value, HOUR_IN_SECONDS );
	    }
		return in_array( absint( $product_id ), $result, true );
	}
	
	function wc_customer_on_hold_order_url( $customer_email, $user_id, $product_id , $course_id ) {
		global $wpdb;
	
		$transient_name    = 'wc_customer_on_hold_order_' . md5( $customer_email . $user_id.'-'.$course_id );
		$transient_version = WC_Cache_Helper::get_transient_version( 'orders' );
		$transient_value   = get_transient( $transient_name );  
	
	    if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
			$result = $transient_value['value'];
		} else {
			$customer_data = array( $user_id );
	
			if ( $user_id ) {
				$user = get_user_by( 'id', $user_id );
	
				if ( isset( $user->user_email ) ) {
					$customer_data[] = $user->user_email;
				}
			}
	
			if ( is_email( $customer_email ) ) {
				$customer_data[] = $customer_email;
			}
	
			$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );
			$statuses      = array_map( 'esc_sql', $this->wc_get_is_on_hold_statuses() );
	
			if ( count( $customer_data ) === 0 ) {
				return false;
			}
	
			$result = $wpdb->get_var(
				"
				SELECT i.order_id  FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
				WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
				AND pm.meta_key IN ( '_billing_email', '_customer_user' )
				AND im.meta_key IN ( '_product_id', '_variation_id' )
				AND im.meta_value != 0
				AND im.meta_value = ".$product_id."
				AND pm.meta_value IN ( '" . implode( "','", $customer_data ) . "' )
			"
			); // WPCS: unprepared SQL ok.
	
			$transient_value = array(
				'version' => $transient_version,
				'value'   => $result,
			);
	
		    set_transient( $transient_name, $transient_value, DAY_IN_SECONDS );
	    }
		return apply_filters( 'woocommerce_get_view_order_url', wc_get_endpoint_url( 'view-order', $result , wc_get_page_permalink( 'myaccount' ) ), $result );

	}
	
	public function wplms_course_order_url( $product_id,$course_id ){
		if( !is_user_logged_in() ){
			return $product_id;
		}
		if( is_numeric($product_id) && bp_course_get_post_type($product_id) == 'product' ){
			$user_id 	= get_current_user_id();
			$user 		= get_userdata( $user_id );
			if( $this->wc_customer_on_hold_product( $user->user_email, $user_id, $product_id ) ){		
				return $this->wc_customer_on_hold_order_url( $user->user_email, $user_id, $product_id, $course_id );
			}
		}
		return  $product_id;
	}
	
	public function wplms_course_payment_on_hold_label(  $label,$course_id  ){
		if( !is_user_logged_in() ){
			return $label;
		}
		$product_id = get_post_meta($course_id,'vibe_product',true);
		if( is_numeric($product_id) && bp_course_get_post_type($product_id) == 'product' ){
			$user_id 	= get_current_user_id();
			$user 		= get_userdata( $user_id );
			if( $this->wc_customer_on_hold_product( $user->user_email, $user_id, $product_id ) ){		
				$label = _x('Payment pending','Payment pending label for course, when order is on hold','vibe');
			}
		}
		return $label;
	}
	
		
} // END class WWPLMS_Application_WooCommerce


