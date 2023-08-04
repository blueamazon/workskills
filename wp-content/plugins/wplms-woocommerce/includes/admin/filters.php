<?php
/**
 * Installation related functions and filters.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-WooCommerce/Includes/admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Woo_Filters{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Filters();
        return self::$instance;
    }

	private function __construct(){
		add_filter('wplms_batches_add_to_cart_link',array($this,'wplms_batch_add_to_cart_link'),10,3);
	}

	function wplms_batch_add_to_cart_link($cart_url,$product_id,$batch_id){
		$product = wc_get_product($product_id);
		if( empty($product) )
			return;

		if( $product->is_type( 'variable' )){
			$variations = $product->get_available_variations();
			foreach($variations as $variation){
				$id = get_post_meta($variation['variation_id'],'vibe_course_batches',true);
				if(!empty($id) && is_numeric($id) && $id == $batch_id){
					$cart_url = $cart_url.'&variation_id='.$variation['variation_id'].'&batch_id='.$batch_id;
					foreach($variation['attributes'] as $key => $value){
						$cart_url = $cart_url.'&'.$key.'='.$value;
					}
				}
			}
		}

		return $cart_url;
	}
}

Wplms_Woo_Filters::init();
