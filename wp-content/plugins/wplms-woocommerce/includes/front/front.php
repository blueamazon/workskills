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



class Wplms_Woo_Front{



	public static $instance;

    

    public static function init(){



        if ( is_null( self::$instance ) )

            self::$instance = new Wplms_Woo_Front();

        return self::$instance;

    }



	private function __construct(){



		//add_filter('wplms_course_front_details',array($this,'woocommerce_variable_form'));

		add_filter('wplms_take_course_button_html',array($this,'woocommerce_variable_form'),999,2);

		add_filter('wplms_course_partial_credits',array($this,'woocommerce_variable_form_in_partial_course'),10,2);

		add_filter('wplms_expired_course_button',array($this,'renew_form'),10,2);

		add_action('template_redirect',array($this,'redirect'));

	}





	function renew_form($html,$course_id){
	
		$product_id = get_post_meta($course_id,'vibe_product',true);

		if(is_array($product_id)){

			$product_id = $product_id[0];

		}

		global $woocommerce,$product;

		$product = wc_get_product($product_id);

		if(empty($product))

			return $html;



		$user_id = 0;

		$status = '';

		if(is_user_logged_in()){

			$user_id = get_current_user_id();

		}



		if(!empty($user_id) && function_exists('bp_course_get_user_course_status')){

			$status = bp_course_get_user_course_status($user_id,$course_id);

		}



		if(!empty($status) && $status > 2){

			return $html;

		}



		$partial_free_course = get_post_meta($course_id,'vibe_partial_free_course',true);

		if(empty($status) && vibe_validate($partial_free_course) ){

			return $html;

		}





		if( $product->is_type( 'variable' )){ 

			

			//CHECK IF LMS SETTINGS ENABLED

			if(class_exists('WPLMS_tips')){

				$tips = WPLMS_tips::init();

				if(isset($tips) && isset($tips->settings) && isset($tips->settings['wplms_woocoommerce_variable_products_popup'])){

					$this->popup_function($course_id);

					return $html;

				}

			}

			

			//ELSE

			ob_start();

			woocommerce_variable_add_to_cart(); echo $this->return_style($course_id);

			$html .=ob_get_clean();

		}

		return $html;

	}



	function woocommerce_variable_form_in_partial_course($return,$course_id){

		if(is_singular('course'))

			return $return;

		$this->woocommerce_variable_form($return,$course_id);

	}



	function woocommerce_variable_form($return,$course_id){

		//Check Partial free course setting.

		$user_id = 0;

		$status = '';

		if(is_user_logged_in()){

			$user_id = get_current_user_id();

		}



		if(!empty($user_id) && function_exists('bp_course_get_user_course_status')){

			$status = bp_course_get_user_course_status($user_id,$course_id);

		}

		if(!empty($status)){

			return $return;

		}



		$partial_free_course = get_post_meta($course_id,'vibe_partial_free_course',true);



		if(empty($status) && vibe_validate($partial_free_course) ){

			return $return;

		}



		//CHECK IF LMS SETTINGS ENABLED

		if(class_exists('WPLMS_tips')){

			$tips = WPLMS_tips::init();

			if(isset($tips) && isset($tips->settings) && isset($tips->settings['wplms_woocoommerce_variable_products_popup'])){

				$this->popup_function($course_id);

				return $return;

			}

		}

		

		//ELSE

		

		global $woocommerce,$product;

		

		$product_id = get_post_meta($course_id,'vibe_product',true);

		

		if(get_post_type($product_id) !='product')

			return $return;



		if(is_array($product_id)){

			$product_id = $product_id[0];

		}



		$flag = 0;

		if(!is_numeric($product_id) || !empty($flag))

			return $return;



		$flag = 1;



		$product = wc_get_product($product_id);

		if(empty($product))

			return;



		if( $product->is_type( 'variable' )){ 

		  // a variable product

			ob_start();

			woocommerce_variable_add_to_cart();

			$variable_html = ob_get_clean();

			$return .= $variable_html.$this->return_style();

		}

		

		return $return;

	}



