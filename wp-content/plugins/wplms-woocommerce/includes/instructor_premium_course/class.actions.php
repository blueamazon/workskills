<?php
/**
 * Initialise WPLMS Instructor Premium Courses
 *
 * @class       Wplms_Premium_Courses_Actions
 * @author      Vibethemes(H.K. Latiyan)
 * @category    Admin
 * @package     WPLMS-Woocommerce/includes/instructor_premium_course
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Premium_Courses_Actions{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new Wplms_Premium_Courses_Actions();
	    return self::$instance;
	}

	private function __construct(){

		//Check Woocommerce and LMS Setting status.

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) ){

			if(class_exists('WPLMS_tips')){
				$tips = WPLMS_tips::init();
				if(isset($tips) && isset($tips->settings) && isset($tips->settings['enable_instructor_premium_courses'])){
					
					//Ajax function for buying premium courses via form
					add_action('wp_ajax_nopriv_buy_premium_course',array($this,'buy_premium_course'));
					add_action('wp_ajax_buy_premium_course',array($this,'buy_premium_course'));

					//Ajax function for updating the total courses in backend
					add_action('wp_ajax_save_premium_total_courses',array($this,'save_premium_total_courses'));

					//When the order is completed update the user meta and delete the product
					add_action('woocommerce_order_status_completed',array($this,'delete_product_on_order_completion'));

					//Show premium courses form if instructor cannot publish a course on front end
					add_action('wplms_course_go_live',array($this,'display_premium_course_info_frontend'),10,2);
			    	
				}
			}
		}

	} // END public function __construct

	function buy_premium_course(){

		//Check Security
		if( !isset($_POST['courses']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'hkl_security') ){

			_e('Security check Failed. Contact Administrator.','wplms-woo');
            die();
		}

		/* Define Variables */
		$courses = $_POST['courses'];
		$lms_settings = get_option('lms_settings');
		if(!isset($lms_settings['premium_courses']) || $lms_settings['premium_courses'] == 0){
			_e('Price Per Course not set. Contact Administrator.','wplms-woo');
            die();
		}

		$per_price = $lms_settings['premium_courses'];
		$total_price = $courses * $per_price;

		/* Creat product */
        $post_args = array('post_type' => 'product','post_status'=>'publish','post_title'=>'Premium Course');
        $product_id = wp_insert_post($post_args);

        /* Product Price */
        update_post_meta($product_id,'_price', $total_price);

        /* Product Settings */
        wp_set_object_terms($product_id, 'simple', 'product_type');
        update_post_meta($product_id,'_visibility','hidden');
        update_post_meta($product_id,'_virtual','yes');
        update_post_meta($product_id,'_downloadable','yes');
        update_post_meta($product_id,'_sold_individually','yes');
        update_post_meta($product_id,'_stock_status','instock');

        /* Add Batch information in product meta */
        $premium_courses = array(
        		'premium_courses' => $courses,
        	);
        update_post_meta($product_id,'wplms_premium_courses',$premium_courses);

        /* Redirect user to cart page on ajax success */
        global $woocommerce;
        $cart_url = $woocommerce->cart->get_cart_url();
        $cart_url = $cart_url.'?add-to-cart='.$product_id;

        //Return cart url with product id added in it
        echo $cart_url;
        die();

	}

	function save_premium_total_courses(){

		//Check security
		if(!isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'hkl_security') || !isset($_POST['courses']) || !isset($_POST['user_id'])){

			_e('Security check Failed. Contact Administrator.','wplms-woo');
            die();
		}

		//Define variables
		$courses = $_POST['courses'];
		$user_id = $_POST['user_id'];

		//Update user meta for the premium courses
		update_user_meta($user_id,'instructor_premium_courses',$courses);

		die();

	}

	function delete_product_on_order_completion($order_id){

		$order = new WC_Order( $order_id );
		$items = $order->get_items();
		$user_id = $order->user_id;

		foreach($items as $item){

			//check for premium courses on order complettion
			$premium_courses = get_post_meta($item['product_id'],'wplms_premium_courses',true);
			if(!empty($premium_courses)){

				$courses = $premium_courses['premium_courses'];
				$pre_courses = get_user_meta($user_id,'instructor_premium_courses',true);

				//Update the courses if already present in the meta
				if(!empty($pre_courses)){
					$courses = $courses + $pre_courses;
				}

				//update the user meta
				update_user_meta($user_id,'instructor_premium_courses',$courses);

				/* Delete product */
				wp_delete_post($item['product_id'],true);

				do_action('instructor_premium_courses_purchased',$courses);
			}

		}

	}

	function display_premium_course_info_frontend($course_id,$the_post){

		//Check for admin
		if(current_user_can('manage_options')){
			return $the_post;
		}

		//Get total/published/remaining courses of instructor
		$user_id = $the_post['post_author'];
		$total_courses = get_user_meta($user_id,'instructor_premium_courses',true);

    	$published_course = count_user_posts_by_type($user_id,'course');
    	$remaining_courses = $total_courses - $published_course;

    	//Show instructor the total/published/remaining courses
		?>
		<div class="premium_course_info" style="padding-top:30px;">
			<ul>
				<li><label style="display:inline-block;line-height:3em;"><?php _e('Total Courses you can publish','wplms-woo'); ?></label>
					<span style="float:right;"><?php echo $total_courses; ?></span>
				</li>
				<li><label style="display:inline-block;line-height:3em;"><?php _e('Published Courses','wplms-woo'); ?></label>
					<span style="float:right;"><?php echo $published_course; ?></span>
				</li>
				<li><label style="display:inline-block;line-height:3em;"><?php _e('Remaining Courses','wplms-woo'); ?></label>
					<span style="float:right;"><?php echo $remaining_courses; ?></span>
				</li>
			</ul>
		</div>
		<?php

		return $the_post;

	}


} // End of class Wplms_Buy_Batches_Actions

add_action('plugins_loaded',function(){Wplms_Premium_Courses_Actions::init();},99);
