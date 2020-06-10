<?php


namespace GemsRandomizer;

use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\SetFrontControllerDirectory;
use Gems\Event\Application\TranslatableNamedArrayEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModuleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            GetDatabasePaths::NAME => [
                ['getDatabasePaths'],
            ],
            /*'gems.tracker.fieldtypes.get' => [
                ['getFieldTypes'],
            ], // */
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
        $contMenu = $prevMenu->getParent();

        $contMenu->addBrowsePage($label, 'prr.randomizationblocks', 'randomization', ['order' => $prevMenu->get('order') + 4]);

        $menu->addPage($translateAdapter->_('Sample Module Test'), null, 'module-test', 'index');
    }


    public function getDatabasePaths(GetDatabasePaths $event)
    {
        $path = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'db';
        $event->addPath(ModuleSettings::$moduleName, $path);
    }

    /*public function getFieldTypes(TranslatableNamedArrayEvent $event)
    {
        $translateAdapter = $event->getTranslatorAdapter();
        $fieldTypes = [
            'fieldName' => $translateAdapter->_('Field Label'),
        ];

        $event->addItems($fieldTypes);
    }*/

    public function setFrontControllerDirectory(SetFrontControllerDirectory $event)
    {
        $applicationPath = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'controllers';
        $event->setControllerDirIfControllerExists($applicationPath);
    }
}
