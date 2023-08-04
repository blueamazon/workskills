<?php
/**
 * 
 *
 * @class       Wplms_Application_Forms_Update
 * @author      VibeThemes(H.K.)
 * @category    Admin
 * @package     wplms-aaplication-forms/include
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Application_Forms_Update{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Application_Forms_Update();
        return self::$instance;
    }

	private function __construct(){
		add_filter('wplms_addon_class',array($this,'check_addon_license_status'),10,2);
		add_filter('wplms_lms_addons',array($this,'addon'));
		add_filter('admin_init',array($this,'wplms_application_forms_activate_license'));
		add_action('admin_init', array($this,'wplms_application_forms_deactivate_license'));
	}

	function addon($fields){
		$status = get_option('wplms_application_forms_license_status');
		switch($status){
			case 'valid':
				$fields['wplms-application-forms']['price'] = 'ACTIVE';
			break;
			case 'invalid':
				$fields['wplms-application-forms']['price'] = 'INVALID LICENSE KEY';
			break;
			default:
				$fields['wplms-application-forms']['price'] = 'INACTIVE';
			break;
		}
		return $fields;
	}
	
	function check_addon_license_status($class,$addon){

		if($addon['license_key'] != 'wplms_application_forms_license_key')
			return $class;

		$status = get_option('wplms_application_forms_license_status');
		if(empty($status)){
			$class .=' inactive';
		}else{
			$class .=' '.$status;
		}

		return $class;
	}
	

	function sanitize_license( $new ) {
		$old = get_option( 'wplms_application_forms_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'wplms_application_forms_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}



	/************************************
	* this illustrates how to activate
	* a license key
	*************************************/

	function wplms_application_forms_activate_license($message) {

		// listen for our activate button to be clicked
		if( empty($_POST['license_key']) || empty($_POST['wplms_application_forms_license_key']) || $_POST['wplms_application_forms_license_key'] != 'Activate') 
			return;

		if( ! check_admin_referer( 'wplms-application-forms', 'wplms-application-forms' ) )
			return; // get out if we didn't click the Activate button


		$license = trim($_POST['license_key']);
		update_option('wplms_application_forms_license_key',$license);
		// retrieve the license from the database
		

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( 'wplms-application-forms' ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPLMS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ){
			return __('Unable to contact server','wplms-af');
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'wplms_application_forms_license_status', $license_data->license );
		set_transient( 'wplms_application_forms_license_check',  $license_data->license,7 * DAY_IN_SECONDS );
		return $message;
	}
	


	/***********************************************
	* Illustrates how to deactivate a license key.
	* This will descrease the site count
	***********************************************/

	function wplms_application_forms_deactivate_license() {

		if( empty($_POST['license_key']) || empty($_POST['wplms_application_forms_license_key']) || $_POST['wplms_application_forms_license_key'] != 'Deactivate') 
			return;

		// listen for our activate button to be clicked
		

		// run a quick security check
	 	if( ! check_admin_referer( 'wplms-application-forms', 'wplms-application-forms' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'wplms_application_forms_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( 'wplms-application-forms' ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPLMS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ){
			delete_option( 'wplms_application_forms_license_status' );
			delete_option( 'wplms_application_forms_license_key' );
		}

		if($license_data->license == 'failed')
			delete_option( 'wplms_application_forms_license_key' );

	}
	


	/************************************
	* this illustrates how to check if
	* a license key is still valid
	* the updater does this for you,
	* so this is only needed if you
	* want to do something custom
	*************************************/

	function wplms_application_forms_check_license() {

		global $wp_version;

		$license = trim( get_option( 'wplms_application_forms_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license,
			'item_name' => urlencode( 'wplms-application-forms' ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPLMS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );


		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'valid' ) {
			return 'valid';
			// this license is still valid
		} else {
			return 'invalid';
			// this license is no longer valid
		}
		return;
	}
}

Wplms_Application_Forms_Update::init();
