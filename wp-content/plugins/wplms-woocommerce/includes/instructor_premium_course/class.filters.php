<?php
/**
 * Initialise WPLMS Instructor Premium Courses
 *
 * @class       Wplms_Premium_Courses_Filters
 * @author      Vibethemes(H.K. Latiyan)
 * @category    Admin
 * @package     WPLMS-Woocommerce/includes/instructor_premium_course
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Premium_Courses_Filters{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new Wplms_Premium_Courses_Filters();
	    return self::$instance;
	}

	private function __construct(){

    	//Check Woocommerce and LMS Setting status.

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) ){

			if(class_exists('WPLMS_tips')){
				$tips = WPLMS_tips::init();
				if(isset($tips) && isset($tips->settings) && isset($tips->settings['enable_instructor_premium_courses'])){
					
					// ADD Instructor Premium Courses TAB IN LMS - SETTINGS
			    	add_filter('wplms_lms_commission_tabs',array($this,'add_premium_courses_tab'));

			    	// Handle Instructor Premium Courses.
			    	add_filter('lms_general_settings',array($this,'handle_premium_courses'));

			    	//Show premium courses form if instructor cannot publish the course
			    	add_filter('instructor_premium_course_form',array($this,'premium_course_form_frontend'));

			    	//Show course count in instructing courses
			    	add_filter('wplms_get_instructor_course_count',array($this,'modify_course_count_according_to_premium_courses'),10,2);

				}
			}
		}

	} // END public function __construct

	function add_premium_courses_tab($settings){

		if(!isset($_GET['tab']) || $_GET['tab'] == 'general' ){

			//Add instructor premium courses tab in lms settings
			$settings['premium_courses'] = _x('Instructor Premium Courses','','wplms-woo');
		}

    	return $settings;

	}

	function handle_premium_courses($settings){

		if(!isset($_GET['sub']) || $_GET['sub'] != 'premium_courses'){
    		return $settings;
		}
    	
    	//Show instructor premium courses setting in the tab
    	$settings = array();

    	echo '<h3>'.__('Instructor Premium Courses','wplms-woo').'</h3>';
		echo '<p>'.__('Check and manage instructor premium courses','wplms-woo').'</p>';

		//Get woocommerce currency symbol
		global  $woocommerce;
   		$currency_symbol = get_woocommerce_currency_symbol();
   		$description = sprintf( __( 'The price is in: %s', 'wplms-woo' ), $currency_symbol );

   		//Save premium course per price in lms settings
   		$this->save_premium_course_price();
   		
   		$lms_settings = get_option('lms_settings');
   		$per_price = '';
   		//Check per_price in lms saved settings
		if(isset($lms_settings['premium_courses'])){
			$per_price = $lms_settings['premium_courses'];
		}
		$save_text = _x('Save Settings','Save Premium Course Per Price','wplms-woo');

   		//Form for premium courses per price 
		?>
		<form method="POST">
			<label style="font-size:14px;font-weight:500;"><?php _e('Price per Course','wplms-woo'); ?></label>
			<input style="margin-left:100px" type="number" name="premium_price" value="<?php echo $per_price; ?>" />
			<span><?php echo $description; ?></span></br></br>
			<input class="button button-primary" type="submit" name="premium_price_submit" value="<?php echo $save_text; ?>">
		</form>
		<?php

		//Show all instructors and their total/published/remaining courses
		$this->instructor_premium_courses();

    	return $settings;

	}

	function save_premium_course_price(){

		if(!isset($_POST['premium_price'])){
			return;
		}

		//Save price settings
		$lms_settings = get_option('lms_settings');
		$lms_settings['premium_courses'] = $_POST['premium_price'];
		update_option('lms_settings',$lms_settings);

	}

	function instructor_premium_courses(){

		$number = 20;// total no of instructors to display
	    $paged = (isset($_GET['p'])?$_GET['p']:1);
	    $s = (isset($_GET['s'])?$_GET['s']:'');
	    if( $paged == 1 ){
	      $offset = 0;  
	    }else{
	       $offset = ( $paged - 1 ) * $number;
	    }

		$args = apply_filters('wplms_allinstructors',array(
                'role' => 'instructor', // instructor
    			'number' => $number,
    			'offset' => $offset,
                'orderby' => 'post_count', 
                'order' => 'DESC' 
    		));

		if(!empty($s)){
			$args['search'] = '*'.$s.'*';
			$args['search_columns']= array('user_login', 'user_email','user_nicename','ID');
		}
		$user_query = new WP_User_Query( $args );
		$instructors = array();

		if ( !empty( $user_query->results ) ) {
		    foreach ( $user_query->results as $user ) {
		        $instructors[] = $user->ID;
		    }
		}

		//Get all instructors IDs
		$instructors = array_unique($instructors);

		if(isset($instructors) && is_array($instructors) && count($instructors)){
			$this->search_instructor_form();
			?>
			<!-- Table for all instructors premium courses -->
            <table class="instructors_premium_courses" style="padding-top:30px;">
            	<tr>
            		<th><?php _e('Instructor Name','wplms-woo'); ?></th>
            		<th><?php _e('Published Courses','wplms-woo'); ?></th>
            		<th><?php _e('Remaining Courses','wplms-woo'); ?></th>
            		<th><?php _e('Total Courses','wplms-woo'); ?></th>
            	</tr>
            <?php
            foreach($instructors as $instructor){

            	//Get premium courses for each instructor
            	$total_courses = get_user_meta($instructor,'instructor_premium_courses',true);
				if(empty($total_courses)){$total_courses = 0;}

            	$published_course = count_user_posts_by_type($instructor,'course');
            	$remaining_courses = $total_courses - $published_course;

            	$save_text = _x('Update','Save button when updating total courses','wplms-woo');
            	$cancel_text = _x('Cancel','Cancel button when updating total courses','wplms-woo');
             ?>
         		<tr class="instructor">
					<td class="instructor_name"><?php echo bp_core_get_userlink($instructor); ?>
					</td>
					<td><a class="published_premium_courses" href="<?php echo get_author_posts_url(  $instructor ).'instructing-courses/'; ?>"><?php echo  $published_course; ?></a>
					</td>
					<td>
						<span class="remaining_premium_courses"><?php echo $remaining_courses; ?></span>
					</td>
					<td>
						<span class="total_premium_courses" data-id="<?php echo $instructor; ?>"><?php echo $total_courses; ?></span>
						<span class="edit_total_premium_course button" style="margin-left:10px;margin-top:5px;"><?php _e('Edit','wplms-woo'); ?></span>
						<input type="hidden" name="save_text" class="save_text" value="<?php echo $save_text; ?>">
						<input type="hidden" name="cancel_text" class="cancel_text" value="<?php echo $cancel_text; ?>">
						<?php wp_nonce_field('hkl_security','pc_security'); ?>
					</td>
				</tr>

             <?php
	        }
	        ?>
	        </table>

	        <style>
	        
	        table.instructors_premium_courses,tr.instructor {width:100%;}
	        tr th {text-align:initial;}
	        tr.instructor {line-height:3em;}
	        tr.instructor td a {text-decoration:none;color:#444;}
	        tr.instructor td:not(.instructor_name) {padding-left:50px;}
	        .edit_total_premium_course {padding-left:20px;color:#1c20d2;}

	        .pagination {padding:20px 0;font-size:11px;line-height:13px;}
			.pagination .page-numbers {
				display:block;
				float:left;
				margin: 2px 2px 2px 0;
				padding:6px 9px 5px 9px;
				text-decoration:none;
				color:#fff;
				background: #555;
			}
			.pagination .page-numbers:hover{color:#444;background: #fff;}
			.pagination .current{padding:6px 9px 5px 9px;background: #fff;color:#444;}

	        </style>

	        <script>
	        	jQuery(document).ready(function($){

	        		//Click on edit button
	        		$('.edit_total_premium_course').on('click',function(){

	        			var courses = $(this).parent().find('.total_premium_courses');
	        			var total_courses = courses.text();
	        			var save_text = $(this).parent().find('.save_text').val();
	        			var cancel_text = $(this).parent().find('.cancel_text').val();
	        			
	        			courses.hide();
	        			$(this).hide();

	        			$(this).after('<input type="number" class="new_total_courses" name="new_total_courses" value="'+total_courses+'" />&nbsp;&nbsp;<a id="save_new_total_courses" style="margin-top:5px;" class="button button-primary">'+save_text+'</a>&nbsp;&nbsp;<a id="cancel_new_total_courses" style="margin-top:5px;" class="button">'+cancel_text+'</a>');

	        			$('body').trigger('update_total_courses');

	        		});

	        		//Click on update button
	        		$('body').on('update_total_courses',function(){

	        			$('#save_new_total_courses').on('click',function(){

	        				var new_courses = $(this).parent().find('.new_total_courses').val();
	        				var user_id = $(this).parent().find('.total_premium_courses').attr('data-id');

		        			// Ajax Call
				            $.ajax({
								type: "POST",
								url: ajaxurl,
								data: { action: 'save_premium_total_courses',
										courses: new_courses,
										user_id: user_id,
										security: $('#pc_security').val(),
								    },
								cache: false,
								success: function () {
									window.location.reload();
								}
				            });

		        		});

	        			//Click on cancel button
		        		$('#cancel_new_total_courses').on('click',function(){

		        			var $this = $(this).parent();
		        			$this.find('.new_total_courses').hide();
		        			$this.find('#save_new_total_courses').hide();
		        			$(this).hide();
		        			$this.find('.total_premium_courses').show();
		        			$this.find('.edit_total_premium_course').show();

		        		});

	        		});

	        	});
	        </script>

	        <?php
	    }else {
	    	if(!empty($_GET['s'])){
	    		$this->search_instructor_form();
	    	}
			echo '<div id="message"><p>'.__('No Instructors found.','vibe').'</p></div>';
		}

		//Pagination
		$total_user = $user_query->total_users;  
        $total_pages = ceil($total_user/$number);

        $query_string = $_SERVER['QUERY_STRING'];
		$base = admin_url('admin.php').'?'.remove_query_arg('p', $query_string).'%_%';

		echo '<div class="pagination">';
		echo paginate_links(array(  
			'base' => $base,
			'format' => '&p=%#%',
			'current' => $paged,
			'total' => $total_pages,
			'prev_text' => __('&lsaquo; Previous','wplms-woo'),
			'next_text' => __('Next &rsaquo;','wplms-woo'),
		));
		echo '</div>';
	}

	function search_instructor_form(){
		$s = (isset($_GET['s'])?$_GET['s']:'');
		?>
		<div class="search_instructors">
			<form method="get">
				<div class="search_input">
					<input type="search" name="s" value="<?php echo $s; ?>" placeholder="<?php _ex('Search Instructor','instructor premium course search','wplms-woo'); ?>" />
					<?php
					foreach($_GET as $key=>$value){
						if($key != 's'){
							echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';	
						}
						
					}
					?>
				</div>
				<div class="search_submit">
					<input type="submit" value="<?php _ex('Search','instructor premium course','wplms-woo'); ?>" class="button button-primary" />
				</div>
			</form>
		</div>
		<style>
		.search_instructors{margin-top:30px;}
        .search_instructors form {
		    display: grid;
		    grid-template-columns: 1fr 60px;
		    grid-gap: 10px;    width: calc(100% - 30px);
		    align-items: center;
		    justify-content: start;
		}.search_instructors .search_input {
		    background: #fff;
		    border: 1px solid rgba(0,0,0,0.2);
		    border-radius: 2px;
		}.search_instructors .search_input input{border: none;width: 100%;}
		</style>
		<?php
	}
	function premium_course_form_frontend($flag){

		//Check for admin
		if(current_user_can('manage_options')){
			return 1;
		}

		//Get instructor's total/published/remaining courses
		$user_id = get_current_user_id();
		$total_courses = get_user_meta($user_id,'instructor_premium_courses',true);
		$published_course = count_user_posts_by_type($user_id,'course');
    	$remaining_courses = $total_courses - $published_course;
		
		if(empty($total_courses) || $total_courses == 0 || $remaining_courses == 0){

			?>
	        <strong class="heading"><?php _e('Instructor Premium Course Form','wplms-woo' ); ?></strong>
	        <p style="padding: 30px 0 30px 0;">
	        	<?php _e('Please complete our instructor premium course form in order to create courses in our website.','wplms-woo'); ?>
	        </p>
	        <?php
	        //Show premium courses form
	        echo do_shortcode('[wplms_premium_course_form]');

	        return 0;
		}

		return 1;
	}

	function modify_course_count_according_to_premium_courses($count,$user_id){

		//Check for admin
		if(current_user_can('manage_options')){
			return $count;
		}

		//Get instructor's total/published/remaining courses
		$total_courses = get_user_meta($user_id,'instructor_premium_courses',true);
		$published_course = count_user_posts_by_type($user_id,'course');
		$count = $published_course.'/'.$total_courses;

		return $count;
    	
	}
	

} // End of class Wplms_Instructor_Premium_Courses_Filters

add_action('plugins_loaded',function(){Wplms_Premium_Courses_Filters::init();},99);
