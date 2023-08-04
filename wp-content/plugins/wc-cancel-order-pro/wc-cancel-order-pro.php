<?php
/**
*Plugin Name: WC Cancel Order Pro
*Plugin URI: https://wpexpertshub.com
*Description: Allow customers to send order cancellation request from my account page.
*Author: WpExperts Hub 
*Version: 3.8
*Author URI: https://wpexpertshub.com
*Text Domain: wc-cancel-order
*WC requires at least: 5.4
*WC tested up to: 6.8
*Requires at least: 5.4
*Tested up to: 6.0 
*Requires PHP: 7.2
**/

defined( 'ABSPATH' ) || exit;

class WC_Cancel_Order_Pro{

	protected $settings = array();
	protected static $_instance = null;

	public static function instance(){
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function __construct(){

		if(!defined('WC_CANCEL_DIR')){
			@define('WC_CANCEL_DIR',__DIR__);
		}
        if(!defined('WC_CANCEL_VERSION')){
            define('WC_CANCEL_VERSION',3.8);
        }
        if(!defined('WC_CANCEL_SC_FOOTER')){
            @define('WC_CANCEL_SC_FOOTER',true);
        }

        register_activation_hook(__FILE__,array($this,'wc_cancel_sql'));
		add_filter('plugin_action_links_'.plugin_basename(__FILE__),array($this,'wc_cancel_action_links'),10,1);
		spl_autoload_register(array($this,'init_autoload'));

        add_action('init',array($this,'wc_cancel_text'));
		add_action('init',array($this,'plugin_updates'));

	    add_action('woocommerce_loaded',array($this,'load_wc_cancel_order'));
	    add_action('woocommerce_admin_field_wc_cancel_setting',array($this,'wc_cancel_setting_view'),10,1);
		add_action('admin_enqueue_scripts',array($this,'wc_cancel_admin_scripts'),999);
		add_filter('woocommerce_screen_ids',array($this,'add_screen_id'),999,1);
		add_action('woocommerce_update_options',array($this,'wc_cancel_save_settings'),10,1);

		add_filter('woocommerce_my_account_my_orders_actions',array($this,'add_cancel_button'),100,2);
		add_action('um_woocommerce_orders_tab_actions',array($this,'um_add_cancel_button'),100,2);

		add_action('wp_enqueue_scripts',array($this,'wc_cancel_front_scripts'),10);
		add_action('wp_enqueue_scripts',array($this,'enqueue_scripts'),20);

		add_action('wp_ajax_wc_cancel_request',array($this,'wc_cancel_request'));
		add_action('wp_ajax_nopriv_wc_cancel_request',array($this,'wc_cancel_request'));
		add_action('wp_ajax_wc-cancel-request',array($this,'wc_cancel_request_backend'));

		add_action('init',array($this,'register_status'),999);
		add_filter('wc_order_statuses',array($this,'add_wc_cancel_status'));
		add_action('admin_menu',array($this,'admin_menu'));
		add_filter('woocommerce_email_classes',array($this,'wc_cancel_email_classes'),999,1);
		add_action('woocommerce_email_wc_cancel_reason',array($this,'add_cancellation_reason'),10,4);
		add_action('woocommerce_order_status_changed',array($this,'trigger_emails'),999,4);
		add_action('woocommerce_checkout_update_order_meta',array($this,'wc_cancel_key'),1,2);
		add_action('wc_cancel_request',array($this,'wc_cancel_clear_key'),10,1);
		add_filter('the_posts',array($this,'guest_cancel_page'));
		add_shortcode('wc_cancel_order_details',array($this,'wc_cancel_order_details'));
		add_action('woocommerce_email_customer_details',array( $this,'add_cancel_link' ),999,3);

		//Wc Subscription
		add_filter('wcs_view_subscription_actions',array($this,'subscription_actions'),10,2);
		add_action('woocommerce_my_subscriptions_actions',array($this,'um_subscription_actions'),10,1);

    }

	function plugin_updates(){
		$plugin_file = basename(__DIR__).'/'.basename(__FILE__);
		$plugin_slug = strtolower(basename(__DIR__));
		$this->licence = new WpExpertshub_Licence($plugin_slug,$plugin_file);
	}

	function wc_cancel_action_links($links){
		$plug_link = array(
			'<a href="'.admin_url('admin.php?page=wc-settings&tab=wc_cancel_settings').'">'.__('Settings','wc-cancel-order').'</a>',
		);
		$slug = strtolower(basename(__DIR__));
		$key_status = get_option('_'.$slug.'_key_status');
		$status_text = $key_status=='active' ? __('Deactivate License','wc-cancel-order') : __('Activate License','wc-cancel-order');
		$plug_link[] = '<a href="'.admin_url('plugins.php?page=wpexperts-hub-license').'">'.$status_text.'</a>';
		return array_merge($links,$plug_link);
	}

	function plugin_url(){
		return untrailingslashit(plugins_url('/', __FILE__ ));
	}

	function clean_str($str){
        return sanitize_text_field($str);
	}

	function load_wc_cancel_order(){
		$this->load_settings();
    	include(dirname(__FILE__) . '/includes/settings.php');
		WC_Cancel_Settings::init();
	}

	function wc_cancel_save_settings(){
		if(isset($_POST['wc-cancel'])){
			$this->settings = array(
				'req-status' => isset($_POST['wc-cancel']['req-status']) ? $_POST['wc-cancel']['req-status'] : array(),
				'cancel-status' => isset($_POST['wc-cancel']['cancel-status']) ? $_POST['wc-cancel']['cancel-status'] : array(),
				'approval' => isset($_POST['wc-cancel']['approval']) ? $_POST['wc-cancel']['approval'] : 'wc-cancelled',
				'decline' => isset($_POST['wc-cancel']['decline']) ? $_POST['wc-cancel']['decline'] : 'wc-processing',
				'role' => isset($_POST['wc-cancel']['role']) ? $_POST['wc-cancel']['role'] : array('administrator','customer'),
				'reason' => isset($_POST['wc-cancel']['reason']) && $_POST['wc-cancel']['reason']==1 ? true : false,
				'reason-options' => isset($_POST['wc-cancel']['reason-options']) ? stripslashes_deep($_POST['wc-cancel']['reason-options']) : '',
				'text-input' => isset($_POST['wc-cancel']['text-input']) ? $_POST['wc-cancel']['text-input'] : 'display-always',
				'text-required' => isset($_POST['wc-cancel']['text-required']) ? $_POST['wc-cancel']['text-required'] : 0,
				'hide-cancel' => isset($_POST['wc-cancel']['hide-cancel']) ? $_POST['wc-cancel']['hide-cancel'] : false,
				'time-interval' => isset($_POST['wc-cancel']['time-interval']) ? $_POST['wc-cancel']['time-interval'] : 0,
				'time-period' => isset($_POST['wc-cancel']['time-period']) ? $_POST['wc-cancel']['time-period'] : 0,
				'confirm-note' => isset($_POST['wc-cancel']['confirm-note']) ? stripslashes_deep($_POST['wc-cancel']['confirm-note']) : '',
				'guest-cancel' => isset($_POST['wc-cancel']['guest-cancel']) ? $_POST['wc-cancel']['guest-cancel'] : 0,
			);
			update_option('wc_cancel_settings',$this->settings,'no');
		}
		$this->load_settings();
	}

	function load_settings(){

		$pro_settings = get_option('wc_cancel_settings');
		$cancel_options = array(
			__('The order was placed by mistake.','wc-cancel-order'),
			__('I ordered wrong product.','wc-cancel-order'),
			__('Other.','wc-cancel-order'),
		);
		$this->settings = array(
			'req-status' => isset($pro_settings['req-status']) ? $pro_settings['req-status'] : array('wc-pending','wc-processing','wc-on-hold'),
			'cancel-status' => isset($pro_settings['cancel-status']) ? $pro_settings['cancel-status'] : array(),
			'approval' => isset($pro_settings['approval']) ? $pro_settings['approval'] : 'wc-cancelled',
			'decline' => isset($pro_settings['decline']) ? $pro_settings['decline'] : 'wc-processing',
			'role' => isset($pro_settings['role']) ? $pro_settings['role'] : array('administrator','customer'),
			'reason' => isset($pro_settings['reason']) ? $pro_settings['reason'] : 1,
			'reason-options' => isset($pro_settings['reason-options']) ? stripslashes_deep($pro_settings['reason-options']) : implode("\r\n",$cancel_options),
			'text-input' => isset($pro_settings['text-input']) ? $pro_settings['text-input'] : 'display-always',
			'text-required' => isset($pro_settings['text-required']) ? $pro_settings['text-required'] : 0,
			'hide-cancel' => isset($pro_settings['hide-cancel']) ? $pro_settings['hide-cancel'] : false,
			'time-interval' => isset($pro_settings['time-interval']) ? $pro_settings['time-interval'] : 0,
			'time-period' => isset($pro_settings['time-period']) ? $pro_settings['time-period'] : 0,
			'confirm-note' => isset($pro_settings['confirm-note']) ? stripslashes_deep($pro_settings['confirm-note']) : '',
			'guest-cancel' => isset($pro_settings['guest-cancel']) ? $pro_settings['guest-cancel'] : 1,
		);
	}

	function get_settings(){
		return $this->settings;
	}

	function wc_cancel_get_order_statuses(){

		$statuses = wc_get_order_statuses();
		if(isset($statuses['wc-cancelled'])){
			unset($statuses['wc-cancelled']);
		}
		if(isset($statuses['wc-refunded'])){
			unset($statuses['wc-refunded']);
		}
		if(isset($statuses['wc-failed'])){
			unset($statuses['wc-failed']);
		}
		if(isset($statuses['wc-cancel-request'])){
			unset($statuses['wc-cancel-request']);
		}
		return apply_filters('wc_cancel_order_status',$statuses);
	}

	function wc_cancel_get_statuses(){
		$statuses = wc_get_order_statuses();
		if(isset($statuses['wc-cancel-request'])){
			unset($statuses['wc-cancel-request']);
		}
		return apply_filters('wc_cancel_default_status',$statuses);
	}

	function get_status($status){
		if(is_array($status) && !empty($status)){
			foreach($status as $k=>$v){
				$status[$k] = 'wc-' === substr($v,0,3) ? substr($v,3) : $v;
			}
		}
		elseif(is_string($status))
		{
			$status = 'wc-' === substr($status,0,3) ? substr($status,3) : $status;
		}
		return $status;
	}

	function check_order_customer($order){
		$customer_id = $order->get_customer_id();
		$user_id = get_current_user_id();
		return $user_id>0 && $user_id==$customer_id ? true : false;
	}

	function user_has_role(){
		$has_role = false;
		if(isset($this->settings['guest-cancel']) && $this->settings['guest-cancel']){
			return true;
		}
		elseif(is_array($this->settings['role']) && !empty($this->settings['role'])){
			$user = wp_get_current_user();
			foreach($this->settings['role'] as $role){
				if(in_array($role,(array)$user->roles)){
					$has_role = true;
					break;
				}
			}
		}
		return $has_role;
	}

	function add_cancel_button($actions,$order){
		$actions = $this->get_cancel_action($order,$actions);
		return $actions;
	}

	function get_cancel_action($order,$actions = array()){
		if(is_array($actions) && !empty($actions)){
			foreach($actions as $key=>$value){
				if('cancel' === $key){
					unset($actions[$key]);
				}
			}
		}
		if($this->check_order_time($order) && !$this->is_declined_in_past($order) && is_a($order,'WC_Order') && $order->has_status($this->get_status($this->settings['req-status']))){
			if($this->user_has_role()){
				$actions['wc-cancel-order'] = array(
					'url'=>wp_nonce_url(admin_url('admin-ajax.php?action=wc_cancel_request&order_id='.$order->get_id().'&order_num='.$order->get_order_number()),'wc-cancel-request'),
					'name'=> __('Cancel Request','wc-cancel-order'),
					'action'=>'cancel-request',
				);
			}
		}
		elseif($this->check_order_time($order) && !$this->is_declined_in_past($order) && is_a($order,'WC_Order') && $order->has_status($this->get_status($this->settings['cancel-status']))){
			if($this->user_has_role()){
				$actions['wc-cancel-order'] = array(
					'url'=>wp_nonce_url(admin_url('admin-ajax.php?action=wc_cancel_request&order_id='.$order->get_id().'&order_num='.$order->get_order_number()),'wc-cancel-request'),
					'name'=> __('Cancel','wc-cancel-order'),
					'action'=>'cancel-request',
				);
			}
		}
        $actions = apply_filters('wc_cancel_order_btn',$actions,$order);
		return $actions;
	}

	function add_cancel_btn($actions){
		if(!empty($actions)){
			foreach($actions as$key=>$action){
				echo '<a href="'.esc_url($action['url']).'" class="woocommerce-button button ' . sanitize_html_class($key).'">'.esc_html($action['name']).'</a>';
			}
		}
	}

	function um_add_cancel_button($order,$customer_orders){
		$actions = $this->get_cancel_action($order);
		$this->add_cancel_btn($actions);
	}

	function add_screen_id($screen_ids){
		$screen_ids[] = 'woocommerce_page_wc_cancel';
		return $screen_ids;
	}

	function wc_cancel_admin_scripts(){

		$init_script = false;
		$screen = get_current_screen();
		if(isset($screen->id) && in_array($screen->id,array('woocommerce_page_wc-settings'))){
			$init_script = isset($_REQUEST['tab']) && $_REQUEST['tab']=='wc_cancel_settings' ? true : false;
		}
		elseif(isset($screen->id) && in_array($screen->id,array('woocommerce_page_wc_cancel'))){
			$init_script = true;
			$Admin_Assets = new WC_Admin_Assets();
			$Admin_Assets->admin_scripts();
			$Admin_Assets->admin_styles();

			wp_enqueue_script('fancybox',$this->plugin_url().'/assets/js/jquery.fancybox.min.js',array('jquery'),WC_CANCEL_VERSION,WC_CANCEL_SC_FOOTER);
			wp_enqueue_style('fancybox',$this->plugin_url().'/assets/css/jquery.fancybox.min.css',array(),WC_CANCEL_VERSION);
			wp_register_script('wc-cancel-admin',$this->plugin_url().'/assets/js/admin.js',array('fancybox'),WC_CANCEL_VERSION,WC_CANCEL_SC_FOOTER);
			$translation_array = array(
				'wcc_view'         => __('Cancellation Request Detail','wc-cancel-order'),
				'wcc_approval'     => __('Approve Cancellation Request ?','wc-cancel-order'),
				'wcc_decline'      => __('Decline Cancellation Request ?','wc-cancel-order'),
				'wcc_order_text'   => __('Order #','wc-cancel-order'),
				'wcc_confirm_btn'  => __('Confirm','wc-cancel-order'),
				'wcc_close'        => __('Close','wc-cancel-order'),
				'wcc_ajax'         => admin_url( 'admin-ajax.php' ),
				'wcc_nonce'        => wp_create_nonce('wc-cancel-back'),
			);
			wp_localize_script('wc-cancel-admin','wc_cancel_back',$translation_array);
			wp_enqueue_script('wc-cancel-admin');
		}

		if($init_script){
			wp_enqueue_style('wc_cancel-admin',$this->plugin_url().'/assets/css/admin.css');
		}
	}

	function wc_cancel_front_scripts(){
		$reasons = explode("\r\n",$this->settings['reason-options']);
		if(is_array($reasons) && !empty($reasons)){
			foreach($reasons as $rk=>$rv){
				$reasons[$rk] = __($rv,'wc-cancel-order');
			}
		}

		wp_register_script('fancybox',$this->plugin_url().'/assets/js/jquery.fancybox.min.js',array('jquery'),WC_CANCEL_VERSION,WC_CANCEL_SC_FOOTER);
		wp_register_style('fancybox',$this->plugin_url().'/assets/css/jquery.fancybox.min.css',array(),WC_CANCEL_VERSION);
		wp_register_style('wc-cancel-style',$this->plugin_url().'/assets/css/front.css',array(),WC_CANCEL_VERSION);

		wp_register_script('wc-cancel-script',$this->plugin_url().'/assets/js/front.js',array('fancybox'),WC_CANCEL_VERSION,WC_CANCEL_SC_FOOTER);
		$translation_array = array(
			'wcc_reason'       => $this->settings['reason'] && $this->settings['reason']=='1' ? true : false,
			'wcc_reasons'      => $reasons,
			'wcc_text_input'   => isset($this->settings['text-input']) ? $this->settings['text-input'] : '',
			'wcc_text_required'=> $this->settings['text-required'] && $this->settings['text-required']=='1' ? true : false,
			'wcc_note'         => __($this->settings['confirm-note'],'wc-cancel-order'),
			'wcc_head_text'    => __('Request Order Cancellation','wc-cancel-order'),
			'wcc_order_text'   => __('Order #','wc-cancel-order'),
			'wcc_r_head'       => __('Please select a reason for cancellation.','wc-cancel-order'),
			'wcc_additional'   => __('Additional details','wc-cancel-order'),
			'wcc_confirm'      => __('Confirm Cancellation','wc-cancel-order'),
			'wcc_close'        => __('Close','wc-cancel-order'),
			'wcc_error'        => __('Cancellation reason required!','wc-cancel-order'),
			'wcc_txt_error'    => __('Additional details required!','wc-cancel-order'),
			'wcc_ajax'         => admin_url( 'admin-ajax.php' ),
			'wcc_nonce'        => wp_create_nonce('wc-cancel-request'),
		);
		wp_localize_script('wc-cancel-script','wc_cancel',$translation_array);
	}

	function enqueue_scripts(){
		global $post;
		//$query_vars = WC()->query->get_query_vars();
		$init = false;
		if(is_wc_endpoint_url('orders')){
			$init = true;
		}
		elseif(is_wc_endpoint_url('view-subscription') || is_wc_endpoint_url('subscriptions')){
			$init = true;
		}
		elseif(is_wc_endpoint_url('view-order')){
			$init = true;
		}
		elseif(is_a($post,'WP_Post') && has_shortcode($post->post_content,'ultimatemember_account')){
			$init = true;
		}
        elseif(is_a($post,'WP_Post') && has_shortcode($post->post_content,'wc-cancel-order-page')){
            $init = true;
        }
		elseif(is_a($post,'WP_Post') && has_shortcode($post->post_content,'wc_cancel_order_details')){
			$init = true;
		}

		$init = apply_filters('wc_cancel_order_init_script',$init);
		if($init){
			do_action('wc_cancel_order_before_script');
			wp_enqueue_script('fancybox');
			wp_enqueue_style('fancybox');
			wp_enqueue_style('wc-cancel-style');
			wp_enqueue_script('wc-cancel-script');
			do_action('wc_cancel_order_after_script');
		}
	}

	function check_order_time($order){
		$order_date = $order->get_date_created()->date('Y-m-d H:i:s');
		if(isset($this->settings['hide-cancel']) && $this->settings['hide-cancel']){
			$time_interval = isset($this->settings['time-interval']) && $this->settings['time-interval'] > 0 ? $this->settings['time-interval'] : 0;
			$time_period = isset($this->settings['time-period']) ? $this->settings['time-period'] : '';
			if($time_period!='' && $time_interval>0){
				$check_time = strtotime($order_date. ' +'.$time_interval.' '.$time_period);
				if(current_time('timestamp')>$check_time && $check_time){
					return false;
				}
			}
		}
		return true;
	}

	function is_declined_in_past($order){
		$bol = true;
		if(is_a($order,'WC_Order')){
			global $wpdb;
			$id = $order->get_id();
			$bol = $wpdb->get_var("SELECT is_approved FROM ".$wpdb->prefix."wc_cancel_orders WHERE order_id=".$id);
		}
		return $bol;
	}

	function add_req($id,$status=0){
		global $wpdb;
		$req_count = $wpdb->get_var("SELECT COUNT(id) as total FROM ".$wpdb->prefix."wc_cancel_orders WHERE order_id=".$id);
		if(!$req_count){
			$wpdb->insert($wpdb->prefix."wc_cancel_orders",
				array(
					'order_id'=>$id,
					'user_id'=>get_current_user_id(),
					'is_approved'=>$status,
					'cancel_request_date'=>current_time('mysql')
				),array('%d','%d','%d','%s')
			);
		}
		else
		{
			$wpdb->update($wpdb->prefix."wc_cancel_orders",
				array(
					'is_approved'=>$status,
					'cancel_request_date'=>current_time('mysql')
				),
				array('order_id'=>$id),
				array('%d','%s'),
				array('%d')
			);
		}
	}

	function check_status_prefix($status){
		$status = 'wc-' === substr($status,0,3) ? substr($status,3) : $status;
		return $status;
	}

	function wc_cancel_setting_view($value){
		include(dirname(__FILE__).'/includes/wc-cancel-options.php');
	}

	function wc_cancel_text(){
		if(function_exists('determine_locale')){
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		load_textdomain('wc-cancel-order', WC_CANCEL_DIR . '/lang/wc-cancel-order-'.$locale.'.mo');
		load_plugin_textdomain('wc-cancel-order',false,basename(dirname(__FILE__)).'/lang');
	}

	function register_status(){
		register_post_status('wc-cancel-request',
			array(
				'label' => __('Cancel Request','wc-cancel-order'),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Cancel Request <span class="count">(%s)</span>', 'Cancel Request <span class="count">(%s)</span>')
			)
		);
	}

	function add_wc_cancel_status($statuses){
		$statuses['wc-cancel-request'] = __('Cancel Request','wc-cancel-order');
		return $statuses;
	}

	function admin_menu(){
		add_submenu_page('woocommerce',__('WC Cancel','wc-cancel-order'),__('WC Cancel','wc-cancel-order'),'manage_woocommerce','wc_cancel',array($this,'wc_cancel_dashboard'));
	}

	function wc_cancel_dashboard(){
		include(dirname(__FILE__).'/includes/dashboard.php');
	}

	function init_autoload($class){
		$dir = dirname(__FILE__).'/classes/';
		$class = 'class-'.str_replace('_','-',strtolower($class)).'.php';
		if(file_exists("{$dir}{$class}")){
			include("{$dir}{$class}");
			return;
		}
	}

	function wc_cancel_sql(){
		$sql = new Wc_Cancel_Sql();
		$sql->create();
	}

	function get_roles(){
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		return $all_roles;
	}

	function wc_cancel_request(){
		$order_id = isset($_REQUEST['order_id']) ? $this->clean_str($_REQUEST['order_id']) : 0;
		if($order_id){
			if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'],'wc-cancel-request')){
				$order = wc_get_order($order_id);
				if(is_a($order,'WC_Order')){
					$order_id_by_key = 0;
					$status_key = $this->get_status_key($order->get_status());
					$order_key = isset($_REQUEST['order_key']) ? $_REQUEST['order_key'] : '';
					if($order_key!=''){
						$details = new WC_Cancel_Order_Details($order_key,$this->settings);
						$order_id_by_key = $details->get_order_id($order_key);
					}

					if(($this->check_order_time($order) && $this->user_has_role() && $this->check_order_customer($order)) || ($this->check_order_time($order) && $this->user_has_role() && $order_id_by_key==$order_id)){

						if(isset($_REQUEST['reason'])){
							update_post_meta($order_id,'_wc_cancel_reason',$this->clean_str($_REQUEST['reason']));
						}
						if(isset($_REQUEST['additional_details'])){
							update_post_meta($order_id,'_wc_cancel_additional_txt',$this->clean_str($_REQUEST['additional_details']));
						}

						if(is_array($this->settings['req-status']) && in_array($status_key,$this->settings['req-status'])){
							$this->add_req($order_id);
							$order->update_status('cancel-request',__('Order Status updated by Wc Cancel Order.','wc-cancel-order'));
							do_action('wc_cancel_request',$order_id);
						}
						elseif(is_array($this->settings['cancel-status']) && in_array($status_key,$this->settings['cancel-status'])){
							$this->add_req($order_id,1);
							$new_status = $this->check_status_prefix($this->settings['approval']);
							$order->update_status($new_status,__('Order Status updated by Wc Cancel Order.','wc-cancel-order'));
							update_post_meta($order_id,'_wc_cancel_request_data',array('approved'=>true,'head'=>__('Cancellation Request Approved.','wc-cancel-order'),'date'=>current_time('mysql')));
							if(class_exists('WC_Subscriptions') && class_exists('WC_Subscriptions_Manager')){
								$parent_order = $this->get_subscription_parent($order);
								if(is_a($parent_order,'WC_Order')){
									WC_Subscriptions_Manager::cancel_subscriptions_for_order($parent_order);
								}
							}
							do_action('wc_cancel_request',$order_id);
						}
					}
				}
			}
		}

		if(isset($_REQUEST['wcc_ajax']) && $_REQUEST['wcc_ajax']){
            $html='<div class="wc-cancel-notice wxp-col-12"><div class="wcc_sucess">'.__('Cancellation request sent successfully.','wc-cancel-order').'</div></div>';
			wp_send_json(array('res'=>true,'fragments'=>array('div.wc-cancel-notice'=>$html)));
		}
		else
		{
			if(wp_get_referer()){
				wp_safe_redirect(wp_get_referer());
			} else {
				$endpoint_url = wc_get_account_endpoint_url('orders');
				wp_safe_redirect($endpoint_url);
			}
			exit;
		}
	}

	function wc_cancel_request_backend(){
		$res = array('reload'=>true,'html'=>'');
		if(!current_user_can('manage_woocommerce')){
			wp_die( -1 );
		}
		$is_ajax = isset($_REQUEST['wcc_ajax']) ? true : false;
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'],'wc-cancel-backend')){
			$order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;
			if($order_id){
				$format = get_option('date_format') . ' ' . get_option('time_format');
				if(isset($_REQUEST['req']) && $_REQUEST['req']=='view'){
					global $wpdb;
					$date = $wpdb->get_var("SELECT cancel_request_date FROM ".$wpdb->prefix."wc_cancel_orders WHERE order_id=".$order_id);
					$reason = get_post_meta($order_id,'_wc_cancel_reason',true);
					$reason = trim($reason)=='' ? '--' : $reason;
					$additional_txt = get_post_meta($order_id,'_wc_cancel_additional_txt',true);
					$additional_txt = trim($additional_txt)=='' ? '--' : $additional_txt;
					$html = '<p class="wc-cancel-meta"><label>'.__('Cancellation Reason:','wc-cancel-order').'</label>'.$reason.'</p>';
					$html.= '<p class="wc-cancel-meta"><label>'.__('Additional details :','wc-cancel-order').'</label>'.$additional_txt.'</p>';
					$html.= '<p class="wc-cancel-meta"><label>'.__('Date:','wc-cancel-order').'</label>'.date_i18n($format,strtotime($date)).'</p>';
					$meta = get_post_meta($order_id,'_wc_cancel_request_data',true);
					if(isset($meta['approved']) && $meta['approved']){
						$html.= '<p class="wc-cancel-meta wc-cancel-approved">';
						$html.= '<span class="wc-cancel-approved-icon">'.$meta['head'].'</span>';
						$html.= '<span><label>'.__('Date:','wc-cancel-order').'</label>'.date_i18n($format,strtotime($meta['date'])).'</span>';
						$html.= '</p>';
					}
					elseif(isset($meta['approved']) && !$meta['approved']){
						$html.= '<p class="wc-cancel-meta wc-cancel-declined">';
						$html.= '<span class="wc-cancel-declined-icon">'.$meta['head'].'</span>';
						$html.= '<span><label>'.__('Date:','wc-cancel-order').'</label>'.date_i18n($format,strtotime($meta['date'])).'</span>';
						$html.= '</p>';
					}
					$res = array('reload'=>false,'html'=>$html);
				}
				elseif(isset($_REQUEST['req']) && $_REQUEST['req']=='approve'){
					$order = wc_get_order($order_id);
					if(is_a($order,'WC_Order') && $order->get_status()=='cancel-request'){
						$this->add_req($order_id,1);
						$new_status = $this->check_status_prefix($this->settings['approval']);
						$order->update_status($new_status,__('Cancellation Request Approved.','wc-cancel-order'));
						update_post_meta($order_id,'_wc_cancel_request_data',array('approved'=>true,'head'=>__('Cancellation Request Approved.','wc-cancel-order'),'date'=>current_time('mysql')));

						if(class_exists('WC_Subscriptions') && class_exists('WC_Subscriptions_Manager')){
							$parent_order = $this->get_subscription_parent($order);
							if(is_a($parent_order,'WC_Order')){
								WC_Subscriptions_Manager::cancel_subscriptions_for_order($parent_order);
							}
						}
						$res = array('reload'=>true,'html'=>'');
					}
				}
				elseif(isset($_REQUEST['req']) && $_REQUEST['req']=='decline'){
					$order = wc_get_order($order_id);
					if(is_a($order,'WC_Order') && $order->get_status()=='cancel-request'){
						$this->add_req($order_id,2);
						$new_status = $this->check_status_prefix($this->settings['decline']);
						$order->update_status($new_status,__('Cancellation Request Declined.','wc-cancel-order'));
						update_post_meta($order_id,'_wc_cancel_request_data',array('approved'=>false,'head'=>__('Cancellation Request Declined.','wc-cancel-order'),'date'=>current_time('mysql')));
						$res = array('reload'=>true,'html'=>'');
					}
				}
			}
		}
		if($is_ajax){
			wp_send_json($res);
		}
		wp_redirect(admin_url('admin.php?page=wc_cancel'));
		exit;
	}

