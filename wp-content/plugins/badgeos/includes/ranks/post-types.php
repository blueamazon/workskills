<?php
/**
 * Ranks Post Types
 *
 * @package Badgeos
 * @subpackage Ranks
 * @author LearningTimes
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 * @link https://credly.com/
 */
/**
 * Register all of our ranks CPTs
 */
function badgeos_register_ranks_post_types() {

	$settings = ( $exists = badgeos_utilities::get_option( 'badgeos_settings' ) ) ? $exists : array();
	if ( ! empty( $settings ) ) {

		/**
		 * Register Rank Type
		 */
		register_post_type(
			trim( $settings['ranks_main_post_type'] ),
			array(
				'labels'             => array(
					'name'                  => esc_html__( 'Rank Types', 'badgeos' ),
					'singular_name'         => esc_html__( 'Rank Type', 'badgeos' ),
					'add_new'               => esc_html__( 'Add New', 'badgeos' ),
					'add_new_item'          => esc_html__( 'Add New Rank Type', 'badgeos' ),
					'edit_item'             => esc_html__( 'Edit Rank Type', 'badgeos' ),
					'new_item'              => esc_html__( 'New Rank Type', 'badgeos' ),
					'all_items'             => esc_html__( 'Rank Types', 'badgeos' ),
					'view_item'             => esc_html__( 'View Rank Type', 'badgeos' ),
					'search_items'          => esc_html__( 'Search Rank Types', 'badgeos' ),
					'not_found'             => esc_html__( 'No Rank types found', 'badgeos' ),
					'not_found_in_trash'    => esc_html__( 'No Rank types found in Trash', 'badgeos' ),
					'parent_item_colon'     => '',
					'menu_name'             => esc_html__( 'Rank Types', 'badgeos' ),
					'featured_image'        => esc_html__( 'Badgeos Rank Image', 'badgeos' ),
					'set_featured_image'    => esc_html__( 'Set badgeos Rank image', 'badgeos' ),
					'remove_featured_image' => esc_html__( 'Remove badgeos Rank image', 'badgeos' ),
					'use_featured_image'    => esc_html__( 'Use badgeos Rank image', 'badgeos' ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => current_user_can( badgeos_get_manager_capability() ),
				'show_in_menu'       => 'badgeos_badgeos',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => str_replace( '_', '-', $settings['ranks_main_post_type'] ) ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);

		/**
		 * Register a sub Rank type post type i.e. Rank Requirement
		 */
		register_post_type(
			trim( $settings['ranks_step_post_type'] ),
			array(
				'labels'             => array(
					'name'                  => esc_html__( 'Ranks Requirement', 'badgeos' ),
					'singular_name'         => esc_html__( 'Rank Requirement', 'badgeos' ),
					'add_new'               => esc_html__( 'Add New', 'badgeos' ),
					'add_new_item'          => esc_html__( 'Add New Rank Requirement', 'badgeos' ),
					'edit_item'             => esc_html__( 'Edit Rank Requirement', 'badgeos' ),
					'new_item'              => esc_html__( 'New Rank Requirement', 'badgeos' ),
					'all_items'             => esc_html__( 'Rank Requirement', 'badgeos' ),
					'view_item'             => esc_html__( 'View Rank Requirement', 'badgeos' ),
					'search_items'          => esc_html__( 'Search Rank Requirement', 'badgeos' ),
					'not_found'             => esc_html__( 'No Rank Requirements found', 'badgeos' ),
					'not_found_in_trash'    => esc_html__( 'No Rank Requirements found in Trash', 'badgeos' ),
					'parent_item_colon'     => '',
					'menu_name'             => esc_html__( 'Rank Requirement', 'badgeos' ),
					'featured_image'        => esc_html__( 'Badgeos Rank Image', 'badgeos' ),
					'set_featured_image'    => esc_html__( 'Set badgeos Rank image', 'badgeos' ),
					'remove_featured_image' => esc_html__( 'Remove badgeos Rank image', 'badgeos' ),
					'use_featured_image'    => esc_html__( 'Use badgeos Rank image', 'badgeos' ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => str_replace( '_', '-', $settings['ranks_step_post_type'] ) ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title' ),
			)
		);
	}

}
add_action( 'init', 'badgeos_register_ranks_post_types' );

/**
 * Register each of our achievement Types as CPTs
 */
function badgeos_register_ranks_type_cpt() {

	$settings = ( $exists = badgeos_utilities::get_option( 'badgeos_settings' ) ) ? $exists : array();
	if ( ! empty( $settings ) ) {

		/**
		 * Grab all of our rank type posts
		 */
		$rank_types = get_posts(
			array(
				'post_type'      => trim( $settings['ranks_main_post_type'] ),
				'posts_per_page' => -1,
			)
		);

		/**
		 * Loop through each rank type post and register it as a CPT
		 */
		foreach ( $rank_types as $rank_type ) {

			$rank_name_singular = $rank_type->post_title;
			$rank_name_plural   = badgeos_utilities::get_post_meta( $rank_type->ID, '_badgeos_plural_name', true );

			if ( empty( $rank_name_plural ) ) {
				$rank_name_plural = $rank_name_singular;
			}

			/**
			 * Determine whether this achievement type should be visible in the menu
			 */
			$show_in_menu = badgeos_utilities::get_post_meta( strtolower( $rank_type->ID ), '_badgeos_show_in_menu', true ) ? 'badgeos_ranks' : false;

			/**
			 * Register the post type
			 */
			register_post_type(
				$rank_type->post_name,
				array(
					'labels'             => array(
						'name'               => $rank_name_plural,
						'singular_name'      => $rank_name_singular,
						'add_new'            => esc_html__( 'Add New', 'badgeos' ),
						'add_new_item'       => sprintf( esc_html__( 'Add New %s', 'badgeos' ), $rank_name_singular ),
						'edit_item'          => sprintf( esc_html__( 'Edit %s', 'badgeos' ), $rank_name_singular ),
						'new_item'           => sprintf( esc_html__( 'New %s', 'badgeos' ), $rank_name_singular ),
						'all_items'          => $rank_name_plural,
						'view_item'          => sprintf( esc_html__( 'View %s', 'badgeos' ), $rank_name_singular ),
						'search_items'       => sprintf( esc_html__( 'Search %s', 'badgeos' ), $rank_name_plural ),
						'not_found'          => sprintf( esc_html__( 'No %s found', 'badgeos' ), strtolower( $rank_name_plural ) ),
						'not_found_in_trash' => sprintf( esc_html__( 'No %s found in Trash', 'badgeos' ), strtolower( $rank_name_plural ) ),
						'parent_item_colon'  => '',
						'menu_name'          => $rank_name_plural,
					),
					'public'             => true,
					'publicly_queryable' => true,
					'show_ui'            => current_user_can( badgeos_get_manager_capability() ),
					'show_in_menu'       => $show_in_menu,
					'query_var'          => true,
					'rewrite'            => array( 'slug' => strtolower( $rank_type->post_name ) ),
					'capability_type'    => 'post',
					'has_archive'        => true,
					'hierarchical'       => true,
					'menu_position'      => null,
					'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail' ),
				)
			);

			/**
			 * Register the rank type
			 */
			badgeos_register_ranks_type( $rank_type->post_name, strtolower( $rank_name_singular ), strtolower( $rank_name_plural ) );

		}
	}
}
add_action( 'init', 'badgeos_register_ranks_type_cpt', 8 );
