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

use Gems\Event\Application\CreateMenuEvent;
use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\LoaderInitEvent;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\MenuBuildItemsEvent;
use Gems\Event\Application\ModelCreateEvent;
use Gems\Event\Application\NamedArrayEvent;
use Gems\Event\Application\SetFrontControllerDirectory;
use Gems\Event\Application\TrackFieldDependencyListEvent;
use Gems\Event\Application\TrackFieldsListEvent;
use Gems\Event\Application\TranslatableNamedArrayEvent;
use Gems\Event\Application\ZendTranslateEvent;
use Gems\Handlers\EmptyHandler;
use Gems\Menu\HandlerMenuTrait;
use GemsRandomizer\Handlers\RandomizationAssignmentHandler;
use GemsRandomizer\Handlers\RandomizationHandler;
use GemsRandomizer\Handlers\RandomizationStrataHandler;
use GemsRandomizer\Handlers\RandomizationStudyHandler;
use GemsRandomizer\Handlers\RandomizationValueHandler;
use GemsRandomizer\Util\RandomUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zalt\Base\TranslateableTrait;
use Zalt\Base\TranslatorInterface;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Module
 * @since      Class available since version 1.8.8
 */
class ModuleSubscriber implements EventSubscriberInterface
{
    use HandlerMenuTrait;
    use TranslateableTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translate = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            // GetDatabasePaths::NAME => [
            //     ['getDatabasePaths'],
            // ],
            // LoaderInitEvent::NAME => [
            //     ['initLoader'],
            // ],
            // 'gems.model.create.conditions' => [
            //     ['createConditionModel'],
            // ],
            TrackFieldDependencyListEvent::class => [
                'getFieldDependencies',
            ],
            TrackFieldsListEvent::class => [
                'getFieldTypes',
            ],
            MenuBuildItemsEvent::class => [
                'createProjectMenu',
            ],
            CreateMenuEvent::class => [
                'updateMenu',
            ],
            // SetFrontControllerDirectory::NAME => [
            //     ['setFrontControllerDirectory'],
            // ],
            // ZendTranslateEvent::NAME => [
            //     ['addTranslation'],
            // ],
        ];
    }

    public function createProjectMenu(MenuBuildItemsEvent $event)
    {
        $items = $event->getItems();
    }

    /**
     * @param CreateMenuEvent $event
     */
    public function updateMenu(CreateMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuConfig = [
            $this->createMenuForHandler(
                controllerClass: RandomizationHandler::class,
                name: 'track-builder.randomization',
                label: $this->_('Block randomization'),
                parent: 'track-builder',
            ),
            $this->createMenuForHandler(
                controllerClass: RandomizationAssignmentHandler::class,
                name: 'track-builder.randomization.assignments',
                label: $this->_('Assignments'),
                parent: 'track-builder.randomization.index',
            ),
            $this->createMenuForHandler(
                controllerClass: RandomizationStudyHandler::class,
                name: 'track-builder.randomization.studies',
                label: $this->_('Studies'),
                parent: 'track-builder.randomization.index',
            ),
            [
                'name' => 'track-builder.randomization.strata.index',
                'label' => $this->translate->trans('Conditions'),
                'type' => 'route-link-item',
                'parent' => 'track-builder.randomization.index',
                'children' => [
                    [
                        'name' => 'track-builder.randomization.strata.create',
                        'label' => $this->translate->trans('Create'),
                        'type' => 'route-link-item',
                    ],
                    [
                        'name' => 'track-builder.randomization.strata.show',
                        'label' => $this->translate->trans('Show'),
                        'type' => 'route-link-item',
                        'children' => [
                            [
                                'name' => 'track-builder.randomization.strata.edit',
                                'label' => $this->translate->trans('Edit'),
                                'type' => 'route-link-item',
                            ],
                            [
                                'name' => 'track-builder.randomization.strata.delete',
                                'label' => $this->translate->trans('Delete'),
                                'type' => 'route-link-item',
                            ],
                        ],
                    ],
                ],
            ],
            $this->createMenuForHandler(
                controllerClass: RandomizationValueHandler::class,
                name: 'track-builder.randomization.values',
                label: $this->_('Values'),
                parent: 'track-builder.randomization.index',
            ),
        ];

        // $blockMenu = $contMenu->addContainer($translateAdapter->_('Block randomization'), null, ['order' => $prevMenu->get('order') + 4]);

        // $blockMenu->addBrowsePage($translateAdapter->_('Studies'), 'prr.studies', 'randomization-study')
        //     ->addAction($translateAdapter->_('Reset study'), 'prr.studies.reset', 'reset');
        // $blockMenu->addBrowsePage($translateAdapter->_('Strata'), 'prr.strata', 'randomization-strata');
        // $blockMenu->addBrowsePage($translateAdapter->_('Values'), 'prr.values', 'randomization-value');
        // $blockMenu->addBrowsePage($translateAdapter->_('Assignments'), 'prr.assignments', 'randomization');
 
        $menu->addFromConfig($menu, $menuConfig);

        // See randomization outcome
        // $menu->addHiddenPrivilege('prr.assignments.seeresult', $this->translate->_(
        //     'Grant right to see the outcome of a randomization.'
        // ));
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
     * @param TrackFieldDependencyListEvent $event
     */
    public function getFieldDependencies(TrackFieldDependencyListEvent $event)
    {
        $event->addItems([
            'randomization' => 'RandomizerDependency',
        ]);
    }

    /**
     * @param TrackFieldsListEvent $event
     */
    public function getFieldTypes(TrackFieldsListEvent $event)
    {
        $event->addItems([
            'randomization' => $this->_('Randomization'),
        ]);
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
