<?php

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Util;

use Gems\Util\UtilAbstract;
use GemsRandomizer\Model\RandomizationStudyModel;
use GemsRandomizer\Model\RandomizationValueModel;
use GemsRandomizer\Tracker\RandomizationAssignment;

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomUtil extends UtilAbstract
{
    /**
     * @var array assignmentId => GemsRandomizer\Tracker\RandomizationAssignment
     */
    private $_assignments = [];
    
    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return \GemsRandomizer\Model\RandomizationStudyModel
     */
    public function createStudyModel($detailed, $action)
    {
        $model = new RandomizationStudyModel();
        $this->source->applySource($model);

        $model->applySettings($detailed, $action);

        return $model;
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
     * @return \GemsRandomizer\Model\RandomizationValueModel
     */
    public function createValueModel($detailed, $action)
    {
        $model = new RandomizationValueModel();
        $this->source->applySource($model);

        $model->applySettings($detailed, $action);

        return $model;
    }

    /**
     * @param array|string $blockData
     * @return \GemsRandomizer\Tracker\RandomizationAssignment                 
     */
    public function getRandomAssignment($blockData)
    {
        if (is_array($blockData)) {
            if (! isset($blockData['grb_block_id'])) {
                return null;
            }
            $blockId = $blockData['grb_block_id'];
        } else {
            $blockId = $blockData;
        }
        if (! isset($this->_assignments[$blockId])) {
            $this->_assignments[$blockId] = new RandomizationAssignment($blockData, $this->db);
        }
        
        return $this->_assignments[$blockId];    
    }
        
    /**
     * @return array study => study with description
     */
    public function getRandomStudies()
    {
        $sql = "SELECT grs_study_id, grs_study_name FROM gemsrnd__randomization_studies ORDER BY grs_study_name;";

        return $this->_getSelectPairsCached(__FUNCTION__, $sql, [], ['randomstudies']);
    }

    /**
     * @param int|null $studyId
     * @return array valueId => label
     */
    public function getRandomExportValues($studyId = null)
    {
        $sql = "SELECT grv_value_id, grv_value FROM gemsrnd__randomization_values";
        if ($studyId) {
            $sql .= " WHERE grv_study_id = ?";
        }
        $sql .= " ORDER BY grv_value;";

        return $this->_getSelectPairsCached(__FUNCTION__, $sql, $studyId, ['randomvalues']);
    }
    
    /**
     * @param int|null $studyId
     * @return array valueId => label
     */
    public function getRandomValues($studyId = null)
    {
        $sql = "SELECT grv_value_id, grv_value_label FROM gemsrnd__randomization_values";
        if ($studyId) {
            $sql .= " WHERE grv_study_id = ?";
        }
        $sql .= " ORDER BY grv_value_label;";
        
        return $this->_getSelectPairsCached(__FUNCTION__, $sql, $studyId, ['randomvalues']);
    }
}