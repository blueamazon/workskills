<?php
/**
 * Gutenberg Integration
 * @version  2.6.14
 */

class EVO_Gutenberg{
	function __construct(){
		if(!function_exists('register_block_type')) return false;
		add_action( 'init', array($this,'gutenberg_boilerplate_block') );
	}
	function gutenberg_boilerplate_block(){
		 wp_register_script(
	        'gutenberg-boilerplate-es5-step01',
	        EVO()->assets_path. 'js/admin/evo_gutenberg.js',
	        //plugins_url( 'step-01/block.js', __FILE__ ),
	        array( 'wp-blocks', 'wp-element' )
	    );


		register_block_type( 'gutenberg-boilerplate-es5/hello-world-step-01', array(
	        'editor_script' => 'gutenberg-boilerplate-es5-step01',
	    ) );
	   

	}
}

new EVO_Gutenberg();