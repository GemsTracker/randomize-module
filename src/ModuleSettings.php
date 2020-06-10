<?php


namespace GemsRandomizer;


use Gems\Modules\ModuleSettingsAbstract;

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
