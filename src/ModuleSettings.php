<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @license    New BSD License
 */

namespace GemsRandomizer;

use Gems\Modules\ModuleSettingsAbstract;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class ModuleSettings extends ModuleSettingsAbstract
{
    /**
     * @var string
     */
    public static $moduleName = 'GemsRandomizer';

    /**
     * @var string
     */
    public static $eventSubscriber = ModuleSubscriber::class;
}
