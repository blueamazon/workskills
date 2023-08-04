<?php
/*
Plugin Name: WPLMS SELL QUIZ
Plugin URI: http://www.Vibethemes.com
Description: A simple WordPress plugin to sell wplms quizzes
Version: 1.0
Author: H.K.Latiyan
Author URI: http://www.vibethemes.com
License: GPL2
Text Domain: wplms-sq
Domain Path: /languages/
*/

/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS SELL QUIZ program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

WPLMS SELL QUIZ program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with WPLMS SELL QUIZ program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( !defined( 'ABSPATH' ) ) exit; 

include_once 'includes/init.php';

add_action('plugins_loaded','wplms_sell_quiz_translations');
function wplms_sell_quiz_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-sq');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-sq', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-sq', $mofile_global );
    } else {
        load_textdomain( 'wplms-sq', $mofile_local );
    }  
}


