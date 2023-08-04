<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Steps\Ready,
	Barn2\WQM_Lib\Util as Lib_Util;

/**
 * Completed Step.
 *
 * @package   Barn2/woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Completed extends Ready {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'woocommerce-quantity-manager' ) );
		$this->set_title( esc_html__( 'Complete Setup', 'woocommerce-quantity-manager' ) );
		$this->set_description( sprintf(
			__( 'Congratulations, you have finished setting up the plugin! Your global quantity rules will start working straight away. You can also add quantity rules for specific %1$s and %2$s.', 'woocommerce-quantity-manager' ),
			Lib_Util::format_link( admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ), __( 'categories', 'woocommerce-quantity-manager' ), true ),
			Lib_Util::format_link( admin_url( 'edit.php?post_type=product' ), __( 'products', 'woocommerce-quantity-manager' ), true ),
		) );
	}

}
