<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @license    Not licensed, do not copy
 */

namespace GemsRandomizer;

use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\ModelCreateEvent;
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
            'gems.model.create.conditions' => [
                ['createConditionModel'],
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

    /**
     * @param \Gems\Event\Application\MenuAdd $event
     */
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

    /**
     * @param \Gems\Event\Application\ModelCreateEvent $event
     */
    public function createConditionModel(ModelCreateEvent $event)
    {
        // \MUtil_Echo::track($event->getModel()->getName());
        // TODO Action!
        $model = $event->getModel();

        $snippets = $model->getMeta('ConditionShowSnippets', []);
//        $snippets[] = 'Agenda\\ApplyFiltersInformation';
//        $model->setMeta('ConditionShowSnippets', $snippets);
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
