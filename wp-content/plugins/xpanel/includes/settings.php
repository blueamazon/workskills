<?php
/**
 * xPanel Settings Page in Admin
 *
 * Uses xPanel_Settings_API Class
 */

if ( ! class_exists('Generate_xPanel_Settings' ) ) :

	class Generate_xPanel_Settings {
	
		private $settings_api;
	
		function __construct() {
			$this->settings_api = new xPanel_Settings_API;
	
			add_action( 'admin_init', array($this, 'admin_init') );
			add_action( 'admin_menu', array($this, 'admin_menu') );
		}
	
		function admin_init() {
	
			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
	
			//initialize settings
			$this->settings_api->admin_init();
		}
	
		function admin_menu() {
			add_options_page( 'xPanel Settings', 'xPanel', 'manage_options', 'xpanel_settings', array($this, 'plugin_page') );
		}
	
		function get_settings_sections() {
			$sections = array(
				array(
					'id' => 'xp_general',
					'title' => esc_attr__( 'General', 'xpanel' )
				),
				array(
					'id' => 'xp_display',
					'title' => esc_attr__( 'Display', 'xpanel' )
				),
				array(
					'id' => 'xp_hideon',
					'title' => esc_attr__( 'Hide on', 'xpanel' )
				)
			);
			return $sections;
		}
	
		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		function get_settings_fields() {
			$settings_fields = array(
				'xp_general' => array(
					array(
						'name'  => 'xp_create_sb',
						'label' => esc_attr__( 'Register new widget area', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to register a new widget area for side panel. You can access this widget area as Appearance > Widgets > xPanel Sidebar', 'xpanel' ),
						'type'  => 'checkbox'
					),
					array(
						'name'              => 'xp_sidebar',
						'label'             => esc_attr__( 'Available widget areas for side panel', 'xpanel' ),
						'desc'              => esc_attr__( 'Choose from available widget areas for the side panel.', 'xpanel' ),
						'type'              => 'sidebars',
						'default'           => '',
						'sanitize_callback' => ''
					),
					array(
						'name'  => 'xp_convert_sb',
						'label' => esc_attr__( 'Convert existing sidebar into side panel', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to convert existing sidebar on front end into the side panel. Your sidebar CSS will be changed to show it as sliding panel.', 'xpanel' ),
						'type'  => 'checkbox'
					),
	
					array(
						'name'              => 'xp_sb_wrapper',
						'label'             => esc_attr__( 'Sidebar container selector', 'xpanel' ),
						'desc'              => esc_attr__( 'Provide a class or ID name for the container in which sidebar is placed on your site. This container will be converted into side panel. E.g. #sidebar or .sidebar', 'xpanel' ),
						'type'              => 'text',
						'default'           => '',
						'sanitize_callback' => ''
					),
				),
				'xp_display' => array(
					array(
						'name'    => 'xp_panel_pos',
						'label'   => esc_attr__( 'Side panel placement', 'xpanel' ),
						'desc'    => esc_attr__( 'Choose a placement for side panel.', 'xpanel' ),
						'type'    => 'select',
						'options' => array(
							'left' => 'Left',
							'right'  => 'Right'
						)
					),
	
					array(
						'name'    => 'xp_panel_transition',
						'label'   => esc_attr__( 'Panel transition style', 'xpanel' ),
						'desc'    => esc_attr__( 'Choose a transition style for side panel.', 'xpanel' ),
						'type'    => 'select',
						'options' => array(
							'overlay' => 'Overlay',
							'offcanvas'  => 'Off Canvas'
						)
					),
	
					array(
						'name'    => 'xp_button_style',
						'label'   => esc_attr__( 'Toggle button style', 'xpanel' ),
						'desc'    => esc_attr__( 'Choose a style for panel toggle button.', 'xpanel' ),
						'type'    => 'select',
						'options' => array(
							'icon' => 'Icon at top',
							'bar'  => 'Bar at bottom with text'
						)
					),
					array(
						'name'    => 'xp_button_text',
						'label'   => esc_attr__( 'Text for bar style button', 'xpanel' ),
						'desc'    => esc_attr__( 'Provide a text for the bar style toggle button.', 'xpanel' ),
						'type'    => 'text',
						'default' => 'Menu'
					),
					array(
						'name'  => 'xp_viewport_width',
						'label' => esc_attr__( 'Show panel below viewport width', 'xpanel' ),
						'desc'  => esc_attr__( 'Provide a viewport width (in px), without unit, below which panel should be active. E.g. 768. Minimum value 340.', 'xpanel' ),
						'type'  => 'number',
						'default' => '768',
						'min'	=> '340'
					),
					array(
						'name'  => 'xp_collapse_lists',
						'label' => esc_attr__( 'Enable collapsible list items in side panel list widgets', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to enable collapsible list items.', 'xpanel' ),
						'type'  => 'checkbox'
					),
					array(
						'name'  => 'xp_widget_selectors',
						'label' => esc_attr__( 'Widget selectors', 'xpanel' ),
						'desc'  => esc_attr__( 'Provide comma separated widget selectors for collapsible items. These can be ID or class names of widget containers in which list are located. E.g. .widget_pages, .widget_nav_menu', 'xpanel' ),
						'type'  => 'text',
						'default' => '',
						'sanitize_callback' => ''
					),
				),
				'xp_hideon' => array(
					array(
						'name'  => 'xp_hide_all_pages',
						'label' => esc_attr__( 'All pages', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to hide panel on all pages', 'xpanel' ),
						'type'  => 'checkbox'
					),
					array(
						'name'              => 'xp_hide_pages',
						'label'             => esc_attr__( 'Selected page IDs', 'xpanel' ),
						'desc'              => esc_attr__( 'Provide numeric page ids (separated by comma) on which panel shall be disabled. E.g. 45,67,90', 'xpanel' ),
						'type'              => 'text',
						'default'           => '',
						'sanitize_callback' => ''
					),
					array(
						'name'  => 'xp_hide_all_posts',
						'label' => esc_attr__( 'All posts', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to hide panel on all posts', 'xpanel' ),
						'type'  => 'checkbox'
					),
					array(
						'name'              => 'xp_hide_posts',
						'label'             => esc_attr__( 'Selected post IDs', 'xpanel' ),
						'desc'              => esc_attr__( 'Provide numeric post ids (separated by comma) on which panel shall be disabled. E.g. 45,67,90', 'xpanel' ),
						'type'              => 'text',
						'default'           => '',
						'sanitize_callback' => ''
					),
					array(
						'name'  => 'xp_hide_all_archives',
						'label' => esc_attr__( 'All archives', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to hide panel on all archives', 'xpanel' ),
						'type'  => 'checkbox'
					),
					array(
						'name'              => 'xp_hide_cats',
						'label'             => esc_attr__( 'Selected category IDs', 'xpanel' ),
						'desc'              => esc_attr__( 'Provide numeric category ids (separated by comma) on which panel shall be disabled. E.g. 45,67,90', 'xpanel' ),
						'type'              => 'text',
						'default'           => '',
						'sanitize_callback' => ''
					),
					array(
						'name'  => 'xp_show_woo',
						'label' => esc_attr__( 'Show on WooCommerce Store', 'xpanel' ),
						'desc'  => esc_attr__( 'Check to enable panel on WooCommerce shop pages and archives', 'xpanel' ),
						'type'  => 'checkbox'
					)									
				)
			);
	
			return $settings_fields;
		}
	
		function plugin_page() {
			echo '<div class="wrap">';
			echo '<h1>' . esc_attr__( 'xPanel Settings', 'xpanel' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
	
			echo '</div>';
		}
	
		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ($pages as $page) {
					$pages_options[$page->ID] = $page->post_title;
				}
			}
	
			return $pages_options;
		}
	}
	
	$generate_xpanel_settings = new Generate_xPanel_Settings();

endif;