<?php
/**
 * Elementor Integration
 * @version 2.6.14
 */

namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EVO_Elementor_Wig extends Widget_Base {


   public function get_id() {
      return 'eventon';
   }

   public function get_name() {
		return "EventON";
	}

   public function get_title() {
      return __( 'EventON', 'eventon' );
   }

   public function get_categories() {
		return [ 'general' ];
	}
  
   public function get_icon() {
      return 'eicon-coding';
   }

   protected function _register_controls() {

      $this->add_control(
         'section_evo_shortcode',
         [
            'label' => __( 'EventON Calendar', 'eventon' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'evo_shortcode',
         [
            'label' => __( 'Type or paste EventON shortcode', 'eventon' ),
            'type' => Controls_Manager::TEXTAREA,
            'default' => '',
            'title' => __( 'Enter shortcode', 'eventon' ),
            'section' => 'section_evo_shortcode',
         ]
      );
   }

   protected function render( $instance = [] ) {
   		$settings = $this->get_settings_for_display();

      
      	if(empty( $settings['evo_shortcode'] )){
      		echo "No shortcode entered!";
      	}else{

      		$C =$this->parse_text_editor( $settings['evo_shortcode'] );
      		echo $C;
      	} 
   }

   protected function content_template() {}

   public function render_plain_content( $instance = [] ) {}

}

Plugin::instance()->widgets_manager->register_widget_type( new EVO_Elementor_Wig );
