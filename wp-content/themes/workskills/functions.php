<?php

if ( !defined( 'VIBE_URL' ) )
define('VIBE_URL',get_template_directory_uri());

require get_stylesheet_directory(). '/includes/wplms-actions.php';
require get_stylesheet_directory(). '/includes/wplms-functions.php';
require get_stylesheet_directory(). '/includes/wplms-diploma-actions.php';
require get_stylesheet_directory(). '/includes/wplms-diploma-functions.php';

// Disable cart fragments
add_action( 'wp_enqueue_scripts', 'bladewp_disable_woocommerce_cart_fragments', 11 );

function bladewp_disable_woocommerce_cart_fragments() {
    wp_dequeue_script('wc-cart-fragments');
}

// make header search , search on course directory
add_action('init',function(){
  $actions = WPLMS_Actions::init();
  remove_action('wp_footer',array($actions,'search'));
});
add_action('wp_footer','dir_cc_search');
function dir_cc_search(){
  $pages = get_option('bp-pages');
  $course_dir = isset($pages['course'])?$pages['course']:0;
  if(function_exists('icl_object_id')){
    $course_dir = icl_object_id($course_dir, 'page', true);
  }
  ?>
      <div id="searchdiv">
          <form role="search" method="get" id="searchform" action="<?php echo get_permalink($course_dir); ?>">
              <input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Hit enter to search...','vibe'); ?>" />
          </form>
          <span></span>
      </div>
  <?php
}

/* Clean up WordPress head tag? 
 * Alter dns-prefetch links in <head> */

add_filter('wp_resource_hints', function (array $urls, string $relation): array {
    // If the relation is different than dns-prefetch, leave the URLs intact
    if ($relation !== 'dns-prefetch') {
        return $urls;
    }

    // Remove s.w.org entry
    $urls = array_filter($urls, function (string $url): bool {
        return strpos($url, 's.w.org') === false;
    });

    // List of domains to prefetch:
    $dnsPrefetchUrls = [];
    return array_merge($urls, $dnsPrefetchUrls);
}, 10, 2);

/* Disable RSS feeds by redirecting their URLs to homepage */
foreach (['do_feed_rss2', 'do_feed_rss2_comments'] as $feedAction) {
    add_action($feedAction, function (): void {
        // Redirect permanently to homepage
        wp_redirect(home_url(), 301);
        exit;
    }, 1);
}

/* Remove the feed links from <head> */
remove_action('wp_head', 'feed_links', 2);

/* Display start date on the course block under all courses page */
add_filter('bp_directory_course_item',function(){
  if(function_exists('bp_course_get_start_date')){
    $course_id = get_the_ID();
    $date = bp_course_get_start_date($course_id);
    if( !empty($date) ){
      $date = str_replace('-','/',$date);
      echo '<div class="start_date fa fa-calendar-check-o">'.(date_i18n( get_option( 'date_format' ), strtotime( $date ))).'</div>';
    }
  }
});


/* Disable editor access for Instructors */
add_action('init','remove_edit_course_for_instructor',999);
function remove_edit_course_for_instructor(){
	if(!current_user_can('manage_options') || !is_user_logged_in()){
		if(class_exists('WPLMS_Front_End')){
		$frontend = WPLMS_Front_End::instance();
		remove_action('bp_course_options_nav',array($frontend,'wplms_edit_course_menu_link'));
		}
	}
}


// Restrict course edit for instructors
function restrict_instructors_from_edit_course() {
    // Check if the current user is logged in and has the 'instructor' role
    if (is_user_logged_in() && current_user_can('instructor')) {
        // Get the requested URL
        $requested_url = $_SERVER['REQUEST_URI'];

        // Check if the URL matches the edit-course URL
        if (strpos($requested_url, '/edit-course/') !== false) {
            // Redirect the instructor user to a different page (you can change the URL below)
            wp_redirect(home_url('/access-is-not-allowed/')); // Redirect to the specified page.
            exit;
        }
    }
}
add_action('template_redirect', 'restrict_instructors_from_edit_course');


