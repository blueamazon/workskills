<?php
/**
 * Proccessing functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-WooCommerce/Includes/
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Woo_Process{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Process();
        return self::$instance;
    }

	private function __construct(){

		add_filter('bp_course_product_id',array($this,'variable_product_id'),10,2);
		add_action('woocommerce_order_status_completed',array($this,'order_completed'),10,1);
		add_action('woocommerce_order_status_cancelled',array($this,'order_reversed'),10,1);
		add_action('woocommerce_order_status_refunded',array($this,'order_reversed'),10,1);

		//Process
		add_filter('wplms_course_student_certificate_check',array($this,'enable_certificate'),10,3);
		add_filter('wplms_course_student_badge_check',array($this,'enable_badge'),10,3);

		add_filter('wplms_quiz_retake_count',array($this,'enable_quiz_retakes'),10,4);
		add_filter('wplms_course_retake_count',array($this,'enable_course_retakes'),10,3);

		add_action('wplms_course_unsubscribe',array($this,'remove_course_filters'),10,2);

	}

	function variable_product_id($product_id,$item){ 

		if(!empty($item['variation_id'])){
			$product_id = $item['variation_id'];
		}
		return $product_id;
	}

	function order_completed($order_id){
		$order = new WC_Order( $order_id );
		$items = $order->get_items();
		$user_id=$order->user_id;
		foreach($items as $item){
			$check_wplms = get_post_meta($item['product_id'],'vibe_wplms',true);
			if(!empty($check_wplms)){
				$courses = get_post_meta($item['product_id'],'vibe_courses',true);
				if(!empty($item['variation_id'])){
					$check_variation = get_post_meta($item['variation_id'],'variable_is_wplms',true);
					if(!empty($check_variation) && ($check_variation == 'on' || $check_variation == 'yes')){
						
						$enable_certificate = get_post_meta($item['variation_id'],'vibe_enable_certificate',true);
						$enable_badge = get_post_meta($item['variation_id'],'vibe_enable_badge',true);
						
						$enable_course_retakes = get_post_meta($item['variation_id'],'vibe_enable_course_retakes',true);
						$course_retakes = get_post_meta($item['variation_id'],'vibe_course_retakes',true);
						
						$enable_quiz_retakes = get_post_meta($item['variation_id'],'vibe_enable_quiz_retakes',true);
						$quiz_retakes = get_post_meta($item['variation_id'],'vibe_quiz_retakes',true);

						$user_variation_array = apply_filters('wplms_woo_variable_product_user_meta',array(
							'enable_certificate' => (($enable_certificate == 'S')?1:0),
							'enable_badge' => (($enable_badge == 'S')?1:0),
							'enable_course_retakes' => (($enable_course_retakes == 'S')?1:0),
							'course_retakes'=> $course_retakes,
							'enable_quiz_retakes' => (($enable_quiz_retakes == 'S')?1:0),
							'quiz_retakes'=> $quiz_retakes,
						));

						foreach($courses as $course){
							update_user_meta($user_id,'course_filters'.$course,$user_variation_array);
						}
						if(function_exists('bp_is_active') && bp_is_active('groups')){
							$course_batches = get_post_meta($item['variation_id'],'vibe_course_batches',true);
							if(!empty($course_batches) && is_numeric($course_batches)){
								if(is_wplms_batch($course_batches)){
									$result = groups_join_group($course_batches, $user_id );
									
								}
							}
						}
					}
				}
			}
		}
	}

	function order_reversed($order_id){
		$order = new WC_Order( $order_id );
		$items = $order->get_items();
				foreach($items as $item){
			$check_wplms = get_post_meta($item['product_id'],'vibe_wplms',true);
			if(!empty($check_wplms)){
				$courses = get_post_meta($item['product_id'],'vibe_courses',true);
				if(!empty($item['variation_id'])){
					$check_variation = get_post_meta($item['variation_id'],'variable_is_wplms',true);
					if(!empty($check_variation) && ($check_variation == 'on' || $check_variation == 'yes')){
						foreach($courses as $course){
							update_user_meta($user_id,'course_filters'.$course);
						}
					}
				}
			}
		}
	}

	function enable_certificate($filter,$course_id,$user_id){
		if(empty($this->user_settings)){
			$this->user_settings = get_user_meta($user_id,'course_filters'.$course_id,true);
		}		
		if(isset($this->user_settings['enable_certificate']) && empty($this->user_settings['enable_certificate'])){
			$filter = false;
		}
		return $filter;
	}
	
	function enable_badge($filter,$course_id,$user_id){
		if(empty($this->user_settings)){
			$this->user_settings = get_user_meta($user_id,'course_filters'.$course_id,true);
		}
		if(isset($this->user_settings['enable_badge']) && empty($this->user_settings['enable_badge'])){
			$filter = false;
		}
		return $filter;	
	}
	function enable_quiz_retakes($retakes,$quiz_id,$course_id,$user_id){

		if(empty($this->user_settings)){ 
			$this->user_settings = get_user_meta($user_id,'course_filters'.$course_id,true);
		}

		if(isset($this->user_settings['enable_quiz_retakes'])){
			$retakes = $this->user_settings['quiz_retakes'];
		}
		return $retakes;
	}
	function enable_course_retakes($retakes,$course_id,$user_id){
		if(empty($this->user_settings)){
			$this->user_settings = get_user_meta($user_id,'course_filters'.$course_id,true);
		}
		if(isset($this->user_settings['enable_course_retakes'])){
			$retakes = $this->user_settings['course_retakes'];
		}
		return $retakes;
	}

	function remove_course_filters($course_id,$user_id){
		delete_user_meta($user_id,'course_filters'.$course_id);
	}
}

Wplms_Woo_Process::init();
