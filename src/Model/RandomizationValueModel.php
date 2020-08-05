<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Model;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationValueModel extends \Gems_Model_JoinModel
{
    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;
    
    /**
     * @var \Gems_Util
     */
    protected $util;

    /**
     * Create a model that joins two or more tables
     */
    public function __construct()
    {
        parent::__construct('gemsrnd__randomization_values', 'gemsrnd__randomization_values', 'grv', true);
    }

    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return BlockRandomizationModel
     */
    public function applySettings($detailed, $action)
    {
        if (! $detailed) {
            $this->addLeftTable('gemsrnd__randomization_studies', ['grv_study_id' => 'grs_study_id'], 'grs', false);
        }
        $this->resetOrder();

        if ($detailed) {
            $this->set('grv_study_id', 'label', $this->_('Study name'),
                       'description', $this->_('The study name is used to group blocks.'),
                       'multiOptions', $this->randomUtil->getRandomStudies()
            );
        } else {
            $this->set('grs_study_name', 'label', $this->_('Study name'),
                       'description', $this->_('The study name is used to group blocks.')
            );
        }
        $this->set('grv_value', 'label', $this->_('Randomization export value'),
                   'description', $this->_('The outcome value assigned to a randomization, used for export.'),
                   'validators[unique]', $this->createUniqueValidator(['grv_study_id', 'grv_value'], ['grv_study_id']));
        $this->set('grv_value_label', 'label', $this->_('Randomization value label'),
                   'description', $this->_('The outcome label shown in the field.'));

        if (($action !== 'create') && ($action !== 'import')) {
            // SUM columns
            $sql = "(SELECT COALESCE(SUM(%s), 0)  
                        FROM gemsrnd__randomization_blocks
                        WHERE grb_active = 1 AND grb_value_id = grv_value_id)";

            $this->addColumn(new \Zend_Db_Expr(sprintf($sql, "grb_use_count")), 'used');
            $this->set('used', 'label', $this->_('Used'), 'elementClass', 'Exhibitor');

            $this->addColumn(new \Zend_Db_Expr(sprintf($sql, "grb_use_max - grb_use_count")), 'free');
            $this->set('free', 'label', $this->_('Unused'), 'elementClass', 'Exhibitor');

            $this->addColumn(new \Zend_Db_Expr(sprintf($sql, "grb_use_max")), 'total');
            $this->set('total', 'label', $this->_('Total'), 'elementClass', 'Exhibitor');
        }
        
        return $this;
    }
}