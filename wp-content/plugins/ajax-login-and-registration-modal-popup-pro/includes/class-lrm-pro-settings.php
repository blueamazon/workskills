<?php

use underDEV\Utils\Settings\CoreFields;

/**
 * Class LRM_Pro_Settings
 * @since 1.11
 */
class LRM_Pro_Settings {

	/**
	 * @param \underDEV\Utils\Settings $settings_api
	 */
	public static function register_settings__action($settings_api) {
		
		$GENERAL_Section = $settings_api->add_section( __( 'General > PRO' ), 'general_pro' );

		$GENERAL_Section->add_group( __( 'General' ), 'all' )
            ->add_field( array(
                'slug'        => 'hide_form_after_registration',
                'name'        => __('Hide the registration form after the successful registration?', 'ajax-login-and-registration-modal-popup' ),
                'addons'      => array('label' => __( 'Yes' )),
                'default'     => true,
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
                'description'    => 'Only successful message will be visible',
            ) )
            ->add_field( array(
                'slug'        => 'hide_username',
                'name'        => __('Hide username filed from registration form?', 'ajax-login-and-registration-modal-popup' ),
                'addons'      => array('label' => __( 'Yes' )),
                'default'     => false,
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
                'description'    => 'Username will be generated from email',
            ) )
            ->add_field( array(
                'slug'        => 'allow_user_set_password',
                'name'        => __('Allow user set password during registration?', 'ajax-login-and-registration-modal-popup' ),
                'addons' => array('label' => __( 'Yes' )),
                'default'     => false,
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) )
            ->add_field( array(
                'slug'        => 'use_password_confirmation',
                'name'        => __('Show password confirmation field?', 'ajax-login-and-registration-modal-popup' ),
                'addons' => array('label' => __( 'Yes' )),
                'default'     => false,
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) )
			->add_field( array(
				'slug'        => 'use_password_strength',
				'name'        => __('Validate password strength?', 'ajax-login-and-registration-modal-popup'),
				'addons'      => array(
					'options'     => array(
						'yes'  => 'Yes',
						'yes_allow_weak'  => 'Yes, but allow to use weak password in any way',
						'no'   => 'No',
					),
				),
				'default'     => 'yes',
				'render'      => array( new CoreFields\Select(), 'input' ),
				'sanitize'    => array( new CoreFields\Select(), 'sanitize' ),
			) )
            ->add_field( array(
                'slug'        => 'password_strength_lib',
                'name'        => __('Password strength validation library', 'ajax-login-and-registration-modal-popup'),
                'addons'      => array(
                    'options'     => array(
                        'wp'   => 'Wordpress (using zxcvbn.js lib)',
                        'lrm'  => 'Light in-build checker',
                    ),
                ),
                'default'     => 'before',
                'description' => '<a href="https://github.com/dropbox/zxcvbn" target="_blank">zxcvbn</a> is a password strength estimator inspired by password crackers. Through pattern matching and conservative estimation, it recognizes and weighs 30k common passwords, common names and surnames according to US census data, popular English words from Wikipedia and US television and movies, and other common patterns like dates, repeats (aaa), sequences (abcd), keyboard patterns (qwertyuiop), and l33t speak.',
                'render'      => array( new CoreFields\Select(), 'input' ),
                'sanitize'    => array( new CoreFields\Select(), 'sanitize' ),
            ) )
            ->add_field( array(
                'slug'        => 'form_hook_position',
                'name'        => __('Call login/registration hook', 'ajax-login-and-registration-modal-popup'),
                'addons'      => array(
                    'options'     => array(
                        'before_btn'   => 'Before login/registration button (default)',
                        'before_form'   => 'Before login/registration form',
                        'after_form'    => 'After login/registration form',
                    ),
                ),
                'default'     => 'before',
                'description' => __('Position of Social Buttons/reCaptcha/etc depends on this option.', 'ajax-login-and-registration-modal-popup' ),
                'render'      => array( new CoreFields\Select(), 'input' ),
                'sanitize'    => array( new CoreFields\Select(), 'sanitize' ),
            ) );
//			->description(
//				sprintf(
//					'Buttons color you can customize in <a href="%s" class="button button-secondary">WP Customizer</a>',
//					admin_url('customize.php?autofocus[section]=lrm_controls_section')
//				)
//			);

		$redirect_note = sprintf(
			'<strong>' . __('Please disable option «%s» in "General" section for use that options!', 'ajax-login-and-registration-modal-popup' ) . '</strong>',
            __('Reload page after login/registration?', 'ajax-login-and-registration-modal-popup' )
		);
//		$redirect_note .= sprintf(
//			'<br/><strong>' . __('That options only work if «%s» option is disabled.', 'ajax-login-and-registration-modal-popup' ) . '</strong>',
//            __('User must confirm email after registration?', 'ajax-login-and-registration-modal-popup' )
//		);
		$redirect_note .= sprintf(
			'<br/><strong>' . __('Please use link <code>%s</code> for logout', 'ajax-login-and-registration-modal-popup' ) . '</strong>',
            add_query_arg( 'lrm_logout', '1', site_url( '/' ) )
		);
		$redirect_note .= '<br/><strong>' . __('<code>redirect_to</code> param can be used for override global redirect url.', 'ajax-login-and-registration-modal-popup' ) . '</strong>';

//		$GENERAL_Section->add_group( __( 'Redirects' ), 'redirects' )
//            ->add_field( array(
//                'slug'        => 'url_after_login',
//                'name'        => __('Redirect user to following url after successful login:', 'ajax-login-and-registration-modal-popup' ),
//                'description' => __('enter correct url', 'ajax-login-and-registration-modal-popup' ),
//                'default'     => false,
//                'render'      => array( new CoreFields\Url(), 'input' ),
//                'sanitize'    => array( new CoreFields\Url(), 'sanitize' ),
//            ) )
//			->add_field( array(
//                'slug'        => 'url_after_registration',
//                'name'        => __('Redirect user to following url after successful registration and log in:', 'ajax-login-and-registration-modal-popup' ),
//                'description' => sprintf(
//			    '<strong>' . __('If «%s» option is disabled - user will be redirected immediately, else after account verification.', 'ajax-login-and-registration-modal-popup' ) . '</strong>',
//                    __('User must confirm email after registration?', 'ajax-login-and-registration-modal-popup' )
//		        ),
//                'default'     => false,
//                'render'      => array( new CoreFields\Url(), 'input' ),
//                'sanitize'    => array( new CoreFields\Url(), 'sanitize' ),
//            ) )
//    //			->add_field( array(
//    //                'slug'        => 'url_after_registration_need_verification',
//    //                'name'        => __('Redirect user to following url after successful registration (but email verification required):', 'ajax-login-and-registration-modal-popup' ),
//    //                'description' => sprintf(
//    //                    '<strong>' . __('Something like user account url. If «%s» option is enabled - you cloud redirect to the page with email verification instructions.', 'ajax-login-and-registration-modal-popup' ) . '</strong>',
//    //                    __('User must confirm email after registration?', 'ajax-login-and-registration-modal-popup' )
//    //                ),
//    //
//    //                'default'     => false,
//    //                'render'      => array( new CoreFields\Url(), 'input' ),
//    //                'sanitize'    => array( new CoreFields\Url(), 'sanitize' ),
//    //            ) )
//			->add_field( array(
//                'slug'        => 'url_after_logout',
//                'name'        => __('Redirect user to following url after successful logout:', 'ajax-login-and-registration-modal-popup' ),
//                'description' => __('enter correct url, or leave empty for redirect to home page', 'ajax-login-and-registration-modal-popup' ),
//                'default'     => false,
//                'render'      => array( new CoreFields\Url(), 'input' ),
//                'sanitize'    => array( new CoreFields\Url(), 'sanitize' ),
//            ) )
//            ->add_field( array(
//                'slug'        => 'silent_logout',
//                'name'        => __( 'Skip logout verification screen?', 'ajax-login-and-registration-modal-popup' ),
//                'default'     => true,
//                'addons' => array('label' => __( 'Yes' )),
//                'render'      => array( new CoreFields\Checkbox(), 'input' ),
//                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
//            ) )
//			->description($redirect_note);


        LRM_Pro_Auto_Trigger::get()->register_settings( $settings_api );

		$INTEGRATIONS_Section = $settings_api->add_section( __( 'Integrations > PRO' ), 'integrations' );

		$INTEGRATIONS_Section->add_group( __( 'ACF PRO' ), 'acf' )
             ->add_field( array(
                 'slug'        => 'on',
                 'name'        => __( 'Load the custom ACF PRO styles for the ACF registration fields?', 'ajax-login-and-registration-modal-popup' ),
                 'default'     => false,
                 'addons' => array('label' => __( 'Yes' )),
                 'description' => __('In case if you are using ACF to add the custom registration fields', 'ajax-login-and-registration-modal-popup'),
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) );

		$INTEGRATIONS_Section->add_group( __( 'Buddypress' ), 'bp' )
             ->add_field( array(
                 'slug'        => 'on',
                 'name'        => __( 'Replace default registration form with the Buddypress form?', 'ajax-login-and-registration-modal-popup' ),
                 'default'     => false,
                 'addons' => array('label' => __( 'Yes' )),
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) );

		$INTEGRATIONS_Section->add_group( __( 'Restrict Content Pro' ), 'rcp' )
             ->add_field( array(
                 'slug'        => 'on',
                 'name'        => __( 'Replace default registration form with the Restrict Content Pro form?', 'ajax-login-and-registration-modal-popup' ),
                 'description' => '<small><a target="_blank" href="https://docs.maxim-kaminsky.com/lrm/kb/restrict-content-pro-integration/">https://docs.maxim-kaminsky.com/lrm/kb/restrict-content-pro-integration/</a></small>',
                 'default'     => 'true',
                 'addons' => array('label' => __( 'Yes' )),
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) )
			->add_field( array(
				'slug'        => 'shortcode',
				'name'        => __( 'Restrict Content Pro form shortcode', 'ajax-login-and-registration-modal-popup' ),
				'description' => '<small><a target="_blank" href="https://docs.restrictcontentpro.com/article/1597-registerform">https://docs.restrictcontentpro.com/article/1597-registerform</a></small>',
				'default'     => '[register_form]',
				'render'      => array( new LRM_Field_Textarea_With_Html(), 'input' ),
				'sanitize'    => array( new LRM_Field_Textarea_With_Html(), 'sanitize' ),
			) );


		$INTEGRATIONS_Section->add_group( __( 'Woocommerce' ), 'woo' )
            ->add_field( array(
                'slug'        => 'use_wc_reset_page',
                'name'        => __( 'Use the WooCommerce Reset password page?' ),
                'default'     => 'true',
                'addons' => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) )

             ->add_field( array(
                 'slug'        => 'on',
                 'name'        => __( 'Display modal when user is not logged and add product to cart?' ),
                 'default'     => false,
                 'addons' => array('label' => __( 'Yes' )),
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) )
             ->add_field( array(
                 'slug'        => 'replace_form',
                 'name'        => __( 'Replace WooCommerce Account login/registration form?' ),
                 'default'     => false,
                 'addons' => array('label' => __( 'Yes' )),
                 'description' => '<a href="https://monosnap.com/file/GlypwOsTXY1B2DcPOX4ovVqMLGr4mz" target="_blank">Example</a>. 
                    Please note, in favor of some Woo settings: <u>"Allow customers to create an account on the "My account" page"</u>, <u>"When creating an account, automatically generate a username from the customer\'s email address"</u>, <u>"When creating an account, automatically generate an account password"</u> will be used current plugin settings.',
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) )
             ->add_field( array(
                 'slug'        => 'on_proceed_to_checkout',
                 'name'        => __( 'Display modal when user is not logged and click "Proceed to checkout" on Cart page?' ),
                 'default'     => false,
                 'addons' => array('label' => __( 'Yes' )),
                 'description' => 'Please note, enabling this option does not guarantee that user can\'t open Checkout without registration. He can simply open Checkout page url in browser.',
                 'render'      => array( new CoreFields\Checkbox(), 'input' ),
                 'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
             ) );

        $INTEGRATIONS_Section->add_group( __( 'Gravity Forms' ), 'gf' )
            ->add_field( array(
                'slug'        => 'replace_with',
                'name'        => __( 'Replace default registration form with Gravity Forms from?', 'ajax-login-and-registration-modal-popup' ),
                'default'     => false,
                'addons' => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) )
            ->add_field( array(
                'slug'        => 'show_title_and_desc',
                'name'        => __( 'Show form title and description from Gravity form "Settings"?', 'ajax-login-and-registration-modal-popup' ),
                'default'     => false,
                'addons'      => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) )
	        ->add_field( array(
		        'slug'        => 'form_id',
		        'name'        => __('Select a Form', 'ajax-login-and-registration-modal-popup'),
		        'addons'      => array(
			        'options'     => LRM_Pro_GravityForms::get_forms_flat(),
		        ),
		        'default'     => '',
		        'description' => 'Select a Form to display',
		        'render'      => array( new CoreFields\Select(), 'input' ),
		        'sanitize'    => array( new CoreFields\Select(), 'sanitize' ),
	        ) );


        $INTEGRATIONS_Section->add_group( __( 'UltimateMember' ), 'um' )
            ->add_field( array(
                'slug'        => 'respect_require_admin_review',
                'name'        => __( 'Respect UM Role "Require Admin Review" value from "Registration Status" option?', 'ajax-login-and-registration-modal-popup' ),
                'default'     => false,
                'addons' => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
                'description' => 'This allows you to enable UM users review for a new registrations (role based) while using current plugin default registration form.',
            ) )
            ->add_field( array(
                'slug'        => 'replace_with',
                'name'        => __( 'Replace default registration form with UltimateMember from?', 'ajax-login-and-registration-modal-popup' ),
                'default'     => false,
                'addons' => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) );

        $INTEGRATIONS_Section->add_group( __( 'MailChimp For Woocommerce' ), 'mc_for_wc' )
            ->add_field( array(
                'slug'        => 'add_checkbox',
                'name'        => __( 'Add "MailChimp For Woocommerce" checkbox to the registration form?', 'ajax-login-and-registration-modal-popup' ),
                'default'     => false,
                'addons' => array('label' => __( 'Yes' )),
                'render'      => array( new CoreFields\Checkbox(), 'input' ),
                'sanitize'    => array( new CoreFields\Checkbox(), 'sanitize' ),
            ) );

		$MESSAGES_SECTION = $settings_api->add_section( __( 'Expressions > PRO' ), 'messages_pro' );

		// Compatibility with LRM free < 1.37
		$html_field_class = class_exists('LRM_Field_Textarea_With_Html_Extended') ? 'LRM_Field_Textarea_With_Html_Extended' : 'LRM_Field_Textarea_With_Html';

		$MESSAGES_SECTION->add_group( __( 'Custom Text/HTML' ), 'info', true )

            ->add_field( array(
                'slug'        => 'login_before_form',
                'name'        => __('Before login form', 'ajax-login-and-login-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            ->add_field( array(
                'slug'        => 'login_before_button',
                'name'        => __('Before login button', 'ajax-login-and-login-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            ->add_field( array(
                'slug'        => 'login_after_form',
                'name'        => __('After login form', 'ajax-login-and-login-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            
            ->add_field( array(
                'slug'        => 'registration_before_form',
                'name'        => __('Before registration form', 'ajax-login-and-registration-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            ->add_field( array(
                'slug'        => 'registration_before_button',
                'name'        => __('Before registration button', 'ajax-login-and-registration-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            ->add_field( array(
                'slug'        => 'registration_after_form',
                'name'        => __('After registration form', 'ajax-login-and-registration-modal-popup' ),
                'render'      => array( new $html_field_class(), 'input' ),
                'sanitize'    => array( new $html_field_class(), 'sanitize' ),
                'addons'      => array('rows'=>2),
            ) )
            ->description( 'Here you can add some information for a user. Html and shortcodes allowed.' );

		$MESSAGES_SECTION->add_group( __( 'Registration/etc' ), 'registration', true )
             ->add_field( array(
                 'slug'        => 'verification_required',
                 'name'        => __('Account verification required', 'ajax-login-and-registration-modal-popup' ),
                 'default'        => __('<strong>ERROR</strong>: Please verify your account with clicking to link in registration email.', 'ajax-login-and-registration-modal-popup'),
                 'render'      => array( new LRM_Field_Textarea_With_Html(), 'input' ),
                 'sanitize'    => array( new LRM_Field_Textarea_With_Html(), 'sanitize' ),
             ) )
             ->add_field( array(
                 'slug'        => 'verification_completed',
                 'name'        => __('Verification completed message', 'ajax-login-and-registration-modal-popup' ),
                 'default'        => __('You account have been verified, thank you. Now you can <a href="{{LOGIN_URL}}">log in</a> to website.', 'ajax-login-and-registration-modal-popup'),
                 'render'      => array( new LRM_Field_Textarea_With_Html(), 'input' ),
                 'sanitize'    => array( new LRM_Field_Textarea_With_Html(), 'sanitize' ),
             ) )
                ->description('Password expressions can be now changed in the Expressions tab > Password (registration/reset password) group');

		$MESSAGES_SECTION->add_group( __( 'Match Captcha' ), 'match_captcha', true )
	         ->add_field( array(
	             'slug'        => 'label',
	             'name'        => __( 'Label' ),
	             'default'     => __('Prove your Humanity:', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => 'timeout',
	             'name'        => __( 'Message: Expired' ),
	             'default'     => __('Captcha has been expired, please try again!', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => 'invalid',
	             'name'        => __( 'Message: Invalid' ),
	             'default'     => __('Invalid captcha value.', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
		        ->add_field( array(
	             'slug'        => 'missing',
	             'name'        => __( 'Message: Empty value' ),
	             'default'     => __('Please enter captcha value.', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) );

		$MESSAGES_SECTION->add_group( __( 'Integrations' ), 'integrations', true )
	         ->add_field( array(
	             'slug'        => 'recaptcha_error',
	             'name'        => __( 'Message: reCAPTCHA error' ),
	             'default'     => __('Wrong reCAPTCHA!', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => 'googleauthenticator_label',
	             'name'        => __( 'Message: 2FA field label' ),
	             'default'     => __('2FA OTP code', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => 'googleauthenticator_required',
	             'name'        => __( 'Message: 2FA code is required' ),
	             'default'     => __('Please enter the 2FA code using the app on your device.', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => '2fa_code_invalid',
	             'name'        => __( 'Message: 2FA code is invalid' ),
	             'default'     => __('The 2FA code provided is either expired or invalid. Please try again.', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
            ->add_field( array(
	             'slug'        => 'two_factor_redirecting',
	             'name'        => __( 'Message: Redirecting to the Two Factor verification page (for https://wordpress.org/plugins/two-factor/)' ),
	             'default'     => __('Redirecting to the Two Factor verification page', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) )
	         ->add_field( array(
	             'slug'        => 'rscaptcha_error',
	             'name'        => __( 'Message: Really Simple CAPTCHA error' ),
	             'default'     => __('Your entered code is incorrect.', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) );


		$MESSAGES_SECTION->add_group( __( 'Woocommerce' ), 'woo', true )
	         ->add_field( array(
	             'slug'        => 'must_register',
	             'name'        => __( 'Must login/register for add item to the cart!' ),
	             'default'     => __('Please login or register before!', 'ajax-login-and-registration-modal-popup' ),
	             'render'      => array( new CoreFields\Text(), 'input' ),
	             'sanitize'    => array( new CoreFields\Text(), 'sanitize' ),
	         ) );


        $LICENSE_Section = $settings_api->add_section( __( 'License > PRO' ), 'license', false );

	}

}