<?php

/**
 * LRM_Pro_Mailchimp_for_Woocommerce checkbox to form
 * https://wordpress.org/plugins/mailchimp-for-woocommerce/
 *
 * @since 2.02
 *
 * Class LRM_Pro_Mailchimp_for_Woocommerce
 */
class LRM_Pro_Mailchimp_for_Woocommerce {

    /**
     * Add all necessary hooks
     */
    static function init() {
        if ( !class_exists('MailChimp_Newsletter') ) {
            return;
        }

        if ( !lrm_setting( 'integrations/mc_for_wc/add_checkbox' ) ) {
        	return;
        }

	    $MailChimp_Newsletter = MailChimp_Newsletter::instance();

	    add_action('lrm/register_form/before_button', [$MailChimp_Newsletter, 'applyNewsletterField'], 10);

	    add_action('lrm/registration_successful', function($user_id) use($MailChimp_Newsletter) {
		    $MailChimp_Newsletter->processNewsletterField(false, false);
	    });
    }

}