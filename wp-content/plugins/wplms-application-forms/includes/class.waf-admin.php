<?php
/**
 *
 * @class       WPLMS_Application_Forms_Admin
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if( !defined( 'BACKEND_URL' ) )
	define( "BACKEND_URL", get_bloginfo('url').'/wp-admin/' ); 

class WPLMS_Application_Forms_Admin{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPLMS_Application_Forms_Admin();
	    return self::$instance;
	}

	private function __construct(){
		
		add_action('admin_menu', array($this,'course_applications_menu'), 10);
		add_action('admin_print_scripts', array($this,'course_applications_form_script'), 101);
		
		
	} // END public function __construct

	/*************END**************/
	function course_applications_menu(){
		add_submenu_page(
	        'lms',
			__('Course applications','wplms-af' ), // page title
			__('Applications','wplms-af' ), // menu title
			'manage_options', // capability
			'course_applications', // menu slug
			array( $this, 'course_applications_display') // callback function
		);
	}

	function course_applications_display(){
		global $filter, $course_id, $filter_array;
		$course_text = $course_id = '';
		
		$filter_array = array(
							"2" 	=> _x('Pending','Application status','wplms-af'),
							"1"		=> _x('Approved','Application status','wplms-af'),
							"3"		=> _x('Rejected','Application status','wplms-af'),
							"4"		=> _x('Removed','Application status','wplms-af'),
							"5"		=> _x('Re-enabled','Application status','wplms-af'),
							"all"	=> _x('All','Application status','wplms-af'),
						);

		if( isset($_GET['filter']) && $_GET['filter'] && isset( $filter_array[ $_GET['filter'] ]) ){
			$filter = esc_attr($_GET['filter']);
		}else{
			$filter = '2';
		}
		
				
		if(isset($_GET['course']) && $_GET['course']){
			$course_id = esc_attr($_GET['course']);
			$course_text = ' from course <a href="'.get_permalink($course_id).'" target="_blank">'.get_the_title($course_id).'</a>';
		}

		if(isset($_GET['s']) && $_GET['s'] ){
			$s = esc_attr($_GET['s']);
		}else{
			$s= '';
		}

		//Create an instance of our package class...
		$WAF_table = new WPLMS_Application_Forms_Table();
		//Fetch, prepare, sort, and filter our data...
		$WAF_table->prepare_items();
		?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php 
						echo sprintf(__("%s applications", 'wplms-af'), $filter_array[ $filter ]).$course_text;
					?></h1>
					<hr class="wp-header-end">
					<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->

					<form id="form-search-filter" method='get' action="<?php $_SERVER['HTTP_REFERER']; ?>">
						<p class="search-box">
							<label class="screen-reader-text" for="application-input">Search entries:</label>

							<input type='search' id="application-input" name='s' size="35" placeholder="Search applicatin by user E-mail ..." value='<?php echo $s; ?>'></label>
							<input type='submit' id="search-submit" class="button" value='Search' >
						</p>
						<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" >
						<input type="hidden" name="filter" value="all" >
					</form>

                    <form id="application-filter" method="get" class="alignleft actions">
                        <input type="hidden" name="page" value="course_applications">
                        <?php $WAF_table->display(); ?>
                    </form>

				<br class="clear">
			</div>            
		<?php
	}

	public function course_applications_form_script() {
		$screen = get_current_screen();
		
		if($screen->id === 'lms_page_course_applications') {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.application.action div a, .delete-link a').on('click',function(e){
						
						var $this = jQuery(this);
						var action = 'reject';
						if($this.hasClass('approve')){
							action = 'approve';
						}
						if($this.hasClass('enable')){
							action = 'enable';
						}
						if($this.hasClass('delete')){
							if ( confirm("<?php _e( "Are you sure, you want to delete this application?", 'wplms-af') ?>") == false) {
								return false;
							}
							action = 'delete';
						}
						$this.addClass('loading');						
						jQuery.ajax({
						   type: "POST",
						   url: ajaxurl,
						   data: { action: 'manage_user_application',
						           act:action,
						           security: $this.parent().attr('data-security'),
						           user_id:$this.parent().attr('data-id'),
						           course_id:$this.parent().attr('data-course'),
						         },
						   cache: false,
						   success: function (html) {
								if( action === 'delete' )
									location.reload();
								else
					       			$this.parent().html('<span class="'+action+'"></sapn>');
						   }
						});
						e.preventDefault();
					});
				});
			</script>
			<style type="text/css">
				.applications div a.approve.loading:before,
				.applications div a.reject.loading:before,
				.applications div a.enable.loading:before,
				.application.action div a.delete.loading:before {
					content:"\f110"
				}
				.approved, .rejected, .removed, .enabled,
				.applications span.reject, .applications span.approve , .applications span.enable  {
					font-family: dashicons; 
					font-size: 32px;
				    display: block;
					margin:15px auto;
				}
				.enabled:before, .applications span.enable:before{
					content: "\f463";
					color: #70c989;
				}
				.approved:before, .applications span.approve:before {
					content: "\f12a";
					color: #70c989;
				}
				.rejected:before, .applications span.reject:before {
					content: "\f153";
					color: #fa7252;
				}
				.removed:before {
					content: "\f460";
				    background-color: #6262f9;
				    border-radius: 50%;
				    color: #fff;
				    font-size: 27px;
				    font-weight: 900;
				}
				.applications div a.reject,
				.applications div a.approve,
				.applications div a.enable,
				.application.action div a.delete {
				    margin-left: 10px;
					margin-top: 20px;
				    font-size: 11px;
				    color: #bbb;
				    cursor: pointer;
				    text-transform: uppercase;
				    text-align: center;
				    display: inline-block;
				}
				.applications div a.reject:before,
				.applications div a.approve:before,
				.applications div a.enable:before ,
				.application.action div a.delete:before {
					display: block;
				    font-size: 24px;
				    color: #bbb;
				    content: "\f10c";
				    line-height: 1;
				    font-family: 'evo_FontAwesome';
				}
				.applications div a.reject:hover {
				    color: #fa7252;
				}
				.applications div a.reject:hover:before {
				    content: "\f05c";
				    color: #fa7252;
				}
				.applications div a.approve:hover {
				    color: #70c989;
				}
				.applications div a.approve:hover:before {
				    content: "\f05d";
				    color: #70c989;
				}				
				.applications div a.enable:hover {
				    color: #70c989;
				}
				.applications div a.enable:hover:before {
				    content: "\f021";
				    color: #70c989;
				}
				.application.action div a.delete:hover {
				    color: #fa7252;
				}
				.application.action div a.delete:hover:before {
				    content: "\f05e";
				    color: #fa7252;
				}
			</style>
			<?php
		}
	}

} // END class WPLMS_Application_Forms_Admin


