<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Composer\Installers;

class ElggInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'mod/{$name}/');
}
