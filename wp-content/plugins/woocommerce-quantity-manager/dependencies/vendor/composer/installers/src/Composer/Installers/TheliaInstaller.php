<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Dependencies\Composer\Installers;

class TheliaInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'local/modules/{$name}/', 'frontoffice-template' => 'templates/frontOffice/{$name}/', 'backoffice-template' => 'templates/backOffice/{$name}/', 'email-template' => 'templates/email/{$name}/');
}
