<?php

// Progress bar for courses in course 
function is_diploma( $course_id = '' ){
    return ( $course_id && get_post_meta( $course_id,'ng_course_type', true) == 'S' ) ? true : false ;
}

function ng_course_get_post_type( $id ){
   $template = BP_Course_Template::init();
   return $template->get_post_type( $id );
}

function ng_get_diploma_curriculum( $course_id ){
	
	$diploma_curriculum[$course_id] = get_post_meta($course_id,'ng_diploma_curriculum',true);	
	
	return $diploma_curriculum[$course_id];
}

function ng_course_get_diploma_course_curriculum( $course_id = NULL,$user_id ){
	$diploma_curriculum=array();
	global $post;
	if(empty($course_id) && $post->post_type == 'course')
		$course_id = $post->ID;
	
	if( !isset($course_id) || !is_numeric( $course_id ) ) 
		return $diploma_curriculum;

	$course_items = ng_get_diploma_curriculum( $course_id );
	if( !empty( $course_items ) ){
		foreach($course_items as $key => $item){
			if(is_numeric($item)){
				$type = ng_course_get_post_type ($item);
				$labels = $free_access = '';

				if($type == 'course'){
					$free_access = get_post_meta($item,'vibe_free',true);
					$labels = (vibe_validate($free_access)?'<span class="free">'.__('FREE','vibe').'</span>':'');	
				}
				
				$duration = get_post_meta($item,'vibe_duration',true);
				if( empty($duration) )
				$duration = 0;
				$duration_parameter = apply_filters("vibe_".$type."_duration_parameter",60,$item);
				$total_duration = $duration*$duration_parameter;
				$duration = '<span class="time"><i class="fa fa-clock-o"></i> '.(($duration >9998)?_x('Unlimited','Unlimited unit duration label','vibe'):(($total_duration >= 86400)?tofriendlytime($total_duration):gmdate("H:i:s",$total_duration))).'</span>';
				$curriculum_course_link = apply_filters('wplms_curriculum_course_link',0,$item,$course_id);
				
				$diploma_curriculum[] = array(
					'id'		=>  $item,
					'key'		=>	$key,
					'type'		=>	$type,
					'labels' 	=>  apply_filters('bp_course_curriculum_item_labels',$labels,$item,$type),
					'title'		=>	get_the_title($item),
					'link'		=>	(( vibe_validate($free_access) || ($post->post_author == get_current_user_id()) || current_user_can('manage_options') || $curriculum_course_link)? ( empty($curriculum_course_link)?get_permalink($item).'?id='.$course_id:$curriculum_course_link):''),	
					'duration' 	=>  $duration,
					'status'	=>  bp_course_get_user_course_status($user_id,$item),
					'progress'	=>  bp_course_get_user_progress($user_id,$item),
					'pbar'      =>  ng_course_progressbar($item,$user_id),
				);
	   
			} else{
				$diploma_curriculum[] = array(
					'type'	=>	'section',
					'key'	=>	$key,
					'title'	=>	$item
				);	
			}
	
		}
		return $diploma_curriculum;  
    }
}

function ng_course_progressbar($course_id,$user_id){
	if( bp_course_is_member($course_id,$user_id) ){
		$percentage = bp_course_get_user_progress($user_id,$course_id);
		
		$units = array();
		if(function_exists('bp_course_get_curriculum_units'))
			$units = bp_course_get_curriculum_units($course_id);
		
		$total_units = count($units);
		if(empty($total_units))
			$total_units = 1;
		if(empty($percentage)){
			$percentage = 0;
		}
		
		if($percentage > 100)
		  $percentage= 100;
		
		$unit_increase = round(((1/$total_units)*100),2);
		
		return '<div class="progress course_progressbar" data-increase-unit="'.$unit_increase.'" data-value="'.$percentage.'">
					 <div class="bar animate cssanim stretchRight load" style="width: '.$percentage.'%;"><span>'.$percentage.'%</span></div>
				   </div>';
	}
}