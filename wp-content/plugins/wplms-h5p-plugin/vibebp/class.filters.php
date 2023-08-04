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


class Wplms_H5p_Filters{

	public static $instance;
	
	public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_H5p_Filters();
        return self::$instance;
    }

	private function __construct(){
        add_filter('wplms_course_creation_tabs',array($this,'h5p_quiz'));
        
        add_filter('wplms_quiz_types',array($this,'wplms_quiz_types'));

       	add_filter('wplms_selectcpt_field_results',array($this,'selectcpt'),10,5);
        add_filter('wplms_selectcpt_field_options',array($this,'select_options'),10,3);

       	add_filter('bp_course_api_course_curriculum_quiz',array($this,'check_h5p'),10,3);

        add_filter('wplms_unit_the_content',array($this,'wplms_h5p_content'),10,4);
        add_filter('wplms_quiz_type',array($this,'quiz_type'),10,2);
        add_filter('wplms_get_element_type',array($this,'identify_h5p_quiz'),10,3);
        //add_filter('wplms_quiz_metabox',array($this,'add_h5p'));

        add_filter('wplms_selectcpt_title',array($this,'edit_course_title'),10,3);

    }

    function edit_course_title($title,$id,$field){

        if($field['post_type'] == 'h5p'){
            global $wpdb;
            $table = $wpdb->prefix.'h5p_contents';
            $h5ptitle = $wpdb->get_var("SELECT title FROM {$table} WHERE id= {$id}");
            if(!empty($h5ptitle)){
                return $h5ptitle;
            }
        }

        return $title;
    }
    function quiz_type($type,$quiz_id){

        $h5p = get_post_meta($quiz_id,'wplms_h5p_content',true);
        if(!empty($h5p)){
            $type='h5p_quiz';
        }
        

        return $type;
    }

    function identify_h5p_quiz($type,$id,$post_type){

        if($post_type == 'quiz'){
            $h5p = get_post_meta($id,'wplms_h5p_content',true);
            if(!empty($h5p)){
                $type='h5p_quiz';
            }
        }

        return $type;
    }

    function add_h5p($settings){
        $settings[] = array(
                    'label'=> __('Add h5p content','wplms-h5p' ),
                    'type'=> 'selectcpt',
                    'level'=>'h5p',
                    'post_type'=>'h5p',
                    'value_type'=>'single',
                    'upload_title'=>__('Upload h5p content','wplms-h5p' ),
                    'desc'=>__('Select a H5p content.','wplms-h5p' ),
                    'style'=>'small_icon',
                    'from'=>'meta',
                    'is_child'=>true,
                    'id' => 'wplms_h5p_content',
                    'default'=> '',
                );

        return $settings;
    }

    function wplms_quiz_types($types){

        $types[] = array('label' => 'H5P','value'=>'h5p_quiz');
        return $types;
    }

    function select_options($show_values,$field,$id){
        $content_id = get_post_meta($id,'wplms_h5p_content',true);

        if(!empty($content_id ) && is_numeric($content_id ) && $field['id']=='wplms_h5p_content'){
            global $wpdb;
            $table = $wpdb->prefix.'h5p_contents';
                
            $h5p_contents = $wpdb->get_results("SELECT id,title FROM {$table} WHERE id= {$content_id}");
            
            
            if(!empty($h5p_contents)){
                $show_values = array();
                foreach ($h5p_contents as $key => $cc) {
                    $show_values = array('id'=>$cc->id,
                                'text'=>$cc->title);
                }
            }  
        }
        return $show_values;
    }

    function wplms_h5p_content($content,$user_id=null,$unit_id=null,$course_id=null){

        if(!empty($unit_id)){
            if(get_post_type($unit_id) == 'unit'){
                $h5p_content = get_post_meta($unit_id,'wplms_h5p_content',true);
                if(!empty($h5p_content)){
                    $html = '<div class="wplms_iframe_wrapper"><iframe src="'.admin_url('admin-ajax.php').'?action=h5p_embed&id='.$h5p_content.'"></iframe></div>';

                    $content = $html.$content;    
                }
            }
        }
        return $content;
    }
    	
    function check_h5p($meta){
    	$quiz_id = $meta['id'];
    	$is_h5p = get_post_meta($quiz_id,'wplms_h5p_content',true);
    	if(!empty($is_h5p)){
    		$meta['quiz_type'] = 'h5p_quiz';
    		$meta['content_id'] = $is_h5p;
    	}
    	return $meta;
    }


    function selectcpt($results,$search,$cpt,$request,$user){
    	if($cpt=='h5p'){
    		global $wpdb;
    		$table = $wpdb->prefix.'h5p_contents';

            $privacy  = apply_filters('wplms_h5p_instructor_contents_selectcpt',false);

    		if(user_can($user->id,'manage_options') || !$privacy){
    			
    			$h5p_contents = $wpdb->get_results("SELECT id,title FROM {$table} WHERE title LIKE '%{$search}%'");
    		}else{
    			
    			$h5p_contents = $wpdb->get_results("SELECT id,title FROM {$table} WHERE title LIKE '%{$search}%' AND user_id = {$user->id}");
    		}
    		
    		if(!empty($h5p_contents)){
    			foreach ($h5p_contents as $key => $cc) {
    				$results[] = array('id'=>$cc->id,
                                'text'=>$cc->title);
    			}
    		}
    		
    	}
    	return $results;
    }

    function h5p_quiz($tabs){
		$tabs['course_curriculum']['fields'][0]['curriculum_elements'][2]['types'][]= array(
            'id'=>'h5p_quiz',
            'icon'=>'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64" height="64" x="0px" y="0px" viewBox="0 0 345 150" enable-background="new 0 0 345 150" xml:space="preserve"><g><path d="M325.7,14.7C317.6,6.9,305.3,3,289,3h-43.5H234v31h-66l-5.4,22.2c4.5-2.1,10.9-4.2,15.3-5.3c4.4-1.1,8.8-0.9,13.1-0.9c14.6,0,26.5,4.5,35.6,13.3c9.1,8.8,13.6,20,13.6,33.4c0,9.4-2.3,18.5-7,27.2c-4.7,8.7-11.3,15.4-19.9,20c-3.1,1.6-6.5,3.1-10.2,4.1h42.4H259V95h25c18.2,0,31.7-4.2,40.6-12.5c8.9-8.3,13.3-19.9,13.3-34.6C337.9,33.6,333.8,22.5,325.7,14.7z M288.7,60.6c-3.5,3-9.6,4.4-18.3,4.4H259V33h13.2c8.4,0,14.2,1.5,17.2,4.7c3.1,3.2,4.6,6.9,4.6,11.5C294,53.9,292.2,57.6,288.7,60.6z"/><path d="M176.5,76.3c-7.9,0-14.7,4.6-18,11.2L119,81.9L136.8,3h-23.6H101v62H51V3H7v145h44V95h50v53h12.2h42c-6.7-2-12.5-4.6-17.2-8.1c-4.8-3.6-8.7-7.7-11.7-12.3c-3-4.6-5.3-9.7-7.3-16.5l39.6-5.7c3.3,6.6,10.1,11.1,17.9,11.1c11.1,0,20.1-9,20.1-20.1S187.5,76.3,176.5,76.3z"/></g></svg>',
            'label'=>__('H5P','wplms-h5p'),
            'fields'=>array(
                array(
                    'label'=> __('Quiz title','wplms-h5p' ),
                    'type'=> 'title',
                    'id' => 'post_title',
                    'from'=>'post',
                    'value_type'=>'single',
                    'style'=>'full',
                    'default'=> __('Quiz Name','wplms-h5p' ),
                    'desc'=> __('This is the title of the unit which is displayed on top of every unit','wplms-h5p' )
                    ),
                array(
                    'label'=> __('Quiz type','wplms-h5p' ),
                    'type'=> 'taxonomy',
                    'taxonomy'=> 'quiz-type',
                    'from'=>'taxonomy',
                    'value_type'=>'single',
                    'style'=>'assign_cat',
                    'id' => 'quiz-type',
                    'default'=> __('Select a type','wplms-h5p' ),
                ),
                array(
                    'label'=> __('What is the quiz about','wplms-h5p' ),
                    'type'=> 'editor',
                    'style'=>'',
                    'value_type'=>'single',
                    'id' => 'post_content',
                    'from'=>'post',
                    'extras' => '',
                    'default'=> __('Enter a short description about the quiz.','wplms-h5p' ),
                ),
               	array(
                    'label'=> __('Add h5p content','wplms-h5p' ),
                    'type'=> 'selectcpt',
                    'level'=>'h5p',
                    'post_type'=>'h5p',
                    'value_type'=>'single',
                    'upload_title'=>__('Upload h5p content','wplms-h5p' ),
                    'desc'=>__('Select a H5p content.','wplms-h5p' ),
                    'style'=>'small_icon',
                    'from'=>'meta',
                    'is_child'=>true,
                    'id' => 'wplms_h5p_content',
                    'default'=> '',
                ), 
                array(
                    'label' => __('Number of Extra Quiz Retakes','wplms-h5p'),
                    'desc'  => __('Student can reset and start the quiz all over again. Number of Extra retakes a student can take.','wplms-h5p'),
                    'id'    => 'vibe_quiz_retakes',
                    'from'=>'meta',
                    'type'  => 'number',
                    'std'   => 0
                ), 
                array(
                    'label' => __('Post Quiz Message','wplms-h5p'),
                    'desc'  => __('This message is shown to users when they submit the quiz','wplms-h5p'),
                    'id'    => 'vibe_quiz_message',
                    'type'  => 'editor',
                    'from'=>'meta',
                    'default'   => 'Thank you for Submitting the Quiz. Check Results in your Profile.'
                ),
                
            )
        );
		return $tabs;
	}
	
}

Wplms_H5p_Filters::init();

