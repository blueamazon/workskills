<?php
/**
 * Elementor Integration of eventON
 */

class EVO_Elementor{
	private static $instance = null;

   public static function get_instance() {
      if ( ! self::$instance )
         self::$instance = new self;
      return self::$instance;
   }

   public function init(){
      add_action( 'elementor/widgets/widgets_registered', array( $this, 'widgets_registered' ) );
   }

   public function widgets_registered() {

      if(defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')){

         $widget_file = AJDE_EVCAL_PATH.'/includes/integration/elementor_widget.php';

         require_once $widget_file;

      }
   }
}
EVO_Elementor::get_instance()->init();

