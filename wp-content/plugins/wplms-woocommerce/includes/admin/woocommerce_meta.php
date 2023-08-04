<?php
/**
 * Installation related functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Vibe Projects/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Woocommerce_Meta{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Woocommerce_Meta();
        return self::$instance;
    }

	private function __construct(){
		add_action('woocommerce_product_after_variable_attributes', array($this, 'display_variation_selection'), 10, 3);
		add_action('woocommerce_product_options_general_product_data', array($this, 'display_simple_product_selection'));
		add_action('woocommerce_process_product_meta_simple', array($this, 'process_simple_product_meta'),10,1);
		add_action('woocommerce_process_product_meta_variable', array($this, 'process_variable_product_meta'),10,1);
        add_action('woocommerce_save_product_variation', array($this, 'process_variable_product_meta_vars'),10,1);
	}

	function display_simple_product_selection(){
		?>
			<div class="options_group wplms_options">
			    <p class="form-field vibe_courses">
			        <label for="courses"><?php _e('Courses', 'wplms-woo'); ?></label>
			        <?php  
			        	$courses  = get_post_meta(get_the_ID(),'vibe_courses',true);
			        	$args= array(
                    	'post_type'=>'course',
                    	'numberposts'=> -1
                    	);
						$args = apply_filters('wplms_backend_cpt_query',$args,$id);
						$kposts=get_posts($args);
			        ?>
			        <select id="courses" name="vibe_courses[]" class="select2_courses" multiple></option>
			        	<?php
			        	foreach($kposts as $post){
			        		echo '<option value="'.$post->ID.'" '.(in_array($post->ID,$courses)?'selected="selected"':'').'>'.$post->post_title.'</option>';
			        	}
			        	?>
			        </select>
			        <script>jQuery(document).ready(function(){jQuery('.select2_courses').select2();});</script>
			        <span class="description"><?php _e('Select courses to connect with this product','wplms-woo'); ?></span>
			    </p>
			    <p class="form-field vibe_subscription show_if_simple">
				    <label for="subscription"><?php _e('Subscription', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta(get_the_ID(),'vibe_subscription',true);?>
		              <select name="vibe_subscription" id="vibe_subscription" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> >Show</option>
		              <option value="H" <?php echo  (($meta !='S')?'selected=selected':'');?> >Hide</option>
	               	  </select>
	               	  <span class="description"><?php _e('Enable subscription, users will subscribe to connected course for limited duration','wplms-woo'); ?></span>
			    </p>
			    <script>
			    	jQuery(document).ready(function($){
			    		if($('#vibe_subscription').val() != 'S'){
			    			$('.form-field.duration.show_if_simple').hide();
			    		}
			    		$('#vibe_subscription').on('change',function(){
			    			if($('#vibe_subscription').val() != 'S'){
				    			$('.form-field.duration.show_if_simple').hide(200);
				    		}else{
				    			$('.form-field.duration.show_if_simple').show(200);
				    		}
				    	});
			    	});
			    </script>
			    <p class="form-field duration show_if_simple">
			    	<?php $duration  = get_post_meta(get_the_ID(),'vibe_duration',true);?>
			        <label for="vibe_duration"><?php _e('Duration', 'wplms-woo'); ?></label>
			        <input type="number" name="vibe_duration" id="vibe_duration" value="<?php echo ((isset($duration))?$duration:'') ; ?>" size="20" />
			    	<span class="description"><?php _e('Add subscription duration. Duration for this users subscribe to connected courses.','wplms-woo'); ?></span>
			    </p>

			    <p class="form-field duration show_if_simple">
			    	<?php $product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,get_the_ID());?>
			        <label for="vibe_product_duration_parameter"><?php _e('Duration parameter', 'wplms-woo'); ?></label>
			        <select name="vibe_product_duration_parameter" id="vibe_product_duration_parameter">
			        	<option value="86400" <?php selected($product_duration_parameter,'86400');?>><?php _e('DAY', 'wplms-woo'); ?></option>
			        	<option value="604800" <?php selected($product_duration_parameter,'604800');?>><?php _e('WEEK', 'wplms-woo'); ?></option>
			        	<option value="2592000" <?php selected($product_duration_parameter,'2592000');?>><?php _e('MONTH', 'wplms-woo'); ?></option>
			        	<option value="31536000" <?php selected($product_duration_parameter,'31536000');?>><?php _e('YEAR', 'wplms-woo'); ?></option>
			        	<option value="3600" <?php selected($product_duration_parameter,'3600');?>><?php _e('HOUR', 'wplms-woo'); ?></option>
			        	<option value="60" <?php selected($product_duration_parameter,'60');?>><?php _e('MINUTE', 'wplms-woo'); ?></option>
			        </select>
			    	<span class="description"><?php _e('Duration parameter for selected duration','wplms-woo'); ?></span>
			    </p>
			    <?php
			    
			    ?>
			</div>
			<style>
			.wplms_options{display:none;}
			.wplms_options.show_if_wplms_checked{display:block;}
			</style>
			<script>jQuery(document).ready(function($){
				$('#vibe_wplms').each(function(){
					var value = $('.vibe_wplms').val();
					if($(this).is(':checked')){
						$('.wplms_options').addClass('show_if_wplms_checked');
					}else{
						$('.wplms_options').removeClass('show_if_wplms_checked');
					}
					$(this).on('change',function(){
						if($(this).is(':checked')){
							$('.wplms_options').addClass('show_if_wplms_checked');
						}else{
							$('.wplms_options').removeClass('show_if_wplms_checked');
						}
					});
				});
			});</script>
		<?php
	}

	function display_variation_selection($loop, $variation_data, $variation){
		$post_id = $variation->ID;
		$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$post_id);
	 	?>
	 		<script>
			    jQuery(document).ready(function($){
			    	$('#vibe_wplms').each(function(){
						if($(this).is(':checked')){
							$('.wplms_options').addClass('show_if_wplms_checked');
						}else{
							$('.wplms_options').removeClass('show_if_wplms_checked');
						}
						$(this).on('change',function(){
							if($(this).is(':checked')){
								$('.wplms_options').addClass('show_if_wplms_checked');
							}else{
								$('.wplms_options').removeClass('show_if_wplms_checked');
							}
						});
					});
		    		$('.vibe_woo_subscription').each(function(){
		    			var $this = $(this);
		    			var duration = $this.parent().parent().find('p.duration');
		    			if($this.val() == 'S'){
		    				duration.show();
		    			}else{
		    				duration.hide(200);
		    			}

		    			$this.on('change',function(){
		    				if($this.val() == 'S'){
			    				duration.show(200);
			    			}else{
			    				duration.hide(200);
			    			}
		    			});
		    		});
		    		$('.vibe_enable_course_retakes').each(function(){
		    			var $this = $(this);
		    			var course_retakes = $this.parent().parent().find('p.course_retakes');
		    			if($this.val() == 'S'){
		    				course_retakes.show();
		    			}else{
		    				course_retakes.hide(200);
		    			}

		    			$this.on('change',function(){
		    				if($this.val() == 'S'){
			    				course_retakes.show(200);
			    			}else{
			    				course_retakes.hide(200);
			    			}
		    			});
		    		});
		    		$('.vibe_enable_quiz_retakes').each(function(){
		    			var $this = $(this);
		    			var quiz_retakes = $this.parent().parent().find('p.quiz_retakes');
		    			if($this.val() == 'S'){
		    				quiz_retakes.show();
		    			}else{
		    				quiz_retakes.hide(200);
		    			}

		    			$this.on('change',function(){
		    				if($this.val() == 'S'){
			    				quiz_retakes.show(200);
			    			}else{
			    				quiz_retakes.hide(200);
			    			}
		    			});
		    		});
			    });
		    </script>
			<div class="options_group show_if_wplms_variable wplms_options" style="c;ear:both">
				<h4><span class="dashicons dashicons-welcome-learn-more"></span> <?php _e('LMS Settings','wplms-woo'); ?></h4>
			    <p class="form-row form-row-first vibe_subscription">
				    <label for="subscription"><?php _e('Subscription', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta($post_id,'vibe_subscription',true);?>
		              <select class="vibe_woo_subscription" name="vibe_subscription[<?php echo $loop; ?>]" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> ><?php _e('Enable','wplms-woo'); ?></option>
		              <option value="H" <?php echo  (($meta=='H')?'selected=selected':'');?> ><?php _e('Disable','wplms-woo'); ?></option>
	               	  </select>
			    </p>
			    <p class="form-row form-row-last duration">
			    	<?php $duration  = get_post_meta($post_id,'vibe_duration',true);?>
			        <label for="vibe_duration"><?php _e('Duration', 'wplms-woo'); ?></label>
			        <input type="number" name="vibe_duration[<?php echo $loop; ?>]" id="vibe_duration" value="<?php echo ((isset($duration))?$duration:'') ; ?>" size="20" />
			        <select name="vibe_product_duration_parameter[<?php echo $loop; ?>]">
			        	<option value="1" <?php selected('1',$product_duration_parameter)?>><?php _e('Seconds','wplms-woo'); ?></option>
			        	<option value="<?php echo MINUTE_IN_SECONDS; ?>" <?php selected(MINUTE_IN_SECONDS,$product_duration_parameter)?>><?php _e('Minutes','wplms-woo'); ?></option>
			        	<option value="<?php echo HOUR_IN_SECONDS; ?>" <?php selected(HOUR_IN_SECONDS,$product_duration_parameter)?>><?php _e('Hours','wplms-woo'); ?></option>
			        	<option value="<?php echo DAY_IN_SECONDS; ?>" <?php selected(DAY_IN_SECONDS,$product_duration_parameter)?>><?php _e('Days','wplms-woo'); ?></option>
			        	<option value="<?php echo WEEK_IN_SECONDS; ?>" <?php selected(WEEK_IN_SECONDS,$product_duration_parameter)?>><?php _e('Weeks','wplms-woo'); ?></option>
			        	<option value="<?php echo MONTH_IN_SECONDS; ?>" <?php selected(MONTH_IN_SECONDS,$product_duration_parameter)?>><?php _e('Months','wplms-woo'); ?></option>
			        	<option value="<?php echo YEAR_IN_SECONDS; ?>" <?php selected(YEAR_IN_SECONDS,$product_duration_parameter)?>><?php _e('Years','wplms-woo'); ?></option>
			        </select>
			    </p>
			    <p class="form-row form-row-first enable_certificate">
				    <label for="certificate"><?php _e('Certificate', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta($post_id,'vibe_enable_certificate',true);?>
		              <select class="vibe_enable_certificate" name="vibe_enable_certificate[<?php echo $loop; ?>]" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> ><?php _e('Enable Certificate','wplms-woo'); ?></option>
		              <option value="H" <?php echo  (($meta !='S')?'selected=selected':'');?> ><?php _e('Disable Certificate','wplms-woo'); ?></option>
	               	  </select>
			    </p>
			    <p class="form-row form-row-last enable_badge">
				    <label for="badge"><?php _e('Course Badge', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta($post_id,'vibe_enable_badge',true);?>
		              <select class="vibe_enable_certificate" name="vibe_enable_badge[<?php echo $loop; ?>]" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> ><?php _e('Enable Badge','wplms-woo'); ?></option>
		              <option value="H" <?php echo  (($meta !='S')?'selected=selected':'');?> ><?php _e('Disable Badge','wplms-woo'); ?></option>
	               	  </select>
			    </p>
			    <p class="form-row form-row-first enable_course_retakes">
				    <label for="certificate"><?php _e('Enable Course Retakes (Override Course settings)', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta($post_id,'vibe_enable_course_retakes',true);?>
		              <select class="vibe_enable_course_retakes" name="vibe_enable_course_retakes[<?php echo $loop; ?>]" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> ><?php _e('Enable','wplms-woo'); ?></option>
		              <option value="H" <?php echo  (($meta !='S')?'selected=selected':'');?> ><?php _e('Disable','wplms-woo'); ?></option>
	               	  </select>
			    </p>
			    <p class="form-row form-row-last course_retakes">
			    	<?php $retakes  = get_post_meta($post_id,'vibe_course_retakes',true);?>
			        <label for="vibe_course_retakes"><?php _e('Limit Course Retakes', 'wplms-woo'); ?></label>
			        <input type="number" name="vibe_course_retakes[<?php echo $loop; ?>]" id="vibe_course_retakes" value="<?php echo ((isset($retakes))?$retakes:'') ; ?>" size="20" />
			    </p>
			    <p class="form-row form-row-first enable_quiz_retakes">
				    <label for="certificate"><?php _e('Enable Quiz Retakes (Override Quiz settings)', 'wplms-woo'); ?></label>
					  <?php $meta  = get_post_meta($post_id,'vibe_enable_quiz_retakes',true);?>
		              <select class="vibe_enable_quiz_retakes" name="vibe_enable_quiz_retakes[<?php echo $loop; ?>]" >
		              <option value="S" <?php echo  (($meta=='S')?'selected=selected':'');?> ><?php _e('Enable','wplms-woo'); ?></option>
		              <option value="H" <?php echo  (($meta !='S')?'selected=selected':'');?> ><?php _e('Disable','wplms-woo'); ?></option>
	               	  </select>
			    </p>
			    <p class="form-row form-row-last quiz_retakes">
			    	<?php $retakes  = get_post_meta($post_id,'vibe_quiz_retakes',true);?>
			        <label for="vibe_quiz_retakes"><?php _e('Limit Quiz Retakes', 'wplms-woo'); ?></label>
			        <input type="number" name="vibe_quiz_retakes[<?php echo $loop; ?>]" id="vibe_quiz_retakes" value="<?php echo ((isset($retakes))?$retakes:'') ; ?>" size="20" />
			    </p>
			    <?php
			    if (( in_array( 'wplms-batches/wplms-batches.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'wplms-batches/wplms-batches.php'))) && (function_exists('bp_is_active') && bp_is_active('groups'))) {
			    ?>
			    <p class="form-row form-row-last course_batches">
			    	<?php $vibe_course_batches  = get_post_meta($post_id,'vibe_course_batches',true);
			    	if(!is_array($vibe_course_batches)){$vibe_course_batches=array($vibe_course_batches);}
			    	?>
			        <label for="vibe_course_batches"><?php _e('Select Course batches', 'wplms-woo'); ?></label>
			        <?php
			        	if(empty($this->batches)){
				        	global $wpdb,$bp;
				        	$batches = $wpdb->get_results("SELECT g.name as name,g.id as id FROM {$bp->groups->table_name} as g LEFT JOIN {$bp->groups->table_name_groupmeta} as meta ON g.id = meta.group_id WHERE meta.meta_key = 'course_batch' AND meta.meta_value=1");
				        	$this->batches = $batches; 
				        }else{
				        	$batches = $this->batches;
				        }

			        ?>
			        <select name="vibe_course_batches[]" class="select2_batches" multiple></option>
			        	<?php
			        	if(!empty($batches)){
			        	foreach($batches as $batch){
			        		echo '<option value="'.$batch->id.'" '.(in_array($batch->id,$vibe_course_batches)?'selected="selected"':'').'>'.$batch->name.'</option>';
			        	}
			        }else{
			        	echo '<option>'.__('No Batch found','wplms-woo').'</option>';
			        }

			        	?>
			        </select>
			    </p>
			    <?php
				}
			    ?>
			    <?php
			    do_action('wplms_woocommerce_meta_variable_product',$loop);
			    ?>
			</div>
		<?php

	}

	function process_simple_product_meta($post_id){
		$vibe_wplms = $_POST['vibe_wplms'];
		if(!empty($vibe_wplms)){
			$courses = $_POST['vibe_courses'];
			if(empty($courses)){
				delete_post_meta($post_id,'vibe_courses');
				delete_post_meta($post_id,'vibe_subscription');
			}else{
				update_post_meta($post_id,'vibe_wplms',1);
				$subscription = $_POST['vibe_subscription'];
				$duration = $_POST['vibe_duration'];
				update_post_meta($post_id,'vibe_courses',$courses);
				update_post_meta($post_id,'vibe_subscription',$subscription);
				update_post_meta($post_id,'vibe_duration',$duration);
				if(!empty($_POST['vibe_product_duration_parameter'])){
					update_post_meta($post_id,'vibe_product_duration_parameter',$_POST['vibe_product_duration_parameter']);
				}
			}
		}else{
			update_post_meta($post_id,'vibe_wplms',0);
			delete_post_meta($post_id,'vibe_courses');
		}
	}

	function process_variable_product_meta($post_id){
		$vibe_wplms = $_POST['vibe_wplms'];
		if(!empty($vibe_wplms)){
			$courses = $_POST['vibe_courses'];
			if(empty($courses)){
				delete_post_meta($post_id,'vibe_courses');
			}else{
				update_post_meta($post_id,'vibe_wplms',1);
				update_post_meta($post_id,'vibe_courses',$courses);
			}
		}else{
			update_post_meta($post_id,'vibe_wplms',0);
			delete_post_meta($post_id,'vibe_courses');
		}
	}
	function process_variable_product_meta_vars($post_id){
        $all_ids = $_POST['variable_post_id'];
        $max_id = max(array_keys($all_ids));
        for ($i = 0; $i <= $max_id; $i++) {

            // Skip non-existing keys
            if (!isset($all_ids[$i])) {
                continue;
            }

            $variable_post_id = (int) $all_ids[$i];
            if(!empty($_POST['variable_is_wplms'][$i])){
            	update_post_meta($variable_post_id,'variable_is_wplms',$_POST['variable_is_wplms'][$i]);
            	update_post_meta($variable_post_id,'vibe_subscription',$_POST['vibe_subscription'][$i]);
            	update_post_meta($variable_post_id,'vibe_duration',$_POST['vibe_duration'][$i]);
            	update_post_meta($variable_post_id,'vibe_product_duration_parameter',$_POST['vibe_product_duration_parameter'][$i]);
            	update_post_meta($variable_post_id,'vibe_enable_certificate',$_POST['vibe_enable_certificate'][$i]);
            	update_post_meta($variable_post_id,'vibe_enable_badge',$_POST['vibe_enable_badge'][$i]);
            	update_post_meta($variable_post_id,'vibe_enable_course_retakes',$_POST['vibe_enable_course_retakes'][$i]);
            	update_post_meta($variable_post_id,'vibe_course_retakes',$_POST['vibe_course_retakes'][$i]);
            	update_post_meta($variable_post_id,'vibe_enable_quiz_retakes',$_POST['vibe_enable_quiz_retakes'][$i]);
            	update_post_meta($variable_post_id,'vibe_quiz_retakes',$_POST['vibe_quiz_retakes'][$i]);
            	update_post_meta($variable_post_id,'vibe_course_batches',$_POST['vibe_course_batches'][$i]);
            }
        }
    }
}

Wplms_Woocommerce_Meta::init();