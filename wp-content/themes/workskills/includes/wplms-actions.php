<?php

//Add Course meta box to enter Custom Course details (a repeatable field)
add_filter('wplms_course_metabox','wplms_custom_course_details_repeatable');
function wplms_custom_course_details_repeatable($metabox){
    $metabox['vibe_course_details'] = array(
        'label' => __('Custom Course details','vibe-customtypes'), // <label>
        'desc'  => __('custom course details.','vibe-customtypes'), // description
        'id'    => 'vibe_course_details', // field id and name
        'type'  => 'repeatable', // type of field
        'std'   => ''
    );
    return $metabox;
}

//Content added using vibe_course_details meta field shown on course page
add_filter('wplms_course_details_widget','wplms_custom_course_details_information',9999999);
function wplms_custom_course_details_information($details){
    $custom_info = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_course_details',false));
    if(isset($custom_info) && is_array($custom_info)){
        foreach($custom_info as $k=>$val){
            $details[]='<li>'.htmlspecialchars_decode($val).'</li>';
        }
    }
    return $details;
}

//Add metabox on Page, post & course to enter Header Background Image
add_filter('wplms_post_metabox','ng_custom_background');
add_filter('wplms_page_metabox','ng_custom_background');
add_filter('wplms_course_metabox','ng_custom_background');
function ng_custom_background($metabox) {
	$metabox['header_background_image'] = array(
        'label' => __('Header Background Image','nextgates'), // <label>
        'desc'  => __('Custom header background image if it is not set then it will call default background image to header.','nextgates'), // description
        'id'    => 'header_background_image', // field id and name
        'type'  => 'image', // type of field
        'std'   => ''
    );
    return $metabox;
}

//Set Course title background image
add_action('wp_head', 'wplms_custom_course_background_image');
function wplms_custom_course_background_image() {
	if ( is_singular('course') || is_singular('post') || is_page() ) {		
		
		//Custom background on Page, post & course added using ng_custom_background
		$custom_info = get_post_meta(get_the_ID(), 'header_background_image', true);
		$bg = wp_get_attachment_image_src($custom_info,'full');

		if( $bg ) { ?>
			<style type="text/css">
				.minimal .pusher #title {
					background: #225a66 url("<?php echo $bg[0]; ?>") center center no-repeat;
				}
			</style>
			<?php
		}
	}
}

/**
*	Audio script code 
*/

// Add media element JS & css on front, needed for audio player
add_action('wp_enqueue_scripts', 'enqueue_audio_scripts');
function enqueue_audio_scripts() {
    wp_enqueue_style('wp-mediaelement');
    wp_enqueue_script('wp-mediaelement');
}

//Adds meta box uneder units, to upload audio & audio seconds list
add_filter('wplms_unit_metabox','wplms_unit_js_variables');
function wplms_unit_js_variables($metabox){	
    $metabox['vibe_js_audio'] = array(
        'label' => __('Audio file','vibe-customtypes'), // <label>
        'desc'  => __('Upload audio file..','vibe-customtypes'), // description
        'id'    => 'vibe_js_audio', // field id and name
        'type'  => 'audio', // type of field
        'std'   => ''
    );
    $metabox['vibe_js_variables'] = array(
        'label' => __('Second timing for JS','vibe-customtypes'), // <label>
        'desc'  => __('Enter comma separated seconds for audio.','vibe-customtypes'), // description
        'id'    => 'vibe_js_variables', // field id and name
        'type'  => 'textarea', // type of field
        'std'   => ''
    );
    return $metabox;
}

