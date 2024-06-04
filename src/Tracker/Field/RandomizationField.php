<?php
                
/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\FIeld
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Tracker\Field;

use Gems\Condition\TrackConditionInterface;
use Gems\Model;
use Gems\Tracker\Field\FieldAbstract;
use Zalt\Html\Html;

/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\FIeld
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomizationField extends FieldAbstract
{
    /**
     * @var \Gems\User\User
     */
    protected $currentUser;
    
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var \Gems\Loader
     */
    protected $loader;

    /**
     * @var \Gems\Menu\Menu
     */
    protected $menu;

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;
    
    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     * @var \Gems\Tracker
     */
    protected $tracker;

    /**
     * Add the model settings like the elementClass for this field.
     *
     * elementClass is overwritten when this field is read only, unless you override it again in getDataModelSettings()
     *
     * @param array $settings The settings set so far
     */
    protected function addModelSettings(array &$settings): void
    {
        $settings['elementClass']   = 'Exhibitor';
        $settings['formatFunction'] = array($this, 'showRandomization');
    }

    /**
     * Calculation the field info display for this type
     *
     * @param array $currentValue The current value
     * @param array $fieldData The other values loaded so far
     * @return mixed the new value
     */
    public function calculateFieldInfo($currentValue, array $fieldData): mixed
    {
        if ($currentValue) {
            $assignment = $this->randomUtil->getRandomAssignment($currentValue);

            if ($assignment && $assignment->exists) {
                return $assignment->getValueLabel();
            }
        }

        return $currentValue;
    }

    /**
     * Calculate the field value using the current values
     *
     * @param array $currentValue The current value
     * @param array $fieldData The other known field values
     * @param array $trackData The currently available track data (track id may be empty)
     * @return mixed the new value
     */
    public function calculateFieldValue($currentValue, array $fieldData, array $trackData): mixed
    {
        // \MUtil_Echo::track($this->fieldDefinition, $fieldData, $trackData);
        if ($currentValue) {
            return $currentValue;
        }

        $conditions = $this->loader->getConditions();
        $respTrack  = $this->tracker->getRespondentTrack($trackData['gr2t_id_respondent_track']); // Request on track id, otherwise the data is reloaded from the db
        $study      = $this->fieldDefinition['gtf_calculate_using'];

        $sql1 = "SELECT grb_condition
                    FROM gemsrnd__randomization_blocks
                    WHERE grb_use_max > grb_use_count AND grb_active = 1 AND grb_study_id = ?
                    GROUP BY grb_condition
                    ORDER BY MIN(grb_value_order) ASC";

        // \MUtil_Echo::track($study, $sql1);
        $condIds = $this->db->fetchCol($sql1, [$study]);
        // \MUtil_Echo::track(count($condIds));
        if (! $condIds) {
            return null;
        }

        $outputCondition = false;
        foreach ($condIds as $condId) {
            $condition = $conditions->loadCondition($condId);
            if ($condition instanceof TrackConditionInterface) {
                // \MUtil_Echo::track($condition->getName());
                if ($condition->isTrackValid($respTrack, $fieldData)) {
                    $outputCondition = $condId;
                    break;
                }
            }
        }
        IF (! $outputCondition) {
            return null;
        }

        $sql2 = "SELECT grb_block_id, grb_use_count
                    FROM gemsrnd__randomization_blocks
                    WHERE (grb_use_max > grb_use_count OR grb_use_max = 0) AND grb_active = 1 AND 
                          grb_condition = ? AND grb_study_id = ?
                    ORDER BY grb_value_order";
        // \MUtil_Echo::track($outputCondition, $sql2);
        $block = $this->db->fetchRow($sql2, [$outputCondition, $study]);

        if (! $block) {
            return null;
        }

        $this->db->update('gemsrnd__randomization_blocks', ['grb_use_count' => $block['grb_use_count'] + 1], [
            'grb_block_id = ?' => $block['grb_block_id'],
            'grb_study_id = ?' => $study
            ]);

        return $block['grb_block_id'];
    }

    /**
     * Dispaly an appoitment as text
     *
     * @param mixed $value
     * @return string
     */
    public function showRandomization($value)
    {
        if ($value) {
            if (! $this->currentUser->hasPrivilege('prr.assignments.seeresult')) {
                return '******';
            }
            $assignment = $this->randomUtil->getRandomAssignment($value);

            if ($assignment && $assignment->exists) {
                $showItem = $this->menu->findAllowedController('randomization', 'show');
                if ($showItem) {
                    if (! $this->request) {
                        $this->request = \Zend_Controller_Front::getInstance()->getRequest();
                    }
                    $href = $showItem->toHRefAttribute(
                        [Model::REQUEST_ID => $assignment->getBlockId()],
                        $this->request
                    );
                    if ($href) {
                        return Html::create('a', $href, $assignment->getValueLabel());
                    }
                } 
                return $assignment->getValueLabel();
            }
        }

        return $value;
    }
}