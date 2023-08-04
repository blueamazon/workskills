<?php
/*
Plugin Name: WPLMS Application Forms Extended
Plugin URI: https://wpgenius.in
Description: A WPLMS Addon to get details of the user while applying for a course. This is extended addon from original plugin.
Version: 2.4.1
Author: Team WPGenius (Makarand Mane)
Author URI: https://tycheventures.com
Text Domain: wplms-af
*/
/*
Copyright 2019  Team WPGenius  (email : makarand@wpgenius.in)
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


include_once 'includes/class.woocommerce.php';
include_once 'includes/class.init.php';
include_once 'includes/class.database.php';
include_once 'includes/class.waf-actions.php';
include_once 'includes/class.waf-admin.php';


// Add text domain
add_action('plugins_loaded','wplms_application_forms_translations');
function wplms_application_forms_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-af');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-af', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-af', $mofile_global );
    } else {
        load_textdomain( 'wplms-af', $mofile_local );
    }  
}

if(class_exists('WPLMS_Application_Forms_Init')){
    // instantiate the plugin class
 	$init = WPLMS_Application_Forms_Init::Instance_WPLMS_Application_Forms_Init();
}

if(class_exists('WPLMS_Application_Forms_Extended'))
 	WPLMS_Application_Forms_Extended::init();
	
if(class_exists('WPLMS_Application_WooCommerce'))
 	WPLMS_Application_WooCommerce::init();

if(class_exists('WPLMS_Application_Forms_Admin') && is_admin())
 	WPLMS_Application_Forms_Admin::init();

register_activation_hook(__FILE__,'flush_rewrite_rules');

register_activation_hook(__FILE__, array( $wafdb, 'install_application_database'));