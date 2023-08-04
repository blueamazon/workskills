<?php
/*
Plugin Name: WPLMS WooCommerce
Plugin URI: http://www.vibethemes.com/
Description: This plugin extends WooCommerce for WPLMS
Version: 1.8
Author: Mr.Vibe
Author URI: http://www.vibethemes.com/
Text Domain: wplms-woo
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) exit;
/*  Copyright 2013 VibeThemes  (email: vibethemes@gmail.com) */

include_once 'includes/admin/updater.php';
include_once 'includes/class.config.php';
include_once 'includes/admin/admin.php';
include_once 'includes/front/front.php';
include_once 'includes/class.process.php';
include_once 'includes/admin/actions.php';
include_once 'includes/admin/filters.php';
include_once 'includes/instructor_premium_course/class.actions.php';
include_once 'includes/instructor_premium_course/class.filters.php';
include_once 'includes/instructor_premium_course/class.premium_course.php';
include_once 'includes/instructor_premium_course/dashboard_premium_widget.php';

add_action('plugins_loaded','wplms_woo_translations');
function wplms_woo_translations(){

    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-woo');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-woo', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-woo', $mofile_global );
    } else {
        load_textdomain( 'wplms-woo', $mofile_local );
    }  
}


function Wplms_WooCommerce_Plugin_updater() {

    $license_key = trim( get_option( 'wplms_woocommerce_license_key' ) );
    $edd_updater = new Wplms_WooCommerce_Plugin_Updater( 'http://wplms.io', __FILE__, array(
            'version'   => '1.8',               
            'license'   => $license_key,        
            'item_name' => 'WPLMS WOOCOMMERCE',    
            'author'    => 'VibeThemes' 
        )
    );
}
add_action( 'admin_init', 'Wplms_WooCommerce_Plugin_updater', 0 );
