<?php
/**
 * Initialise WPLMS Instructor Premium Courses
 *
 * @class       Wplms_Premium_Courses_Class
 * @author      Vibethemes(H.K. Latiyan)
 * @category    Admin
 * @package     WPLMS-Woocommerce/includes/instructor_premium_course
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Premium_Courses_Class{

	public static $instance;
	public static function init(){
		
	    if ( is_null( self::$instance ) )
	        self::$instance = new Wplms_Premium_Courses_Class();
	    return self::$instance;
	}

	private function __construct(){

		//Check Woocommerce and LMS Setting status.

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) ){

			if(class_exists('WPLMS_tips')){
				$tips = WPLMS_tips::init();
				if(isset($tips) && isset($tips->settings) && isset($tips->settings['enable_instructor_premium_courses'])){
					
					//Shortcode for premium courses form
					add_shortcode('wplms_premium_course_form', array($this,'wplms_premium_course_form_shortcode'));
			    	
				}
			}
		}

	} // END public function __construct

	function wplms_premium_course_form_shortcode($atts,$content = null){

		$defaults = array(
			'per_price_label'=>__('Price Per Course','wplms-woo'),
			'courses_label'=>__('Number of courses','wplms-woo'),
			'button_label'=>__('Buy Courses','wplms-woo'),
		);

		/* merge atts and defaults */
		$atts = wp_parse_args($atts,$defaults);
		$lms_settings = get_option('lms_settings');
   		$per_price = '';
		if(isset($lms_settings['premium_courses'])){
			$per_price = $lms_settings['premium_courses'];
		}

		//Get woocommerce currency symbol
		global  $woocommerce;
   		$currency_symbol = get_woocommerce_currency_symbol();

		ob_start();
		/* create premium course form */
		?>
		<div class="wplms_premium_course_form">
			<form method="post">
				<ul>
					<li><label><?php echo $atts['per_price_label']; ?></label>
					<span class="currency_symbol"><?php echo $currency_symbol; ?></span>
					<span class="price_per_course"><?php echo $per_price; ?></span>
					</li>
					<li>
						<label><?php echo $atts['courses_label']; ?></label>
						<span><input type="number" name="premium_courses" class="premium_courses form_field" placeholder="<?php echo $atts['courses_label']; ?>"></span>
					</li>
					<li>
						<?php echo '<a class="button-primary button disabled" id="wplms_premium_course_button">'.$atts['button_label'].'</a>'; ?>
					</li>
					<?php
	   				wp_nonce_field('hkl_security','pc_security');
					?>
				</ul>
			</form>
		</div>
		<?php

		$return = ob_get_clean();

		add_action('wp_footer',function(){
			?>
			<style>
				.wplms_premium_course_form ul{list-style:none;}
				.wplms_premium_course_form ul li{display:inline-block;width: 100%;}
				.wplms_premium_course_form ul li label{display:inline-block;width:20%;}
				.wplms_premium_course_form ul li .premium_courses.form_field{display:inline-block;width:50%;}
				.wplms_premium_course_form ul li+li{margin: 10px 0;}
				.total_price_li {color:#70c989;font-size: 18px;font-weight: 700;}
				.wplms_premium_course_form .button.disabled{opacity:0.6;}
			</style>
			
			<script>
				jQuery(document).ready(function($){

			      var per_price = $('.wplms_premium_course_form').find('.price_per_course').text();

			      $('.premium_courses').on('change',function(){
			            // Define Variables
			            var courses = $('.wplms_premium_course_form').find('.premium_courses').val();
			            var total_price = 0;

			            // Calculate Pricee
			            total_price = courses * per_price;

			            // Remove class disabled if the price is not 0
			            if(total_price != 0){
			                  $('#wplms_premium_course_button').removeClass('disabled');
			            }else{
			                  $('#wplms_premium_course_button').addClass('disabled');
			            }
			            
			            // Display Price
			            var currency_symbol = $('.wplms_premium_course_form').find('.currency_symbol').text();
			            if( !$('.total_price').length ){
			                  $('.premium_courses').after('<li class="total_price_li"><label><?php _ex('Total Price','instructor premium course','wplms-woo'); ?></label><span class="total_price"></span></li>');
			            }
			            $('.total_price').text(currency_symbol+' '+total_price);
			      });

			      $('#wplms_premium_course_button').on('click',function(){

						// Return if class is disabled
						if($(this).hasClass('disabled')){
						      return;
						}

						// Change Button Text
						$(this).text('.....');

						// Define Variables
						var courses = $('.wplms_premium_course_form').find('.premium_courses').val();

			            // Ajax Call
			            $.ajax({
							type: "POST",
							url: ajaxurl,
							data: { action: 'buy_premium_course',
									courses: courses,
									security: $('#pc_security').val(),
							    },
							cache: false,
							success: function (html) {
							    window.location.href = html;
							}
			            });
					});

			    });

			</script>
			<?php
		});

		return $return;

	}

} // End of class Wplms_Premium_Courses_Class

add_action('plugins_loaded',function(){Wplms_Premium_Courses_Class::init();},99);
