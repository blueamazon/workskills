<?php
/**
 * Installation related functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-WooCommerce/Includes/admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Woo_Actions{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Actions();
        return self::$instance;
    }

	private function __construct(){
		add_action('wplms_batch_price',array($this,'display_batch_price_on_course_page'));
	}

	function display_batch_price_on_course_page($batch_id){ 
		if(class_exists('WPLMS_tips')){
			$tips = WPLMS_tips::init();
			if(isset($tips) && isset($tips->settings) && isset($tips->settings['show_batch_price_on_course_page'])){

				if(!is_singular('course'))
					return;

				$course_id = get_the_ID();

				if(!is_single($course_id))
					return;

				$product_id = get_post_meta($course_id,'vibe_product',true);

				if( is_array($product_id) ){
					$product_id = $product_id[0];
				}

				if( !is_numeric($product_id) )
					return;

				$product = wc_get_product($product_id);
				if( empty($product) )
					return;
		
				if( $product->is_type( 'variable' )){
					$variations = $product->get_available_variations();
					foreach($variations as $variation){
						$variable_is_wplms = get_post_meta($variation['variation_id'],'variable_is_wplms',true);
	    				if(!empty($variable_is_wplms) && $variable_is_wplms == 'on'){
	    					$id = get_post_meta($variation['variation_id'],'vibe_course_batches',true);
	    					if(!empty($id) && is_numeric($id) && $id == $batch_id){
	    						$vproduct = wc_get_product($variation['variation_id']);
								$batch_price = $vproduct->get_price_html();
	    						echo '<div class="batch_price">'.$batch_price.'</div>';
	    					}
	    				}
					}
				}
			}
		}
	}
}

Wplms_Woo_Actions::init();
