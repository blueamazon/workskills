<?php

/**
 * @package   Barn2\setup-wizard
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Barn2\Setup_Wizard\Interfaces;

interface Restartable
{
    /**
     * Send data back to the react app when the wizard is restarted.
     *
     * @return void
     */
    public function on_restart();
}
