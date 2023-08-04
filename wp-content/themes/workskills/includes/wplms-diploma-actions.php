<?php

add_filter( 'wplms_course_metabox', 'ng_diploma_metabox' );
function ng_diploma_metabox( $setttings ){

    $prefix = 'ng_';
	$old_setttings = $setttings;
	$setttings = array();
	
    $course_type = array( // Text Input
        'label'	=> __('Course Type','nextgates'), // <label>
        'desc'	=> __('Select your course type. It will change your fronend look.','nextgates'), // description
        'id'	=> $prefix.'course_type', // field id and name
        'type'	=> 'yesno', // type of field
        'options' => array(
          array('value' => 'H',
                'label' =>__('Normal','nextgates')),
          array('value' => 'S',
                'label' =>__('Diploma','nextgates')),
           ),
        'std'   => 'H'
	);

	$diploma_curriculum = array( // Text Input
		'label'		=> __('Diploma Curriculum','nextgates'), // <label>
		'desc'		=> __('Set Diploma Course Curriculum, prepare courses before setting up curriculum','nextgates'), // description
		'id'		=> $prefix.'diploma_curriculum', // field id and name
		'post_type1'=> 'course',
		'type'		=> 'curriculum_diploma' // type of field
	);
	
	foreach( $old_setttings as $k => $v ){
		
		if( $k == 'vibe_course_curriculum' ){			
			$setttings[ $prefix.'course_type' ] 		= $course_type;
			$setttings[ $k ] 							= $v;
			$setttings[ $prefix.'diploma_curriculum' ] 	= $diploma_curriculum;
		}
		else
			$setttings[ $k ] = $v;
			
	}

	return $setttings;

}

add_filter('custom_meta_box_type','ng_custom_meta_box_type',10,5);

function ng_custom_meta_box_type( $type,$meta,$id,$desc,$post_type ){
	
	if( $type == 'curriculum_diploma' ){
		
		$post_type1 = 'course';
		
		echo '<a class="meta_box_add_section button-primary button-large" href="#">'.__('Add Section','vibe-customtypes').'</a>
				<a class="meta_box_add_posttype1 button-primary button-large" href="#">Add '.$post_type1.'</a>
				
				<ul id="' . $id . '-repeatable" class="meta_box_repeatable">';
		$i = 0;
		if ( $meta ) {
			foreach( $meta as $row ) {
				echo '<li><span class="sort handle dashicons dashicons-sort"></span>
							 <input type="text" name="' . $id . '[' . $i . ']" id="' . $id . '" class="'.(is_numeric($row)?'small postid':'').'" value="' . esc_attr( $row ) . '" size="30" READONLY /> <a href="'.get_edit_post_link($row).'"><span>'.(is_numeric($row)?get_the_title($row):'').'</span></a>
							<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
				$i++;
			}
		}
		echo '<li class="section hide"><span class="sort handle dashicons dashicons-sort"></span>
					<input type="text" rel-name="' . $id . '[]" id="' . $id . '" value="" size="30" />
					<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
		
		echo '<li class="posttype1 hide"><span class="sort handle dashicons dashicons-sort"></span>
				<select rel-name="' . $id . '[]"  data-id="'.$post->ID.'" class="" data-cpt="'. $post_type1.'" data-placeholder="'.sprintf(__('Select a %s','vibe-customtypes'),$post_type1).'">
				</select>';
		echo '<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
			
		echo '</ul>
			<span class="description">' . $desc . '</span>';
	}
	return $type;
}


add_action('wplms_after_course_description','ng_course_curriculum_below_description_wplms_course_tabs', 1);
function ng_course_curriculum_below_description_wplms_course_tabs(){
  
    if( is_diploma( get_the_ID() ) ){
		if(class_exists('WPLMS_tips'))
			remove_action('wplms_after_course_description',array( WPLMS_tips::init(),'course_curriculum_below_description'));  
        remove_action( 'wplms_after_course_description', array(  WPLMS_Actions::init(), 'course_curriculum_below_description_wplms_course_tabs' ) );
        $class='';
        
        if(class_exists('Wplms_tips')){
            $tips = Wplms_tips::init();
            if(isset($tips->settings['curriculum_accordion']))
            $class="accordion";
        }
        ?>

        <div id="course-curriculum">
            <div class="course_curriculum <?php echo $class; ?>">
                <?php
                    include( get_stylesheet_directory() . '/course/single/curriculum-diploma.php' );
                ?>
            </div>
        </div>
        <?php
	}
}