	function redirect(){

		

		if(!is_singular('course'))

			return;



		$product_id = get_post_meta(get_the_ID(),'vibe_product',true);



		$cart = WC()->cart->get_cart();

		if (sizeof($cart) != 0) {

			foreach($cart as $prd){

				if($prd['product_id'] == $product_id){

					$check=vibe_get_option('direct_checkout');

			      	$check =intval($check);

				    if(isset($check) &&  $check == 2){

			            $checkout_url = WC()->cart->get_checkout_url();

			            wp_redirect( $checkout_url);  

			            exit();

				    }else if ($check == 3){

		                $cart_url = WC()->cart->get_cart_url(); 

		                wp_redirect( $cart_url); 

		            	exit();

				    }

					break;

				}

			}

		}

	}



	function return_style($cid = NULL){

		return '<style>.variations{width:100%}

			.variations .value select{width:100% !important;text-align:center;}

			#buddypress .course_button.button.disabled{background:#EEE;color:#444;}td.value{width:100% !important;}

			.single_add_to_cart_button,.qty{display:none;}.variations_form cart{padding: 0 8px 0 0;}

			.variations_form cart+.course_details{margin-top:0;}.chosen-container.chosen-container-single,.chosen-container.chosen-container-single li {width: 100% !important;}

			.variations .label{display:none;}

			.variations .reset_variations{font-size:11px;text-transform:uppercase;color:#aaa;}

			.variations .value select,.variations .value #duration{margin:10px 0 0;color:#444;}

			</style>

			<script>

			jQuery(document).ready(function($){

				$(".variations tr").each(function(){

					var defaulttext = $(this).find(".label").text();

					$(this).find("select").attr("data-placeholder", defaulttext);

					$(this).find("select>option:first").text(defaulttext);

					//$(this).find("select").chosen({disable_search_threshold: 10});

				});

				

				

				$(".reset_variations").on("click",function(){$(".variations select").trigger("liszt:updated");});

				$(".course_button").addClass("disabled");

				

				$(".variations_form").on("reset_data",function(){ 

					$(".course_button").addClass("disabled");

				});

				$(".single_variation_wrap").on("show_variation",function(){ 

					$(".course_button").removeClass("disabled");

				});

				$(".course_button'.(empty($cid)?'':',.course_single_item.course_id_'.$cid.' .button').'").on("click",function(event){

					event.preventDefault();

					console.log("clicked");

					if($(this).hasClass("disabled"))

						return;

					else{

						$(".single_add_to_cart_button").trigger("click");

					}

				});	

			});

			</script>';

	}



	// VARIABLE POPUP

    function popup_function($course_id){

        $this->course_id = $course_id;

        add_action('wp_footer',function()use($course_id){

            $this->generate_popup_html($course_id);

        });

    }

    function generate_popup_html($course_id){

        $product_id = get_post_meta($course_id,'vibe_product',true);



        if( is_array($product_id) ){

            $product_id = $product_id[0];

        }



        if( !is_numeric($product_id) )

            return;

        $product = wc_get_product($product_id);

        if( empty($product) )

            return;

        $return = '';

        if( $product->is_type( 'variable' )){

        	global $woocommerce;

            $cartUrl = $woocommerce->cart->get_cart_url();

            $variations = $product->get_available_variations();

            ?> 

            <div id="variations_popup" class="white-popup mfp-hide">

                <div class="container-fluid">

                    <div class="row">

                <?php

                 

                    $count = count($variations);

                    $col_md_class = $this->get_class($count);

                    

                    foreach($variations as $variation){

                        $cart_url = $cartUrl.'?add-to-cart='.$product_id.'&variation_id='.$variation['variation_id'];

                        foreach($variation['attributes'] as $key => $value){

                            $cart_url = $cart_url.'&'.$key.'='.$value;

                        }

                        $variable_is_wplms = get_post_meta($variation['variation_id'],'variable_is_wplms',true);

                        if(!empty($variable_is_wplms) && $variable_is_wplms == 'on'){

                            $course_subscription = get_post_meta($variation['variation_id'],'vibe_subscription',true);

                            $course_certificate = get_post_meta($variation['variation_id'],'vibe_enable_certificate',true);

                            $course_badge = get_post_meta($variation['variation_id'],'vibe_enable_badge',true);

                            $course_retake = get_post_meta($variation['variation_id'],'vibe_enable_course_retakes',true);

                            $quiz_retake = get_post_meta($variation['variation_id'],'vibe_enable_quiz_retakes',true);

                            $batch_id = get_post_meta($variation['variation_id'],'vibe_course_batches',true);

                ?>

                        <div class="<?php echo $col_md_class; ?>">

                            <div class="popup_block">

                                <h4><?php echo $variation['price_html']; ?></h4>

                                <?php do_action("wplms_woocommerce_variation_popup_item",$variation);?>

                                <ul>

                                    <li><?php 

                                        if(!empty($course_subscription) && $course_subscription == 'S'){

                                            $duration = get_post_meta($variation['variation_id'],'vibe_duration',true);

                                            $product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$variation['variation_id']);

                                            if(!empty($duration)){

                                                echo tofriendlytime($duration*$product_duration_parameter).'<i class="icon-clock"></i>';

                                            }

                                        }else{

                                            echo __('Full Duration','wplms-woo').'<i class="icon-clock"></i>';

                                        }

                                    ?></li>

                                    <?php 

                                    if(!empty($course_certificate) && $course_certificate == 'S'){

                                        echo '<li>';

                                        echo __('Course Certificate','wplms-woo').'<i class="icon-certificate-file"></i></li>';

                                    }

                                    if(!empty($course_badge) && $course_badge == 'S'){

                                        echo '<li>';

                                        echo __('Course Badge','wplms-woo').'<i class="icon-award-stroke"></i></li>';

                                    }

                                    if(!empty($course_retake) && $course_retake == 'S'){

                                        $course_retake_count = get_post_meta($variation['variation_id'],'vibe_course_retakes',true);

                                        if(!empty($course_retake_count)){

                                            echo '<li>';

                                            echo __('Course Retake','wplms-woo').'<i>'.$course_retake_count.'</i></li>';

                                        }

                                    }

                                    if(!empty($quiz_retake) && $quiz_retake == 'S'){

                                        $quiz_retake_count = get_post_meta($variation['variation_id'],'vibe_quiz_retakes',true);

                                        if(!empty($quiz_retake_count)){

                                            echo '<li>';

                                            echo __('Quiz Retake','wplms-woo').'<i>'.$quiz_retake_count.'</i></li>';

                                        }

                                    }

                                    if(!empty($batch_id)){

                                        $batch = groups_get_group( array( 'group_id' => $batch_id) );

                                        echo '<li>';

                                        echo __('Batch: ','wplms-woo').'<strong style="float:right;">'.$batch->name.'</strong></li>';

                                    }

                                    ?>

                                </ul>

                                <p><a href="<?php echo $cart_url; ?>" class=" button button-primary"><?php echo __('Select','wplms-woo');  ?></a></p>

                            </div>

                        </div>

                        <?php

                        }

                    }

                ?>

                    </div>

                </div>

            </div>

            <style>

            .white-popup {position: relative;background: transparent;padding: 20px 0;width: auto;max-width: 100%;max-height: 100%;} .mfp-close-btn-in .white-popup .mfp-close{color:#fff;}

            .popup_block {box-shadow:0 0 3px rgba(0,0,0,0.6);border-radius:4px;padding: 15px; margin-top: 15px;height: auto;background:#fff;}

            .popup_block ul {height: 200px;}

            #variations_popup .col-md-4 h4 {text-align: center;}

            .popup_block ul li {padding: 6px;border-bottom: 1px dotted rgba(0,0,0,.08);}

            .popup_block ul li i {float: right;}

            .popup_block p,.popup_block h4 {text-align: center;}

            </style>

            <script>

            jQuery(document).ready(function($){

                $("a.course_button").magnificPopup({

                      items: {

                          src: "#variations_popup",

                          type: "inline"

                      }

                });

                $("a.course_button").on("click",function(event){

                    event.preventDefault();

                });

            });

            </script>

            <?php

            

        }

        return $return;

    }



	function get_class($count){

		$class='col-md-3';

		$check = $count%4;

		if((in_array($check,array(0,3)) && $count>3) || $count > 10)

			return 'col-md-3 col-sm-6';



		$check = $count%3;

		if(in_array($check,array(0,2)) && $count > 2)

			return 'col-md-4 col-sm-6';



		$check = $count%2;

		if(in_array($check,array(0)))

			return 'col-md-6 col-sm-6';

		else

			return 'col-md-offset-2 col-md-8';

		return $class;

	}

}



Wplms_Woo_Front::init();



