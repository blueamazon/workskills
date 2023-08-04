<?php

/**
 * Gravity Forms Registration form to modal
 *
 * @since 2.01
 *
 * Class LRM_Pro_GravityForms
 */
class LRM_Pro_GravityForms {

	/**
	 * @return array|string[]
	 */
	static function get_forms_flat() {
		$forms_flat = [0=>'No Gravity Forms installed or No forms created!'];
		if ( class_exists('GFAPI') ) {
			$forms = GFAPI::get_forms();
				if ( $forms ) {
					$forms_flat = [];
					foreach ( $forms as $form ) {
						$forms_flat[ $form['id'] ] = sprintf('%s [#%d]', $form['title'], $form['id']);
					}
				}
		}

		return $forms_flat;
	}

	static function get_selected_form_id() {
		$form_id = lrm_setting( 'integrations/gf/form_id' );
		if ( $form_id && (int)$form_id !== 0 ) {
			$form = GFAPI::get_form( $form_id );
			if ( $form ) {
				return $form_id;
			}
		}
		return false;
	}

	static function display_selected_form() {
		$form_id = self::get_selected_form_id();

		if ( $form_id ) {
			$extras = '';
			if ( !lrm_setting( 'integrations/gf/show_title_and_desc' ) ) {
				$extras = ' title="false" description="false" ';
			}
			echo do_shortcode( sprintf('[gravityform id="%d" ajax="true" %s]', $form_id, $extras) );
			return;
		}
		echo '<h3>Error: No Gravity Forms form is selected to display!</h3>';
	}

	static function is_gf_active() {
		return class_exists('GFAPI');
	}

}