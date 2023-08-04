<?php
/**
 * Initialise WPLMS Bbb
 *
 * @class       Wplms_H5p_Actions
 * @author      VibeThemes
 * @category    Admin
 * @package     WPLMS-Bbb/classes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_H5p_Actions{

	public static $instance;
	
	public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_H5p_Actions();
        return self::$instance;
    }

	private function __construct(){
        add_action('wp_enqueue_scripts',array($this,'enqueue_script'));
        add_action('wp_enqueue_scripts',array($this,'enqueue_script'));
        add_filter('vibebp_component_icon',array($this,'set_icon'),10,2);
        add_filter('wplms_course_creation_tabs',array($this,'h5p_unit'));
        //add_filter('the_content',)
    }
    
    function set_icon($icon,$component_name){

        if($component_name == 'h5p'){
            return '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64" height="64" x="0px" y="0px" viewBox="0 0 345 150" enable-background="new 0 0 345 150" xml:space="preserve"><g><path d="M325.7,14.7C317.6,6.9,305.3,3,289,3h-43.5H234v31h-66l-5.4,22.2c4.5-2.1,10.9-4.2,15.3-5.3c4.4-1.1,8.8-0.9,13.1-0.9c14.6,0,26.5,4.5,35.6,13.3c9.1,8.8,13.6,20,13.6,33.4c0,9.4-2.3,18.5-7,27.2c-4.7,8.7-11.3,15.4-19.9,20c-3.1,1.6-6.5,3.1-10.2,4.1h42.4H259V95h25c18.2,0,31.7-4.2,40.6-12.5c8.9-8.3,13.3-19.9,13.3-34.6C337.9,33.6,333.8,22.5,325.7,14.7z M288.7,60.6c-3.5,3-9.6,4.4-18.3,4.4H259V33h13.2c8.4,0,14.2,1.5,17.2,4.7c3.1,3.2,4.6,6.9,4.6,11.5C294,53.9,292.2,57.6,288.7,60.6z"/><path d="M176.5,76.3c-7.9,0-14.7,4.6-18,11.2L119,81.9L136.8,3h-23.6H101v62H51V3H7v145h44V95h50v53h12.2h42c-6.7-2-12.5-4.6-17.2-8.1c-4.8-3.6-8.7-7.7-11.7-12.3c-3-4.6-5.3-9.7-7.3-16.5l39.6-5.7c3.3,6.6,10.1,11.1,17.9,11.1c11.1,0,20.1-9,20.1-20.1S187.5,76.3,176.5,76.3z"/></g></svg>';
        }
        return $icon;
    }
    
	function enqueue_script(){
        $blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }
        if(!class_exists('WPLMS_tips'))
            return;
        
        $tips = WPLMS_tips::init();
        $quiz_passing_score=0;
        if(isset($tips) && isset($tips->settings['quiz_passing_score'])){
              
              
              $quiz_passing_score = $tips->settings['quiz_passing_score'];
              if(!empty($quiz_passing_score)){
                $quiz_passing_score = true;
              }else{
                $quiz_passing_score = false;
              }
        }
            
        $zoom=apply_filters('wplms_h5p_data_script_args',array(
            'api'=>array(
                'url'=>get_rest_url($blog_id,WPLMS_H5P_API_NAMESPACE),
            ),
            'quiz_passing_score' => $quiz_passing_score,
            'ajax_url' => admin_url('admin-ajax.php'),//?action=h5p_embed&id=1
            'show_always' =>false,
            'translations'=>array(
               
            )
        ));
        if(function_exists('bp_is_user') && bp_is_user() || apply_filters('vibebp_enqueue_profile_script',false) || is_singular('quiz')){
            wp_enqueue_script('create-h5p',plugins_url('assets/js/wplms_h5p.js',__FILE__),array('wp-element','wp-data'),WPLMS_H5P_VERSION);
            wp_localize_script('create-h5p','wplms_h5p_data',$zoom);
            wp_enqueue_style('create-h5p',plugins_url('assets/css/wplms_h5p.css',__FILE__),array(),WPLMS_H5P_VERSION);

        }

    }

    function h5p_unit($tabs){
        $tabs['course_curriculum']['fields'][0]['curriculum_elements'][1]['types'][]= array(
            'id'=>'h5p',
            'icon'=>'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64" height="64" x="0px" y="0px" viewBox="0 0 345 150" enable-background="new 0 0 345 150" xml:space="preserve"><g><path d="M325.7,14.7C317.6,6.9,305.3,3,289,3h-43.5H234v31h-66l-5.4,22.2c4.5-2.1,10.9-4.2,15.3-5.3c4.4-1.1,8.8-0.9,13.1-0.9c14.6,0,26.5,4.5,35.6,13.3c9.1,8.8,13.6,20,13.6,33.4c0,9.4-2.3,18.5-7,27.2c-4.7,8.7-11.3,15.4-19.9,20c-3.1,1.6-6.5,3.1-10.2,4.1h42.4H259V95h25c18.2,0,31.7-4.2,40.6-12.5c8.9-8.3,13.3-19.9,13.3-34.6C337.9,33.6,333.8,22.5,325.7,14.7z M288.7,60.6c-3.5,3-9.6,4.4-18.3,4.4H259V33h13.2c8.4,0,14.2,1.5,17.2,4.7c3.1,3.2,4.6,6.9,4.6,11.5C294,53.9,292.2,57.6,288.7,60.6z"/><path d="M176.5,76.3c-7.9,0-14.7,4.6-18,11.2L119,81.9L136.8,3h-23.6H101v62H51V3H7v145h44V95h50v53h12.2h42c-6.7-2-12.5-4.6-17.2-8.1c-4.8-3.6-8.7-7.7-11.7-12.3c-3-4.6-5.3-9.7-7.3-16.5l39.6-5.7c3.3,6.6,10.1,11.1,17.9,11.1c11.1,0,20.1-9,20.1-20.1S187.5,76.3,176.5,76.3z"/></g></svg>',
            'label'=>__('H5P','wplms-h5p'),
            'fields'=>array(
                array(
                    'label'=> __('Unit title','wplms-h5p' ),
                    'type'=> 'title',
                    'id' => 'post_title',
                    'from'=>'post',
                    'value_type'=>'single',
                    'style'=>'full',
                    'default'=> __('Unit Name','wplms-h5p' ),
                    'desc'=> __('This is the title of the unit which is displayed on top of every unit','wplms-h5p' )
                    ),
                array(
                    'label'=> __('Unit Tag','wplms-h5p' ),
                    'type'=> 'taxonomy',
                    'taxonomy'=> 'module-tag',
                    'from'=>'taxonomy',
                    'value_type'=>'single',
                    'style'=>'assign_cat',
                    'id' => 'module-tag',
                    'default'=> __('Select a tag','wplms-h5p' ),
                ),
                array(
                    'label'=> __('Add H5P element','wplms-h5p' ),
                    'type'=> 'selectcpt',
                    'post_type'=>'h5p',
                    'value_type'=>'single',
                    'desc'=>__('Select a H5P module.','wplms-h5p' ),
                    'style'=>'small_icon',
                    'from'=>'meta',
                    'is_child'=>true,
                    'id' => 'wplms_h5p_content',
                    'default'=> '',
                ),
                array(
                    'label'=> __('What is the unit about','wplms-h5p' ),
                    'type'=> 'editor',
                    'style'=>'',
                    'value_type'=>'single',
                    'id' => 'post_content',
                    'from'=>'post',
                    'extras' => '',
                    'default'=> __('Enter description about the unit.','wplms-h5p' ),
                ),
                array(
                    'label'=> __('Unit duration','wplms-h5p' ),
                    'type'=> 'duration',
                    'style'=>'course_duration_stick_left',
                    'id' => 'vibe_duration',
                    'from'=> 'meta',
                    'default'=> array('value'=>9999,'parameter'=>86400),
                    'from'=>'meta',
                ),
                array( 
                    'label' => __('Free Unit','wplms-h5p'),
                    'desc'  => __('Set Free unit, viewable to all','wplms-h5p'), 
                    'id'    => 'vibe_free',
                    'type'  => 'switch',
                    'default'   => 'H',
                    'from'=>'meta',
                ),
                array(
                    'label' => __('Attachments','wplms-h5p'),
                    'desc'  => __('Display these attachments below units to be downloaded by students','wplms-h5p'),
                    'id'    => 'vibe_unit_attachments', 
                    'type'  => 'multiattachments', 
                    'from'=>'meta',
                ),
            ),
        );
        return $tabs;
    }
}

Wplms_H5p_Actions::init();

