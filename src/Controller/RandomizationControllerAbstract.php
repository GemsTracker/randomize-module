<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Controller;

use Gems\Snippets\Generic\ContentTitleSnippet;
use Gems\Snippets\Generic\CurrentSiblingsButtonRowSnippet;
use Gems\Snippets\ModelConfirmDeleteSnippet;
use Gems\Snippets\ModelDetailTableSnippet;
use Gems\Snippets\ModelFormSnippetAbstract;
use GemsRandomizer\Snippets\Randomizer\AddRandomizerInformation;
use MUtil\Controller\ModelSnippetActionAbstract;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
abstract class RandomizationControllerAbstract extends ModelSnippetActionAbstract
{
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

    /**
     * The snippets used for the create and edit actions.
     *
     * @var mixed String or array of snippets name
     */
    protected $createEditSnippets = [
        ModelFormSnippetAbstract::class,
        AddRandomizerInformation::class,
    ];

    /**
     * The snippets used for the delete action.
     *
     * @var mixed String or array of snippets name
     */
    protected $deleteSnippets = [
        ModelConfirmDeleteSnippet::class,
        AddRandomizerInformation::class,
    ];

    /**
     * The snippets used for the index action, after those in autofilter
     *
     * @var mixed String or array of snippets name
     */
    protected $indexStopSnippets = [
        CurrentSiblingsButtonRowSnippet::class,
        AddRandomizerInformation::class,
    ];

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    public $randomUtil;

    /**
     * The snippets used for the show action
     *
     * @var mixed String or array of snippets name
     */
    protected $showSnippets = [
        ContentTitleSnippet::class,
        ModelDetailTableSnippet::class,
        AddRandomizerInformation::class,
    ];
}