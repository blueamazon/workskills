<?php

/**
 * Initialise WPLMS Sell Quiz Plugins
 *
 * @class       Wplms_Sell_Quiz_Init
 * @author      H.K. Latiyan
 * @category    Admin
 * @package     WPLMS-Sell-Quiz/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Sell_Quiz_Init{
        
    public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new Wplms_Sell_Quiz_Init();
	    return self::$instance;
	}

    private function __construct(){   

        add_filter('wplms_quiz_metabox',array($this,'wplms_sell_quiz_as_product'));
        add_filter('wplms_start_quiz_button',array($this,'change_the_quiz_button'),999,2);
        
    } // END public function __construct

    function wplms_sell_quiz_as_product( $metabox ){

    	if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
        	$metabox['vibe_quiz_product'] = array(
						'label'	=> __('Associated Product','wplms-sq'), // <label>
						'desc'	=> __('Associated Product with the Course.','wplms-sq'), // description
						'id'	=> 'vibe_quiz_product', // field id and name
						'type'	=> 'selectcpt', // type of field
						'post_type' => 'product',
				        'std'   => ''
					);
    	}

    	if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && is_user_logged_in()) {
    		$levels = pmpro_getAllLevels();
				foreach($levels as $level){
					$level_array[] = array('value' =>$level->id,'label'=>$level->name);
				}
        	$metabox['vibe_quiz_pmpro_membership']=array(
						'label'	=> __('PMPro Membership','wplms-sq'), // <label>
						'desc'	=> __('Required Membership level for this quiz','wplms-sq'), // description
						'id'	=> 'vibe_quiz_pmpro_membership', // field id and name
						'type'	=> 'multiselect', // type of field
				        'options' => $level_array,
					);
    	}

    	if(in_array('wplms-mycred-addon/wplms-mycred-addon.php', apply_filters('active_plugins', get_option('active_plugins')))){

				$metabox['vibe_quiz_mycred_points'] = array( // Text Input
					'label'	=> __('MyCred Points','wplms-sq'), // <label>
					'desc'	=> __('MyCred Points required to take this quiz.','wplms-sq'),
					'id'	=> 'vibe_quiz_mycred_points', // field id and name
					'type'	=> 'number' // type of field
				);
    	}

		return $metabox;
	}

	function change_the_quiz_button( $button,$quiz_id ){
		global $post;
		
		$quiz_id = get_the_ID();
		$user_id = get_current_user_id();
		$flag = 1;
		$woo_flag = 0;$pm_flag= 0;
		if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
			$pid = get_post_meta($quiz_id,'vibe_quiz_product',true);
			if( isset($pid) && is_numeric($pid) && get_post_type($pid) == 'product' ){
		    	$product_taken = wc_customer_bought_product('',$user_id,$pid);
		      	if( !$product_taken ){
			        $pid = get_permalink($pid);
			        $check = vibe_get_option('direct_checkout');
			        $check = intval($check);
			        if( isset($check) &&  $check ){
			          $pid .= '?redirect';
		  		    }
		  		    $flag = 0;

		  		    $html = '<a href="'.$pid.'"class="button create-group-button full"> '.__('Take this Quiz','wplms-sq').'</a>';
		 		}else{
		 			
		 			$flag = 1;
		 		}
		 		$woo_flag = 1;
			}
		}

		if (empty($woo_flag) && in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && is_user_logged_in()) {
		    $membership_ids = vibe_sanitize(get_post_meta($quiz_id,'vibe_quiz_pmpro_membership',false));
		    if( isset($membership_ids) && count($membership_ids) >= 1 ){
		        $membership_taken = pmpro_hasMembershipLevel($membership_ids,$user_id);
		        if( !$membership_taken ){
		        	$pmpro_levels_page_id = get_option('pmpro_levels_page_id');
					$link = get_permalink($pmpro_levels_page_id);
					$html = '<a href="'.$link.'"class="button create-group-button full"> '.__('Take this Quiz','wplms-sq').'</a>';
					$flag = 0;
		        }else{
					$flag = 1;
		        }    
		    }
		    $pm_flag = 1;
		}

		if(empty($pm_flag) && in_array('wplms-mycred-addon/wplms-mycred-addon.php', apply_filters('active_plugins', get_option('active_plugins')))){
          	$points = get_post_meta($quiz_id,'vibe_quiz_mycred_points',true);
          	if(!empty($points)){
          		$mycred = mycred();
				$balance = $mycred->get_users_cred( $user_id );
				if(!empty($balance) && $balance < $points ){
					$flag = 0;
					$html = '<a href="#"class="button create-group-button full"> '.__('Take this Quiz','wplms-sq').'<span>'.__('<br/>Not enough points.','wplms-sq').'</span></a>';
				}

				if(!empty($points) && !empty($balance) &&  !$mycred->has_entry( 'purchase_quiz',$quiz_id,$user_id) ){
					$deduct = -1*$points;
					$mycred->update_users_balance( $user_id, $deduct);
					$mycred->add_to_log('purchase_quiz',$user_id,$deduct,__('Student subscibed to quiz','wplms-mycred'),$quiz_id);
					
		        }else{
		        	$flag = 1;
		        }
          	}
	    }

		if( !$flag ){
			return $html;
		}  
        return $button;
	}

} //End of class Wplms_Sell_Quiz_Init

Wplms_Sell_Quiz_Init::init();




add_action( 'widgets_init', 'wplms_my_purchased_quizzes_widget' );

function wplms_my_purchased_quizzes_widget() {
    register_widget('wplms_my_purchased_quizzes_widget');
}

class wplms_my_purchased_quizzes_widget extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
      $widget_ops = array( 'classname' => 'wplms_my_purchased_quizzes_widget', 'description' => __('Student Purchased Quizzes', 'wplms-dashboard') );
      $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_my_purchased_quizzes_widget' );
      parent::__construct( 'wplms_my_purchased_quizzes_widget', __(' DASHBOARD : Sell Quiz : My Purchased Quizzes', 'wplms-dashboard'), $widget_ops, $control_ops );
    }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
        extract( $args );

        if(!is_user_logged_in())
            return;


        //Our variables from the widget settings.
        $title = apply_filters('widget_title', $instance['title'] );
        $width =  $instance['width'];
        

        echo '<div class="'.$width.'">
                <div class="dash-widget">'.$before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;


       
        $user_id=get_current_user_id();
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT post_id as quiz_id, meta_value as product_id FROM {$wpdb->postmeta} WHERE meta_key = %s",'vibe_quiz_product'));
        if(!empty($results)){
          $product_ids = array();
          $quiz_ids = array();
          foreach($results as $result){
            
            if(!in_Array($result->product_id,$product_ids)){
              $product_purchased = wc_customer_bought_product('',$user_id,$result->product_id);  
              if(!empty($product_purchased)){
                $product_ids[]=$result->product_id;//$product_taken;  
              }
            }
            
            if(in_Array($result->product_id,$product_ids)){
              $quiz_ids[] = $result->quiz_id;
            }
          }

          if(!empty($quiz_ids)){
            echo '<ul class="purchased_quizzes">';
            foreach($quiz_ids as $quiz_id){
              $marks = get_post_meta( $quiz_id, $user_id,true);
              echo '<li style="padding: 8px 0;"><strong>'.get_the_title($quiz_id).'</strong><span class="right">'.(bp_course_get_user_quiz_status($user_id,$quiz_id)?$marks:'<a href="'.get_permalink($quiz_id).'" class="button small">'.__('Not attempted','wplms-sq')).'</a></span></li>';
            }
            echo '</ul>';
          }
        }else{
           _ex('No Quizzes purchased yet !','no purchased quizzes','wplms-sq');
           do_action('wplms_sell_quiz_no_quizzes_dashboard');
        }
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['width'] = $new_instance['width'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('My Purchased Quizzes','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12'
                    );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
            <option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','wplms-dashboard'); ?></option>
            <option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','wplms-dashboard'); ?></option>
            <option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','wplms-dashboard'); ?></option>
            <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','wplms-dashboard'); ?></option>
             <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','wplms-dashboard'); ?></option>
            <option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','wplms-dashboard'); ?></option>
          </select>
        </p>
        <?php 
    }
} 
