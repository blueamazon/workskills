<?php
/*
Plugin Name: WPLMS H5p Plugin
Plugin URI: http://www.vibethemes.com
Description: Plugin to integrate wplms and H5p .Requires h5p plugin .
Version: 2.3
Author: VibeThemes,alexhal
Author URI: http://www.vibethemes.com
License: GPL2
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

wplms_h5p program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

wplms_h5p program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with wplms_h5p program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('WPLMS_H5P_VERSION','2.3');

include_once 'classes/wplms.h5p.class.php';
include_once 'vibebp/class.init.php';
include_once 'vibebp/class.filters.php';
include_once 'vibebp/class.api.php';
define( 'WPLMS_H5P_API_NAMESPACE', 'wplmsh5p/v1'  );
if(class_exists('Wplms_H5p_Class'))
{ 
    // Installation and uninstallation hooks
    

    // instantiate the plugin class
    
     $active_plugins =get_option( 'active_plugins' );
     if ( (( (in_array( 'vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-customtypes/vibe-customtypes.php')) &&

               (in_array( 'vibe-course-module/loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-course-module/loader.php')) )
      || (in_array( 'wplms_plugin/loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'wplms_plugin/loader.php'))
    )

               &&

                   (in_array( 'h5p/h5p.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'h5p/h5p.php'))

          ) {

       
           $wplms_h5p = Wplms_H5p_Class::init();
       }
   
     
}
add_action('plugins_loaded','wplms_h5p_translations');
function wplms_h5p_translations(){
  $locale = apply_filters("plugin_locale", get_locale(), 'wplms-h5p');
  $lang_dir = dirname( __FILE__ ) . '/languages/';
  $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-h5p', $locale );
  $mofile_local  = $lang_dir . $mofile;
  $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

  if ( file_exists( $mofile_global ) ) {
      load_textdomain( 'wplms-h5p', $mofile_global );
  } else {
      load_textdomain( 'wplms-h5p', $mofile_local );
  }  
}