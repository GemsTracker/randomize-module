<?php

declare(strict_types=1);

/**
 * @package    GemsRandomizer
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer;

use Gems\Route\ModelSnippetActionRouteHelpers;
use Gems\Util\RouteGroupTrait;
use GemsRandomizer\Handlers\RandomizationAssignmentHandler;
use GemsRandomizer\Handlers\RandomizationHandler;
use GemsRandomizer\Handlers\RandomizationStrataHandler;
use GemsRandomizer\Handlers\RandomizationStudyHandler;
use GemsRandomizer\Handlers\RandomizationValueHandler;

/**
 * @package    GemsRandomizer
 * @since      Class available since version 2.0
 */
class ConfigProvider
{
    use ModelSnippetActionRouteHelpers;
    use RouteGroupTrait;

    public function __invoke(): array
    {
        return [
            'overLoaderPaths'  => ['GemsRandomizer'],
            'migrations'   => $this->getMigrations(),
            'routes'       => $this->getRoutes(),
        ];
    }

    public function getRoutes()
    {
        return [
            ...$this->routeGroup([
                'middleware' => \Gems\Config\Route::$loggedInMiddleware,
                ],
                [
                    ...$this->createHandlerRoute(baseName: 'track-builder.randomization', controllerClass: RandomizationHandler::class),
                    ...$this->createHandlerRoute(baseName: 'track-builder.randomization.assignments',  controllerClass: RandomizationAssignmentHandler::class),
                    ...$this->createHandlerRoute(baseName: 'track-builder.randomization.studies',  controllerClass: RandomizationStudyHandler::class),
                    ...$this->createSnippetRoutes(baseName: 'track-builder.randomization.strata', controllerClass: RandomizationStrataHandler::class),
                    ...$this->createHandlerRoute(baseName: 'track-builder.randomization.values',  controllerClass: RandomizationValueHandler::class),
                ]
            ),
        ];
    }

    protected function getMigrations(): array
    {
        return [
            'tables' => [
                __DIR__ . '/../configs/db/tables',
            ],
            /*'seeds' => [
                __DIR__ . '/../configs/db/seeds',
            ],
            'patches' => [
                __DIR__ . '/../configs/db/patches',
            ],*/
        ];
    }

}
