<?php
/**
 *
 * @class       WPLMS_Application_Forms_Init
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPLMS_Application_DB {

	public function __construct(){
		/*
		* https://code.tutsplus.com/tutorials/custom-database-tables-creating-the-table--wp-28124
		* #blog #gist
		*/
		add_action( 'init', array( $this, 'wplms_applications_table'), 1 );
		add_action( 'switch_blog', array( $this, 'wplms_applications_table') );

	} // END public function __construct
	
	
	function wplms_applications_table() {
		global $wpdb;
		$wpdb->applications = "{$wpdb->prefix}wplms_applications";
	}

	function get_application_status( $user_id, $course_id ){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->applications} WHERE  user_id = %d AND course_id = %d", $user_id, $course_id ) );
	}

	function get_application_form( $user_id, $course_id ){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT application_form FROM {$wpdb->applications} WHERE  user_id = %d AND course_id = %d", $user_id, $course_id ) );
	}

	function get_attachments( $user_id, $course_id ){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT attachments FROM {$wpdb->applications} WHERE  user_id = %d AND course_id = %d", $user_id, $course_id ) );
	}

	function get_form_details_by_id( $id ){
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->applications} WHERE id = %d", $id ), ARRAY_A );
	}

	function delete_application_form( $user_id, $course_id, $attachments = '' ){
		global $wpdb;
		if( !is_null( $attachments ) && empty( $attachments ) ){
			$attachments = $this->get_attachments( $user_id, $course_id);
		}
		if( $attachments ){				
			$attachments = maybe_unserialize( $attachments );
			foreach( $attachments as $attachment )
				wp_delete_attachment( $attachment );
		}
		$wpdb->delete(
			$wpdb->applications,
			array(  'user_id' => $user_id,  'course_id' => $course_id  )
		);
		delete_user_meta($user_id,'apply_course'.$course_id,$course_id);
	}
	
	function install_application_database(){
		global $wpdb;
		
		$table_name = $wpdb->prefix . "wplms_applications"; 
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				  id int(11) NOT NULL AUTO_INCREMENT,
				  user_id int(11) NOT NULL COMMENT 'User ID as per WordPress user table',
				  course_id int(11) NOT NULL COMMENT 'Post ID of post_type course',
				  application_form longtext,
				  attachments text,
				  do_submission timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of application submission',
				  do_process timestamp NULL DEFAULT NULL COMMENT 'Date of application approved or rejected',
				  status set('1','2','3','4','5') NOT NULL DEFAULT '2' COMMENT '1: Approved, 2: Pending, 3: Rejected, 4: Removed/unsubscribed from course, 5: Re-Enabled to apply',
				  PRIMARY KEY (id),
				  UNIQUE KEY `app_id` (`user_id`,`course_id`) USING BTREE
				) $charset_collate; COMMIT;";
					
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
$wafdb = new WPLMS_Application_DB();