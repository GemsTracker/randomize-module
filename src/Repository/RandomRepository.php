<?php

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Repository;

use Gems\Condition\ConditionLoader;
use Gems\Db\CachedResultFetcher;
use Gems\Db\ResultFetcher;
use Gems\Repository\StaffRepository;
use Gems\Util\Translated;
use Gems\Util\UtilDbHelper;
use GemsRandomizer\Model\BlockRandomizationModel;
use GemsRandomizer\Model\RandomizationStudyModel;
use GemsRandomizer\Model\RandomizationValueModel;
use GemsRandomizer\Tracker\RandomizationAssignment;
use Zalt\Base\TranslatorInterface;
use Zalt\SnippetsActions\SnippetActionInterface;

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomRepository
{
    public function __construct(
        protected readonly UtilDbHelper $utilDbHelper,
        protected readonly Translated $translatedUtil,
        protected readonly TranslatorInterface $translate,
        protected readonly ResultFetcher $resultFetcher,
        protected readonly CachedResultFetcher $cachedResultFetcher,
        protected readonly ConditionLoader $conditionLoader,
        protected readonly StaffRepository $staffRepository,
    ) {
    }

    /**
     * @var array assignmentId => GemsRandomizer\Tracker\RandomizationAssignment
     */
    private array $assignments = [];
    
    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param SnippetActionInterface $action The current action.
     * @return RandomizationStudyModel
     */
    public function createStudyModel(bool $detailed, SnippetActionInterface $action): RandomizationStudyModel
    {
        $model = new RandomizationStudyModel($this->translatedUtil, $this->translate);
        //$this->source->applySource($model);

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
     * @param SnippetActionInterface $action The current action.
     * @return RandomizationValueModel
     */
    public function createValueModel(bool $detailed, SnippetActionInterface $action): RandomizationValueModel
    {
        $model = new RandomizationValueModel($this, $this->translate);
        //$this->source->applySource($model);

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
     * @param SnippetActionInterface $action The current action.
     * @return BlockRandomizationModel
     */
    public function createBlockModel(bool $detailed, SnippetActionInterface $action): BlockRandomizationModel
    {
        $model = new BlockRandomizationModel($this->translatedUtil, $this->conditionLoader, $this->staffRepository, $this, $this->translate);
        //$this->source->applySource($model);

        $model->applySettings($detailed, $action);

        return $model;
    }

    /**
     * @param array|string $blockData
     * @return RandomizationAssignment|null
     */
    public function getRandomAssignment(array|string $blockData): RandomizationAssignment|null
    {
        if (is_array($blockData)) {
            if (! isset($blockData['grb_block_id'])) {
                return null;
            }
            $blockId = $blockData['grb_block_id'];
        } else {
            $blockId = $blockData;
        }
        if (! isset($this->assignments[$blockId])) {
            $this->assignments[$blockId] = new RandomizationAssignment($blockData, $this->resultFetcher);
        }
        
        return $this->assignments[$blockId];
    }
        
    /**
     * @return array study => study with description
     */
    public function getRandomStudies(): array
    {
        $sql = "SELECT grs_study_id, grs_study_name FROM gemsrnd__randomization_studies ORDER BY grs_study_name;";

        return $this->cachedResultFetcher->fetchPairs(__FUNCTION__, $sql, [], ['randomstudies']);
    }

    /**
     * @param int|null $studyId
     * @return array valueId => label
     */
    public function getRandomExportValues(int|null $studyId = null): array
    {
        $sql = "SELECT grv_value_id, grv_value FROM gemsrnd__randomization_values";
        $params = null;
        if ($studyId) {
            $sql .= " WHERE grv_study_id = ?";
            $params = [$studyId];
        }
        $sql .= " ORDER BY grv_value;";

        return $this->cachedResultFetcher->fetchPairs(__FUNCTION__, $sql, $params, ['randomvalues']);
    }
    
    /**
     * @param int|null $studyId
     * @return array valueId => label
     */
    public function getRandomValues(int|null $studyId = null): array
    {
        $sql = "SELECT grv_value_id, grv_value_label FROM gemsrnd__randomization_values";
        $params = null;
        if ($studyId) {
            $sql .= " WHERE grv_study_id = ?";
            $params = [$studyId];
        }
        $sql .= " ORDER BY grv_value_label;";
        
        return $this->cachedResultFetcher->fetchPairs(__FUNCTION__, $sql, $params, ['randomvalues']);
    }
}