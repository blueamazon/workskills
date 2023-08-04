<?php
/**
 * Helper functions used in the plugin
 */


// Add panel sidebar toggle button for mobile in footer
function xpanel_sidebar_actions() {

	$opts_general 			= get_option( 'xp_general' );
	$opts_display 			= get_option( 'xp_display' );
	$opts_hideon			= get_option( 'xp_hideon' );
	$xp_sidebar 			= isset( $opts_general['xp_sidebar'] ) ? $opts_general['xp_sidebar'] : null;
	$xp_create_sb 			= ( isset( $opts_general['xp_create_sb']) && 'on' == $opts_general['xp_create_sb'] ) ? true : false;
	$xp_convert_sb 			= ( isset( $opts_general['xp_convert_sb'] ) && 'on' == $opts_general['xp_convert_sb'] ) ? true : false;
	$xp_btn_text 			= ( isset( $opts_display['xp_button_text']) && '' != $opts_display['xp_button_text'] ) ? $opts_display['xp_button_text'] : esc_attr__( 'Menu', 'xpanel' );
	$xp_btn_style 			= isset( $opts_display['xp_button_style'] ) ? $opts_display['xp_button_style'] : 'bar';
	
	$xp_hide_all_pages		= ( isset( $opts_hideon['xp_hide_all_pages'] ) && 'on' == $opts_hideon['xp_hide_all_pages'] ) ? true : false;
	$xp_hide_pages 			= isset( $opts_hideon['xp_hide_pages'] ) ? explode( ',', $opts_hideon['xp_hide_pages'] ) : array();
	
	$xp_hide_all_posts		= ( isset( $opts_hideon['xp_hide_all_posts'] ) && 'on' == $opts_hideon['xp_hide_all_posts'] ) ? true : false;
	$xp_hide_posts 			= isset( $opts_hideon['xp_hide_posts'] ) ? explode( ',', $opts_hideon['xp_hide_posts'] ) : array();
	
	$xp_hide_all_archives	= ( isset( $opts_hideon['xp_hide_all_archives'] ) && 'on' == $opts_hideon['xp_hide_all_archives'] ) ? true : false;
	$xp_hide_cats 			= isset( $opts_hideon['xp_hide_cats'] ) ? explode( ',', $opts_hideon['xp_hide_cats'] ) : array();
	$xp_show_woo			= ( isset( $opts_hideon['xp_show_woo'] ) && 'on' == $opts_hideon['xp_show_woo'] ) ? true : false;
	
	$show_panel = 'true';
	
	if ( ( is_page() && $xp_hide_all_pages ) || is_page( $xp_hide_pages ) ) {
		$show_panel = false;
	}
	
	if ( ( is_single() && $xp_hide_all_posts ) || is_single( $xp_hide_posts ) ) {
		$show_panel = false;
	}
	
	if ( ( is_archive() && $xp_hide_all_archives ) || is_category( $xp_hide_cats ) ) {
		$show_panel = false;
	}
	
	if ( ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) && $xp_show_woo ) {
		$show_panel = 'true';
	}
	
	if ( ! $xp_convert_sb && 'true' == $show_panel ) {
	?>
		<div class="xpanel-sidebar">
			<?php
				if ( is_active_sidebar( $xp_sidebar ) ) {
					dynamic_sidebar( $xp_sidebar );
				}
				else {
					echo '<p>' . esc_attr__( 'No widgets found in selected sidebar. Please choose appropriate sidebar inside Settings > xPanel Settings, or add widgets inside the selected sidebar.', 'xpanel' ) . '</p>';
				} ?>
		</div><!-- /.xpanel-sidebar -->
	<?php }

	if ( 'icon' != $xp_btn_style && 'true' == $show_panel ) {
	?>
        <div id="sliding-panel-actions">
            <div  class="container clearfix">
                <?php
                if ( 'bar' == $xp_btn_style ) {
                    echo apply_filters( 'xpanel_toggle_button_bar', sprintf( '<a class="panel-toggle" href="#">%s</a>', esc_html($xp_btn_text ) ) );
				}
				else {
					echo apply_filters( 'xpanel_toggle_buttonicon', sprintf( '<a class="panel-toggle" href="#"><span class="screen-reader-text">%s</span></a>', esc_html($xp_btn_text ) ) );
				}
				?>
            </div><!-- /.container -->
        </div><!-- /#sliding-panel-actions -->
    <?php } // If not icon style button
	?>

    <div class="panel-body-mask"></div><!-- /.panel-body-mask -->
	<?php
}

add_action( 'wp_footer', 'xpanel_sidebar_actions' );
?>