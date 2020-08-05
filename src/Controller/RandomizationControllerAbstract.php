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

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
abstract class RandomizationControllerAbstract extends \Gems_Controller_ModelSnippetActionAbstract
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
    protected $createEditSnippets = ['ModelFormSnippetGeneric', 'Randomizer\\AddRandomizerInformation'];

    /**
     * The snippets used for the delete action.
     *
     * @var mixed String or array of snippets name
     */
    protected $deleteSnippets = ['ModelItemYesNoDeleteSnippetGeneric', 'Randomizer\\AddRandomizerInformation'];

    /**
     * The snippets used for the index action, after those in autofilter
     *
     * @var mixed String or array of snippets name
     */
    protected $indexStopSnippets = ['Generic\\CurrentSiblingsButtonRowSnippet', 'Randomizer\\AddRandomizerInformation'];

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    public $randomUtil;

    /**
     * The snippets used for the show action
     *
     * @var mixed String or array of snippets name
     */
    protected $showSnippets = ['Generic\\ContentTitleSnippet', 'ModelItemTableSnippetGeneric', 'Randomizer\\AddRandomizerInformation'];
}