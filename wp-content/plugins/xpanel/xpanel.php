<?php
/**
 * Plugin Name:	xPanel
 * Author:		SaurabhSharma
 * Author URI:	http://codecanyon.net/user/saurabhsharma
 * Version:		1.3.0
 * Text Domain:	xpanel
 * Domain Path:	/languages/
 * Description:	Sliding sidebar panel and content widget area for WordPress themes.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'xPanel' ) ) {

	class xPanel {

		function __construct() {

			// Include required files
			add_action( 'plugins_loaded', array( &$this, 'xpanel_includes' ) );

			// Register widget areas or widgets
			add_action( 'widgets_init', array( &$this, 'xpanel_widgets_init') );

			// Load text domain
			add_action( 'init', array( &$this, 'xpanel_init' ) );

			// Load scripts and stylesheets
			add_action( 'wp_enqueue_scripts', array( &$this, 'xpanel_scripts' ) );
		}

		function xpanel_init() {

			// Translation
			load_plugin_textdomain( 'xpanel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		function xpanel_includes() {

			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class.settings-api.php' );
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/functions.php' );
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/settings.php' );

		}

		function xpanel_widgets_init() {

			$opts_general = get_option( 'xp_general' );
			$xp_create_sb = ( isset( $opts_general['xp_create_sb'] ) && 'on' == $opts_general['xp_create_sb'] ) ? true : false;
			if ( $xp_create_sb ) {
				register_sidebar( array(
					'name' 			=> esc_html__( 'xPanel Sidebar', 'xpanel' ),
					'id' 			=> 'xpanel-sidebar',
					'description' 	=> esc_html__( 'The widget area for sliding panel.', 'xpanel' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget' 	=> "</aside>",
					'before_title' 	=> '<h3 class="widget-title">',
					'after_title' 	=> '</h3>'
				) );
			}

		}

		function xpanel_scripts() {
		
			$opts_hideon = get_option( 'xp_hideon' );
			$xp_hide_all_pages		= ( isset( $opts_hideon['xp_hide_all_pages'] ) && 'on' == $opts_hideon['xp_hide_all_pages'] ) ? true : false;
			$xp_hide_pages 			= isset( $opts_hideon['xp_hide_pages'] ) ? explode( ',', $opts_hideon['xp_hide_pages'] ) : array();			
			$xp_hide_all_posts		= ( isset( $opts_hideon['xp_hide_all_posts'] ) && 'on' == $opts_hideon['xp_hide_all_posts'] ) ? true : false;
			$xp_hide_posts 			= isset( $opts_hideon['xp_hide_posts'] ) ? explode( ',', $opts_hideon['xp_hide_posts'] ) : array();			
			$xp_hide_all_archives	= ( isset( $opts_hideon['xp_hide_all_archives'] ) && 'on' == $opts_hideon['xp_hide_all_archives'] ) ? true : false;
			$xp_hide_cats 			= isset( $opts_hideon['xp_hide_cats'] ) ? explode( ',', $opts_hideon['xp_hide_cats'] ) : array();
			$xp_show_woo			= ( isset( $opts_hideon['xp_show_woo'] ) && 'on' == $opts_hideon['xp_show_woo'] ) ? true : false;
			
			$show_panel = 'true';
			
			if ( ( is_page() && $xp_hide_all_pages ) || is_page( $xp_hide_pages ) ) {
				$show_panel = false;
			}
			
			if ( ( is_single() && $xp_hide_all_posts ) || is_single( $xp_hide_posts ) ) {
				$show_panel = false;
			}
			
			if ( ( is_archive() && $xp_hide_all_archives ) || is_category( $xp_hide_cats ) ) {
				$show_panel = false;
			}
			
			if ( ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) && $xp_show_woo ) {
				$show_panel = 'true';
			}			

			if ( 'true' == $show_panel ) {
				// Plugins stylesheet
				wp_enqueue_style( 'xpanel-styles', plugin_dir_url( __FILE__ ) . 'assets/css/xpanel.style.css', array(), null );
	
				// JavaScript files
				wp_enqueue_script( 'xpanel-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/xpanel.frontend.js', array( 'jquery' ), '', true );
	
				// Localize text strings and variables used in xpanel.frontend.js file
				$opts_general 			= get_option( 'xp_general' );
				$opts_display 			= get_option( 'xp_display' );
				$xp_convert_sb 			= ( isset( $opts_general['xp_convert_sb'] ) && 'on' == $opts_general['xp_convert_sb'] ) ? true : false;
				$xp_sb_wrapper 			= ! empty( $opts_general['xp_sb_wrapper'] ) ? $opts_general['xp_sb_wrapper'] : '';
				$xp_viewport_width 		= ! empty( $opts_display['xp_viewport_width'] ) ? $opts_display['xp_viewport_width'] : '768';
				$xp_btn_style 			= isset( $opts_display['xp_button_style'] ) ? $opts_display['xp_button_style'] : 'bar';
				$xp_btn_text 			= ( isset( $opts_display['xp_button_text'] ) && '' != $opts_display['xp_button_text'] ) ? $opts_display['xp_button_text'] : esc_attr__( 'Menu', 'xpanel' );
				$xp_panel_transition 	= isset( $opts_display['xp_panel_transition'] ) ? $opts_display['xp_panel_transition'] : 'overlay';
				$xp_panel_pos 			= isset( $opts_display['xp_panel_pos'] ) ? $opts_display['xp_panel_pos'] : 'left';
				$xp_collapse_lists 		= ( isset( $opts_display['xp_collapse_lists'] ) && 'on' == $opts_display['xp_collapse_lists'] ) ? true : false;
				$xp_widget_selectors 	= ! empty( $opts_display['xp_widget_selectors'] ) ? $opts_display['xp_widget_selectors'] : '';
	
				if ( (int)$xp_viewport_width < 340 ) {
					$xp_viewport_width = 340;
				}
	
				$custom_css = '@media only screen and (min-width:' . sanitize_text_field( $xp_viewport_width ) . 'px) {
					.xpanel-sidebar,
					.panel-toggle.icon-style { display: none; }
					.panel-wrap { padding: 0; overflow-y: auto; }
				}';
	
				wp_add_inline_style( 'xpanel-styles', $custom_css );
	
				$localization = array(
					'sb_container' 		=> esc_attr( $xp_sb_wrapper ),
					'sb_convert'		=> $xp_convert_sb,
					'viewport_width'	=> sanitize_text_field( $xp_viewport_width ),
					'button_style'		=> $xp_btn_style,
					'button_text'		=> sanitize_text_field( $xp_btn_text ),
					'panel_transition'	=> $xp_panel_transition,
					'panel_pos'			=> $xp_panel_pos,
					'collapse_lists'	=> $xp_collapse_lists,
					'list_selectors'	=> $xp_widget_selectors,
					'expand_text'		=> apply_filters( 'xpanel_expand_menu_title', esc_attr__( 'Expand or collapse menu items', 'xpanel' ) )
				);
	
				wp_localize_script( 'xpanel-frontend', 'xpanel_localize', $localization );
			}
		}
	}

	$xpanel = new xPanel();
}
?>