//Print script after unit only if audio & list is present. Plays audio file & sync with highlighting content
add_action( 'wplms_after_every_unit', 'filter_the_content_in_the_main_loop', 0 );
function filter_the_content_in_the_main_loop( $id ) { 
	$seconds = get_post_meta($id, 'vibe_js_variables', true);
	$audio_id = get_post_meta($id, 'vibe_js_audio', true);
	$audio	 = wp_get_attachment_url( $audio_id,'full' );	
	if( $audio ) 
		echo '<div class="scrollcontroller"><audio id="audio-'.$id.'" ontimeupdate="playTranscript()" controls>
			  <source src="'.$audio .'" type="audio/mpeg">
			  Your browser does not support the audio element.
			  </source>
			</audio></div>';

	if( $audio && $seconds ) { 
		?>
		<script type="text/javascript">
			var dialogueTimings = [<?php echo $seconds; ?>],
				dialogues = document.querySelectorAll('.main_unit_content > .speaker1'),
				transcriptWrapper = document.querySelector('.main_unit_content '),
				audio = document.querySelector('#audio-<?php echo $id ?>'),
				previousDialogueTime = -1;  
			function playTranscript() {		 
				var currentDialogueTime = Math.max.apply(Math, dialogueTimings.filter(function(v){return v <= audio.currentTime}));		 
				if(previousDialogueTime !== currentDialogueTime) {
					previousDialogueTime = currentDialogueTime;
					var currentDialogue = dialogues[dialogueTimings.indexOf(currentDialogueTime)];
					if(currentDialogue === undefined)
						return;
					transcriptWrapper.scrollTop  = currentDialogue.offsetTop - 50; 
					var previousDialogue = document.getElementsByClassName('speaking')[0];
					if(previousDialogue !== undefined)
						previousDialogue.className = previousDialogue.className.replace('speaking','');
					currentDialogue.className +=' speaking';
				}
			}
		</script>
		<?php
	}
}

/* TinyMCE editor button */
// Add button to TinyMCE editor
function nextgates_class_editor_buttons() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    if (get_user_option('rich_editing') !== 'true') {
        return;
    }
    add_filter('mce_external_plugins', 'nextgates_class_editor_add_buttons');
    add_filter('mce_buttons', 'nextgates_class_register_buttons');
}
add_action('admin_head', 'nextgates_class_editor_buttons');
add_action('wp_head', 'nextgates_class_editor_buttons');

function nextgates_class_editor_add_buttons($plugin_array) {
    $buttons_js_path = get_stylesheet_directory_uri() . '/assets/js/nextgates_tinymce_buttons.js';
    $plugin_array['nextgates_custom_buttons'] = $buttons_js_path;
    return $plugin_array;
}

function nextgates_class_register_buttons($buttons) {
    array_push($buttons, 'tip_tag', 'var_tag', 'sayit_tag');
    return $buttons;
}

/* TinyMCE editor button ends */


//Changing the wp-login.php?action=lostpassword to /lost-password/
function custom_lost_password_page() {
    return wc_lostpassword_url();
}
add_filter('lostpassword_url', 'custom_lost_password_page');

//Hide course curriculum if course has nothing under curriculum
add_action('wplms_after_course_description','ng_hide_empty_curriculum', 1);
function ng_hide_empty_curriculum(){  
    $course_curriculum = bp_course_get_full_course_curriculum( get_the_ID() );
	if(empty($course_curriculum)){
		remove_action('wplms_after_course_description',array( WPLMS_tips::init(),'course_curriculum_below_description'));  
        remove_action( 'wplms_after_course_description', array(  WPLMS_Actions::init(), 'course_curriculum_below_description_wplms_course_tabs' ) );
	}
}

//Add feature in course setting to change Take this course label
add_filter('wplms_course_metabox','wplms_take_this_course_label');
function wplms_take_this_course_label($fields){
    $fields[]=array( // Text Input
                'label' => 'Set Take this course label', // <label>
                'desc'  => 'Set label',
                'id'    => 'vibe_take_this_course', // field id and name
                'type'  => 'text', // type of field
                'std' => ''
                );
    return $fields;
}

//Custom label for take this course button
add_filter('wplms_take_this_course_button_label','wplms_course_take_course_button',10,2);
function wplms_course_take_course_button($label,$course_id){
    $new_label = get_post_meta($course_id,'vibe_take_this_course',true);
    if(isset($new_label) && $new_label)
        $label = $new_label;
    return $label;
}

