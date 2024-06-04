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

use Gems\Condition\ConditionLoader;
use Gems\Handlers\TrackBuilder\ConditionHandler;
use Gems\Model\ConditionModel;
use Gems\Snippets\Condition\ConditionAndOrTableSnippet;
use Gems\Snippets\Generic\ContentTitleSnippet;
use Gems\Snippets\Generic\CurrentButtonRowSnippet;
use Gems\Snippets\ModelDetailTableSnippet;
use Gems\Snippets\ModelFormSnippet;
use GemsRandomizer\Snippets\Randomizer\AddRandomizerInformation;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationStrataHandler extends ConditionHandler
{
    /**
     * The snippets used for the create and edit actions.
     *
     * @var array String or array of snippets name
     */
    protected array $createEditSnippets = [
        ModelFormSnippet::class,
        AddRandomizerInformation::class,
    ];

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
    protected array $defaultParameters = ['randomizationStep' => 'strata'];

    /**
     * The default search data to use.
     *
     * @var array()
     */
    protected array $defaultSearchData = ['gcon_type' => ConditionLoader::TRACK_CONDITION];

    /**
     * The snippets used for the show action
     *
     * @var array String or array of snippets name
     */
    protected array $showSnippets = [
        ContentTitleSnippet::class,
        ModelDetailTableSnippet::class,
        CurrentButtonRowSnippet::class,
        ConditionAndOrTableSnippet::class,
        AddRandomizerInformation::class,
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
     * @return ConditionModel
     */
    protected function createModel(bool $detailed, string $action): ConditionModel
    {
        $model = parent::createModel($detailed, $action);
        
        $options = $model->getMetamodel()->get('gcon_type', 'multiOptions');
        $option[ConditionLoader::TRACK_CONDITION] = $options[ConditionLoader::TRACK_CONDITION];
        $model->getMetaModel()->set('gcon_type', 'multiOptions', $option, 'default', ConditionLoader::TRACK_CONDITION);
        
        return $model;
    }
    
    /**
     * Helper function to get the title for the index action.
     *
     * @return string
     */
    public function getIndexTitle(): string
    {
        return $this->_('Strata');
    }

    /**
     * Helper function to allow generalized statements about the items in the model.
     *
     * @param int $count
     * @return string
     */
    public function getTopic($count = 1): string
    {
        return $this->plural('stratum', 'strata', $count);
    }
}