	function get_status_key($status){
		$statues = wc_get_order_statuses();
		$status = wc_get_order_status_name($status);
		$key = array_search($status,$statues);
		return $key;
	}

	function subscription_actions($actions,$subscription){
		$order_id = $subscription->get_parent_id();
		$order = wc_get_order($order_id);
		if(is_a($order,'WC_Order')){
			$actions = $this->get_cancel_action($order,$actions);
		}
		return $actions;
	}

	function um_subscription_actions($subscription){
		$actions = $this->subscription_actions(array(),$subscription);
		$this->add_cancel_btn($actions);
	}

	function get_subscription_parent($order){
		$main_order = null;
		$subscriptions = wcs_get_subscriptions_for_order($order,array('order_type'=>'any'));
		if(is_array($subscriptions) && !empty($subscriptions)){
			foreach($subscriptions as $subscription_id=>$subscription){
				if(is_object($subscription) && wcs_is_subscription($subscription)){
					$parent_order = $subscription->get_parent();
					if(is_a($parent_order,'WC_Order')){
						$main_order =  $parent_order;
						break;
					}
				}
			}
		}
		return $main_order;
	}

	function wc_cancel_email_classes($classes){
		$classes['Wc_Cancel_Request_Received'] = include 'classes/class-wc-cancel-request-received.php';
		$classes['Wc_Cancel_Request_Approved'] = include 'classes/class-wc-cancel-request-approved.php';
		$classes['Wc_Cancel_Request_Declined'] = include 'classes/class-wc-cancel-request-declined.php';
		return $classes;
	}

