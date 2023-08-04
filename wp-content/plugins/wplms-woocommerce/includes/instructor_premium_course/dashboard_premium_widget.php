<?php

add_action( 'widgets_init', 'wplms_dash_instructor_premium_course_widget' );

function wplms_dash_instructor_premium_course_widget() {
    register_widget('wplms_dash_instructor_premium_course');
}

class wplms_dash_instructor_premium_course extends WP_Widget{
  /** constructor -- name this the same as the class above */
  function __construct(){

    $widget_ops = array( 'classname' => 'wplms_dash_instructor_premium_course', 'description' => __('Instructor Premium Course for Dashboard', 'wplms-woo') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_instructor_premium_course' );
    parent::__construct( 'wplms_dash_instructor_premium_course', __(' DASHBOARD : Instructor Premium Courses', 'wplms-woo'), $widget_ops, $control_ops );

  }//End of construct function

  /** @see WP_Widget::widget -- do not rename this */
  function widget( $args, $instance ){

    extract( $args );
    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $user_id = get_current_user_id();

    echo '<div class="'.$width.'"><div class="dash-widget premium_course">'.$before_widget;

    //Get premium courses for instructor
    if(current_user_can('manage_options')){
      $total_courses = __('Unlimited','wplms-woo');
      $published_course = count_user_posts_by_type($user_id,'course');
      echo '<div class="dash-stats">';
      echo '<h5>'.$published_course.'/'.$total_courses.'<span>'.$title.'</span></h5>';
    }else{
      $total_courses = get_user_meta($user_id,'instructor_premium_courses',true);
      if(empty($total_courses)){$total_courses = 0;}
      $published_course = count_user_posts_by_type($user_id,'course');
      echo '<div class="dash-stats">';
      echo '<h3>'.$published_course.'/'.$total_courses.'<span>'.$title.'</span></h3>';
    }

    echo '</div>';
    echo $after_widget.'</div></div>';

    echo '<style>.dash-widget.premium_course{background:#da90ff;color:#FFF;padding:15px 0;}
            .dash-stats h5{font-size: 30px;font-weight: 800;line-height: 1.5;margin: 0;color: #FFF;}
            .dash-stats h5>span{font-size: 11px;text-transform: uppercase;max-width: 50%;display: inline-block;color: #FFF;}
          </style>';
  }

  /** @see WP_Widget::update -- do not rename this */
  function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['width'] = $new_instance['width'];
    return $instance;
  }

  /** @see WP_Widget::form -- do not rename this */
  function form($instance){

    $defaults = array( 
                    'title'  => __('Premium Courses','wplms-woo'),
                    'max' => 5,
                    'width' => 'col-md-6 col-sm-12'
                );
    $instance = wp_parse_args( (array) $instance, $defaults );
    $title  = esc_attr($instance['title']);
    $width = esc_attr($instance['width']);
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-woo'); ?></label> 
      <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','wplms-woo'); ?></label> 
      <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
        <option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','wplms-woo'); ?></option>
        <option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','wplms-woo'); ?></option>
        <option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','wplms-woo'); ?></option>
        <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','wplms-woo'); ?></option>
         <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','wplms-woo'); ?></option>
        <option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','wplms-woo'); ?></option>
      </select>
    </p>
    <?php 
  }

}//End of wplms_dash_instructor_premium_course

?>