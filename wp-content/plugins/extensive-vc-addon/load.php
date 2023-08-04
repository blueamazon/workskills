<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( extensive_vc_is_wpbakery_page_builder_installed() ) {
	include_once EXTENSIVE_VC_ABS_PATH . '/lib/framework.php';
	include_once EXTENSIVE_VC_ABS_PATH . '/lib/helpers-functions.php';
	include_once EXTENSIVE_VC_ABS_PATH . '/lib/predefined-style-class.php';
	
	if ( file_exists( EXTENSIVE_VC_ABS_PATH . '/post-types' ) ) {
		include_once EXTENSIVE_VC_ABS_PATH . '/post-types/post-types-interface.php';
		include_once EXTENSIVE_VC_ABS_PATH . '/post-types/post-types-functions.php';
	}
	
	if ( file_exists( EXTENSIVE_VC_ABS_PATH . '/shortcodes' ) ) {
		include_once EXTENSIVE_VC_ABS_PATH . '/shortcodes/shortcodes-extends-class.php';
		include_once EXTENSIVE_VC_ABS_PATH . '/shortcodes/shortcodes-functions.php';
	}
	
	if ( file_exists( EXTENSIVE_VC_ABS_PATH . '/widgets' ) ) {
		include_once EXTENSIVE_VC_ABS_PATH . '/widgets/widgets-class.php';
		include_once EXTENSIVE_VC_ABS_PATH . '/widgets/widgets-functions.php';
	}
	
	if ( file_exists( EXTENSIVE_VC_ABS_PATH . '/plugins' ) ) {
		include_once EXTENSIVE_VC_ABS_PATH . '/plugins/plugins-functions.php';
	}
}