	function add_cancellation_reason($order,$sent_to_admin,$plain_text,$email){

		$reason = get_post_meta($order->get_id(),'_wc_cancel_reason',true);
		if($plain_text && $reason!=''){
			echo "\n".__('Cancellation Reason:','wc-cancel-order').' '.$reason."\n\n";
		}
		elseif($reason!=''){
			$reason_text = '<strong>'.__('Cancellation Reason:','wc-cancel-order').'</strong> '.$reason;
			echo wp_kses_post(wpautop(wptexturize($reason_text)));
		}

		if(isset($this->settings['text-input']) && $this->settings['text-input']!='disable-always'){
			$additional_txt = get_post_meta($order->get_id(),'_wc_cancel_additional_txt',true);
			if($plain_text && $additional_txt!=''){
				echo "\n".__('Additional Details:','wc-cancel-order').' '.$additional_txt."\n\n";
			}
			elseif($additional_txt!=''){
				$reason_textt = '<strong>'.__('Additional Details:','wc-cancel-order').'</strong> '.$additional_txt;
				echo wp_kses_post(wpautop(wptexturize($reason_textt)));
			}
		}

	}

	function trigger_emails($order_id,$status_from,$status_to,$order){

		$from_key = $this->get_status_key($status_from);
		$to_key = $this->get_status_key($status_to);
		if($to_key=='wc-cancel-request'){
			$mails = WC()->mailer()->get_emails();
			$mails['Wc_Cancel_Request_Received']->trigger($order_id);
		}
		elseif($from_key=='wc-cancel-request' && $to_key==$this->settings['approval']){
			$mails = WC()->mailer()->get_emails();
			$mails['Wc_Cancel_Request_Approved']->trigger($order_id);
		}
		elseif($from_key=='wc-cancel-request' && $to_key==$this->settings['decline']){
			$mails = WC()->mailer()->get_emails();
			$mails['Wc_Cancel_Request_Declined']->trigger($order_id);
		}
		elseif(isset($this->settings['cancel-status']) && is_array($this->settings['cancel-status']) && in_array($from_key,$this->settings['cancel-status']) && $to_key==$this->settings['approval']){
			$mails = WC()->mailer()->get_emails();
			$mails['Wc_Cancel_Request_Approved']->trigger($order_id);
		}
	}

