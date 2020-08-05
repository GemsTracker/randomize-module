<?php
                
/**
 *
 * @package    GemsRandomize
 * @subpackage Model\Translator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Model\Translator;

use Gems\Conditions;

/**
 *
 * @package    GemsRandomize
 * @subpackage Model\Translator
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class BlockImportTranslator extends \MUtil_Model_ModelTranslatorAbstract
{
    /**
     * @var array cond id => row
     */
    protected $_conditionIds;

    /**
     * @var array cond id => label
     */
    protected $_studyIds;

    /**
     * @var array cond id => export value
     */
    protected $_valueExportIds;

    /**
     * @var array cond id => value
     */
    protected $_valueIds;

    /**
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     *
     * @var \Gems_loader
     */
    protected $loader;

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;
    
    /**
     * Create an empty form for filtering and validation
     *
     * @return \MUtil_Form
     */
    protected function _createTargetForm()
    {
        return new \Gems_Form();
    }

    /**
     * Get information on the field translations
     *
     * @return array of fields sourceName => targetName
     * @throws \MUtil_Model_ModelException
     */
    public function getFieldsTranslations()
    {
        return [
            'study'       => 'grb_study_id',
            'stratum'     => 'grb_condition',
            'id'          => 'grb_block_id',
            'order'       => 'grb_value_order',
            'value'       => 'grb_value_id',
            'description' => 'grb_block_description',
            'info'        => 'grb_block_info',
            'active'      => 'grb_active',
            'use_count'   => 'grb_use_count',
            'use_max'     => 'grb_use_max',
            ];
    }

    /**
     * Set the target model, where the data is going to.
     *
     * @param \MUtil_Model_ModelAbstract $sourceModel The target of the data
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setTargetModel(\MUtil_Model_ModelAbstract $targetModel)
    {
        $this->_conditionIds = $targetModel->get('grb_condition', 'multiOptions');
        $this->_studyIds     = $targetModel->get('grb_study_id', 'multiOptions');
        $this->_valueIds     = $targetModel->get('grb_value_id', 'multiOptions');

        return parent::setTargetModel($targetModel);
    }

    /**
     * Perform any translations necessary for the code to work
     *
     * @param mixed $row array or \Traversable row
     * @param scalar $key
     * @return mixed Row array or false when errors occurred
     */
    public function translateRowValues($row, $key)
    {
        $study = $row['study'];
        // Create study if new
        if ($study && (! (isset($this->_studyIds[$study]) || in_array($study, $this->_studyIds)))) {
            // \MUtil_Echo::track($study, $this->_studyIds);
            $sModel  = $this->randomUtil->createStudyModel(true, 'create');
            $sValues = [
                'grs_study_name' => $study,
                'grs_active'     => 1,
                ];

            $sResult = $sModel->save($sValues);

            $this->_studyIds[$sResult['grs_study_id']] = $study;
            $this->addMultiOption('grb_study_id', $sResult['grs_study_id'], $study);

            $row['study'] = $sResult['grs_study_id'];
        }

        $cond = $row['stratum'];
        // Create condition if new
        if ($cond && (! (isset($this->_conditionIds[$cond]) || in_array($cond, $this->_conditionIds)))) {
            $classes = $this->loader->getConditions()->listConditionsForType(Conditions::TRACK_CONDITION);
            unset($classes[""]);
            reset($classes);

            // \MUtil_Echo::track($classes);
            $cModel  = $this->loader->getModels()->getConditionModel();
            $cValues = [
                'gcon_type'   => Conditions::TRACK_CONDITION,
                'gcon_class'  => key($classes),
                'gcon_name'   => $cond,
                'gcon_active' => 0,
            ];

            $cResult = $cModel->save($cValues);

            $this->_conditionIds[$cResult['gcon_id']] = $cond;
            $this->addMultiOption('grb_condition', $cResult['gcon_id'], $cond);
        }

        $val = $row['value'];
        // Check for export values instead of label values 
        if ($val && (! (isset($this->_valueIds[$val]) || in_array($val, $this->_valueIds)))) {
            if ($study) {
                $export = array_search($val, $this->randomUtil->getRandomExportValues($study));
                if (false !== $export) {
                    $val          = $export;
                    $row['value'] = $export;
                }
            }
        }
        // Create value if new
        if ($val && (! (isset($this->_valueIds[$val]) || in_array($val, $this->_valueIds)))) {
            $vModel  = $this->randomUtil->createValueModel(true, 'create');
            $vValues = [
                'grv_study_id'    => $row['study'],
                'grv_value'       => $val,
                'grv_value_label' => $val,
            ];

            $vResult = $vModel->save($vValues);

            $this->_valueIds[$val] = $val;
            $this->addMultiOption('grb_value_id', $val, $val);
        }

        $row = parent::translateRowValues($row, $key);

        if (!$row) {
            return false;
        }

        // \MUtil_Echo::track($row);

        return $row;
    }
}