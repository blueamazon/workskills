<?php
/*
Plugin Name: WooCommerce WP-Login
Plugin URI: https://www.machineitservices.com/shop/services/development/web-development/plugins/woo-wp-login/
Description: Redirects wp-login.php page to default WooCommerce "My account" page, IF WooCommerce is installed AND active. 
Text Domain: woo-wp-login
Version: 1.2.0
Author: MachineITSvcs <contact@machineitservices.com>
Author URI: https://www.machineitservices.com
*/

if(!defined('ABSPATH')) {
  exit;
}

$network_plugins = apply_filters('active_plugins', get_site_option('active_sitewide_plugins'));
$subsite_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if(!is_admin() && (!function_exists('get_blog_status') || function_exists('get_current_blog_id') && empty(get_blog_status(get_current_blog_id(), 'deleted'))) && ((!empty($subsite_plugins) && in_array('woocommerce/woocommerce.php', $subsite_plugins)) || (!empty($network_plugins) && array_key_exists('woocommerce/woocommerce.php', $network_plugins)))) add_action('init', function() {
	$myaccount = wc_get_page_permalink('myaccount');
	global $pagenow;
	if(home_url() != $myaccount) {
		$action_array = array_unique(array_merge(((array) apply_filters('woo_wp_login_actions', array())), array('logout', 'rp', 'resetpass', 'resetpassword', 'enter_recovery_mode')));
		if('wp-login.php' == $pagenow && (!isset($_GET['action']) || !in_array($_GET['action'], $action_array)) && !isset($_REQUEST['interim-login'])) {
			if(!empty($_GET['action']) && $_GET['action'] == "lostpassword") {
				unset($_GET['action']);
				if(wp_redirect($myaccount . strtok(wc_get_endpoint_url('lost-password'), '/') . ((http_build_query($_GET)) ? '?' . http_build_query($_GET) : ''))) exit();
			} else {
				$woo_wp_redirect = ((isset($_GET['redirect_to'])) ? 'redirect-to=' . strtok($_GET['redirect_to'], '?') : '');
				unset($_GET['redirect_to'],$_GET['loggedout'],$_GET['reauth']);
				$woo_wp_combined_query = (($woo_wp_redirect) ? $woo_wp_redirect : '') . ((http_build_query($_GET)) ? (($woo_wp_redirect) ? '&' : '') . http_build_query($_GET) : '');
				if(wp_redirect($myaccount . (($woo_wp_combined_query) ? '?' . $woo_wp_combined_query : ''))) exit();
			}
		}
		if(isset($_GET['redirect-to'])) {
			$myaccount_redirect = $_GET['redirect-to'];
			unset($_GET['redirect-to'],$_GET['reauth']);
			if((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?') == $myaccount && is_user_logged_in() ) {
				if(wp_redirect($myaccount_redirect . ((http_build_query($_GET)) ? '?' . http_build_query($_GET) : ''))) exit();
			} elseif((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?') != $myaccount) {
				if(wp_redirect((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?') . ((http_build_query($_GET)) ? '?' . http_build_query($_GET) : ''))) exit();
			}
		}
	}
});
