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
            'id'          => 'grb_value_id',
            'stratum'     => 'grb_condition',
            'value'       => 'grb_value',
            'study'       => 'grb_study_name',
            'order'       => 'grb_value_order',
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
        $cond = $row['stratum'];
        // Create condition if new
        if ($cond && (! (isset($this->_conditionIds[$cond]) || in_array($cond, $this->_conditionIds)))) {
            $classes = $this->loader->getConditions()->listConditionsForType(Conditions::TRACK_CONDITION);
            unset($classes[""]);
            reset($classes);

            // \MUtil_Echo::track($classes);
            $cModel = $this->loader->getModels()->getConditionModel();
            $values = [
                'gcon_type'   => Conditions::TRACK_CONDITION,
                'gcon_class'  => key($classes),
                'gcon_name'   => $cond,
                'gcon_active' => 0,
                ];

            $result = $cModel->save($values);

            $this->_conditionIds[$result['gcon_id']] = $cond;
            $this->addMultiOption('grb_condition', $result['gcon_id'], $cond);
        }

        $row = parent::translateRowValues($row, $key);

        if (!$row) {
            return false;
        }

        // \MUtil_Echo::track($row);

        return $row;
    }
}