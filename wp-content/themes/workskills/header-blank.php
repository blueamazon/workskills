<?php
//Header File
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
wp_head();
?>
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
    <div class="login_sidebar">
        <div class="login_content lrm-login">
        <?php
            vibe_include_template("login/default_login.php");
         ?>
        </div>
    </div>
    <div class="pusher">
        <header id="blank_header">
        </header>
