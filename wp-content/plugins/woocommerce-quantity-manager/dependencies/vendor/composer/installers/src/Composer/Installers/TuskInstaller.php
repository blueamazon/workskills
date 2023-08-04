<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Composer\Installers;

/**
 * Composer installer for 3rd party Tusk utilities
 * @author Drew Ewing <drew@phenocode.com>
 */
class TuskInstaller extends BaseInstaller
{
    protected $locations = array('task' => '.tusk/tasks/{$name}/', 'command' => '.tusk/commands/{$name}/', 'asset' => 'assets/tusk/{$name}/');
}
