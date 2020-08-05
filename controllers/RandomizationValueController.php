<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

use GemsRandomizer\Controller\RandomizationControllerAbstract;
use GemsRandomizer\Model\RandomizationValueModel;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationValueController extends RandomizationControllerAbstract
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
            'grv_value_label' => SORT_ASC,
        ],
    ];

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    public $cacheTags = ['randomvalues'];

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
    protected $defaultParameters = ['randomizationStep' => 'values'];

    /**
     * @inheritDoc
     */
    protected function createModel($detailed, $action)
    {
        return $this->randomUtil->createValueModel($detailed, $action);
    }
    
    /**
     * Helper function to get the title for the index action.
     *
     * @return $string
     */
    public function getIndexTitle()
    {
        return $this->_('Randomization values');
    }

    /**
     * Helper function to allow generalized statements about the items in the model.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
        return $this->plural('randomization value', 'randomization values', $count);
    }
}