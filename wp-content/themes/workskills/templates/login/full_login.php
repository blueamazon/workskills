<?php

if ( is_user_logged_in() ) :
	do_action( 'bp_before_sidebar_me' ); ?>
	<div id="sidebar-me">
		<div id="bpavatar">
			<?php bp_loggedin_user_avatar( 'type=full' ); 
			$show_view_profile = apply_filters('wplms_sidebarme_show_view_profile',1);
			?>
		</div>
		<ul>
			<li id="username"><a href="<?php bp_loggedin_user_link(); ?>"><?php bp_loggedin_user_fullname(); ?></a></li>
			<?php do_action('wplms_header_top_login'); ?>
			<?php if($show_view_profile){?>
				<li><a href="<?php echo bp_loggedin_user_domain() . BP_XPROFILE_SLUG ?>/" title="<?php _e('View profile','vibe'); ?>"><?php _e('View profile','vibe'); ?></a></li>
			<?php } ?>
			<li id="vbplogout"><a href="<?php echo wp_logout_url( get_permalink() ); ?>" id="destroy-sessions" rel="nofollow" class="logout" title="<?php _e( 'Log Out','vibe' ); ?>"><i class="icon-close-off-2"></i> <?php _e('LOGOUT','vibe'); ?></a></li>
			<?php 

			if(vibe_get_option('wp_admin_access') > 1){
			?>
			<li id="admin_panel_icon"><?php if (current_user_can("edit_posts"))
		       echo '<a href="'.vibe_site_url() .'wp-admin/" title="'.__('Access admin panel','vibe').'"><i class="icon-settings-1"></i></a>'; ?>
		  	</li>
		  	<?php
		  	}
		  	?>
		</ul>	
		<ul>
<?php
$loggedin_menu = array(
  'courses'=>array(
              'icon' => 'icon-book-open-1',
              'label' => __('Courses','vibe'),
              'link' => bp_loggedin_user_domain().BP_COURSE_SLUG
              ),
  'stats'=>array(
              'icon' => 'icon-analytics-chart-graph',
              'label' => __('Stats','vibe'),
              'link' => bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_STATS_SLUG
              )
  );
if ( bp_is_active( 'messages' ) ){
  $loggedin_menu['messages']=array(
              'icon' => 'icon-letter-mail-1',
              'label' => __('Inbox','vibe').(messages_get_unread_count()?' <span>' . messages_get_unread_count() . '</span>':''),
              'link' => bp_loggedin_user_domain().BP_MESSAGES_SLUG
              );
}
if ( bp_is_active( 'notifications' ) ){  
  $n=vbp_current_user_notification_count();
  $loggedin_menu['notifications']=array(
              'icon' => 'icon-exclamation',
              'label' => __('Notifications','vibe').(($n)?' <span>'.$n.'</span>':''),
              'link' => bp_loggedin_user_domain().BP_NOTIFICATIONS_SLUG
              );
}
if ( bp_is_active( 'groups' ) ){
  $loggedin_menu['groups']=array(
              'icon' => 'icon-myspace-alt',
              'label' => __('Groups','vibe'),
              'link' => bp_loggedin_user_domain().BP_GROUPS_SLUG 
              );
}

$loggedin_menu['settings']=array(
              'icon' => 'icon-settings',
              'label' => __('Settings','vibe'),
              'link' => bp_loggedin_user_domain().BP_SETTINGS_SLUG
              );
$loggedin_menu = apply_filters('wplms_logged_in_top_menu',$loggedin_menu);
foreach($loggedin_menu as $item){
  echo '<li><a href="'.$item['link'].'"><i class="'.$item['icon'].'"></i>'.$item['label'].'</a></li>';
}
?>
		</ul>
	
	<?php
	do_action( 'bp_sidebar_me' ); ?>
	</div>
	<?php do_action( 'bp_after_sidebar_me' );

/***** If the user is not logged in, show the log form and account creation link *****/

else :
	if(!isset($user_login))$user_login='';
	do_action( 'bp_before_sidebar_login_form' ); ?>
	
	
	<?php
endif;
?>
