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
use Gems\Util\Translated;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;
use Zalt\Model\Type\ActivatingYesNoType;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\Validator\Model\ModelUniqueValidator;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationStudyModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Translated $translatedUtil,
    ) {
        parent::__construct('gemsrnd__randomization_studies', $metaModelLoader, $sqlRunner, $translate, 'gemsrnd__randomization_studies');
    }

    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param bool $detailed True when the current action is not in $summarizedActions.
     * @param bool $addUsage add usage stats to model
     * @return RandomizationStudyModel
     */
    public function applySettings(bool $detailed, bool $addUsage = false): self
    {
        $this->addColumn(new Expression("CASE WHEN grs_active = 1 THEN '' ELSE 'DELETED' END"), 'row_class');

        $this->metaModel->set('grs_study_name', [
            'label' => $this->_('Study name'),
            'description' => $this->_('The study name is used to group blocks.'),
            'required' => true,
            'validators[unique]' => new ModelUniqueValidator('grs_study_name'),
        ]);
        $this->metaModel->set('grs_active', [
            'label' => $this->_('Active'),
            'type' => new ActivatingYesNoType($this->translatedUtil->getYesNo(), 'row_class'),
        ]);

        if (!$addUsage) {
            // SUM columns
            $sql = "(SELECT COALESCE(SUM(%s), 0)  
                        FROM gemsrnd__randomization_blocks
                        WHERE grb_active = 1 AND grb_study_id = grs_study_id)";

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