//Script for course type button admin backend to hide and show curriculum of perticular course type
add_action( 'admin_print_scripts', 'ng_load_scripts', 100 );
function ng_load_scripts( ) {
	
	global $pagenow, $typenow, $wpdb, $post;					
			
	if ( ( 'post.php' == $pagenow  && $post->post_type == 'course'  )
		|| ( ('post-new.php' == $pagenow ) && isset($_GET['post_type']) && $_GET['post_type'] == 'course'  ) ){
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				if($( "#ng_course_type option:selected" ).text() == "Diploma"){
						 $("#vibe_course_curriculum-repeatable").parent().parent().hide();
						$("#ng_diploma_curriculum-repeatable").parent().parent().show();
				}
				else if($( "#ng_course_type option:selected" ).text() == "Normal"){
						 $("#vibe_course_curriculum-repeatable").parent().parent().show();
						$("#ng_diploma_curriculum-repeatable").parent().parent().hide();
				}
				
				$("#ng_course_type").prev().click(function(){
					if ($("#ng_course_type").prev().hasClass("select_button yesno enable")) {
							$("#vibe_course_curriculum-repeatable").parent().parent().hide();
							$("#ng_diploma_curriculum-repeatable").parent().parent().show();
					}
					else if ($("#ng_course_type").prev().hasClass("select_button yesno")){
							$("#vibe_course_curriculum-repeatable").parent().parent().show();
							$("#ng_diploma_curriculum-repeatable").parent().parent().hide();
					}
				});
			});
		</script>
	<?php
	}
}

// Progress bar for courses in course 
add_action( 'after_diploma_curriculum', 'ng_diploma_progress_update', 10, 3 );
function ng_diploma_progress_update( $course_id, $user_id = NULL, $course_curriculum = array() ){
	
	if( !is_user_logged_in() )
		return ;
	
	//User is logged in proced to next
	$coursetaken = bp_course_get_user_expiry_time( $user_id, $course_id );
	$auto_subscribe = 0; 

	if(vibe_validate($free_course) && is_user_logged_in() && (!isset($coursetaken) || !is_numeric($coursetaken))){ 
		$auto_subscribe = 1;
	}
	
	$auto_subscribe = apply_filters('wplms_auto_subscribe',$auto_subscribe,$course_id);
	if($auto_subscribe){
		$t = bp_course_add_user_to_course($user_id,$course_id);

		if($t){
			$new_duration = apply_filters('wplms_free_course_check',$t);
			$coursetaken = $new_duration;
		}
	}
	
	if( empty( $coursetaken ) || $coursetaken < time() )
		return ;
		
	//course is active proceed to next
	
	if( empty( $course_curriculum ) )
		 $course_curriculum = ng_course_get_diploma_course_curriculum( $course_id, $user_id ); 
		
	$progress = $units = 0;
	// NEW COURSE STATUSES in comment
	$course_status = array(
							's1' => 0, // 1 : START COURSE
							's2' => 0, // 2 : CONTINUE COURSE
							's3' => 0, // 3 : FINISH COURSE : COURSE UNDER EVALUATION
							's4' => 0, // 4 : COURSE EVALUATED
						);
	$cStatus = bp_course_get_user_course_status( $user_id, $course_id ) ;
	
	if( !empty($course_curriculum) && $cStatus < 4 ){
		foreach( $course_curriculum as $lesson ){
			if( $lesson['type'] == 'course') {
				if( $lesson['status'] == 4 && $lesson['progress'] >= 100 )
					$progress++;
				$units ++;
				
				if( $lesson['status'] == '' )
					$lesson['status'] = 1;
					
				$course_status[ 's'.$lesson['status'] ] ++;
			}
		}
		
		//Calculate dimploma course status
		if( $course_status[ 's1']  == $units ) 
			$status = 0;
		else if( $course_status[ 's1'] || $course_status[ 's2'] || $course_status[ 's3'] ) 
			$status = 1; //if any course in progress or under evaluation dimploma is under progress
		else if( $course_status[ 's4'] == $units ) 
			$status = 2;  //all courses are comeplete by student then diploma status update to under evaluation
		//Update dimploma course status
		if( $status != ( $cStatus - 1 ) ){
			bp_course_update_user_course_status( $user_id, $course_id, $status );
			if( $status == 2 )
				do_action('wplms_submit_course',$course_id,$user_id); //course submit activity
		}
		
		//Calculate dimploma course progress
		if( $units && $progress > 0 )
			$progress = ( $progress /$units ) * 100;			
		//Update dimploma course progress
		if( $progress != bp_course_get_user_progress( $user_id, $course_id ) )
			bp_course_update_user_progress($user_id,$course_id,$progress);
	}
}


