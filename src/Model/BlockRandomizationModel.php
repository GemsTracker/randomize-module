<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @author     mjong
 * @license    New BSD License
 */

namespace GemsRandomizer\Model;

use Gems\Condition\ConditionLoader;

use Gems\Db\ResultFetcher;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Repository\StaffRepository;
use Gems\SnippetsActions\Form\CreateAction;
use Gems\Util\Translated;
use GemsRandomizer\Model\Dependency\StudyValueDependency;
use GemsRandomizer\Model\Dependency\UseCountDependency;
use GemsRandomizer\Repository\RandomRepository;
use Laminas\Db\Sql\Expression;
use Laminas\Filter\Digits;
use Laminas\Validator\Digits as DigitsValidator;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;
use Zalt\Model\Type\ActivatingYesNoType;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\Validator\Model\ModelUniqueValidator;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @since      Class available since version 1.8.8
 */
class BlockRandomizationModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Translated $translatedUtil,
        protected readonly ConditionLoader $conditionLoader,
        protected readonly StaffRepository $staffRepository,
        protected readonly RandomRepository $randomRepository,
        protected readonly ResultFetcher $resultFetcher,

    ) {
        parent::__construct('gemsrnd__randomization_blocks', $metaModelLoader, $sqlRunner, $translate, 'gemsrnd__randomization_blocks');

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
    public function applySettings(bool $detailed, bool $showChanged = true)
    {
        if (! $detailed) {
            $this->addLeftTable('gems__conditions', ['grb_condition' => 'gcon_id'], false);
            $this->addLeftTable('gemsrnd__randomization_studies', ['grb_study_id' => 'grs_study_id'], false);
            $this->addLeftTable('gemsrnd__randomization_values', ['grb_value_id' => 'grv_value', 'grb_study_id' => 'grv_study_id'], false);
        }
        $this->metaModel->resetOrder();
        if ($detailed) {
            $this->copyKeys();
        }

        $this->addColumn(new Expression("CASE WHEN grb_active = 1 THEN '' ELSE 'DELETED' END"), 'row_class');

        $this->metaModel->set('grb_study_id', [
            'label' => $this->_('Study name'),
            'description' => $this->_('The study name is used to group blocks.'),
            'import_descr' => $this->_('The study name is used to group blocks.'),
            'multiOptions' => $this->randomRepository->getRandomStudies(),
        ]);

        $this->metaModel->set('grb_condition', [
            'multiOptions' => $this->conditionLoader->getConditionsFor(ConditionLoader::TRACK_CONDITION, false),
        ]);
        $this->metaModel->set('grb_condition', [
            'label' => $this->_('Stratum / condition'),
            'description' => $this->_('A stratum is a track level condition.'),
            'import_descr' => $this->_('A stratum is a track level condition.') . ' ' . $this->_('If it does not exist it will be created as an inactive condition.'),
        ]);

        $this->metaModel->set('grb_block_id', [
            'label' => $this->_('Assignment id'),
            'description' => $this->_('A unique name identifying the randomization value.'),
            'import_descr' => $this->_('A unique name identifying the randomization value.'),
            'validators[unique]' => new ModelUniqueValidator('grb_block_id'),
        ]);
        $this->metaModel->set('grb_value_order', [
            'label' => $this->_('Selection order'),
            'default' => '',
            'description' => $this->_('The order of use within a study, leave empty to add to end of stack.'),
            'import_descr' => $this->_('The order of use within a study, leave empty to add by order of import.'),
            'required' => false,
            'validators[int]' => DigitsValidator::class,
            'validators[unique]' => new ModelUniqueValidator(['grb_value_order', 'grb_study_id'], ['grb_block_id']),
        ]);

        $this->metaModel->set('grb_value_id', [
            'label' => $this->_('Assign value'),
            'description' => $this->_('The outcome value assigned to a randomization.'),
            'multiOptions' => $this->randomRepository->getRandomValues(),
        ]);

        $this->metaModel->set('grb_block_description', [
            'label' => $this->_('Block Description'),
            'description' => $this->_('Optional block description, not used by GemsTracker'),
            'import_descr' => $this->_('Optional extra information, not used by GemsTracker'),
        ]);
        $this->metaModel->set('grb_block_info', [
            'label' => $this->_('Block Info'),
            'description' => $this->_('Optional extra information, not used by GemsTracker'),
            'import_descr' => $this->_('Optional extra information, not used by GemsTracker'),
        ]);

        $this->metaModel->set('grb_active', [
            'label' => $this->_('Active'),
            'type' => new ActivatingYesNoType($this->translatedUtil->getYesNo(), 'row_class'),
        ]);

        $this->metaModel->set('grb_use_count', [
            'label' => $this->_('Usage'),
            'filters[digits]' => Digits::class,
        ]);
        $this->metaModel->set('grb_use_max', [
            'label' => $this->_('Maximum'),
           'description' => $this->_('0 means unlimited use'),
           'import_descr' =>  $this->_('0 means unlimited use'),
           'filters[digits]' => Digits::class,
        ]);

        $elementClass = (!$showChanged ? 'None' : 'Exhibitor');
        $this->metaModel->set('grb_changed', [
            'label' => $this->_('Changed on'),
            'elementClass' => $elementClass,
            'formatFunction' => [$this->translatedUtil, 'formatDateTime'],
        ]);
        $this->metaModel->set('grb_changed_by', [
            'label' => $this->_('Changed by'),
            'elementClass' => $elementClass,
            'multiOptions' => $this->staffRepository->getStaff(),
        ]);

        if ($detailed) {
            $this->metaModel->addDependency(new StudyValueDependency($this->translate, $this->randomRepository));
            $this->metaModel->addDependency(new UseCountDependency($this->translate));
        }

        return $this;
    }

    public function save(array $newValues, array $filter = null, array $saveTables = null): array
    {
        if (! (isset($newValues['grb_value_order']) && $newValues['grb_value_order'])) {
            if (isset($newValues['grb_study_id'])) {
                $sql = "SELECT COALESCE(MAX(grb_value_order), 0) + 10  FROM gemsrnd__randomization_blocks WHERE grb_study_id = ?";
                $newValues['grb_value_order'] = $this->resultFetcher->fetchOne($sql,  $newValues['grb_study_id']);
                // \MUtil_Echo::track($newValues['grb_value_order']);
            }
        }
        return parent::save($newValues, $filter, $saveTables);
    }
}