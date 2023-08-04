<?php
/**
 * Calendar Data Generator, Calendar Options
 * @version 2.6.14
 * @updated 2.7.2
 */

class EVO_Cal_Gen{
	public $loaded_options = array();

	public $current_options = false; // same as op_tab
	private $op_pre = '';

	public function __construct(){
		// initiate global eventon calendar settings
		foreach(apply_filters('evo_cal_gen_options',array(
			'evcal_1'=> array('evcal_options_', true),
			'evcal_2'=> array('evcal_options_', true),
		)) as $tab=>$data){
			$this->op_pre = !isset($data[0])? 'evcal_options_': $data[0];
			$op_global = !isset($data[1])? true: $data[1];
			
			$this->loaded_options[$tab] = $this->get_options_data($this->op_pre,$tab, $op_global);
		}
	}

	// set the current options tab
	public function set_cur($op_tab){
		$this->current_options = $op_tab;
	}

	// load more calendar options into object after construct
	// @updated 2.7.2
	public function load_more($op_tab,$op_pre ='evcal_options_' , $option_values=''){
		if(isset($this->loaded_options[$op_tab])) return true; // avoid reloading already loaded values

		if( !empty($option_values) && is_array($option_values)){
			$this->loaded_options[$op_tab] = $option_values;
			return true;
		}
		$this->loaded_options[$op_tab] = $this->get_options_data($op_pre,$op_tab, true);
	}


	// return a already loaded cal options
	public function get_op($op_tab){
		if(!isset($this->loaded_options[$op_tab])) return false;
		$this->current_options = $op_tab;
		return $this->loaded_options[$op_tab];
	}
	public function get_prop($field, $current_op_tab=''){
		if(!empty($current_op_tab)) $this->current_options = $current_op_tab;
		if(!isset($this->loaded_options[$this->current_options])) return false;
		if(!isset($this->loaded_options[$this->current_options][$field])) return false;
		return maybe_unserialize( $this->loaded_options[$this->current_options][$field] );
	}

	public function check_yn($field, $current_op_tab=''){
		if(!empty($current_op_tab)) $this->current_options = $current_op_tab; // setting current focused options tab if passed
		return ($this->get_prop($field) && $this->get_prop($field) == 'yes')? true: false; 
	}

	private function get_options_data($op_pre, $op_tab, $load_fresh = false){
		$op_name = $op_pre.$op_tab;

		return ($load_fresh)? get_option($op_name): $this->get_global($op_name);
	}

	// retrieve from global if value exists or get from DB and set to global
	private function get_global($op_name){	
		if(array_key_exists('EVO_Settings', $GLOBALS) && isset($GLOBALS['EVO_Settings'][$op_name])){
			global $EVO_Settings;
			return $EVO_Settings[$op_name];
		}else{
			return $GLOBALS['EVO_Settings'][$op_name] = get_option( $op_name);
		}		
	}

	// SET values for an option
		function set_prop($field, $value){
			if(!isset($this->loaded_options[$this->current_options])) return false;

			$this->loaded_options[$this->current_options][$field] = $value;
			$op_name = $this->op_pre.$this->current_options;

			update_option($op_name, $this->loaded_options[$this->current_options]);

			return true;
		}

	// Testing  debug functions
	public function _print($op_tab){
		if(!isset($this->loaded_options[$op_tab])) return false;
		print_r($this->loaded_options[$op_tab]);
	}
	public function _get_loaded_op_tabs(){
		if(count($this->loaded_options)==0) return false;
		return array_keys( $this->loaded_options);
	}
}