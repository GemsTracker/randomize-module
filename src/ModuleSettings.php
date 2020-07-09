<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @author     mjong
 * @license    Not licensed, do not copy
 */

namespace GemsRandomizer;


use Gems\Modules\ModuleSettingsAbstract;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @since      Class available since version 1.8.8
 */
class ModuleSettings extends ModuleSettingsAbstract
{
    public static $moduleName = 'GemsRandomizer';

    public static $eventSubscriber = ModuleSubscriber::class;

    /**
     * @return string
     */
    protected static function getCurrentDir()
    {
        return __DIR__;
    }
}