//when admin mark 
add_action( 'wplms_bulk_action', 'ng_change_course_status', 10, 3 );
function ng_change_course_status( $action, $course_id, $members ){
	if( $action == 'change_course_status' && $_POST['status_action'] == 'finish_course' && bp_course_get_post_type($course_id) == 'course' && !is_diploma( $course_id ) ){
		
		foreach( $members as $mkey => $user_id )
			if( is_numeric($user_id) )
				if( 100 != bp_course_get_user_progress( $user_id, $course_id ) )
					bp_course_update_user_progress($user_id,$course_id, 100);
		
	}
}

add_action('wplms_evaluate_course', 'ng_complete_course_progress', 10, 3);
function ng_complete_course_progress( $course_id,$marks,$user_id ){
	 if( bp_course_get_post_type($course_id) == 'course' && !is_diploma( $course_id )){
	
		if( is_numeric($user_id) )
			if( 100 != bp_course_get_user_progress( $user_id, $course_id ) )
				bp_course_update_user_progress($user_id,$course_id, 100);
		
	}
}

//Change label for diploma
//buy
add_filter( 'wplms_take_this_course_button_label', 'ng_take_this_course_button_label', 11, 2 );
function ng_take_this_course_button_label( $label, $course_id ){
	if( is_diploma( $course_id ) )
		$label = str_replace( array( 'Course', 'COURSE' ), array(  __('Diploma','nextgates'), __('DIPLOMA','nextgates' ) ), $label );
	return $label;
}
//Start
add_filter( 'wplms_start_course_button', 'ng_start_course_button', 10, 2 );
function ng_start_course_button( $button, $course_id ){
	if( is_diploma( $course_id ) )
		$button = '<a href="#course-curriculum" class="course_button full button smoothscroll"> '.__('START DIPLOMA','nextgates').'</a>';
	return $button;
}
//continue
add_filter( 'wplms_continue_course_button', 'ng_continue_course_button', 10, 2 );
function ng_continue_course_button( $button, $course_id ){
	if( is_diploma( $course_id ) )
		$button = '<a href="#course-curriculum" class="course_button full button smoothscroll"> '.__('CONTINUE DIPLOMA','nextgates').'</a>';
	return $button;
}
//under evaluation
add_filter( 'wplms_evaluation_course_button', 'ng_evaluation_course_button', 10, 2 );
function ng_evaluation_course_button( $button, $course_id ){
	if( is_diploma( $course_id ) )
		$button = '<a href="#course-curriculum" class="course_button full button smoothscroll"> '.__('DIPLOMA UNDER EVALUATION','nextgates').'</a>';
	return $button;
}
//finished
add_filter( 'finish_course_button_html', 'ng_finish_course_button_html', 10, 4 );
add_filter( 'finish_course_button_access_html', 'ng_finish_course_button_html', 10, 4 );
function ng_finish_course_button_html( $button, $user_id,$course_id,$course_user ){
	if( is_diploma( $course_id ) )
		$button = '<a href="#course-curriculum" class="course_button full button smoothscroll"> '.__('FINISHED DIPLOMA','nextgates').'</a>';
	return $button;
}

