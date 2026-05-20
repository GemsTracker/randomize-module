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

use Gems\Model\GemsJoinModel;
use Gems\Model\JoinModel;
use Gems\Model\MetaModelLoader;
use Gems\SnippetsActions\Form\CreateAction;
use GemsRandomizer\Repository\RandomRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\Validator\Model\ModelUniqueValidator;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationValueModel extends GemsJoinModel
{

    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly RandomRepository $randomRepository,
    ) {
        parent::__construct('gemsrnd__randomization_values', $metaModelLoader, $sqlRunner, $translate, 'gemsrnd__randomization_values');
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
    public function applySettings(bool $detailed, bool $addUsage = false): self
    {
        if (! $detailed) {
            $this->addLeftTable('gemsrnd__randomization_studies', ['grv_study_id' => 'grs_study_id'], false);
        }
        $this->metaModel->resetOrder();

        $this->metaModel->set('grv_study_id', [
            'label' => $this->_('Study name'),
            'description' => $this->_('The study name is used to group blocks.'),
            'multiOptions' => $this->randomRepository->getRandomStudies(),
        ]);
        $this->metaModel->set('grv_value', [
            'label' => $this->_('Randomization export value'),
            'description' => $this->_('The outcome value assigned to a randomization, used for export.'),
            'validators[unique]' => new ModelUniqueValidator(['grv_study_id', 'grv_value'], ['grv_study_id']),
        ]);
        $this->metaModel->set('grv_value_label', [
            'label' => $this->_('Randomization value label'),
            'description' => $this->_('The outcome label shown in the field.'),
        ]);

        if (!$addUsage) {
            // SUM columns
            $sql = "(SELECT COALESCE(SUM(%s), 0)  
                        FROM gemsrnd__randomization_blocks
                        WHERE grb_active = 1 AND grb_value_id = grv_value_id)";

            $this->addColumn(new Expression(sprintf($sql, "grb_use_count")), 'used');
            $this->metaModel->set('used', [
                'label' => $this->_('Used'),
                'elementClass' => 'Exhibitor',
            ]);

            $this->addColumn(new Expression(sprintf($sql, "grb_use_max - grb_use_count")), 'free');
            $this->metaModel->set('free', [
                'label' => $this->_('Unused'),
                'elementClass' => 'Exhibitor',
            ]);

            $this->addColumn(new Expression(sprintf($sql, "grb_use_max")), 'total');
            $this->metaModel->set('total', [
                'label' => $this->_('Total'),
                'elementClass' => 'Exhibitor',
            ]);
        }
        
        return $this;
    }
}