<?php

/**
 * UltimateMember Registration form to modal
 *
 * @since 1.28
 *
 * Class LRM_Pro_UltimateMember
 */
class LRM_Pro_UltimateMember {

    /**
     * Add all necessary hooks
     */
    static function init() {

        add_action('lrm/registration_successful', ["LRM_Pro_UltimateMember", "AJAX_wp_registration_complete_do_status"]);

        if ( ! self::is_ultimatemember_active() || ! lrm_setting( 'integrations/um/replace_with' ) ) {
            return;
        }

        if ( isset($_GET['lrm']) && isset($_POST['form_id']) ) {
//	        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_register_form' ) || empty( $args['_wpnonce'] ) || ! isset( $args['_wpnonce'] ) ) {
//		        wp_send_json_error( array(
//			        'logged_in' => false,
//			        'message'   => 'Nonce is invalid!',
//		        ) );
//	        }

	        add_action("um_submit_form_register", ["LRM_Pro_UltimateMember", "AJAX_signup"], 9);
	        //add_action("um_user_register", ["LRM_Pro_UltimateMember", "AJAX_um_registration_complete"], 9, 2);

            add_filter('wp_die_handler', function ($function) {
                return ["LRM_Pro_UltimateMember", "_wp_die_handler"];
            });

            add_filter('wp_redirect', function ($redirect_url) {
	            wp_send_json_success( array(
		            'is_um'     => true,
		            'logged_in' => true,
		            'message'   => false,
		            'redirect_url' => $redirect_url,
		            'action'       => 'redirect',
	            ) );
            });

	        UM()->form()->form_init();
	        die;
        }

    }

    /**
     * Check if UltimateMember plugin is installed and activated
     *
     * @return bool
     */
    static function is_ultimatemember_active() {
        return function_exists("is_ultimatemember");
    }

    /**
     * Display Form
     */
    static function render_registration_form() {
        //echo do_shortcode("[ultimatemember form_id=153]");

        global $wpdb;

        // Find Registration form
        $default_register = $wpdb->get_var(
            "SELECT pm.post_id 
				FROM {$wpdb->postmeta} pm 				
				WHERE pm.meta_key = '_um_mode' AND 
					  pm.meta_value = 'register'"
        );

        if ( !$default_register ) {
            echo "Can\'t determine UltimateMember Registration form ID! Tro to contest with a support!";
            return;
        }

        $default_register = apply_filters( 'lrm/um/default_register_form_ID', $default_register );

        echo do_shortcode( "[ultimatemember form_id='{$default_register}']" );

        //echo UM()->shortcodes()->ultimatemember_register();
    }

    /**
     * Process AJAX registration - sent errors
     */
	static function AJAX_signup( $post_form ) {

        if ( ! isset( UM()->form()->errors ) ) {
            return;
        }

        ob_start();
        um_display_login_errors( ['custom_fields'=>['1']] );

        wp_send_json_error( array(
            'logged_in' => false,
            'message'   => ob_get_clean(),
        ) );

	}

    /**
     * Use AJAX instead of simple wp_die()
     *
     * @param $message
     */
    static function _wp_die_handler( $message ) {

        echo "PRE DIE!";

        wp_send_json_error( array(
            'logged_in' => false,
            'message'   => $message,
        ) );

    }

    /**
     * Success LRM registration - check User role & make Pending if for this Role Admin moderation is needed in UM
     *
     * @param $user_id
     */
    static function AJAX_wp_registration_complete_do_status( $user_id ) {

        if ( function_exists('UM') && lrm_setting( 'integrations/um/respect_require_admin_review' ) ) {

            $user_role = UM()->roles()->get_priority_user_role( $user_id );
            $role_meta = UM()->roles()->role_data( $user_role );

            if ( class_exists('\um\core\User') && $role_meta && 'pending' === $role_meta['status'] ) {
                $user = new \um\core\User();
                $user->set( $user_id );
                $user->pending();
            }

        }
    }

    /**
     * Success registration
     *
     * @param $user_id
     * @param $args
     */
	static function AJAX_um_registration_complete( $user_id, $args ) {

        $status = um_user( 'account_status' );

        do_action( "um_post_registration_{$status}_hook", $user_id, $args );

        if ( $status == 'approved' ) {
            UM()->user()->auto_login($user_id);
            UM()->user()->generate_profile_slug($user_id);
	        do_action( 'um_registration_after_auto_login', $user_id );
        }

        $user_role = UM()->roles()->get_priority_user_role( get_current_user_id() );
        var_dump($user_role);
        $role_meta = UM()->roles()->role_data( $user_role );
        var_dump($role_meta);


		um_send_registration_notification( $user_id, $args );


        // Take from "um_check_user_status()"
		um_fetch_user( $user_id );

		$redirect_url = false;
		if ( um_user( 'auto_approve_act' ) == 'redirect_url' && um_user( 'auto_approve_url' ) !== '' ) {
			$redirect_url = um_user( 'auto_approve_url' );
		}

		if ( um_user( 'auto_approve_act' ) == 'redirect_profile' ) {
			$redirect_url = um_user_profile_url();
		}

		if ( !$redirect_url ) {
			$action       = lrm_setting( 'redirects/registration/action' );
			$redirect_url = LRM_Redirects_Manager::get_redirect( 'registration', $user_id );
		} else {
			$action = 'redirect';
		}

		wp_send_json_success( array(
            'is_um'     => true,
            'logged_in' => true,
            'user_id'   => $user_id,
            'message'   => LRM_Settings::get()->setting( 'messages/registration/success' ),
            'redirect_url' => $redirect_url,
            'action'       => $action,
        ) );

    }

}