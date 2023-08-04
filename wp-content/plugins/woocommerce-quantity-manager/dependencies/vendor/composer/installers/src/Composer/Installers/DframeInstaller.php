<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Composer\Installers;

class DframeInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$vendor}/{$name}/');
}