//Add scripts to website, Localise script
function ng_theme_name_scripts() {
    wp_enqueue_script( 'nextgates-script', get_stylesheet_directory_uri() . '/assets/js/custom.js', array(), '1.0.0', true );
	$nextgates = array(
		'alert_title' 	=> __( 'Input required!', 'nextgates' ),
		'alert_content'	=> __( 'Please select one option from dropdown', 'nextgates' ),
	);
	wp_localize_script( 'nextgates-script', 'nextgates', $nextgates );
}
add_action( 'wp_enqueue_scripts', 'ng_theme_name_scripts' );

//Add a Product List to WooCommerce’s Order Columns
add_filter('manage_edit-shop_order_columns', 'misha_order_items_column' );
function misha_order_items_column( $order_columns ) {
    $order_columns['order_products'] = "Purchased products";
    return $order_columns;
}
 
//Custom column under orders admin view
add_action( 'manage_shop_order_posts_custom_column' , 'misha_order_items_column_cnt' );
function misha_order_items_column_cnt( $colname ) {
	global $the_order; // the global order object
 
 	if( $colname == 'order_products' ) {
 
		// get items from the order global object
		$order_items = $the_order->get_items();
 
		if ( !is_wp_error( $order_items ) ) {
			foreach( $order_items as $order_item ) {
 
 				echo $order_item['quantity'] .' × <a href="' . admin_url('post.php?post=' . $order_item['product_id'] . '&action=edit' ) . '">'. $order_item['name'] .'</a><br />';
				// you can also use $order_item->variation_id parameter
				// by the way, $order_item['name'] will display variation name too 
			}
		} 
	}
}

//Force HTML content type format for wp_mail
add_action( 'init', 'my_prefix_bp_wp_mail_html_filters' );
function my_prefix_bp_wp_mail_html_filters() {
    add_filter( 'bp_email_use_wp_mail', function( $bool ) { 
      return false;
    }, 10, 1 );
}

//Hide "check quiz results"
add_action('bp_after_course_results',function(){
  ?>
  <script>
  jQuery(document).ready(function($){
    var check_results = $('body').find('.quiz_results');
    if(typeof check_results != 'undefined'){
      $('.quiz_results>li>a').attr('href','#');
    }
  });
  </script>
  <?php
});

//Change COD payment method order status from processing to on-hold
add_action('woocommerce_thankyou_cod', 'action_woocommerce_thankyou_cod', 10, 1);
function action_woocommerce_thankyou_cod($order_id)
{
	$order = wc_get_order($order_id);
	$order->update_status('on-hold');
}

//Different sidebar on course -category page 
add_filter('wplms_sidebar','wplms_course_cat_custom_sidebar');
  
function wplms_course_cat_custom_sidebar($sidebar){
	if(is_tax('course-cat')){
		$sidebar = 'Course Category Sidebar';
	} 
	return $sidebar;
}

//ALLOW SPAN TAG IN WORDPRESS EDITOR
function override_mce_options($initArray) 
{
  $opts = '*[*]';
  $initArray['valid_elements'] = $opts;
  $initArray['extended_valid_elements'] = $opts;
  return $initArray;
 }
 add_filter('tiny_mce_before_init', 'override_mce_options'); 

