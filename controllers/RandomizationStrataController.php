<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

use Gems\Conditions;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationStrataController extends \Gems_Default_ConditionAction
{
    /**
     * The snippets used for the create and edit actions.
     *
     * @var mixed String or array of snippets name
     */
    protected $createEditSnippets = ['ModelFormSnippetGeneric', 'Randomizer\\AddRandomizerInformation'];

    /**
     * Model level parameters used for all actions, overruled by any values set in any other
     * parameters array except the private $_defaultParamters values in this module.
     *
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected $defaultParameters = ['randomizationStep' => 'strata'];

    /**
     * The default search data to use.
     *
     * @var array()
     */
    protected $defaultSearchData = ['gcon_type' => Conditions::TRACK_CONDITION];
    
    /**
     * The snippets used for the delete action.
     *
     * @var mixed String or array of snippets name
     */
    protected $deleteSnippets = ['ConditionDeleteSnippet', 'Randomizer\\AddRandomizerInformation'];

    /**
     * The snippets used for the index action, after those in autofilter
     *
     * @var mixed String or array of snippets name
     */
    protected $indexStopSnippets = ['Generic\\CurrentSiblingsButtonRowSnippet', 'Randomizer\\AddRandomizerInformation'];

    /**
     * The snippets used for the show action
     *
     * @var mixed String or array of snippets name
     */
    protected $showSnippets = [
        'Generic\\ContentTitleSnippet',
        'ModelItemTableSnippetGeneric',
        'ConditionAndOrTableSnippet',
        'Randomizer\\AddRandomizerInformation'
    ];

    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return \MUtil_Model_ModelAbstract
     */
    protected function createModel($detailed, $action)
    {
        $model = parent::createModel($detailed, $action);
        
        $options = $model->get('gcon_type', 'multiOptions');
        $option[Conditions::TRACK_CONDITION] = $options[Conditions::TRACK_CONDITION];
        $model->set('gcon_type', 'multiOptions', $option, 'default', Conditions::TRACK_CONDITION);
        
        return $model;
    }
    
    /**
     * Helper function to get the title for the index action.
     *
     * @return $string
     */
    public function getIndexTitle()
    {
        return $this->_('Strata');
    }

    /**
     * Helper function to allow generalized statements about the items in the model.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
        return $this->plural('stratum', 'strata', $count);
    }
}