<?php 
    if ( !defined( 'ABSPATH' ) ) exit;
    global $post;
    $course_id= get_the_ID();
        $user_id = get_current_user_id();

    $class='';
    if(class_exists('WPLMS_tips')){
        $wplms_settings = WPLMS_tips::init();
        $settings = $wplms_settings->lms_settings;
        if(isset($settings['general']['curriculum_accordion'])){
            $class="accordion";	
        }
    }
?>
<h3 class="heading">
	<span><?php  _e('Qualification Courses','vibe'); ?></span>
</h3>

<div class="course_curriculum <?php echo $class; ?> diploma_curriculum">
    <?php
        $course_curriculum = ng_course_get_diploma_course_curriculum( $course_id, $user_id ); 
        ng_course_filters::init();
		
        if(!empty($course_curriculum)){

            echo '<table class="table">';
            foreach( $course_curriculum as $lesson ){ 
                switch($lesson['type']){
					case 'course':          
                        ?>
                        <tr id="course-<?php echo $lesson["id"]; ?>" class="course_lesson">
                            <td class="diploma-thumb"><?php echo '<a target="_blank" href=" '.get_permalink($lesson["id"]).' " >'.get_the_post_thumbnail( $lesson["id"] ).'</a>'; ?></td>
                            <td class="diploma-title"><?php echo '<a target="_blank" href=" '.get_permalink($lesson["id"]).' " >'.get_the_title($lesson["id"]).'</a>'; ?></td>
                            <?php if( is_user_logged_in() ) { ?> <td class="diploma-prog-bar"><?php echo $lesson['pbar'];  ?> </td><?php } ?> 
                            <td class="diploma-btn"><?php the_course_button( $lesson["id"] ) ; ?> </td>
                            <td class="diploma-time"><?php echo $lesson['duration']; ?></td>
                        </tr>
                        <?php
					break;
                        
                    case 'section':
                        ?>
                        <tr class="course_section">
                            <td colspan="4"><?php echo $lesson['title']; ?></td>
                        </tr>
                        <?php
                    break;
                }
            }
            echo '</table>';
        }else{
            ?>
                    <div class="message"><?php echo _x('No curriculum found !','Error message for no curriculum found in course curriculum ','vibe'); ?></div>
            <?php	
        }
		ng_course_filters::remove();
		do_action( 'after_diploma_curriculum',  $course_id, $user_id, $course_curriculum );
    ?>
</div>