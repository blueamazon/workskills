<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Composer\Installers;

/**
 * Class DolibarrInstaller
 *
 * @package Composer\Installers
 * @author  Raphaël Doursenaud <rdoursenaud@gpcsolutions.fr>
 */
class DolibarrInstaller extends BaseInstaller
{
    //TODO: Add support for scripts and themes
    protected $locations = array('module' => 'htdocs/custom/{$name}/');
}