//Update CSS within in Admin
function admin_style() {
  wp_enqueue_style('admin-styles', get_stylesheet_directory_uri().'/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_style');

//Compatibility - Currency Switcher plugins
function woocs_fixed_raw_woocommerce_price_method($tmp_val, $product_data, $price){
    remove_filter('woocs_fixed_raw_woocommerce_price', 'woocs_fixed_raw_woocommerce_price_method', 10, 3);
    global $flycart_woo_discount_rules;
    if(!empty($flycart_woo_discount_rules)){
        global $product;
        if(empty($product)){
            $discount_price = $flycart_woo_discount_rules->pricingRules->getDiscountPriceOfProduct($product_data);
            if($discount_price !== null) $tmp_val = $discount_price;
        }
    }
    add_filter('woocs_fixed_raw_woocommerce_price', 'woocs_fixed_raw_woocommerce_price_method', 10, 3);

    return $tmp_val;
}
add_filter('woocs_fixed_raw_woocommerce_price', 'woocs_fixed_raw_woocommerce_price_method', 10, 3);
//The bellow snippet should be used only if the discount value is not correct in cart even after the above snippet is added.(From v1.7.13)
add_filter('woo_discount_rules_woocs_convert_price_based_on_currency', function($convert){
	global $WOOCS;
	if(isset($WOOCS)){
		if (isset($WOOCS->default_currency) && isset($WOOCS->current_currency)){
			if($WOOCS->default_currency != $WOOCS->current_currency){
				$convert = true;
			}
		}
	}
	return $convert;
}, 10);

//Remove default popup login, because I'm using lrm login popup 
add_filter('wplms_single_course_content_end','ng_open_popup_for_non_logged_users', 1);
function ng_open_popup_for_non_logged_users(){
	add_action('wp_footer',function(){
	if(is_user_logged_in()){
		return;
	}
	?>
	<script>
		jQuery(document).ready(function($){
			$('.course_button').addClass('lrm-login');
		});
	</script>
	<?php

	}, 100);
}

//Tweak on refirect at my account page to help adding the shop into buddypress menu
add_action('template_redirect', 'ng_before_member_profile', 1);
function ng_before_member_profile(){
	$url = 'https://' . $_SERVER['SERVER_NAME'] .$_SERVER['REQUEST_URI'];
	if( !is_user_logged_in() && strpos( strtok( $url, '?' ), 'dashboard') !== false ){
	    wp_redirect( esc_url( add_query_arg( array( 'redirect-to' => $url ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) ) );
	    exit();
	  }
}

//To avoid conflict with buddycommerce, there opton is disabled and only menu added for it.
//https://www.isitwp.com/add-custom-navigation-in-buddypress/
/*add_action( 'bp_member_options_nav', 'ng_add_profile_extra_custom_links' );
function ng_add_profile_extra_custom_links() {
    ?>
    <li><a href="<?php echo bp_loggedin_user_domain() ?>/shop/orders/"><?php  _e('Shop','nextgates') ?></a></li>
	<?php 
}*/
add_action( 'bp_setup_nav', 'ng_buddycommerce_shop_nav_item', 50 );
function ng_buddycommerce_shop_nav_item() {
    global $bp;
    bp_core_new_nav_item(
        array(
            'name'                => __( 'Shop', 'nextgates' ),
            'slug'                => 'shop',
            'position'            => 20,
            //'screen_function'     => 'wps_sample_action_template',
            'default_subnav_slug' => 'orders',
            'parent_url'          => $bp->loggedin_user->domain . $bp->slug . '/',
            'parent_slug'         => $bp->slug
        ) );
}

//Remove fields from WooCommerce checkout page.
add_filter( 'woocommerce_checkout_fields' , 'custom_remove_woo_checkout_fields' );
function custom_remove_woo_checkout_fields( $fields ) {
    // remove billing fields
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_email']);    
    return $fields;
}

//Disable add students to a course for instructors
add_action('wplms_course_admin_members_functions','remove_cross_icon');
function remove_cross_icon(){
	if(!current_user_can('manage_options'))
	{
		?>
		<style>
			.remove_user_course{display:none !important;}
		</style>
		<?php
	}
}

// Remove price after taking course
add_filter('wplms_course_credits',function($credits,$course_id){
    if(!is_user_logged_in())
      return $credits;
    $user_id = get_current_user_id();
    if(function_exists('bp_course_is_member') && bp_course_is_member($course_id,$user_id) && is_singular('course')){
     
      $check_course= bp_course_get_user_course_status($user_id,$course_id);
      if(!empty($check_course)){
        $credits = '';
      }
    }
   
    return $credits;
},9999,2);

