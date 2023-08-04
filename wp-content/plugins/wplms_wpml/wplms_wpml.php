<?php
/*
Plugin Name: WPML WPLMS integration
Plugin URI: http://wplms.io/
Description: Integrating WPML  multilingual plugin with WPLMS
Author: Vibethemes
Author URI: http://vibethemes.com/
Text Domain: wplms-wpml
Domain Path: /languages/
Version: 1.0
*/



add_filter('wplms_site_link','wpml_wplms_site_link',10,2);
function wpml_wplms_site_link($link,$point){
	if(function_exists('icl_get_home_url')){
		$link = icl_get_home_url();
	}
	return $link;
}
// WPLMS REGISTRATION PAGE CODE

add_filter('wplms_buddypress_registration_link','wplms_wpml_detect_wpml_on_site');
function wplms_wpml_detect_wpml_on_site($registration_link){
  if(function_exists('icl_object_id') && function_exists('vibe_get_bp_page_id')){
        $pageid = vibe_get_bp_page_id('register');
        $pageid = icl_object_id($pageid, 'page', true);
        $registration_link = get_permalink($pageid);
   }
    return $registration_link;
}


add_shortcode('wpml_language_switcher',function(){
	do_action('wpml_add_language_selector');
});