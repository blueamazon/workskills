<?php
/**
 *
 * @class       WPLMS_Application_Forms_Extended
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPLMS_Application_Forms_Extended{

	public $bp_course_filters;
	public $WPLMS_tips;
	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPLMS_Application_Forms_Extended();
	    return self::$instance;
	}

	private function __construct(){
		
		add_filter('lms_general_settings',array($this,'generate_waf_form'),100);
		add_filter('wplms_course_submission_tabs', array($this,'waf_course_application_tabs_instructor_access'), 9, 2);

		add_action('wplms_user_course_application',array($this,'user_course_application'),10,2);

		add_action( 'wp', array( $this, 'remove_hooks'));
		add_action('add_attachment', array($this,'rename_attachment'));

		add_action('wplms_course_unsubscribe', array($this, 'wplms_course_unsubscribe'), 10, 3);
		add_action( 'bp_course_remove_data', array( $this, 'waf_course_remove_data') );
		
		add_action('wp_ajax_manage_user_application', array( $this, 'wplms_manage_user_application') );
		if( class_exists('BP_Course_Ajax') )
			remove_action('wp_ajax_manage_user_application',array( BP_Course_Ajax::init(),'manage_user_application'));
			
		add_action('wplms_course_submission_applications_tab_content',array($this,'get_course_applications'), 9,1);
		add_action('wp_ajax_fetch_course_applications',array($this,'fetch_course_applications'));
		
		add_action('wp_head', 	array($this, 'waf_css'),	100);
		add_action('wp_footer', array($this, 'waf_js'),		100);

	} // END public function __construct

	function generate_waf_form($settings){
		
    	if( isset($_GET['sub']) && !empty($_GET['sub']) && $_GET['sub'] != 'general')
    		return $settings;

    	$settings[] = array(
			'label'=>__('Form Settings','wplms-af' ),
			'type'=> 'title',
		);

		$settings[] = array(
			'label'=>__('Please select Terms & Conditions and Privacy Policy page for Application Form','wplms-af' ),
			'type'=> 'heading',
		);

		$settings[] = array(
			'label' => __('Terms & Conditions page', 'wplms-af'),
			'name' 	=> 'terms_and_condition',
			'desc' 	=> __('Select a terms and conditions page.', 'wplms-af'),
		 	'type' 	=> 'cptselect',
            'cpt'	=> 'page',
		);

		$settings[] = array(
			'label' => __('Privacy Policy page', 'wplms-af'),
			'name' 	=> 'privacy_policy',
			'desc' 	=> __('Select a privacy policy page.', 'wplms-af'),
			'type' 	=> 'cptselect',
            'cpt'	=> 'page',
		);


		$settings[] = array(
			'label'=>__('Instructor Settings','wplms-af' ),
			'type'=> 'title',
		);
		
		$settings[] = array(
			'label'=>__('Please note, below functionality will disable access for application management for Instructors','wplms-af' ),
			'type'=> 'heading',
		);
		
		$settings[] = array(
       		'label' => __('Disable Instructor Access', 'wplms-af'),
			'name' => 'disable_instructor_access',
			'desc' => __('Check this checkbox to disable access for instructor from application management on frontend. By Default, instructor will have access to application management & he/she can approve/deny/reset applications.', 'wplms-af'),
			'type' => 'checkbox',
		);
    	
    	return $settings;
    }
	
	function waf_course_application_tabs_instructor_access( $tabs ){	
		$lms_settings = get_option('lms_settings');
		if( isset( $lms_settings['general']['disable_instructor_access'] ) && $lms_settings['general']['disable_instructor_access'] =='on' && !current_user_can( 'administrator' ) ){
			remove_filter('wplms_course_submission_tabs',array( $this->bp_course_filters,'apply_course_submission_tab'),10,2);
    	}
		return $tabs;
	}
	
	function user_course_application( $course_id,$user_id ){
		$check_apply_form = get_post_meta($course_id,'vibe_wplms_application_forms',true);
		$check_apply_content = get_post_meta($course_id,'vibe_wplms_application_forms_editor',true);
		if( !vibe_validate($check_apply_form) || empty($check_apply_content) ){
			global $wpdb;
			$wpdb->replace( 
				$wpdb->applications, 
				array(
					'user_id' => $user_id ,
					'course_id' => $course_id ,
					'do_process' => NULL,
					'application_form' => 'N/A',
					'attachments' => NULL, 
					'status' => 2 ,
				)
			);
		}
	}
		
	function remove_hooks(){
		$this->bp_course_filters = bp_course_filters::init();
		$this->WPLMS_tips = WPLMS_tips::init();
		
		if( is_user_logged_in() && is_singular('course') ){
			global $post;
			$course_id = $post->ID;
			$check = get_post_meta($course_id,'vibe_course_apply',true);
			if( vibe_validate($check) ){
				$user_id = get_current_user_id();
				//$check_apply = get_user_meta($user_id,'apply_course'.$course_id,true);
				global $wafdb;
				$status = $wafdb->get_application_status( $user_id, $course_id );
				if( $status == 1 ){			
					remove_filter('wplms_take_this_course_button_label',array( $this->bp_course_filters,'apply_course_button_label'),10,2);
					remove_filter('wplms_private_course_button_label',array( $this->bp_course_filters,'apply_course_button_label'),10,2);
					remove_filter('wplms_course_product_id',array( $this->bp_course_filters,'apply_course_button_link'),10,2);
					remove_filter('wplms_private_course_button',array( $this->bp_course_filters,'apply_course_button_link'),10,2);
				}else if( class_exists('Wplms_Woo_Front') ){
					$this->remove_course_variations();
				}
				add_filter('wplms_auto_subscribe',array($this->WPLMS_tips,'disable_auto_subscribe'));
				remove_filter('wplms_private_course_button',array($this->WPLMS_tips,'manual_subscription'),10,2);
				remove_action('template_redirect',array($this->WPLMS_tips,'subscribe_free_course'),8);

				if(!bp_course_is_member($course_id,$user_id)){
					$this->show_course_credits();
				}
			}
		}
		else{
			$this->show_course_credits();
			if( class_exists('Wplms_Woo_Front') ){
				global $post;
				$course_id = $post->ID;
				$check = get_post_meta($course_id,'vibe_course_apply',true);
				if( vibe_validate($check) )
					$this->remove_course_variations();
			}
		}
	}
	
	public function show_course_credits(){
		remove_filter('wplms_course_details_widget',array( $this->bp_course_filters,'hide_price'),10,2);
		add_filter('wplms_course_credits_array',array( $this,'get_course_application_credits'),10,2 );	
		add_filter('wplms_course_front_details',array( $this,'remove_course_application_credits_hook') );	
	}
	
	public function remove_course_variations(){
		$Wplms_Woo_Front = Wplms_Woo_Front::init();
		remove_filter('wplms_take_course_button_html',array($Wplms_Woo_Front,'woocommerce_variable_form'),999,2);
		remove_filter('wplms_course_partial_credits',array($Wplms_Woo_Front,'woocommerce_variable_form_in_partial_course'),10,2);
		remove_filter('wplms_expired_course_button',array($Wplms_Woo_Front,'renew_form'),10,2);
		remove_filter('template_redirect',array($Wplms_Woo_Front,'redirect'));
	}
	
	public function get_course_application_credits( $credits,$id ){
		
		$free_course = get_post_meta($id,'vibe_course_free',true);
		$apply_course = get_post_meta($id,'vibe_course_apply',true);
		
		if( !vibe_validate($free_course) && vibe_validate($apply_course) ) {
			
			$credits =array();
			
			$product_id = get_post_meta($id,'vibe_product',true);
			if(isset($product_id) && $product_id !='' && function_exists('wc_get_product')){ //WooCommerce installed
				$product = wc_get_product( $product_id );
				if(is_object($product)){
					$link = get_permalink($product_id);
					$check = vibe_get_option('direct_checkout');
        			if(isset($check) && $check)
        				$link .= '?redirect';
        			$price_html = str_replace('class="amount"','class="amount"',$product->get_price_html());
					$credits[$link] = '<strong>'.$price_html.'</strong>';
				}
			}
		
			if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
				$membership_ids = vibe_sanitize(get_post_meta($id,'vibe_pmpro_membership',false));
				if(isset($membership_ids) && is_Array($membership_ids) && count($membership_ids) && function_exists('pmpro_getAllLevels')){
				//$membership_id = min($membership_ids);
				$levels=pmpro_getAllLevels();
					foreach($levels as $level){
						if(in_array($level->id,$membership_ids)){
							$link = get_option('pmpro_levels_page_id');
							$link = get_permalink($link).'#'.$level->id;
							$credits[$link] = '<strong>'.$level->name.'</strong>';
						}
					}
				}
			  }

			$course_credits = get_post_meta($id,'vibe_course_credits',true);
			if(isset($course_credits) && $course_credits != '' ){
				$credits[] = '<strong>'.$course_credits.'</strong>';
			}
		
		}
		return $credits;
	}
	
	function remove_course_application_credits_hook( $return ){
		remove_filter('wplms_course_credits_array',array( $this,'get_course_application_credits'),10,2 );	
		return $return;
	}
	

	function rename_attachment( $post_ID ){
		//Only rename file name when it is uploaded WPLMS ajax form
		if( doing_action( 'wp_ajax_insert_form_file_final' ) ){
			$post = get_post($post_ID);
			$file = get_attached_file($post_ID);
			$path = pathinfo($file);
			$suffix = $this->random_name();
	
			// change to $new_name = $count; if you want just the count as filename
			$new_name = $path['filename'] . '_' . $suffix;
			$new_file = $path['dirname'] . '/' . $new_name . '.' . $path['extension'];
			rename($file, $new_file);
			update_attached_file($post_ID, $new_file);
		}
	}
	
	function random_name(){
		$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		return substr(str_shuffle($permitted_chars), 2, 10);
	}
	
	function wplms_course_unsubscribe($course_id, $user_id, $group_id){
		$apply_course = get_post_meta($course_id,'vibe_course_apply',true);
		
		if( vibe_validate($apply_course) ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->applications,
				array(
					//'do_process' => date("Y-m-d H:i:s"),
					'status' => 4,
				),
				array( 'user_id' => $user_id,  'course_id' => $course_id )
			);
		}
	}
	
	function waf_course_remove_data( $user_id ){
		global $wpdb;
		$wpdb->delete(
			$wpdb->applications,
			array( 'user_id' => $user_id )
		);
	}
	
	function wplms_manage_user_application(){

		$user_id = $_POST['user_id'];
        $course_id = $_POST['course_id'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id.$user_id) || !is_numeric($course_id) || !is_numeric($user_id)){
           _e('Security check Failed. Contact Administrator.','vibe');
           die();
        }
        $action = $_POST['act'];
		global $wpdb;
		$user = get_userdata( $user_id );
        switch($action){

			case 'approve':
				$free_course	= get_post_meta($course_id,'vibe_course_free',true);
				$product_id		= get_post_meta($course_id,'vibe_product',true);
				$product 		= wc_get_product( $product_id );
				add_filter( 'woocommerce_order_is_paid_statuses', array($this, 'wc_get_is_paid_statuses') );
				if ( 
						vibe_validate($free_course) || 
						( !vibe_validate($free_course) && ( $product_id =='' || !is_object($product) || !$product->exists() ) ) || 
						( isset($product_id) && $product_id !='' && wc_customer_bought_product( $user->user_email, $user_id, $product_id ) )
					)
					bp_course_add_user_to_course($user_id,$course_id);
					
				$wpdb->update(
					$wpdb->applications,
					array(
						'do_process' => date("Y-m-d H:i:s"),
						'status' => 1,
					),
					array( 'user_id' => $user_id,  'course_id' => $course_id )
				);
			break;

			case 'reject':
				$wpdb->update(
					$wpdb->applications,
					array(
						'do_process' => date("Y-m-d H:i:s"),
						'status' => 3,
					),
					array(  'user_id' => $user_id,  'course_id' => $course_id  )
				);
			break;

			case 'delete':
				if( !current_user_can( 'administrator' ) ) die;
				
				global $wafdb;				
				$wafdb->delete_application_form( $user_id, $course_id );
				die;
			break;

			case 'enable':
				$wpdb->update(
					$wpdb->applications,
					array(
						'do_process' => date("Y-m-d H:i:s"),
						'status' => 5,
					),
					array(  'user_id' => $user_id,  'course_id' => $course_id  )
				);
			break;

			default:
			break;

        }
        delete_user_meta($user_id,'apply_course'.$course_id,$course_id);
        do_action('wplms_manage_user_application',$action,$course_id,$user_id);
        die();

	}
	
	function get_course_applications($course_id){
		?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#fetch_course_applications').on('click',function(){
						var $this = $(this);
						var parent = $(this).parent();
						$('.course_applications').remove();
						$this.append('<i class="fa fa-spinner"></i>');
						$('#applications .message, #applications ul').remove();
						$.ajax({
	                      	type: "POST",
	                      	url: ajaxurl,
	                      	data: { action: 'fetch_course_applications', 
	                              	security: $('#pending_course_applications').val(),
	                              	course_id:<?php echo $course_id; ?>,
	                              	status:$('#fetch_course_application_status').val(),
	                            	},
	                      	cache: false,
	                      	success: function (html) {
	                      		parent.after(html);
	                      		$this.find('.fa').remove();
	                      		$('#course').trigger('loaded');
	                      	}
	                    });
					});
				});
			</script>
			<div class="submissions_form">
				<select id="fetch_course_application_status">
					<option value="2"><?php echo _x('Pending','Application status','wplms-af') ?></option>
					<option value="1"><?php echo _x('Approved','Application status','wplms-af') ?></option>
					<option value="3"><?php echo _x('Rejected','Application status','wplms-af') ?></option>
					<option value="4"><?php echo _x('Removed','Application status','wplms-af') ?></option>
					<option value="5"><?php echo _x('Re-Enabled','Application status','wplms-af') ?></option>
				</select>
				<?php wp_nonce_field('pending_course_applications','pending_course_applications'); ?>
				<a id="fetch_course_applications" class="button"><?php echo _x('Get','get applications button','wplms-af'); ?></a>
			</div>
		<?php
	}
	
	function wc_get_is_paid_statuses(){
		return  array( 'completed' );	
	}
	
	 function fetch_course_applications(){

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'pending_course_applications') || !is_numeric($_POST['course_id']) || !is_numeric($_POST['status']) ){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
        }

        $course_id 	= $_POST['course_id'];
		$status 	= $_POST['status'];
		
		if( $status == 2 ){
			 BP_Course_Action::get_course_applications($course_id);			
		}else{
			
			global $wpdb;
			
			$query = $wpdb->prepare( "SELECT $wpdb->applications.*, u.user_email as email, u.display_name, p.post_title	as course_name	
					FROM $wpdb->applications
					INNER JOIN  $wpdb->posts p
							ON $wpdb->applications.course_id = p.ID
					INNER JOIN  $wpdb->users u
							ON $wpdb->applications.user_id = u.id
							
					WHERE $wpdb->applications.id = $wpdb->applications.id 
							 AND $wpdb->applications.status = %s
							AND ( p.ID = %d)
					ORDER BY $wpdb->applications.id DESC", $status, $course_id );
					//LIMIT $this->per_page OFFSET $offset";
			
			$items = $wpdb->get_results($query);
			
			if( count($items) ){
				echo '<ul>';
				foreach($items as $item){
				?>
					<li class="user clear" data-id="<?php echo $item->user_id; ?>" data-course="<?php echo $course_id; ?>" data-security="<?php echo wp_create_nonce('security'.$course_id.$item->user_id); ?>">
						<div class="user_data col-md-5">
                        	<p class="help-block clear"><?php echo get_avatar($item->user_id).bp_core_get_userlink( $item->user_id );?></p><br class="clearfix" />
                            <p class="help-block">
								<?php echo  __('<strong>Date of submission:</strong> ','wplms-af').$item->do_submission; ?>	<br />
                                <?php echo  $item->do_process != NULL ? __('<strong>Application processed on:</strong> ','wplms-af').$item->do_process : ''; ?>
                            </p>
                        </div>						
						<div class="user_application_form col-md-6"><?php echo $item->application_form ?></div>  
						<?php if( $status == 3 ){ ?><span class="reset enable"><?php echo _x('Reset','Reset user application for course','vibe'); ?></span><?php } ?>          
					</li>
				<?php
				}
				echo '</ul>';
			}else{ ?>
				 <div class="message">
					<p><?php echo _x('No applications found !','No applications found in course, error on course submissions','vibe'); ?></p>
				</div><?php
			}
		}
		if( in_array( $status, array( 2, 3 ) ) ){ 
		?>
        	<script type="text/javascript">
				$('#applications ul li span').on('click',function(){
					  var $this = $(this);
					  var action = 'reject';
					  if($this.hasClass('approve')){
						action = 'approve';
					  }
					  if($this.hasClass('enable')){
						action = 'enable';
					  }
					  $this.addClass('loading');
						$.ajax({
							type: "POST",
							url: ajaxurl,
							data: { action: 'manage_user_application',
									act:action,
									security: $this.parent().attr('data-security'),
									user_id:$this.parent().attr('data-id'),
									course_id:$this.parent().attr('data-course'),
								  },
							cache: false,
							success: function (html) {
								$this.removeClass('loading');
								$this.addClass('active');
								setTimeout(function(){$this.parent().remove(); }, 1000);
							}
						});
					});
					
			</script>
        <?php
		}
        die();
    }
	
	function waf_css(){
		?>
        <style type="text/css" id="waf-css">
			.external-link		{font-size:22px;color:#43afbf}
			.message.approved,
			.message.pending,
			.message.rejected,
			.message.removed,	
			.message.enabled	{ border-top-width: 3px; }			
			.message.approved	{ border-color: #8fae1b; background: transparent;}
			.message.pending	{ }
			.message.rejected	{ border-color: #b81c23; color: white; background: #cb8282;}
			.message.removed,
			.message.enabled	{ border-color: #1e85be; background: white;}
			#applications ul li span.enable{
				float:right;
				margin-left:10px;
				margin-top:10px;
				font-size:11px;
				color:#bbb;cursor:pointer;
				text-transform: uppercase;
				text-align: center;
			}
			#applications ul li span.enable:before{
				display:block;
				font-size:24px;
				color:#bbb;
				content:"\f10c";
				line-height: 1;
				font-family:'fontawesome';
			}
			#applications ul li span.enable.loading:before{content:"\f110"}
			#applications ul li span.enable.active,
			#applications ul li span.enable:hover{color:#70c989;}
			#applications ul li span.enable:hover:before,
			#applications ul li span.enable.active:before{content:"\f021";color:#70c989;}

			.terms_privacy {
			    margin-top: 10px;
			    text-align: center;
			    font-size: 13px;
			    line-height: 16px;
			}
			.terms_privacy label {
			    cursor: pointer;
			    font-weight: 500;
			    color: #808080;
			}
			.terms_privacy input[type=checkbox] {
			    display: inline-block;
			    width: auto;
			    vertical-align: bottom;
			}
			.terms_privacy a {
			    color: #0f85ad;
			}
		</style>
        <?php
	}
	
	function waf_js(){
		?>
        <script type="text/javascript" id="waf-js">
		</script>
        <?php		
	}
	
} // END class WPLMS_Application_Forms_Extended