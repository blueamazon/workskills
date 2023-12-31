<?php
/**
 * Point Meta Boxes.
 *
 * @package Badgeos
 * @subpackage Point
 * @author LearningTimes, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 * @link https://credly.com
 */

/**
 * Register custom meta box for Badgeos Point type.
 */
function badgeos_credits_type_metaboxes() {

	/**
	 * Start with an underscore to hide fields from custom fields list.
	 */
	$prefix = '_point_';

	/**
	 * Setup our $post_id, if available.
	 */
	$post_id = isset( $_GET['post'] ) ? sanitize_text_field( wp_unslash( $_GET['post'] ) ) : 0;

	/**
	 * New Achievement Types.
	 */
	$settings = ( badgeos_utilities::get_option( 'badgeos_settings' ) ) ? badgeos_utilities::get_option( 'badgeos_settings' ) : array();

	$cmb_obj = new_cmb2_box(
		array(
			'id'           => 'points_type_data',
			'title'        => esc_html__( 'Point Data', 'badgeos' ),
			'object_types' => array( trim( $settings['points_main_post_type'] ) ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		)
	);

	$cmb_obj->add_field(
		array(
			'name' => esc_html__( 'Plural Name', 'badgeos' ),
			'desc' => esc_html__( 'The plural name for this point (Title is singular name).', 'badgeos' ),
			'id'   => $prefix . 'plural_name',
			'type' => 'text_medium',
		)
	);
}
add_action( 'cmb2_admin_init', 'badgeos_credits_type_metaboxes' );
