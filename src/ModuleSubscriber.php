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

use GemsRandomizer\Util\RandomUtil;
use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\LoaderInitEvent;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\ModelCreateEvent;
use Gems\Event\Application\NamedArrayEvent;
use Gems\Event\Application\SetFrontControllerDirectory;
use Gems\Event\Application\TranslatableNamedArrayEvent;
use Gems\Event\Application\ZendTranslateEvent;
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
            LoaderInitEvent::NAME => [
                ['initLoader'],
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
            ],
            ZendTranslateEvent::NAME => [
                ['addTranslation'],
            ],
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

            if ($contMenu instanceof \Gems_Menu_MenuAbstract) {
                $blockMenu = $contMenu->addContainer($translateAdapter->_('Block randomization'), null, ['order' => $prevMenu->get('order') + 4]);

                $blockMenu->addBrowsePage($translateAdapter->_('Studies'), 'prr.studies', 'randomization-study');
                $blockMenu->addBrowsePage($translateAdapter->_('Strata'), 'prr.strata', 'randomization-strata');
                $blockMenu->addBrowsePage($translateAdapter->_('Values'), 'prr.values', 'randomization-value');
                $blockMenu->addBrowsePage($translateAdapter->_('Assignments'), 'prr.assignments', 'randomization');
                
                
                $menu->addHiddenPrivilege('prr.assignments.seeresult', $translateAdapter->_(
                    'Grant right to see the outcome of a randomization.'
                ));
            }
        }
    }

    /**
     * @param \Gems\Event\Application\ZendTranslateEvent $event
     * @throws \Zend_Translate_Exception
     */
    public function addTranslation(ZendTranslateEvent $event)
    {
        $event->addTranslationByDirectory(ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'languages');
    }

    /**
     * @param \Gems\Event\Application\ModelCreateEvent $event
     */
    public function createConditionModel(ModelCreateEvent $event)
    {
        $model = $event->getModel();

        $snippets = $model->getMeta('ConditionShowSnippets', []);
//        $snippets[] = 'Agenda\\ApplyFiltersInformation';
//        $model->setMeta('ConditionShowSnippets', $snippets);
    }

    /**
     * @param \Gems\Event\Application\GetDatabasePaths $event
     */
    public function getDatabasePaths(GetDatabasePaths $event)
    {
        $path = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'db';
        $event->addPath(ModuleSettings::$moduleName, $path);
    }

    /**
     * @param \Gems\Event\Application\GetDatabasePaths $event
     */
    public function getFieldDependencies(NamedArrayEvent $event)
    {
        $dependencies = [
            'randomization' => 'RandomizerDependency',
        ];

        $event->addItems($dependencies);
    }

    /**
     * @param \Gems\Event\Application\TranslatableNamedArrayEvent $event
     */
    public function getFieldTypes(TranslatableNamedArrayEvent $event)
    {
        $translateAdapter = $event->getTranslatorAdapter();
        $fieldTypes = [
            'randomization' => $translateAdapter->_('Randomization'),
        ];

        $event->addItems($fieldTypes);
    }

    /**
     * @param \Gems\Event\Application\LoaderInitEvent $event
     */
    public function initLoader(LoaderInitEvent $event)
    {
        $event->addByName(new RandomUtil(), 'randomUtil');
    }

    /**
     * @param \Gems\Event\Application\SetFrontControllerDirectory $event
     */
    public function setFrontControllerDirectory(SetFrontControllerDirectory $event)
    {
        $applicationPath = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'controllers';
        $event->setControllerDirIfControllerExists($applicationPath);
    }
}
