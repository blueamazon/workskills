<?php
/**
 * API\
 *
 * @class       Vibe_Projects_API
 * @author      VibeThemes
 * @category    Admin
 * @package     vibemeeting
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Wplms_H5p_API{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_H5p_API();
        return self::$instance;
    }

    private function __construct(){

        add_action('rest_api_init',array($this,'register_api_endpoints'));
    }


    function register_api_endpoints(){

        register_rest_route( WPLMS_H5P_API_NAMESPACE, '/user/quiz', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_quiz' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        register_rest_route( WPLMS_H5P_API_NAMESPACE, '/user/submitresult', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'submitresult' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        

      
     

    }

    function submitresult($request){
        $args = json_decode($request->get_body(),true);

        $quiz_id = $args['quiz_id'];
        $course_id = $args['course_id'];
        $user_id = $this->user->id;
        $scored_marks = $args['scored_marks'] ;
        $max_marks = $args['total_marks'] ;
        update_user_meta($user_id,$quiz_id,time());
        $answers = array();
        update_post_meta( $quiz_id, $user_id,$scored_marks);
        do_action('wplms_submit_quiz',$quiz_id,$user_id,$answers);
        bp_course_update_user_quiz_status($user_id,$quiz_id,4);
        do_action('wplms_evaluate_quiz',$quiz_id,$scored_marks,$user_id,$max_marks);
        global $wpdb,$bp;
        $activity_id = $wpdb->get_var($wpdb->prepare( "
                    SELECT id 
                    FROM {$bp->activity->table_name}
                    WHERE secondary_item_id = %d
                  AND type = 'quiz_evaluated'
                  AND user_id = %d
                  ORDER BY date_recorded DESC
                  LIMIT 0,1
                " ,$quiz_id,$user_id));
        $user_result = array();
        if(!empty($activity_id)){
            $user_result = array('h5p'.$quiz_id => array(
                                            'content' => _x('Quiz evaluated','','wplms-h5p'),
                                            'marks' => $scored_marks,
                                            'max_marks' => $max_marks
                                          )
                          );
            bp_course_record_activity_meta(array('id'=>$activity_id,'meta_key'=>'quiz_results','meta_value'=>$user_result));

        }
        if(empty($course_id)){
            $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);        
        }
        $course_curriculum=bp_course_get_curriculum_units($course_id);
        if(!is_array($course_curriculum)){
            $course_curriculum = array();
        }
        $quiz_completion_complete = get_post_meta($quiz_id,'vibe_quiz_message',true);
        $quiz_completion_complete = str_replace(
            array('id="'.$quiz_id.'"',
                'id='.$quiz_id,
                'id=\''.$quiz_id.'\'',
            )
            , 'id="'.$quiz_id.'" key="'.$this->user_id.'"'
            , $quiz_completion_complete
        );  

        $completion_message = '';
        ob_start();
        echo do_shortcode($quiz_completion_complete);
        do_action('wplms_after_quiz_message',$quiz_id,$this->user_id);
        $completion_message = ob_get_clean();
        $stop_progress = apply_filters('bp_course_stop_course_progress',true,$course_id);
        $next_unit = null;
        $flag = apply_filters('wplms_next_unit_access',true,$quiz_id,$this->user_id);
        $continue = 0;
        if( $stop_progress && $flag ){
            $continue = 1;
            $key = array_search($quiz_id,$course_curriculum);
            if($key <=(count($course_curriculum)-1) ){  // Check if not the last unit
                $key++;
                if(isset($course_curriculum[$key])){
                    $next_unit =  $course_curriculum[$key];
                }
            }

        }
       
        $data = array(
            'check_results_url'=>bp_core_get_user_domain( $this->user->id ).BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.$quiz_id,'status'=>true,
            'progress'=>$progress,
            'completion_message'=> $completion_message,
            'next_unit'=>$next_unit,
            //'retake_html'=>$retake_html,
            'ext_flag' => $flag,
            'continue' => $continue,
            'result' =>  $user_result,
            'marks' => $scored_marks,
            'max' => $max_marks
        );

          
        $questions = array(
            'ques'=>array('quiz'),
            'marks'=>array($max_marks),
        );
        bp_course_update_quiz_questions($quiz_id,$user_id,$questions);

        return new WP_REST_Response( $data, 200);  
    }

    function user_permissions_check($request){
        
        $body = json_decode($request->get_body(),true);
        if (empty($body['token'])){
            $client_id = $request->get_param('client_id');
            if($client_id == vibebp_get_setting('client_id')){
                return true;
            }
        }else{
            $token = $body['token'];
        }

        if(!empty($body['token'])){
            global $wpdb;

            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                $this->user_id = $this->user->id;
                return true;
            }
        }

        return false;
    }


    function get_quiz($request){
        $args = json_decode($request->get_body(),true);
        $quiz_id = $args['quiz_id'];
        $return = array(
            'status'=>0,
            'url' =>'',
            'message'=>__('Quiz not set','wplms-bbb')
        );
        if(!empty($quiz_id)){
            $return = array(
                'status'=>1,
                'data' => bp_wplms_get_quiz_data($this->user->id,$quiz_id),
                
            );
            
        }
        return new WP_REST_Response($return, 200);
    }

}

Wplms_H5p_API::init();