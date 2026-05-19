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

use Gems\Condition\ConditionLoader;
use Gems\Condition\TrackConditionInterface;
use Gems\Db\ResultFetcher;
use Gems\Legacy\CurrentUserRepository;
use Gems\Menu\RouteHelper;
use Gems\Model;
use Gems\Tracker;
use Gems\Tracker\Field\FieldAbstract;
use Gems\Util\Translated;
use GemsRandomizer\Repository\RandomRepository;
use Zalt\Base\TranslatorInterface;
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
    public function __construct(
        int $trackId,
        string $fieldKey,
        array $fieldDefinition,
        TranslatorInterface $translator,
        Translated $translatedUtil,
        protected readonly ConditionLoader $conditionLoader,
        protected readonly ResultFetcher $resultFetcher,
        protected readonly RandomRepository $randomRepository,
        protected readonly Tracker $tracker,
        protected readonly RouteHelper $routeHelper,
        protected readonly CurrentUserRepository $currentUserRepository,
    )
    {
        parent::__construct($trackId, $fieldKey, $fieldDefinition, $translator, $translatedUtil);
    }

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
        $settings['formatFunction'] = [$this, 'showRandomization'];
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
            $assignment = $this->randomRepository->getRandomAssignment($currentValue);

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

        $respTrack  = $this->tracker->getRespondentTrack($trackData['gr2t_id_respondent_track']); // Request on track id, otherwise the data is reloaded from the db
        $study      = $this->fieldDefinition['gtf_calculate_using'];

        $sql1 = "SELECT grb_condition
                    FROM gemsrnd__randomization_blocks
                    WHERE grb_use_max > grb_use_count AND grb_active = 1 AND grb_study_id = ?
                    GROUP BY grb_condition
                    ORDER BY MIN(grb_value_order) ASC";

        // \MUtil_Echo::track($study, $sql1);
        $condIds = $this->resultFetcher->fetchCol($sql1, [$study]);
        // \MUtil_Echo::track(count($condIds));
        if (! $condIds) {
            return null;
        }

        $outputCondition = false;
        foreach ($condIds as $condId) {
            $condition = $this->conditionLoader->loadCondition($condId);
            if ($condition instanceof TrackConditionInterface) {
                // \MUtil_Echo::track($condition->getName());
                if ($condition->isTrackValid($respTrack, $fieldData)) {
                    $outputCondition = $condId;
                    break;
                }
            }
        }
        if (! $outputCondition) {
            return null;
        }

        $sql2 = "SELECT grb_block_id, grb_use_count
                    FROM gemsrnd__randomization_blocks
                    WHERE (grb_use_max > grb_use_count OR grb_use_max = 0) AND grb_active = 1 AND 
                          grb_condition = ? AND grb_study_id = ?
                    ORDER BY grb_value_order";
        // \MUtil_Echo::track($outputCondition, $sql2);
        $block = $this->resultFetcher->fetchRow($sql2, [$outputCondition, $study]);

        if (! $block) {
            return null;
        }

        $this->resultFetcher->updateTable('gemsrnd__randomization_blocks',
            ['grb_use_count' => $block['grb_use_count'] + 1],
            [
                'grb_block_id = ?' => $block['grb_block_id'],
                'grb_study_id = ?' => $study,
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
        if (! $value) {
            return $this->translator->_('Unknown');
        }
        if (! $this->currentUserRepository->getCurrentUser()->hasPrivilege('prr.assignments.seeresult')) {
            return '******';
        }

        $assignment = $this->randomRepository->getRandomAssignment($value);
        if (!$assignment || !$assignment->exists) {
            return $value;
        }

        $url = $this->routeHelper->getRouteUrl('track-builder.randomization.show', [
            Model::REQUEST_ID => $assignment->getBlockId(),
        ]);
        if ($url) {
            return Html::create('a', $url, $assignment->getValueLabel());
        }

        return $assignment->getValueLabel();
    }
}