//Replace label from course to diploma for dimploma course
add_filter('wplms_course_credits','ng_wplms_course_credits', 50, 2);
function ng_wplms_course_credits( $credits_html, $course_id ){
	if( is_user_logged_in() && is_diploma( $course_id )) {
		$credits_html  = str_replace( 'COURSE', 'DIPLOMA', $credits_html );
	}
	return $credits_html;
}


//Fix for plugin function
if( class_exists('bp_course_filters') ){
	class ng_course_filters extends bp_course_filters{

		public static $instance;
		
		var $filters = 0;
		var $variation_filters = 0;

		public static function init(){
	
			if ( is_null( self::$instance ) )
				self::$instance = new ng_course_filters();
			return self::$instance;
		}
	
		private function __construct(){
			//parent hooks
			if( has_filter( 'wplms_take_this_course_button_label',array(parent::$instance,'apply_course_button_label') ) ){
				$this->filters = 1;
				remove_filter('wplms_take_this_course_button_label',array(parent::$instance,'apply_course_button_label'),10,2);
				remove_filter('wplms_private_course_button_label',array(parent::$instance,'apply_course_button_label'),10,2);
				remove_filter('wplms_course_product_id',array(parent::$instance,'apply_course_button_link'),10,2);
				remove_filter('wplms_private_course_button',array(parent::$instance,'apply_course_button_link'),10,2);
			}
			if( class_exists('Wplms_Woo_Front') ){
				$Wplms_Woo_Front = Wplms_Woo_Front::init();
				if( has_filter( 'wplms_take_course_button_html',array($Wplms_Woo_Front,'woocommerce_variable_form') ) ){
					$this->variation_filters = 1;
					remove_filter('wplms_take_course_button_html',array($Wplms_Woo_Front,'woocommerce_variable_form'),999,2);
					remove_filter('wplms_course_partial_credits',array($Wplms_Woo_Front,'woocommerce_variable_form_in_partial_course'),10,2);
					remove_filter('wplms_expired_course_button',array($Wplms_Woo_Front,'renew_form'),10,2);
					remove_filter('template_redirect',array($Wplms_Woo_Front,'redirect'));
				}
			}
			// Apply for Course button
			add_filter('wplms_take_this_course_button_label',array($this,'apply_course_button_label'),10,2);
			add_filter('wplms_private_course_button_label',array($this,'apply_course_button_label'),10,2);
			add_filter('wplms_course_product_id',array($this,'apply_course_button_link'),10,2);
			add_filter('wplms_private_course_button',array($this,'apply_course_button_link'),10,2);
			add_action('wp_footer', array($this, 'apply_js'));
		}
		
		function remove(){
			
			//Reassign parent hooks
			if( self::$instance->filters ){
				add_filter('wplms_take_this_course_button_label',array(parent::$instance,'apply_course_button_label'),10,2);
				add_filter('wplms_private_course_button_label',array(parent::$instance,'apply_course_button_label'),10,2);
				add_filter('wplms_course_product_id',array(parent::$instance,'apply_course_button_link'),10,2);
				add_filter('wplms_private_course_button',array(parent::$instance,'apply_course_button_link'),10,2);
			}
			if( self::$instance->variation_filters ){
				$Wplms_Woo_Front = Wplms_Woo_Front::init();
				add_filter('wplms_take_course_button_html',array($Wplms_Woo_Front,'woocommerce_variable_form'),999,2);
				add_filter('wplms_course_partial_credits',array($Wplms_Woo_Front,'woocommerce_variable_form_in_partial_course'),10,2);
				add_filter('wplms_expired_course_button',array($Wplms_Woo_Front,'renew_form'),10,2);
				add_filter('template_redirect',array($Wplms_Woo_Front,'redirect'));
			}
			//remove class hooks
			
			remove_filter('wplms_take_this_course_button_label',array(self::$instance,'apply_course_button_label'),10,2);
			remove_filter('wplms_private_course_button_label',array(self::$instance,'apply_course_button_label'),10,2);
			remove_filter('wplms_course_product_id',array(self::$instance,'apply_course_button_link'),10,2);
			remove_filter('wplms_private_course_button',array(self::$instance,'apply_course_button_link'),10,2);
		}
		
		 /* ==== Apply for Course === */
		function apply_course_button_label($label,$course_id){
			$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);
			if(vibe_validate($coming_soon))
				return $label;
				
			if(empty($this->course_button[$course_id])){
				$check = get_post_meta($course_id,'vibe_course_apply',true);
				$this->course_button[$course_id] = $check;
			}
			if(vibe_validate($this->course_button[$course_id])){
				
				$user_id = get_current_user_id();
				if( class_exists( 'WPLMS_Application_DB' ) ){
					global $wafdb;
					$status = $wafdb->get_application_status( $user_id, $course_id );
					if( $status == 1 )
						return $label;
				}
				
				$label = _x('Apply for Course','Apply for Course label for course','vibe');
				$check = get_user_meta($user_id,'apply_course'.$course_id,true);
				if( !empty($check) ){
					$label = _x('Applied for Course','Apply for Course label for course','vibe');
				}
			}
			return $label;
		}
	
		function apply_course_button_link($link,$course_id){
			$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);
			if(vibe_validate($coming_soon))
				return get_permalink($course_id);
				
			if(empty($this->course_button[$course_id])){
				$check = get_post_meta($course_id,'vibe_course_apply',true);
				$this->course_button[$course_id] = $check;
			}
			if(vibe_validate($this->course_button[$course_id])){
				if(!is_user_logged_in()){
					$link = get_permalink($course_id).'?error=login';
				}else{
					$user_id = get_current_user_id();
					if( class_exists( 'WPLMS_Application_DB' ) ){
						global $wafdb;
						$status = $wafdb->get_application_status( $user_id, $course_id );
						if( $status == 1 )
							return get_permalink($course_id);
					}
					$check = get_user_meta($user_id,'apply_course'.$course_id,true);
					$check_apply_form = get_post_meta($course_id,'vibe_wplms_application_forms',true);
					if( vibe_validate($check_apply_form) ){
						$check_apply_content = get_post_meta($course_id,'vibe_wplms_application_forms_editor',true);
						if( !empty($check_apply_content) ){	
							$url = get_permalink($course_id);
						}
					}else if( !empty($check) )
						$url = get_permalink($course_id);
					else
						$url = '#';
					$link = $url.'" class="'.( empty($check) && $url =='#' ? 'apply_course_button' :'' ).' button" '.(  empty($check) ? 'id="apply_course_button'.$course_id.'"' :'' ).' data-id="'.$course_id.'" data-security="'.wp_create_nonce('security'.$course_id).'" data-url="'.get_permalink($course_id);
				}
			}
			if( is_numeric( $link ) )
				return get_permalink($course_id);
			return $link;
		}
		
		function apply_js(){
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				 $('.apply_course_button').on('click',function( e ){
					var $this = $(this);
					var default_html = $this.html();
					$this.html('<i class="fa fa-spinner animated spin"></i>');
					  $.confirm({
						  text: vibe_course_module_strings.confirm_apply,
						  confirm: function() {
							 $.ajax({
									type: "POST",
									url: ajaxurl,
									data: { action: 'apply_for_course',
											security: $this.attr('data-security'),
											course_id:$this.attr('data-id'),
										  },
									cache: false,
									success: function (html) {
										$this.html(html);
										$this.off('click');
										$this.attr( 'href', $this.data('url') );
									}
							});
						  },
						  cancel: function() {
							  $this.html(default_html);
						  },
						  confirmButton: vibe_course_module_strings.confirm,
						  cancelButton: vibe_course_module_strings.cancel
					  });
					  e.preventDefault();
				  });
			  });
			</script>
			<?php
		}
	}
}