class WPLMS_Application_Forms_Table extends WP_List_Table {

	var $orderby	= 'id';
	var $order		= 'ASC';
	var $per_page	= '20';
	var $search		= '';
	//var $nonce		= '';

    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(

            'singular'  => 'Application',     //singular name of the listed records
            'plural'    => 'Applications',    //plural name of the listed records
            'ajax'      => false ,    //does this table support ajax?
			//'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
        
        ) );

    }

	//student name, date of submission, date of approval or rejection, application linked course, application files 
    function get_columns(){
        $columns = array(
            'cb'        	=>	'<input type="checkbox" />', //Render a checkbox instead of text
            'student'     	=>  __('Student name', 'wplms-af'),
            'course' 		=>  __('Course', 'wplms-af'),
            'request_date'	=>  __('Date of Submission', 'wplms-af'),
            'judement_date' =>  __('Date of Approval or Rejection', 'wplms-af'),
            'apply_details' =>  __('Application details', 'wplms-af'),
            'action' 		=>  __('Status', 'wplms-af'),
        );
        return $columns;
    }
	
	
	function extra_tablenav( $which = '' ) {
		if($which != 'top')
			return;
			
		global $filter, $course_id, $filter_array;
		?>
        <div id="filter-application" class="alignleft actions">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <?php

                $query = new WP_Query( 
                           array(	'post_type' => 'course',
                                    'posts_per_page' => -1,
                                    'order'=> 'DESC',
                                    'post_status' => 'publish',
									'meta_query' => array(
										array(
											'key'     => 'vibe_wplms_application_forms',
											'value'   => 'S',
											'compare' => '=',
										),
									),
                                )
                        );
                if ($query->have_posts()) {
                    echo '<select name="course" id="course">';
					echo "<option value=>".__( "All Courses", 'wplms-af')."</option>";
                    while ( $query->have_posts() ) {
                        $query->the_post();

                        echo "<option value=".get_the_ID()."".selected( $course_id , get_the_ID()).">".get_the_title(get_the_ID())."</option>";
						
					}
                    echo "</select>";
                }
                wp_reset_postdata();

			?>
            <select name="filter" >
            	<?php 
					foreach( $filter_array as $k => $v ){
						echo '<option value="'.$k.'" '.selected( $filter, $k).'" >'.$v.'</option>';
					}
				?>
            </select>
            <input type="submit" name="" value="<?php _e( "Apply Filter", 'wplms-af') ?>" class="button">
            <!-- Now we can render the completed list table -->
        </div>
        
        <?php
	}
	
	public function process_bulk_action() {

        // security check!
        if ( isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( __( 'Nope! Security check failed!', 'wplms-af') );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'delete':
				if( isset( $_GET['form'] ) && !empty( $_GET['form'] ) ){
					foreach( $_GET['form'] as $id ){
						global $wafdb;
						$form = $wafdb->get_form_details_by_id( $id );
						extract($form);			
						$wafdb->delete_application_form( $user_id, $course_id, $attachments );
					}					
				}
				wp_safe_redirect( wp_get_referer() );
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }
	
	function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
	
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'     			=> array('id',false),  
			'request_date'     	=> array('do_submission',false),
           // 'username'     		=> array('login',false),     //true means it's already sorted
        );
        return $sortable_columns;
    }
	
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="form[]" value="%s" />', $item->id
        );    
    }

    function column_course($item){
		//do_action('wplms_course_application_submission_users', $user->user_id, $value->ID); 
        return sprintf('<a href="%2$s" target="_blank">%1$s <br />%3$s</a> <a href="%4$s" target="_blank">(Edit)</a>', 
						get_the_post_thumbnail($item->course_id, 'thumbnail'),
						get_permalink($item->course_id),
						$item->course_name,
						get_edit_post_link($item->course_id)
					);
    }
	
    function column_student($item) {

		$actions = array(
            'profile' => $item->user_id ? sprintf('<a href="'.BACKEND_URL.'user-edit.php?user_id=%s">'.__( 'Edit Profile', 'wplms-af' ).'</a>', $item->user_id) : '(Guest)',
            'orders'  => sprintf('<a href="'.BACKEND_URL.'/edit.php?post_status=all&post_type=shop_order&_customer_user=%s" target="_blank">'.__( 'View orders', 'wplms-af' ).'</a>', $item->user_id),
            'delete'  => sprintf('<span class="delete-link" data-id="%1s" data-course="%2s" data-security="'.wp_create_nonce('security' . $item->course_id . $item->user_id).'"><a href="#" class="delete">'._x('Delete', 'delete user application for course', 'wplms-af').'</a></span>', $item->user_id, $item->course_id),
				
         );

        //Return the title contents
        return sprintf('<a href="%2$s" target="_blank">%1$s <br />%4$s </a> <strong> %3$s</strong> <br />%5$s',
            /*$1%s*/ get_avatar($item->user_id),
			/*$2%s*/ bp_core_get_user_domain($item->user_id),
            /*$3%s*/ $item->email,
            /*$4%s*/ $item->display_name,
            /*$5%s*/ $this->row_actions($actions));
    }
	
    function column_request_date($item){
		return $item->do_submission;
    }
	
    function column_judement_date($item){
        return $item->do_process != NULL ? $item->do_process : '';
        
    }

    function column_apply_details($item){
		if( !empty($item->application_form) ){
			return '<div class="user_application_form" style="width:400px;margin:auto;">ID : '.$item->id.'<br />'.$item->application_form.'</div>';
		}
        return "";
    }

    function column_action($item){	
		
		if( $item->status == 1 ){
		
			return sprintf('<span class="approved"></span>');
		
		} else if( $item->status == 3 ){
		
			return sprintf('
				<div class="application action">
					<div data-id="'.$item->user_id.'" data-course="'.$item->course_id.'" data-security="'.wp_create_nonce('security' . $item->course_id . $item->user_id).'">
						<span class="rejected"></span>
						<a href="#" class="enable">'._x('Reset', 'Re-enable user application for course', 'wplms-af').'</a>
                		<a href="#" class="delete">'._x('Delete', 'delete user application for course', 'wplms-af').'</a>
					</div>
				</div>');

		} else if( $item->status == 4 ){

			return sprintf('<span class="removed"></span>');

		}  else if( $item->status == 5 ){
		
			return sprintf('<span class="enabled"></span>');

		} else if( $item->status == 2 ){
			
			return '<div class="application action">
				<div data-id="'.$item->user_id.'" data-course="'.$item->course_id.'" data-security="'.wp_create_nonce('security' . $item->course_id . $item->user_id).'">
					<a href="#" class="reject">'._x('Reject', 'reject user application for course', 'vibe').'</a>
                	<a href="#" class="approve">'._x('Approve', 'approve user application for course', 'vibe').'</a>
                	<a href="#" class="delete">'._x('Delete', 'delete user application for course', 'wplms-af').'</a>
               	</div>
            </div>';
											
		}
    }

    function prepare_items( ) {

		global $wpdb, $_wp_column_headers, $filter, $course_id; //This is used only if making any database queries

        $screen = get_current_screen();
		
		$columns = $this->get_columns();

		$_wp_column_headers[$screen->id] = $columns;	

		$hidden = array();

		$sortable = $this->get_sortable_columns();	
		
		$this->nonce = wp_create_nonce() ;
		
		$this->_column_headers = array( $columns, $hidden, $sortable );	
		
		$this->search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		
		$this->orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'id';
		
		$this->order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'DESC';
		
		$this->per_page = 10;
		
		$paged = $this->get_pagenum();
		
		$this->process_bulk_action();
		
		$where = "WHERE $wpdb->applications.id = $wpdb->applications.id ";
				
		if ( $filter != 'all' )
			$where .= " AND $wpdb->applications.status = '".$filter."'";

		
		if ( $course_id != "") {

			$where .= " AND ( p.ID = ".$course_id.")";

		}
		
		if ( $this->search != "") {

			$where .= " AND ( u.user_email LIKE '%".$this->search."%')";

		}

		$offset = ( $paged-1 ) * $this->per_page;

		$query = "SELECT $wpdb->applications.*, u.user_email as email, u.display_name, p.post_title	as course_name	
		FROM $wpdb->applications
		INNER JOIN  $wpdb->posts p
				ON $wpdb->applications.course_id = p.ID
		INNER JOIN  $wpdb->users u
				ON $wpdb->applications.user_id = u.id
				
		".$where ."
		ORDER BY $wpdb->applications.$this->orderby $this->order
		LIMIT $this->per_page OFFSET $offset";
		
        $this->items = $wpdb->get_results($query);

        $total_items = $wpdb->get_var("SELECT count(*) FROM $wpdb->applications
		INNER JOIN  $wpdb->posts p
				ON $wpdb->applications.course_id = p.ID
		INNER JOIN  $wpdb->users u
				ON $wpdb->applications.user_id = u.id
		".$where );

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items / $this->per_page )   //WE have to calculate the total number of pages
        ) );
    }

	public function no_items() {
		global $filter;
		switch ($filter) {

			case '1':
					_e( "Didn't found any approved application.", 'wplms-af' );
				break;
			
			case '3':
					_e( "Didn't found any rejected application.", 'wplms-af' );		
				break;
			
			case '4':
					_e( "Didn't found any removed application.", 'wplms-af' );
				break;
			
			case '5':
					_e( "Didn't found any re-enabled application.", 'wplms-af' );
				break;	
			
			case 'all':
					_e( "Didn't found any application. Seems like still no one applied.", 'wplms-af' );
				break;	
			
			default:
					_e( "Didn't found any pending application.", 'wplms-af' );
				break;
		}
    }
}