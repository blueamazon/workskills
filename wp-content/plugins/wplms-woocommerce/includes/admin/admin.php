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

class Wplms_Woo_Admin{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woo_Admin();
        return self::$instance;
    }

	private function __construct(){
		add_filter('product_type_options',array($this,'wplms_product'));
		add_filter('wplms_woocommerce_enable_pricing',array($this,'woocommerce_meta'));
		add_action('woocommerce_variation_options',array($this,'show_wplms_options'),10,3);
		register_activation_hook(__FILE__,array($this,'register_attribute_taxonomies'));
		add_filter('lms_general_settings',array($this,'wplms_woocommerce_restriction_switch'));
	}


	function wplms_product($product_type_options){
		$default = 'no';
		$vibe_courses = get_post_meta(get_the_ID(),'vibe_courses',true);
		if(!empty($vibe_courses)){
			$default = 'yes';
		}

		$product_type_options['wplms'] =array(
				'id'            => 'vibe_wplms',
				'wrapper_class' => 'show_if_wplms',
				'label'         => __( 'WPLMS', 'wplms-woo' ),
				'description'   => __( 'Display WPLMS courses', 'wplms-woo' ),
				'default'       => $default
			);

		return $product_type_options;
	}
	function woocommerce_meta($filter){
		include_once 'woocommerce_meta.php';
		return 0;
	}


	function show_wplms_options($loop, $variation_data, $variation){
		$_wplms = get_post_meta($variation->ID,'variable_is_wplms',true);
		$id = 'variable_is_wplms'.rand(0,9999);
		?>
	 	<script>
	 	jQuery(document).ready(function($){
	 		$('#vibe_wplms').each(function(){
				if($(this).is(':checked')){
					$('#<?php echo $id; ?>').show();
				}else{
					$('#<?php echo $id; ?>').hide();
				}
				$('#vibe_wplms').on('change',function(){
					if($(this).is(':checked')){
						$('#<?php echo $id; ?>').show(200);
					}else{
						$('#<?php echo $id; ?>').hide(200);
					}
				});
				$('#<?php echo $id; ?>').each(function(){
					var parent = $(this).parent().parent();
					var $this = $(this);
					if($this.find('.variable_is_wplms').is(':checked')){
						parent.find('.wplms_options').show();
					}else{
						parent.find('.wplms_options').hide();
					}
					$('#<?php echo $id; ?>').on('click',function(){ 
						if($this.find('.variable_is_wplms').is(':checked')){ 
							parent.find('.wplms_options').show(200);
						}else{
							parent.find('.wplms_options').hide(200);
						}
					});
				});
			});
	 	});
	 	</script>
		<label id="<?php echo $id; ?>"><input type="checkbox" class="checkbox variable_is_wplms" name="variable_is_wplms[<?php echo $loop; ?>]" <?php checked( isset( $_wplms ) ? $_wplms : '', 'on' ); ?> /> <?php _e( 'WPLMS', 'wplms-woo' ); ?> <a class="tips" data-tip="<?php _e( 'Enable this option if the variation is conencted to WPLMS courses', 'wplms-woo' ); ?>" href="#">[?]</a></label>
		<?php
	}

	function wplms_woocommerce_restriction_switch($settings){

		$settings[] = array(
			'label'=>__('WPLMS Woocommerce Settings','wplms-woo' ),
			'type'=> 'heading',
		);

		//Variable Products Popup setting in lms general settings.
		$settings[] = array(
	            'label' => __('Enable Variable Products Popup in Course Page', 'wplms-woo'),
	            'name' => 'wplms_woocoommerce_variable_products_popup',
	            'desc' => __('Enabling this setting will show variable pricing/product in a popup on course page', 'wplms-woo'),
	            'type' => 'checkbox',
			);

		//Batch price settings in lms general settings.
		if( in_array( 'wplms-batches/wplms-batches.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'wplms-batches/wplms-batches.php')) ){

			$settings[] = array(
	            'label' => __('Display Batch Price on Course Page', 'wplms-woo'),
	            'name' => 'show_batch_price_on_course_page',
	            'desc' => __('Enabling this setting will show batch pricing on course page', 'wplms-woo'),
	            'type' => 'checkbox',
			);
		}

		////Add setting in lms general settings to show the instructor premium courses
		$settings[] = array(
	            'label' => __('Enable Instructor Premium Courses', 'wplms-woo'),
	            'name' => 'enable_instructor_premium_courses',
	            'desc' => sprintf(__('Enabling this setting enable the instructor premium courses feature in the website. The admin can restrict the instructors from creating the courses and the instructors will have to purchase the courses to continue publishing courses. %s', 'wplms-woo'),'<a href="https://wplms.io/support/knowledge-base/instructor-premium-courses-feature/" class="button-primary" target="_blank">?</a>'),
	            'type' => 'checkbox',
			);

		return $settings;
	}

	function register_attribute_taxonomies(){
	    
	}
}

Wplms_Woo_Admin::init();
