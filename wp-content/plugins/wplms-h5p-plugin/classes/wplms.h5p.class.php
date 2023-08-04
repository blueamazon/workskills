<?php

if(!class_exists('Wplms_H5p_Class'))
{   
    class Wplms_H5p_Class
    {   
        const VERSION = '1.0';
        public static $core = null;
        public static $interface = null;
        public static $settings = null;
        public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new Wplms_H5p_Class();
            return self::$instance;
        }

        public function __construct(){ 
            $this->plugin_slug = 'h5p';
            $this->wplms_h5p_files = '';
            add_action('admin_notices',array($this,'check_h5p_installed'));
            add_shortcode('wplms_h5p',array($this,'wplms_h5p_shortcode'));
            add_action('wp_ajax_set_user_marks_h5p',array($this,'set_user_marks_h5p'));
            add_action('wp_ajax_set_single_quiz_marks_h5p',array($this,'set_single_quiz_marks_h5p'));
            add_action('wp_enqueue_scripts',array($this,'front_end_add_h5p_js'));
            add_action('wp_ajax_wplms_h5p_get_contents',array($this,'get_contents'));

        }

        function get_contents(){
          if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_h5p_pagination') || !is_user_logged_in() || !current_user_can('edit_posts')){
            _e('Security check Failed. Contact Administrator.','wplms-h5p');
             die();
          }
          $data['contents'] = $this->wplms_get_h5p_contents($_POST['page']);
          if(count($data['contents']) < 10){
            $next = 0;
          }else{
            $next = ($_POST['page']+1);
          }
          $data['paging'] = array('previous'=>($_POST['page']-1),'next'=>$next);  
          echo json_encode($data);
          die();
        }
        
        function wplms_get_h5p_contents($page=null){
            global $wpdb;
            $user_id = get_current_user_id();
            $table = $wpdb->prefix.'h5p_contents';
            $limit = apply_filters('wplms_h5p_contents_limit',10);
            if(empty($page)){
              $offset = 0;
            }else{
              $offset = ($page-1)*$limit;
            }
            
            if(function_exists('vibe_get_option') && vibe_validate(vibe_get_option('instructor_content_privacy'))){
                $h5p_contents = $wpdb->get_results("SELECT id,title,slug FROM {$table} WHERE user_id = {$user_id} LIMIT {$offset},{$limit}");

            }else{
                $h5p_contents = $wpdb->get_results("SELECT id,title,slug FROM {$table} LIMIT {$offset},{$limit}");
            }
            $contents_array = array();
            foreach ($h5p_contents as $value) {
                $contents_array[] = array('id'=>$value->id,'title'=>$value->title); 
            }
            return $contents_array;
        }

        function front_end_add_h5p_js(){

            if(function_exists('is_wplms_4_0') && is_wplms_4_0())
              return;
            $contents_array = $this->wplms_get_h5p_contents();
            wp_enqueue_script('wplms-h5p-front-end',plugins_url('../assets/wplms-h5p-front-end.js',__FILE__),array(),'1.2');
            $t_array = array(
                'insert'=>_x('Insert','Insert label on h5p','wplms-h5p'),
                'title'=>_x('Title','title lable on table','wplms-h5p'),
                'previous'=>_x('Previous','previous','wplms-h5p'),
                'next'=>_x('Next','next','wplms-h5p'),
                'security' => wp_create_nonce('wplms_h5p_pagination'),
                'add_wplms_h5p' => _x('Add wplms h5p','Add wplms h5p label on h5p','wplms-h5p'),
              );
            wp_localize_script( 'wplms-h5p-front-end', 'default_wplms_h5p_strings', $t_array );
            wp_localize_script( 'wplms-h5p-front-end', 'wplms_h5p_contents', $contents_array );
        }

        function set_single_quiz_marks_h5p(){
          if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','vibe');
             die();
          }
          $quiz_id= $_POST['quiz_id'];
          $user_id = get_current_user_id();
          $total_marks = $_POST['scored_marks'] ;
          $max_marks = $_POST['total_marks'] ;
          global $wpdb;
          global $bp;
          $access = get_user_meta($user_id,$quiz_id,true);

   
          update_user_meta($user_id,$quiz_id,time());
          $questions = array(
            'ques'=>array('quiz'),
            'marks'=>array($max_marks),
            );
          bp_course_update_quiz_questions($quiz_id,$user_id,$questions);

          update_post_meta($quiz_id,$user_id,$total_marks);
          bp_course_update_user_quiz_status($user_id,$quiz_id,0);

          do_action('wplms_submit_quiz',$quiz_id,$user_id,$answers);

          $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);

          if(!empty($course_id)){ // Course progressbar fix for single quiz
            
            $curriculum = bp_course_get_curriculum_units($course_id);
            $per = round((100/count($curriculum)),2);

            $progress = bp_course_get_user_progress($user_id,$course_id);
            if(empty($progress))
              $progress = 0;

            $new_progress = $progress+$per;
           
            if($new_progress > 100){
              $new_progress = 100;
            }
            bp_course_update_user_progress($user_id,$course_id,$new_progress);
          }


          bp_course_update_user_quiz_status($user_id,$quiz_id,4);
          do_action('wplms_evaluate_quiz',$quiz_id,$total_marks,$user_id,$max_marks);
          $activity_id = $wpdb->get_var($wpdb->prepare( "
                    SELECT id 
                    FROM {$bp->activity->table_name}
                    WHERE secondary_item_id = %d
                  AND type = 'quiz_evaluated'
                  AND user_id = %d
                  ORDER BY date_recorded DESC
                  LIMIT 0,1
                " ,$quiz_id,$user_id));
          if(!empty($activity_id)){
            $user_result = array('h5p'.$quiz_id => array(
                                            'content' => _x('Quiz evaluated','','wplms-h5p'),
                                            'marks' => $total_marks,
                                            'max_marks' => $max_marks
                                          )
                          );
            bp_course_record_activity_meta(array('id'=>$activity_id,'meta_key'=>'quiz_results','meta_value'=>$user_result));

          }
          die();
        }

        function check_h5p_installed(){
            if(!class_exists('H5P_Plugin')){
                echo '<div class="error"><p>'.__('Please install H5P for WordPress to use WPLMS H5P plugin','wplms-h5p').' <a href="http://vibethemes.com/documentation/wplms/knowledge-base/wplms-h5p-addon/"><span>'.__('Show details','wplms-h5p').'</span></a></p></div>';
            }
        }

        function set_user_marks_h5p(){
          if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
                _e('Security check Failed. Contact Administrator.','wplms-h5p');
            die();
          }
          global $wpdb;
          global $bp;
          $quiz_id = $_POST['quiz_id'];
          $user_id = get_current_user_id();
          $total_marks = $_POST['scored_marks'] ;
          $max_marks = $_POST['total_marks'] ;
          update_user_meta($user_id,$quiz_id,time());
          update_post_meta($quiz_id,$user_id,0);
          bp_course_update_user_quiz_status($user_id,$quiz_id,0);
          $answers = array();
          do_action('wplms_submit_quiz',$quiz_id,$user_id,$answers);

          update_post_meta( $quiz_id, $user_id,$total_marks);
          bp_course_update_user_quiz_status($user_id,$quiz_id,1);
          do_action('wplms_evaluate_quiz',$quiz_id,$total_marks,$user_id,$max_marks);
          $activity_id = $wpdb->get_var($wpdb->prepare( "
                    SELECT id 
                    FROM {$bp->activity->table_name}
                    WHERE secondary_item_id = %d
                  AND type = 'quiz_evaluated'
                  AND user_id = %d
                  ORDER BY date_recorded DESC
                  LIMIT 0,1
                " ,$quiz_id,$user_id));
          if(!empty($activity_id)){
            $user_result = array('h5p'.$quiz_id => array(
                                            'content' => _x('Quiz evaluated','','wplms-h5p'),
                                            'marks' => $total_marks,
                                            'max_marks' => $max_marks
                                          )
                          );
            bp_course_record_activity_meta(array('id'=>$activity_id,'meta_key'=>'quiz_results','meta_value'=>$user_result));

          }
          
          $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);        
          $stop_progress = apply_filters('bp_course_stop_course_progress',true,$course_id);

          

          $flag = apply_filters('wplms_next_unit_access',true,$quiz_id);
          if(is_numeric($course_id) && $stop_progress && $flag){
            $curriculum=bp_course_get_curriculum_units($course_id);
            $key = array_search($quiz_id,$curriculum);
            if($key <=(count($curriculum)-1) ){  // Check if not the last unit
              $key++;
              echo $curriculum[$key].'##';
            }
          }else{
              echo '##';
            }
          
          echo ' ';

          $get_message = trim(get_post_meta($quiz_id,'vibe_quiz_message',true));
          ob_start();
          do_action('wplms_after_quiz_message',$quiz_id,$user_id);
          $after_quiz = ob_get_clean();
          
          if(!empty($after_quiz)){
            echo apply_filters('the_content',$after_quiz);
          }

          
          $questions = array(
            'ques'=>array('quiz'),
            'marks'=>array($max_marks),
            );
          bp_course_update_quiz_questions($quiz_id,$user_id,$questions);
          
          die();
        }

        function enqueue_styles_and_scripts() {
            echo '<link link rel="stylesheet"  type="text/css" media="all"  href="'.plugins_url('h5p/h5p-php-library/styles/h5p.css').'">';
        }

        function wplms_h5p_shortcode($atts,$content=null){
            global $wpdb;
            if (isset($atts['slug'])) {
              $q=$wpdb->prepare(
                "SELECT  id ".
                "FROM    {$wpdb->prefix}h5p_contents ".
                "WHERE   slug=%s",
                $atts['slug']
              );
              $row=$wpdb->get_row($q,ARRAY_A);

              if ($wpdb->last_error) {
                return sprintf(__('Database error: %s.', $this->plugin_slug), $wpdb->last_error);
              }

              if (!isset($row['id'])) {
                return sprintf(__('Cannot find H5P content with slug: %s.', $this->plugin_slug), $atts['slug']);
              }

              $atts['id']=$row['id'];
            }

            $id = isset($atts['id']) ? intval($atts['id']) : NULL;
            $content = $this->get_content($id);
            if (is_string($content)) {
              // Return error message if the user has the correct cap
              return current_user_can('edit_h5p_contents') ? $content : NULL;
            }

            // Log view
            new H5P_Event('content', 'shortcode',
                $content['id'],
                $content['title'],
                $content['library']['name'],
                $content['library']['majorVersion'] . '.' . $content['library']['minorVersion']);
            if(!empty($atts['unit_id']) &&  is_numeric($atts['unit_id'])){
                $content['unit_id'] = $atts['unit_id'];
            }
            return $this->add_assets($content);
        }
        function add_assets($content, $no_cache = FALSE) {
            // Add core assets
            $this->add_core_assets();

            // Detemine embed type
            $embed = H5PCore::determineEmbedType($content['embedType'], $content['library']['embedTypes']);

            // Make sure content isn't added twice
            $cid = 'cid-' . $content['id'];
            if (!isset(self::$settings['contents'][$cid])) {
              self::$settings['contents'][$cid] = $this->get_content_settings($content);
              $core = $this->get_h5p_instance('core');
             
              // Get assets for this content
              $preloaded_dependencies = $core->loadContentDependencies($content['id'], 'preloaded');
              $files = $core->getDependenciesFiles($preloaded_dependencies);
              $this->alter_assets($files, $preloaded_dependencies, $embed);

              if ($embed === 'div') {
                
              }
              elseif ($embed === 'iframe') {
                self::$settings['contents'][$cid]['scripts'] = $core->getAssetsUrls($files['scripts']);
                self::$settings['contents'][$cid]['styles'] = $core->getAssetsUrls($files['styles']);
              }
            }
            if(get_post_type($content['unit_id']) == 'unit'){
              $script = "<script>H5P.externalDispatcher.on('xAPI', function (event) {
                if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
                  console.log('hui gav fiinishwa');
                  console.log(event);
                  jQuery('body').find('#mark-complete').trigger('click');
                  var statement = event.data.statement;
                }
              });</script>";
            }else{
              if($embed != 'div'){
                $content_quiz = '<div class="h5p-iframe-wrapper"><iframe id="h5p-iframe-' . $content['id'] . '" class="h5p-iframe" data-content-id="' . $content['id'] . '" style="height:1px" src="about:blank" frameBorder="0" scrolling="no"></iframe></div>'.$this->add_settings().
                    $this->enqueue_styles_and_scripts().$script;
              }else{
                $content_quiz =  '<div class="h5p-content" data-content-id="' . $content['id'] . '"></div>'.$this->add_settings().
                $this->enqueue_styles_and_scripts().$this->enqueue_assets($files).$script;
              }
              $check_results_link = '<a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.$content['unit_id'].'class="unit_button quiz_results_popup">'._x('Check Results','check results lable h5p','wplms-h5p').'</a>';
              if(!is_singular('quiz')){
                $lms_settings = get_option('lms_settings');
                if(!empty($lms_settings) && !empty($lms_settings['general']) && !empty($lms_settings['general']['disable_ajax'])){
                  $script = "<script>
                    jQuery(document).ready(function(){
                      jQuery('a.start_quiz').addClass('hide');
                      //jQuery('a.start_quiz').hide();
                      H5P.externalDispatcher.on('xAPI', function (event) {
                        if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
                          var score = event.getScore();
                          var maxScore = event.getMaxScore();
                          var element = jQuery('.unit_button.start_quiz');
                          $ = jQuery;
                          jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: { action: 'set_user_marks_h5p', 
                                    security: '".wp_create_nonce("security")."',
                                    quiz_id:".$content['unit_id'].",
                                    total_marks:maxScore,
                                    scored_marks:score
                                },
                            cache: false,
                            success: function (html) {
                              html = $.trim(html);
                              if(html.indexOf('##') > 0){
                                  var nextunit = html.substr(0, html.indexOf('##')); 
                                  html = html.substr((html.indexOf('##')+2));
                                  if(nextunit.length>0){
                                      $('#next_unit').removeClass('hide');
                                      $('#next_unit').attr('data-unit',nextunit);  
                                      $('#next_quiz').removeClass('hide');
                                      $('#next_quiz').attr('data-unit',nextunit); 
                                      $('#unit'+nextunit).find('a').addClass('unit');
                                      $('#unit'+nextunit).find('a').attr('data-unit',nextunit);
                                  }
                              }else{ 
                                  if(html.indexOf('##') == 0){ 
                                      html = html.substr(2);
                                      console.log(html);
                                  }else{
                                      $('#next_unit').removeClass('hide');
                                  }
                              }

                              $('.main_unit_content').html(html);
                              $('.quiz_title .inquiz_timer').trigger('deactivate');
                              $('.in_quiz').trigger('question_loaded');
                              element.removeClass('submit_inquiz');
                              $('.quiz_title .quiz_meta').addClass('hide');
                              element.addClass('quiz_results_popup');
                              element.removeClass('start_quiz');
                              element.removeClass('hide');
                              element.attr('href',$('#results_link').val());
                              
                              element.text(vibe_course_module_strings.check_results);
                              element.parent().find('.save_quiz_progress').remove();
                              $('#unit'+$('#unit.quiz_title').attr('data-unit')).addClass('done');
                              $('body').find('.course_progressbar').removeClass('increment_complete');
                              $('body').find('.course_progressbar').trigger('increment');
                              $('body,html').animate({
                                  scrollTop: 0
                              }, 1200);
                            }
                          });
                          
                        }
                      });
                    });
                      
                    </script>";
                  }else{
                    $script = "<script>
                    
                      jQuery('a.start_quiz').addClass('hide');
                      //jQuery('a.start_quiz').hide();
                      H5P.externalDispatcher.on('xAPI', function (event) {
                        if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
                          var score = event.getScore();
                          var maxScore = event.getMaxScore();
                          var element = jQuery('.unit_button.start_quiz');
                          $ = jQuery;
                          jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: { action: 'set_user_marks_h5p', 
                                    security: '".wp_create_nonce("security")."',
                                    quiz_id:".$content['unit_id'].",
                                    total_marks:maxScore,
                                    scored_marks:score
                                },
                            cache: false,
                            success: function (html) {
                              html = $.trim(html);
                              if(html.indexOf('##') > 0){
                                  var nextunit = html.substr(0, html.indexOf('##')); 
                                  html = html.substr((html.indexOf('##')+2));
                                  if(nextunit.length>0){
                                      $('#next_unit').removeClass('hide');
                                      $('#next_unit').attr('data-unit',nextunit);  
                                      $('#next_quiz').removeClass('hide');
                                      $('#next_quiz').attr('data-unit',nextunit); 
                                      $('#unit'+nextunit).find('a').addClass('unit');
                                      $('#unit'+nextunit).find('a').attr('data-unit',nextunit);
                                  }
                              }else{ 
                                  if(html.indexOf('##') == 0){ 
                                      html = html.substr(2);
                                      console.log(html);
                                  }else{
                                      $('#next_unit').removeClass('hide');
                                  }
                              }

                              $('.main_unit_content').html(html);
                              $('.quiz_title .inquiz_timer').trigger('deactivate');
                              $('.in_quiz').trigger('question_loaded');
                              element.removeClass('submit_inquiz');
                              $('.quiz_title .quiz_meta').addClass('hide');
                              element.addClass('quiz_results_popup');
                              element.removeClass('start_quiz');
                              element.removeClass('hide');
                              element.attr('href',$('#results_link').val());
                              
                              element.text(vibe_course_module_strings.check_results);
                              element.parent().find('.save_quiz_progress').remove();
                              $('#unit'+$('#unit.quiz_title').attr('data-unit')).addClass('done');
                              $('body').find('.course_progressbar').removeClass('increment_complete');
                              $('body').find('.course_progressbar').trigger('increment');
                              $('body,html').animate({
                                  scrollTop: 0
                              }, 1200);
                            }
                          });
                          
                        }
                      });
                    
                      
                    </script>";
                  }
                $script = "<script>
                jQuery(document).ready(function(){
                  jQuery('a.start_quiz').addClass('hide');
                  //jQuery('a.start_quiz').hide();
                  H5P.externalDispatcher.on('xAPI', function (event) {
                    if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
                      var score = event.getScore();
                      var maxScore = event.getMaxScore();
                      var element = jQuery('.unit_button.start_quiz');
                      $ = jQuery;
                      jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: { action: 'set_user_marks_h5p', 
                                security: '".wp_create_nonce("security")."',
                                quiz_id:".$content['unit_id'].",
                                total_marks:maxScore,
                                scored_marks:score
                            },
                        cache: false,
                        success: function (html) {
                          html = $.trim(html);
                          if(html.indexOf('##') > 0){
                              var nextunit = html.substr(0, html.indexOf('##')); 
                              html = html.substr((html.indexOf('##')+2));
                              if(nextunit.length>0){
                                  $('#next_unit').removeClass('hide');
                                  $('#next_unit').attr('data-unit',nextunit);  
                                  $('#next_quiz').removeClass('hide');
                                  $('#next_quiz').attr('data-unit',nextunit); 
                                  $('#unit'+nextunit).find('a').addClass('unit');
                                  $('#unit'+nextunit).find('a').attr('data-unit',nextunit);
                              }
                          }else{ 
                              if(html.indexOf('##') == 0){ 
                                  html = html.substr(2);
                                  console.log(html);
                              }else{
                                  $('#next_unit').removeClass('hide');
                              }
                          }

                          $('.main_unit_content').html(html);
                          $('.quiz_title .inquiz_timer').trigger('deactivate');
                          $('.in_quiz').trigger('question_loaded');
                          element.removeClass('submit_inquiz');
                          $('.quiz_title .quiz_meta').addClass('hide');
                          element.addClass('quiz_results_popup');
                          element.removeClass('start_quiz');
                          element.removeClass('hide');
                          element.attr('href',$('#results_link').val());
                          
                          element.text(vibe_course_module_strings.check_results);
                          element.parent().find('.save_quiz_progress').remove();
                          $('#unit'+$('#unit.quiz_title').attr('data-unit')).addClass('done');
                          $('body').find('.course_progressbar').removeClass('increment_complete');
                          $('body').find('.course_progressbar').trigger('increment');
                          $('body,html').animate({
                              scrollTop: 0
                          }, 1200);
                        }
                      });
                      
                    }
                  });
                });
                  
                </script>";
              }else{
                $script = "<script>
                  jQuery('a.begin_quiz').addClass('hide');
                  H5P.externalDispatcher.on('xAPI', function (event) {
                    if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
                      var score = event.getScore();
                      var maxScore = event.getMaxScore();
                      var element = jQuery('.unit_button.start_quiz');
                      $ = jQuery;
                      jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: { action: 'set_single_quiz_marks_h5p', 
                                security: '".wp_create_nonce("security")."',
                                quiz_id:".$content['unit_id'].",
                                total_marks:maxScore,
                                scored_marks:score
                            },
                        cache: false,
                        success: function (html) {
                          $('#content').append(html);
                          $('#fullbody_mask').remove();
                          window.location.assign(document.URL);
                        }
                      });
                    }
                  });
                </script>";
              }
              
            }
            
            if ($embed === 'div') {
              return '<div class="h5p-content" data-content-id="' . $content['id'] . '"></div>'.$this->add_settings().
            $this->enqueue_styles_and_scripts().$this->enqueue_assets($files).$script;
            }
            else {
              return '<div class="h5p-iframe-wrapper"><iframe id="h5p-iframe-' . $content['id'] . '" class="h5p-iframe" data-content-id="' . $content['id'] . '" style="height:1px" src="about:blank" frameBorder="0" scrolling="no"></iframe></div>'.$this->add_settings().
                    $this->enqueue_styles_and_scripts().$script;
            }

        }

        function add_core_assets() {
            if (self::$settings !== null) {
              return; // Already added
            }

            self::$settings = $this->get_core_settings();
            self::$settings['core'] = array(
              'styles' => array(),
              'scripts' => array()
            );
            self::$settings['loadedJs'] = array();
            self::$settings['loadedCss'] = array();
            $cache_buster = '?ver=' . self::VERSION;

            // Use relative URL to support both http and https.
            $lib_url = plugins_url('h5p/h5p-php-library') . '/';
            $rel_path = '/' . preg_replace('/^[^:]+:\/\/[^\/]+\//', '', $lib_url);

            // Add core stylesheets
            foreach (H5PCore::$styles as $style) {
              self::$settings['core']['styles'][] = $rel_path . $style . $cache_buster;
              
              $this->wplms_h5p_files .= '<link rel="stylesheet" href="'. $lib_url . $style.'" type="text/css" media="all" />';
              //wp_enqueue_style($this->asset_handle('core-' . $style), $lib_url . $style, array(), self::VERSION);
            }

            // Add core JavaScript
            foreach (H5PCore::$scripts as $script) {
              self::$settings['core']['scripts'][] = $rel_path . $script . $cache_buster;
              
              $this->wplms_h5p_files .= '<script type="text/javascript" src="'. $lib_url . $script.'"></script>';

              /*wp_enqueue_script($this->asset_handle('core-' . $script), $lib_url . $script, array(), self::VERSION);*/
            }
            
        }
        function asset_handle($path) {
            return $this->plugin_slug . '-' . preg_replace(array('/\.[^.]*$/', '/[^a-z0-9]/i'), array('', '-'), strtolower($path));
        }
        function get_h5p_url($absolute = FALSE) {
            static $url;

            if (!$url) {
              $upload_dir = wp_upload_dir();

              // Absolute urls are used to enqueue assets.
              $url = array('abs' => $upload_dir['baseurl'] . '/h5p');

              // Check for HTTPS
              if (is_ssl() && substr($url['abs'], 0, 5) !== 'https') {
                // Update protocol
                $url['abs'] = 'https' . substr($url['abs'], 4);
              }

              // Relative URLs are used to support both http and https in iframes.
              $url['rel'] = '/' . preg_replace('/^[^:]+:\/\/[^\/]+\//', '', $url['abs']);
            }

            return $absolute ? $url['abs'] : $url['rel'];
        }
        
        function add_settings() {
            if (self::$settings !== null) {
              $this->print_settings(self::$settings);
            }
        }

        public function print_settings(&$settings, $obj_name = 'H5PIntegration') {
            static $printed;
            if (!empty($printed[$obj_name])) {
              return; // Avoid re-printing settings
            }
            $json_settings = json_encode($settings);
            if ($json_settings !== FALSE) {
              $printed[$obj_name] = TRUE;
              print '<script>' . $obj_name . ' = ' . $json_settings . ';</script>'.$this->wplms_h5p_files;
            }
        }

        function get_core_settings() {
            $current_user = wp_get_current_user();

            $settings = array(
              'baseUrl' => get_site_url(),
              'url' => $this->get_h5p_url(),
              'postUserStatistics' => (get_option('h5p_track_user', TRUE) === '1') && $current_user->ID,
              'ajax' => array(
                'setFinished' => admin_url('admin-ajax.php?token=' . wp_create_nonce('h5p_result') . '&action=h5p_setFinished'),
                'contentUserData' => admin_url('admin-ajax.php?token=' . wp_create_nonce('h5p_contentuserdata') . '&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId')
              ),
              'saveFreq' => get_option('h5p_save_content_state', FALSE) ? get_option('h5p_save_content_frequency', 30) : FALSE,
              'siteUrl' => get_site_url(),
              'l10n' => array(
                'H5P' => array(
                  'fullscreen' => __('Fullscreen', $this->plugin_slug),
                  'disableFullscreen' => __('Disable fullscreen', $this->plugin_slug),
                  'download' => __('Download', $this->plugin_slug),
                  'copyrights' => __('Rights of use', $this->plugin_slug),
                  'embed' => __('Embed', $this->plugin_slug),
                  'size' => __('Size', $this->plugin_slug),
                  'showAdvanced' => __('Show advanced', $this->plugin_slug),
                  'hideAdvanced' => __('Hide advanced', $this->plugin_slug),
                  'advancedHelp' => __('Include this script on your website if you want dynamic sizing of the embedded content:', $this->plugin_slug),
                  'copyrightInformation' => __('Rights of use', $this->plugin_slug),
                  'close' => __('Close', $this->plugin_slug),
                  'title' => __('Title', $this->plugin_slug),
                  'author' => __('Author', $this->plugin_slug),
                  'year' => __('Year', $this->plugin_slug),
                  'source' => __('Source', $this->plugin_slug),
                  'license' => __('License', $this->plugin_slug),
                  'thumbnail' => __('Thumbnail', $this->plugin_slug),
                  'noCopyrights' => __('No copyright information available for this content.', $this->plugin_slug),
                  'downloadDescription' => __('Download this content as a H5P file.', $this->plugin_slug),
                  'copyrightsDescription' => __('View copyright information for this content.', $this->plugin_slug),
                  'embedDescription' => __('View the embed code for this content.', $this->plugin_slug),
                  'h5pDescription' => __('Visit H5P.org to check out more cool content.', $this->plugin_slug),
                  'contentChanged' => __('This content has changed since you last used it.', $this->plugin_slug),
                  'startingOver' => __("You'll be starting over.", $this->plugin_slug),
                  'confirmDialogHeader' => __('Confirm action', $this->plugin_slug),
                  'confirmDialogBody' => __('Please confirm that you wish to proceed. This action is not reversible.', $this->plugin_slug),
                  'cancelLabel' => __('Cancel', $this->plugin_slug),
                  'confirmLabel' => __('Confirm', $this->plugin_slug)
                )
              )
            );

            if ($current_user->ID) {
              $settings['user'] = array(
                'name' => $current_user->display_name,
                'mail' => $current_user->user_email
              );
            }

            return $settings;
        }
        function get_content($id) {
            if ($id === FALSE || $id === NULL) {
              return __('Missing H5P identifier.', $this->plugin_slug);
            }

            // Try to find content with $id.
            $core = $this->get_h5p_instance('core');
            $content = $core->loadContent($id);

            if (!$content) {
              return sprintf(__('Cannot find H5P content with id: %d.', $this->plugin_slug), $id);
            }

            $content['language'] = $this->get_language();
            return $content;
        }
        function get_language() {
            if (defined('WPLANG')) {
              $language = WPLANG;
            }

            if (empty($language)) {
              $language = get_option('WPLANG');
            }

            if (!empty($language)) {
              $languageParts = explode('_', $language);
              return $languageParts[0];
            }

            return 'en';
        }
        function get_content_settings($content) {
            global $wpdb;
            $core = $this->get_h5p_instance('core');

            $safe_parameters = $core->filterParameters($content);
            if (has_action('h5p_alter_filtered_parameters')) {
              // Parse the JSON parameters
              $decoded_parameters = json_decode($safe_parameters);

              /**
               * Allows you to alter the H5P content parameters after they have been
               * filtered. This hook only fires before view.
               *
               * @since 1.5.3
               *
               * @param object &$parameters
               * @param string $libraryName
               * @param int $libraryMajorVersion
               * @param int $libraryMinorVersion
               */
              do_action_ref_array('h5p_alter_filtered_parameters', array(&$decoded_parameters, $content['library']['name'], $content['library']['majorVersion'], $content['library']['minorVersion']));

              // Stringify the JSON parameters
              $safe_parameters = json_encode($decoded_parameters);
            }

            // Getting author's user id
            $author_id = (int)(is_array($content) ? $content['user_id'] : $content->user_id);

            // Add JavaScript settings for this content
            $settings = array(
              'library' => H5PCore::libraryToString($content['library']),
              'jsonContent' => $safe_parameters,
              'fullScreen' => $content['library']['fullscreen'],
              'exportUrl' => get_option('h5p_export', TRUE) ? $this->get_h5p_url() . '/exports/' . ($content['slug'] ? $content['slug'] . '-' : '') . $content['id'] . '.h5p' : '',
              'embedCode' => '<iframe src="' . admin_url('admin-ajax.php?action=h5p_embed&id=' . $content['id']) . '" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
              'resizeCode' => '<script src="' . plugins_url('h5p/h5p-php-library/js/h5p-resizer.js') . '" charset="UTF-8"></script>',
              'url' => admin_url('admin-ajax.php?action=h5p_embed&id=' . $content['id']),
              'title' => $content['title'],
              'displayOptions' => $core->getDisplayOptionsForView($content['disable'], $author_id),
              'contentUserData' => array(
                0 => array(
                  'state' => '{}'
                )
              )
            );

            // Get preloaded user data for the current user
            $current_user = wp_get_current_user();
            if (get_option('h5p_save_content_state', FALSE) && $current_user->ID) {
              $results = $wpdb->get_results($wpdb->prepare(
                "SELECT hcud.sub_content_id,
                        hcud.data_id,
                        hcud.data
                  FROM {$wpdb->prefix}h5p_contents_user_data hcud
                  WHERE user_id = %d
                  AND content_id = %d
                  AND preload = 1",
                $current_user->ID, $content['id']
              ));

              if ($results) {
                foreach ($results as $result) {
                  $settings['contentUserData'][$result->sub_content_id][$result->data_id] = $result->data;
                }
              }
            }

            return $settings;
        }
        function get_h5p_path() {
            $upload_dir = wp_upload_dir();
            return $upload_dir['basedir'] . '/h5p';
        }
        function get_h5p_instance($type) {


            if (self::$interface === null) {
              self::$interface = new H5PWordPress();
              $language = $this->get_language();
              self::$core = new H5PCore(self::$interface, $this->get_h5p_path(), $this->get_h5p_url(), $language, get_option('h5p_export', TRUE));
              self::$core->aggregateAssets = !(defined('H5P_DISABLE_AGGREGATION') && H5P_DISABLE_AGGREGATION === true);
            }

            switch ($type) {
              case 'validator':
                return new H5PValidator(self::$interface, self::$core);
              case 'storage':
                return new H5PStorage(self::$interface, self::$core);
              case 'contentvalidator':
                return new H5PContentValidator(self::$interface, self::$core);
              case 'export':
                return new H5PExport(self::$interface, self::$core);
              case 'interface':
                return self::$interface;
              case 'core':
                return self::$core;
            }
        }

        function alter_assets(&$files, &$dependencies, $embed) {
            if (!has_action('h5p_alter_library_scripts') && !has_action('h5p_alter_library_styles')) {
              return;
            }

            // Refactor dependency list
            $libraries = array();
            foreach ($dependencies as $dependency) {
              $libraries[$dependency['machineName']] = array(
                'majorVersion' => $dependency['majorVersion'],
                'minorVersion' => $dependency['minorVersion']
              );
            }

            /**
              * Allows you to alter which JavaScripts are loaded for H5P. This is
              * useful for adding your own custom scripts or replacing existing once.
             *
             * @since 1.5.3
             *
             * @param array &$scripts List of JavaScripts to be included.
             * @param array $libraries The list of libraries that has the scripts.
             * @param string $embed_type Possible values are: div, iframe, external, editor.
             */
            do_action_ref_array('h5p_alter_library_scripts', array(&$files['scripts'], $libraries, $embed));

            /**
             * Allows you to alter which stylesheets are loaded for H5P. This is
             * useful for adding your own custom stylesheets or replacing existing once.
             *
             * @since 1.5.3
             *
             * @param array &$styles List of stylesheets to be included.
             * @param array $libraries The list of libraries that has the styles.
             * @param string $embed_type Possible values are: div, iframe, external, editor.
             */
            do_action_ref_array('h5p_alter_library_styles', array(&$files['styles'], $libraries, $embed));
        }

        function enqueue_assets(&$assets) {
            $abs_url = $this->get_h5p_url(TRUE);
            $rel_url = $this->get_h5p_url();
            foreach ($assets['scripts'] as $script) {
              $url = $rel_url . $script->path . $script->version;
              if (!in_array($url, self::$settings['loadedJs'])) {
                self::$settings['loadedJs'][] = $url;
                echo '<script type="text/javascript" src="'. $abs_url . $script->path.'"></script>';
                //wp_enqueue_script($this->asset_handle(trim($script->path, '/')), $abs_url . $script->path, array(), str_replace('?ver', '', $script->version));
              }
            }
            foreach ($assets['styles'] as $style) {
              $url = $rel_url . $style->path . $style->version;
              if (!in_array($url, self::$settings['loadedCss'])) {
                self::$settings['loadedCss'][] = $url;
                echo '<link link rel="stylesheet"  type="text/css" media="all"  href="'.$abs_url . $style->path.'">';
                //wp_enqueue_style($this->asset_handle(trim($style->path, '/')), $abs_url . $style->path, array(), str_replace('?ver', '', $style->version));
              }
            }
        } 

        public static function activate(){
          // ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){
          // ADD Custom Code which you want to run when the plugin is de-activated  
        }

        
    } 
}

?>