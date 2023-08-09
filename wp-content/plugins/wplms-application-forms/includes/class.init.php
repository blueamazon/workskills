<?php

/**
 *
 * @class       WPLMS_Application_Forms_Init
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     2.0
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPLMS_Application_Forms_Init
{

	public static $instance;
	public static function Instance_WPLMS_Application_Forms_Init()
	{

		if (is_null(self::$instance))
			self::$instance = new WPLMS_Application_Forms_Init();
		return self::$instance;
	}

	private function __construct()
	{

		add_filter('wplms_course_product_metabox', array($this, 'add_wplms_application_forms_in_course'), 99, 1);
		add_filter('wplms_take_this_course_button_label', array($this, 'add_wplms_application_form_on_course_details'), 99, 2);
		add_filter('wplms_private_course_button_label', array($this, 'add_wplms_application_form_on_course_details'), 99, 2);
		add_action('wp_ajax_submit_course_aaplication_form', array($this, 'submit_course_aaplication_form'));
		add_action('wplms_course_application_submission_users', array($this, 'wplms_show_user_application_form'), 10, 2);
	} // END public function __construct.


	function add_wplms_application_forms_in_course($metabox)
	{

		$metabox['vibe_wplms_application_forms'] = array(
			'label' => __('Invite Application Form', 'wplms-af'),
			'text' => __('Invite Application Form', 'wplms-af'),
			'type' => 'yesno',
			'options'  => array(
				array(
					'value' => 'H',
					'label' => __('No', 'vibe-customtypes')
				),
				array(
					'value' => 'S',
					'label' => __('Yes', 'vibe-customtypes')
				),
			),
			'style' => '',
			'id' => 'vibe_wplms_application_forms',
			'from' => 'meta',
			'default' => 'H',
			'desc' => __('Show application form to the users to get their information before applying for the course.', 'wplms-af'),
		);
		$metabox['vibe_wplms_application_forms_editor'] = array(
			'label' => __('Application Form', 'wplms-af'),
			'text' => __('Add Application Form', 'wplms-af'),
			'type' => 'editor',
			'noscript' => true,
			'style' => '',
			'id' => 'vibe_wplms_application_forms_editor',
			'from' => 'meta',
			'desc' => __('Application form shown to the users before applying for the course.', 'wplms-af'),
		);

		if (!is_admin()) {
			$metabox['vibe_course_apply']['type'] = 'conditionalswitch';
			$metabox['vibe_course_apply']['hide_nodes'] = array('vibe_wplms_application_forms', 'vibe_wplms_application_forms_editor');
			$metabox['vibe_course_apply']['options'] = array('H' => __('Hide', 'wplms-af'), 'S' => __('Show', 'wplms-af'));
			$metabox['vibe_wplms_application_forms']['type'] = 'conditionalswitch';
			$metabox['vibe_wplms_application_forms']['hide_nodes'] = array('vibe_wplms_application_forms_editor');
			$metabox['vibe_wplms_application_forms']['options'] = array('H' => __('DISABLE', 'wplms-af'), 'S' => __('ENABLE', 'wplms-af'));
		}
		return $metabox;
	}

	function add_wplms_application_form_on_course_details($label, $course_id)
	{
		if (!is_user_logged_in()) {
			return $label;
		}
		$check = get_post_meta($course_id, 'vibe_course_apply', true);
		if (vibe_validate($check)) {
			$user_id = get_current_user_id();
			$check_apply = get_user_meta($user_id, 'apply_course' . $course_id, true);
			global $wafdb;
			$status = $wafdb->get_application_status($user_id, $course_id);

			switch ($status) {
				case 1:
					echo '<p class="message approved">' . __('Your application has been approved , Please make payments.', 'wplms-af') . "</p>";
					break;

				case 2:
					echo '<p class="message pending">' . __('You have already applied to this course, kindly wait for approval.', 'wplms-af') . "</p>";
					break;

				case 3:
					echo '<p class="message rejected">' . __('Your application has been rejected , kindly contact Instructor/Administrator for more informaiton.', 'wplms-af') . "</p>";
					$label = _x('Applied for Course', 'Apply for Course label for course', 'vibe');
					add_action('wp_footer', array(bp_course_filters::init(), 'remove_apply_for_course_id'));
					break;

				case 4:
					echo '<p class="message removed">' . __('You has been removed from course , kindly reapply or contact to Instructor/Administrator for more informaiton.', 'wplms-af') . "</p>";
					break;

				case 5:
					echo '<p class="message enabled">' . __('You have been allowed to apply for this course again.', 'wplms-af') . "</p>";
					break;

				default:
					break;
			}
			remove_filter('wplms_private_course_button_label', array($this, 'add_wplms_application_form_on_course_details'), 99, 2);
			if (empty($check_apply) && !in_array($status, array(1, 3))) { //need to show form user can reapply
				$check_apply_form = get_post_meta($course_id, 'vibe_wplms_application_forms', true);
				if (vibe_validate($check_apply_form)) {
					$check_apply_content = get_post_meta($course_id, 'vibe_wplms_application_forms_editor', true);
					if (!empty($check_apply_content)) {
						if (function_exists('is_diploma')) {
							global $post;
							if (is_diploma($post->ID) && $post->ID != $course_id)
								return $label;
						}
						echo '<div class="course_aaplication_form">';
						$aggrement = '';
						$lms_settings = get_option('lms_settings');

						if ($lms_settings['general']['terms_and_condition'] || $lms_settings['general']['privacy_policy']) {
							$list = array();
							if ($lms_settings['general']['terms_and_condition']) {
								$term_page_id = apply_filters('wpml_object_id', $lms_settings['general']['terms_and_condition'], 'page', true);
								$list[] = '<a href="' . get_permalink($term_page_id) . '" target="_blank">' . get_the_title($term_page_id) . '</a>';
							}

							if ($lms_settings['general']['privacy_policy']) {
								$privacy_page_id = apply_filters('wpml_object_id', $lms_settings['general']['privacy_policy'], 'page', true);
								$list[] = '<a href="' . get_permalink($privacy_page_id) . '" target="_blank">' . get_the_title($privacy_page_id) . '</a>';
							}

							$aggrement = "<p class='terms_privacy'>
										<label form='terms_privacy'>
											<input id='terms_privacy' class='form_field terms_privacy' type='checkbox' name='terms_privacy' value='Terms Policy' data-validate='required' data-required='" . __('You must agree with Terms and conditions', 'wplms-af') . "' required>" . __(' I have read and accept the ', 'wplms-af') . implode(__(' and ', 'wplms-af'), $list)
								. "</label>
									</p>";
						}

						echo str_replace('</form>', $aggrement . '</form>', do_shortcode($check_apply_content));

						echo '</div>';
						add_action('wp_footer', array($this, 'add_script_for_apply_for_course_button'));
					}
				}
			}
		}
		return $label;
	}

	function add_script_for_apply_for_course_button()
	{
		?>
		<script>
			jQuery(document).ready(function($) {

				$('#apply_course_button').on('click', function(e) {
					e.preventDefault(); //Added by Makarand. Prevent default # action
					$('p.error').remove();
					if ($('.course_aaplication_form .accordion .accordion-toggle.collapsed').length > 0) {
						$.confirm({
							text: '<?php _e('You need to fill form to apply for this course.', 'wplms-af'); ?>',
							confirm: function() {
								$('.course_aaplication_form .accordion .accordion-toggle').trigger('click');
							},
							confirmButton: '<?php _e('Proceed', 'wplms-af'); ?>',
						});
						e.stopImmediatePropagation();
					}
					
					var flag = true;

					if ($('.course_aaplication_form .accordion .accordion-toggle.collapsed').length == 0) { //if flag set, added by dev
						if ($('.pl_form_uploader_progress').length != 0) {
							if (!$('.pl_form_uploader_progress').hasClass('visible') || jQuery('.attachment_ids').length == 0) {
								flag = false;
								$('#plupload-upload-ui').after("<p class='error'><?php _e('Error : Please Upload Files.', 'wplms-af'); ?></p>");
								e.stopImmediatePropagation(); //Prevent next events - makarand
							}
						} // } added by dev 

						var $this = $(this);
						var default_html = $this.html();
						var form = $('.course_aaplication_form').find('form');
						if (typeof(form) != 'undefined') {
							$this.removeClass('disabled');
						} else {
							$this.addClass('disabled');
						}

						if ($this.hasClass('disabled')) {
							$('#apply_course_button').off('click');
							return;
						}

						var parent = $('.course_aaplication_form').find('form');
						var $response = parent.find(".response");
						var error = '';
						var data = [];
						var label = [];
						var regex = [];
						var attachment = [];
						var event = parent.attr('data-event');
						regex['email'] = /^([a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$)/i;
						regex['phone'] = /[A-Z0-9]{7}|[A-Z0-9][A-Z0-9-]{7}/i;
						regex['numeric'] = /^[0-9]+$/i;
						regex['captcha'] = /^[0-9]+$/i;
						regex['required'] = /([^\s])/;
						var i = 0;

						parent.find('.form_field').each(function() {
							i++;
							var validate = $(this).attr('data-validate');
							var value = $(this).val();

							if ($(this).attr('type') == 'checkbox' && $(this).hasClass('terms_privacy')) {
								if ($(this).is(":not(:checked)")) {
									error += '<br /> ' + $(this).attr('data-required');
									$(this).after("<p class='error'>" + $(this).attr('data-required') + "</p>");
									flag = false;
								}
							} else if (!value.match(regex[validate])) {
								var er = vibe_shortcode_strings.invalid_string + $(this).attr('placeholder')
								error += ' ' + er;
								$(this).css('border-color', '#e16038');
								$(this).after(`<p class="error">${er}</p>`)
							} else {
								data[i] = value;
								label[i] = $(this).attr('placeholder');
								if (parent.hasClass('isocharset')) {
									label[i] = encodeURI(label[i]);
									data[i] = encodeURI(value);
								}

							}
							if (validate === 'captcha') {
								var $num = $(this).attr('id');
								var $sum = $(this).closest('.math-sum');
								var num1 = parseInt($('#' + $num + '-1').text());
								var num2 = parseInt($('#' + $num + '-2').text());
								var sumval = parseInt($(this).val());
								if (sumval != (num1 + num2)) {
									var er = vibe_shortcode_strings.captcha_mismatch
									error += ' ' + er;
									$(this).css('border-color', '#e16038');
									$(this).after(`<p class="error">${er}</p>`)
								}
							}
							if ($(this).hasClass('select') && $(this).val() == "") {
								var er = vibe_shortcode_strings.invalid_string + $(this).attr('placeholder')
								error += ' ' + er;
								$(this).css('border-color', '#e16038');
								$(this).after(`<p class="error">${er}</p>`)
							}
							if (validate === 'date' && $(this).val() == "") {
								var er = vibe_shortcode_strings.invalid_string + $(this).attr('placeholder')
								error += ' ' + er;
								$(this).css('border-color', '#e16038');
								$(this).after(`<p class="error">${er}</p>`)
							}
						});

						var attachment_id = $('.course_aaplication_form').find('.attachment_ids').map(function() {
							return parseInt($(this).val());
						}).get();
						//attachment_id = parseInt(attachment_id);
						if (typeof(attachment_id) == 'object' || typeof(attachment_id) == 'number') {
							attachment[0] = parent.find('.form_upload_label').text();
							attachment[1] = attachment_id;
						}

						if (error !== "") {
							$response.fadeIn("slow");
							//$response.html("<span style='color:#D03922;'>"+vibe_shortcode_strings.error_string+" " + error + "</span>");  //This line is replaced with below if loop
							if (error == "Captcha Mismatch") {
								// $response.html("<span style='color:#D03922;'>" + vibe_shortcode_strings.error_string + " " + error + "</span>");
							} else {
								// $response.html("<span style='color:#D03922;'>" + vibe_shortcode_strings.error_string + " " + error + "</span>");
								if (flag == true) {
									$('#plupload-upload-ui').parent().find('.response span').remove();
								}
							}
						} else {
							var isocharset = false;
							if (parent.hasClass('isocharset')) {
								isocharset = true;
							}
							$('.course_aaplication_form').find('form').find('.response span').remove(); //added by dev
							$this.html('<i class="fa fa-spinner animated spin"></i>');
							$.confirm({
								text: vibe_course_module_strings.confirm_apply,
								confirm: function() {
									$.ajax({
										type: "POST",
										url: ajaxurl,
										data: {
											action: 'apply_for_course',
											security: $this.attr('data-security'),
											course_id: $this.attr('data-id'),
										},
										cache: false,
										success: function(html) {
											$this.html(html);
											$('p.error').remove();
										}
									});
									setTimeout(function() {
										$.ajax({
											type: "POST",
											url: ajaxurl,
											data: {
												action: 'submit_course_aaplication_form',
												security: $response.attr('data-security'),
												isocharset: isocharset,
												label: JSON.stringify(label),
												data: JSON.stringify(data),
												course_id: $this.attr('data-id'),
												event: event,
												attachment: attachment,
											},

											cache: false,
											success: function(html) {
												$('body').find('#apply_course_button').removeAttr('id').off('click');
												form.parent().remove();
												$('.widget p.message').remove();

											}
										});
									}, 1000);
								},
								cancel: function() {
									$this.html(default_html);
								},
								confirmButton: vibe_course_module_strings.confirm,
								cancelButton: vibe_course_module_strings.cancel
							});
						}
					} //end if, added by dev
					e.stopImmediatePropagation(); //Prevent next events - makarand
				});
			});
		</script>
<?php
	}

	function submit_course_aaplication_form()
	{
		$nonce = $_POST['security'];
		$event = $_POST['event'];
		if (!wp_verify_nonce($nonce, 'vibeform_security' . $event) || empty($_POST['course_id'])) {
			echo __("Security check failed, please contact administrator", "wplms-af");
			die();
		}

		global $wpdb;

		$data = json_decode(stripslashes($_POST['data']));
		$labels = json_decode(stripslashes($_POST['label']));

		$message = '<ul>';
		for ($i = 1; $i < count($data); $i++) {
			$message .= '<li>';
			$message .= $labels[$i] . ' : ' . $data[$i];
			$message .= '</li>';
		}

		if (isset($_POST['attachment']) && !empty($_POST['attachment'])) {
			$attachment = $_POST['attachment'];
			$message .= '<li>';
			$message .= $attachment[0];
			foreach ($attachment[1] as $sigle_attachment)
				$message .= ' : <a href="' . wp_get_attachment_url($sigle_attachment) . '" target="_blank" title="' . __('Open in new tab', 'wplms-af') . '"><i class="fa fa-external-link external-link"></i></a>';				//Anchor tag added by dev, did some changes
			$message .= '</li>';
		}
		$message .= '</ul>';
		$user_id = get_current_user_id();
		$course_id = $_POST['course_id'];

		$wpdb->replace(
			$wpdb->applications,
			array(
				'user_id' => $user_id,
				'course_id' => $course_id,
				'do_process' => NULL,
				'application_form' => $message,
				'attachments' => maybe_serialize($attachment[1]),
				'status' => 2,
			)
		);
		die();
	}

	function wplms_show_user_application_form($user_id, $course_id)
	{
		global $wafdb;
		$application_form = $wafdb->get_application_form($user_id, $course_id);
		if (!empty($application_form)) {
			echo '<div class="user_application_form" style="width:400px;margin:auto;">' . $application_form . '</div>';
		}
	}
} // END class WPLMS_Application_Forms_Init
