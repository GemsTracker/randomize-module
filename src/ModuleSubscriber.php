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
use Gems\Event\Application\TrackFieldDependencyListEvent;
use Gems\Event\Application\TrackFieldsListEvent;
use Gems\Menu\HandlerMenuTrait;
use GemsRandomizer\Handlers\RandomizationAssignmentHandler;
use GemsRandomizer\Handlers\RandomizationStudyHandler;
use GemsRandomizer\Handlers\RandomizationValueHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function __construct(
        private readonly TranslatorInterface $translator
    )
    {
        $this->translate = $this->translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TrackFieldDependencyListEvent::class => [
                'getFieldDependencies',
            ],
            TrackFieldsListEvent::class => [
                'getFieldTypes',
            ],
            CreateMenuEvent::class => [
                'updateMenu',
            ],
        ];
    }

    /**
     * @param CreateMenuEvent $event
     */
    public function updateMenu(CreateMenuEvent $event): void
    {
        $menu = $event->getMenu();

        $menuConfig = [
            $this->createMenuItem(
                // controllerClass: RandomizationHandler::class,
                name: 'track-builder.randomization.index',
                label: $this->translator->_('Block randomization'),
                type: 'container',
                parent: 'track-builder',
            ),
            $this->createMenuForHandler(
                controllerClass: RandomizationStudyHandler::class,
                name: 'track-builder.randomization.studies',
                label: $this->translator->_('Studies'),
                parent: 'track-builder.randomization.index',
            ),
            [
                'name' => 'track-builder.randomization.strata.index',
                'label' => $this->translator->trans('Conditions'),
                'type' => 'route-link-item',
                'parent' => 'track-builder.randomization.index',
                'children' => [
                    [
                        'name' => 'track-builder.randomization.strata.create',
                        'label' => $this->translator->trans('Create'),
                        'type' => 'route-link-item',
                    ],
                    [
                        'name' => 'track-builder.randomization.strata.show',
                        'label' => $this->translator->trans('Show'),
                        'type' => 'route-link-item',
                        'children' => [
                            [
                                'name' => 'track-builder.randomization.strata.edit',
                                'label' => $this->translator->trans('Edit'),
                                'type' => 'route-link-item',
                            ],
                            [
                                'name' => 'track-builder.randomization.strata.delete',
                                'label' => $this->translator->trans('Delete'),
                                'type' => 'route-link-item',
                            ],
                        ],
                    ],
                ],
            ],
            $this->createMenuForHandler(
                controllerClass: RandomizationValueHandler::class,
                name: 'track-builder.randomization.values',
                label: $this->translator->_('Values'),
                parent: 'track-builder.randomization.index',
            ),
            $this->createMenuForHandler(
                controllerClass: RandomizationAssignmentHandler::class,
                name: 'track-builder.randomization.assignments',
                label: $this->translator->_('Assignments'),
                parent: 'track-builder.randomization.index',
            ),
        ];

        $menu->addFromConfig($menu, $menuConfig);

        // See randomization outcome
        // $menu->addHiddenPrivilege('prr.assignments.seeresult', $this->translator->_(
        //     'Grant right to see the outcome of a randomization.'
        // ));
    }

    /**
     * @param TrackFieldDependencyListEvent $event
     */
    public function getFieldDependencies(TrackFieldDependencyListEvent $event): void
    {
        $event->addItems([
            'randomization' => 'RandomizerDependency',
        ]);
    }

    /**
     * @param TrackFieldsListEvent $event
     */
    public function getFieldTypes(TrackFieldsListEvent $event): void
    {
        $event->addItems([
            'randomization' => $this->translator->_('Randomization'),
        ]);
    }
}
