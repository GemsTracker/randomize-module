<?php

declare(strict_types=1);

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Handlers;

use Gems\Handlers\BrowseChangeHandler;
use Gems\Model\MetaModelLoader;
use Gems\SnippetsActions\Browse\BrowseSearchAction;
use GemsRandomizer\Snippets\Randomizer\AddRandomizerInformation;
use GemsRandomizer\Util\RandomUtil;
use Psr\Cache\CacheItemPoolInterface;
use Zalt\Base\TranslatorInterface;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\SnippetsLoader\SnippetResponderInterface;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
abstract class RandomizationHandlerAbstract extends BrowseChangeHandler
{
    protected $model;

    /**
     * The parameters used for the autofilter action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialisation
     */
    protected $autofilterParameters = [
        'extraSort' => [
            'grs_study_name' => SORT_ASC,
        ],
    ];

    // protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    // {
    //     if (!$this->model) {
    //         $this->model = $this->createModel(false, $action);
    //     }
    //     return $this->model;
    // }

    public function __construct(
        SnippetResponderInterface $responder,
        MetaModelLoader $metaModelLoader,
        TranslatorInterface $translate,
        CacheItemPoolInterface $cache,
        protected readonly RandomUtil $randomUtil,
    )
    {
        parent::__construct($responder, $metaModelLoader, $translate, $cache);
    }

    public function prepareAction(SnippetActionInterface $action) : void
    {
        parent::prepareAction($action);

        if ($action instanceof BrowseSearchAction) {
            $action->appendStopSnippet(AddRandomizerInformation::class);
        }
    }

}