	function wc_cancel_key($order_id,$data){
		$hash = wp_hash($order_id.'-wc-cancel-order'.current_time('timestamp',0));
		update_post_meta($order_id,'_wc_cancel_key',$hash);
	}

	function wc_cancel_clear_key($order_id){
		//delete_post_meta($order_id,'_wc_cancel_key');
	}

	function guest_cancel_page($posts){
		global $wp,$wp_query;
		if(isset($wp->request) && $wp->request=='guest-cancel-req'){
			$args = array(
				'slug' => 'guest-cancel-req',
				'post_title' => 'Order Details',
				'post content' => '[wc_cancel_order_details]'
			);
			$guest = new WC_Cancel_Guest($args);
			$posts = $guest->guest_page($posts);
		}
		return $posts;
	}

	function wc_cancel_order_details(){
		if(isset($_GET['key']) && $_GET['key']!=''){
			$details = new WC_Cancel_Order_Details($_GET['key'],$this->settings);
			$details->order_details();
		}
	}

	function add_cancel_link($order,$sent_to_admin=false,$plain_text=false){
		if(is_a($order,'WC_Order') && !$sent_to_admin && isset($this->settings['guest-cancel']) && $this->settings['guest-cancel'] && !$this->is_declined_in_past($order) && $order->get_status()!='completed'){
			echo '<p><h4>'.__('Want to cancel this order?','wc-cancel-order').'</h4></p>';
			$key = get_post_meta($order->get_id(),'_wc_cancel_key',true);
			echo '<p><a href="'.get_home_url(get_current_blog_id(),'/guest-cancel-req/?key='.$key).'">'.__('Cancel Order','wc-cancel-order').'</a></p>';
		}
	}

}

if(!function_exists('WC_Cancel_Order_Init')){
    function WC_Cancel_Order_Init(){
        return WC_Cancel_Order_Pro::instance();  
    }
}

if(function_exists('is_multisite') && is_multisite()){
    if(!function_exists( 'is_plugin_active_for_network')){
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }
    if(is_plugin_active_for_network('woocommerce/woocommerce.php')){
        WC_Cancel_Order_Init();
    }
    elseif(in_array('woocommerce/woocommerce.php',apply_filters('active_plugins',get_option('active_plugins')))) {
        WC_Cancel_Order_Init();
    }
}
elseif(in_array('woocommerce/woocommerce.php',apply_filters('active_plugins',get_option('active_plugins')))){
    WC_Cancel_Order_Init();
}

?>