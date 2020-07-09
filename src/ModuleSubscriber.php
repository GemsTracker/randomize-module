<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @subpackage Module
 * @license    Not licensed, do not copy
 */

namespace GemsRandomizer;

use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\NamedArrayEvent;
use Gems\Event\Application\SetFrontControllerDirectory;
use Gems\Event\Application\TranslatableNamedArrayEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @since      Class available since version 1.8.8
 */
class ModuleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            GetDatabasePaths::NAME => [
                ['getDatabasePaths'],
            ],
            'gems.tracker.fielddependencies.get' => [
                ['getFieldDependencies'],
            ],
            'gems.tracker.fieldtypes.get' => [
                ['getFieldTypes'],
            ],
            MenuAdd::NAME => [
                ['addToMenu']
            ],
            SetFrontControllerDirectory::NAME => [
                ['setFrontControllerDirectory'],
            ], // */
        ];
    }

    public function addToMenu(MenuAdd $event)
    {
        $menu = $event->getMenu();
        $translateAdapter = $event->getTranslatorAdapter();

        $prevMenu = $menu->findController('condition');
        if ($prevMenu) {
            $contMenu = $prevMenu->getParent();

            $contMenu->addBrowsePage($translateAdapter->_('Block randomization'), 'pr.randomizations', 'randomization', ['order' => $prevMenu->get('order') + 4]);
        }
    }


    public function getDatabasePaths(GetDatabasePaths $event)
    {
        $path = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'db';
        $event->addPath(ModuleSettings::$moduleName, $path);
    }

    public function getFieldDependencies(NamedArrayEvent $event)
    {
        $dependencies = [
            'randomization' => 'RandomizerDependency',
        ];

        $event->addItems($dependencies);
    }

    public function getFieldTypes(TranslatableNamedArrayEvent $event)
    {
        $translateAdapter = $event->getTranslatorAdapter();
        $fieldTypes = [
            'randomization' => $translateAdapter->_('Randomization'),
        ];

        $event->addItems($fieldTypes);
    }

    public function setFrontControllerDirectory(SetFrontControllerDirectory $event)
    {
        $applicationPath = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'controllers';
        $event->setControllerDirIfControllerExists($applicationPath);
    }
}
