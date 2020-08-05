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
class RandomizationStudyModel extends \Gems_Model_JoinModel
{
    /**
     * @var \Gems_Util
     */
    protected $util;
    
    /**
     * Create a model that joins two or more tables
     */
    public function __construct()
    {
        parent::__construct('gemsrnd__randomization_studies', 'gemsrnd__randomization_studies', 'grs', true);

        $this->addColumn(new \Zend_Db_Expr("CASE WHEN grs_active = 1 THEN '' ELSE 'DELETED' END"), 'row_class');
        $this->setDeleteValues(['grs_active' => 0]);
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
        $this->set('grs_study_name', 'label', $this->_('Study name'),
           'description', $this->_('The study name is used to group blocks.'),
            'required', true,
            'validators[unique]', $this->createUniqueValidator('grs_study_name')
        );
        $this->set('grs_active', 'label', $this->_('Active'),
                   'elementClass', 'Checkbox',
                   'multiOptions', $this->util->getTranslated()->getYesNo()
        );

        if (($action !== 'create') && ($action !== 'import')) {
            // SUM columns
            $sql = "(SELECT COALESCE(SUM(%s), 0)  
                        FROM gemsrnd__randomization_blocks
                        WHERE grb_active = 1 AND grb_study_id = grs_study_id)";

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