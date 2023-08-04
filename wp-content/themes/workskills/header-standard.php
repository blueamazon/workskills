<?php
//Header File
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="facebook-domain-verification" content="fcza1vfz919xorcxya8c3ty2ltklae" />
<?php
	wp_head();
?>
<script type="text/javascript">
// This script is addeed to change currency
	jQuery(function () {
	jQuery('.woocs_currency_link').click(function () {
	    window.location.href = location.protocol + '//' + location.host + location.pathname + '?currency=' + jQuery(this).data('currency');
	 });
	});
</script>
<script type='text/javascript' src='/wp-content/plugins/js_composer/assets/lib/prettyphoto/js/jquery.prettyPhoto.min.js?ver=6.0.5'></script>
<link rel='stylesheet' id='prettyphoto-css'  href='/wp-content/plugins/js_composer/assets/lib/prettyphoto/css/prettyPhoto.min.css?ver=6.0.5' type='text/css' media='all' />
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function() {
    jQuery("a[rel^='prettyPhoto']").prettyPhoto({
	    social_tools:false,
        deeplinking:false,  
	    });
    });
</script>

</head>
<body <?php body_class(); ?>>
<div id="global" class="global">
    <?php
        get_template_part('mobile','sidebar');
    ?> 
    <div class="pusher">
        <?php
            $fix=vibe_get_option('header_fix');
        ?>
        <div id="headertop">
            <div class="<?php echo vibe_get_container(); ?>">
                <div class="row">    
                    <div class="col-md-6 col-sm-6">
                        <div class="headertop_content">
                        	<!--<div class="pickcurrency">
                        		<span><?php _e('Choose a Currency:  ', 'nextgates'); ?></span>
                        		<a href="#" data-currency="USD" class="woocs_currency_link">USD</a>
                                <a href="#" data-currency="EUR" class="woocs_currency_link">EUR</a>
                                <a href="#" data-currency="SEK" class="woocs_currency_link">SEK</a>
                                <a href="#" data-currency="TRY" class="woocs_currency_link">TRY</a>
                        	</div>-->
                            <?php do_action('wcml_currency_switcher', array('format' => '%name% (%symbol%)')); ?>

                            <?php
                                $content = vibe_get_option('headertop_content');
                                echo do_shortcode($content);
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <ul class="topmenu mooc">
                            <li><a id="new_searchicon"><i class="fa fa-search"></i></a></li>
                            <?php
                                if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                            ?>
                            <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><?php bp_loggedin_user_fullname(); ?></a></li>
                            <?php do_action('wplms_header_top_login'); 
                            else:
                            ?>
                            <li><a href="#login" rel="nofollow" class="lrm-login"><?php _e('Login','vibe'); ?></a><a href="#login" rel="nofollow" class="lrm-login smallimg vbplogin"></a></li>
                            <li>
                                <?php
                                $enable_signup = apply_filters('wplms_enable_signup',0);
                                if ( $enable_signup ) : 
                                    $registration_link = apply_filters('wplms_buddypress_registration_link',site_url( BP_REGISTER_SLUG . '/' ));
                                    printf( __( '<a href="%s" class="vbpregister" title="'.__('Create an account','vibe').'">'.__('Sign Up','vibe').'</a> ', 'vibe' ), $registration_link );
                                endif; ?>
                            </li>
                            <?php endif; ?>

                            <?php
                                if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('vibe_check_plugin_installed') && vibe_check_plugin_installed( 'woocommerce/woocommerce.php'))) { global $woocommerce;
                                ?>
                                <li><a class=" vbpcart"><span class="fa fa-shopping-basket"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                                <div class="woocart"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
                                </li>
                                <?php
                                }
                            ?>
                        </ul>
                        <?php

                        echo vibe_socialicons();
                        ?>
                    </div>
                    <?php
                            $style = vibe_get_login_style();
                            if(empty($style)){
                                $style='default_login';
                            }
                        ?>
                    <div id="vibe_bp_login" class="<?php echo $style; ?>">
                        <?php
                            vibe_include_template("login/$style.php");
                         ?>
                   </div>
                </div> 
            </div>
        </div>
        <div class="header_content">
            <div class="<?php echo vibe_get_container(); ?>">
                
            </div>
        </div>
        <header class="standard <?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <div class="<?php echo vibe_get_container(); ?>">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-2">
                        <?php

                            if(is_home()){
                                echo '<h1 id="logo">';
                            }else{
                                echo '<h2 id="logo">';
                            }
                            $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                            if(!empty($url)){
                        ?>
                        
                            <a href="<?php echo vibe_site_url('','logo'); ?>"><img src="<?php  echo $url; ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                        <?php
                            }
                            if(is_home()){
                                echo '</h1>';
                            }else{
                                echo '</h2>';
                            }
                        ?>
                    </div>
                    <div class="col-md-9">
                        <a href="<?php echo vibe_site_url('','logo'); ?>" id="alt_logo"><img src="<?php  echo apply_filters('wplms_logo_url',VIBE_URL.'/images/logo.png','standard_header'); ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                        <div class="navalign"><?php
                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                           
                            wp_nav_menu( $args );

                        /*if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('vibe_check_plugin_installed') && vibe_check_plugin_installed( 'woocommerce/woocommerce.php'))) { global $woocommerce;
                        ?>
                            <li><a class=" vbpcart"><span class="fa fa-shopping-basket"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                            <div class="woocart"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
                            </li>
                        <?php
                        }*/
                           
                        ?></div>
                        <a id="trigger">
                            <span class="lines"></span>
                        </a> 
                    </div>
                </div>
            </div>
        </header>
