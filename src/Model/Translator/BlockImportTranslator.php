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

use Gems\Cache\HelperAdapter;
use Gems\Condition\ConditionLoader;
use Gems\Model\ConditionModel;
use GemsRandomizer\Model\RandomizationStudyModel;
use GemsRandomizer\Model\RandomizationValueModel;
use GemsRandomizer\Repository\RandomRepository;
use Psr\Container\ContainerInterface;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Data\DataWriterInterface;
use Zalt\Model\Translator\ModelTranslatorAbstract;
use Zalt\Model\Translator\ModelTranslatorInterface;

/**
 *
 * @package    GemsRandomize
 * @subpackage Model\Translator
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class BlockImportTranslator extends ModelTranslatorAbstract
{
    private ?RandomizationStudyModel $studyModel = null;
    private ?ConditionModel $conditionModel = null;
    private ?RandomizationValueModel $valueModel = null;

    /**
     * @var array cond id => row
     */
    protected array $_conditionIds;

    /**
     * @var array cond id => label
     */
    protected array $_studyIds;

    /**
     * @var array cond id => value
     */
    protected array $_valueIds;

    public function __construct(
        TranslatorInterface $translator,
        protected readonly ConditionLoader $conditionLoader,
        protected readonly ContainerInterface $container,
        protected readonly HelperAdapter $cache,
        protected readonly RandomRepository $randomRepository,
    )
    {
        parent::__construct($translator);
    }

    /**
     * Temp function to overrule incorrect MUtil 1.9.0 
     * 
     * @deprecated since 1.9.1 
     * @param string $elementName
     * @param mixed $index
     * @param mixed $value
     */
    public function addMultiOption(string $elementName, mixed $index, mixed $value): void
    {
        $targetMetaModel = $this->targetModel->getMetaModel();
        if ($targetMetaModel->has($elementName, 'multiOptions')) {
            $options = $targetMetaModel->get($elementName, 'multiOptions');
            $options[$index] = $value;
            $targetMetaModel->set($elementName, 'multiOptions', $options);
        }

        /*$element = $this->targetForm->getElement($elementName);
        if ($element instanceof \Zend_Form_Element_Multi) {
            $element->addMultiOption($index, $value);

            $validator = $element->getValidator('InArray');
            if ($validator instanceof InArray) {
                $haystack   = $validator->getHaystack();
                $haystack[] = $index; // Validator contains only choice
                $validator->setHaystack($haystack);
            }
        }*/
    }
    
    /**
     * Get information on the field translations
     *
     * @return array of fields sourceName => targetName
     */
    public function getFieldsTranslations(): array
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
     * @param DataWriterInterface $targetModel The target of the data
     * @return ModelTranslatorInterface (continuation pattern)
     */
    public function setTargetModel(DataWriterInterface $targetModel): ModelTranslatorInterface
    {
        $targetMetaModel = $targetModel->getMetaModel();
        $this->_conditionIds = $targetMetaModel->get('grb_condition', 'multiOptions');
        $this->_studyIds     = $targetMetaModel->get('grb_study_id', 'multiOptions');
        $this->_valueIds     = $targetMetaModel->get('grb_value_id', 'multiOptions');

        $targetMetaModel->del('grb_block_id', 'validators');
        
        return parent::setTargetModel($targetModel);
    }

    /**
     * Perform any translations necessary for the code to work
     *
     * @param mixed $row array or \Traversable row
     * @param mixed $rowId
     * @return array|bool Row array or false when errors occurred
     */
    public function translateRowValues($row, mixed $rowId): array|bool
    {
        $study = $row['study'];
        // Create study if new
        if ($study && (! (isset($this->_studyIds[$study]) || in_array($study, $this->_studyIds)))) {
            $sModel = $this->getStudyModel();
            $sResult = $sModel->load(['grs_study_name' => $study]);
            
            if (! $sResult) {
                $sValues = [
                    'grs_study_name' => $study,
                    'grs_active' => 1,
                ];

                $sResult = $sModel->save($sValues);
                // \MUtil_Echo::track($sResult, $this->_studyIds);
            }
            
            $this->_studyIds[$sResult['grs_study_id']] = $study;
            $this->addMultiOption('grb_study_id', $sResult['grs_study_id'], $study);
            $this->cache->invalidateTags(['randomstudies']);

            $row['study'] = $sResult['grs_study_id'];
        }
        $studyId = isset($this->_studyIds[$study]) ? $this->_studyIds[$study] : array_search($study, $this->_studyIds);
            
        $cond = $row['stratum'];
        // Create condition if new
        if ($cond && (! (isset($this->_conditionIds[$cond]) || in_array($cond, $this->_conditionIds)))) {
            $classes = $this->conditionLoader->listConditionsForType(ConditionLoader::TRACK_CONDITION);
            unset($classes[""]);
            reset($classes);

            $cModel = $this->getConditionModel();
            $cResult = $cModel->load(['gcon_type' => ConditionLoader::TRACK_CONDITION, 'gcon_name'   => $cond]);
            
            if (! $cResult) {
                $cValues = [
                    'gcon_type'   => ConditionLoader::TRACK_CONDITION,
                    'gcon_class'  => key($classes),
                    'gcon_name'   => $cond,
                    'gcon_active' => 0,
                ];
    
                $cResult = $cModel->save($cValues);
            }

            $this->_conditionIds[$cResult['gcon_id']] = $cond;
            $this->addMultiOption('grb_condition', $cResult['gcon_id'], $cond);
        }

        $val = $row['value'];
        // Check for export values instead of label values 
        if ($val && (! (isset($this->_valueIds[$val]) || in_array($val, $this->_valueIds)))) {
            if ($studyId) {
                $export = array_search($val, $this->randomRepository->getRandomExportValues($studyId));
                if (false !== $export) {
                    $val          = $export;
                    $row['value'] = $export;
                }
            }
        }
        // Create value if new
        if ($val && (! (isset($this->_valueIds[$val]) || in_array($val, $this->_valueIds)))) {
            $vModel = $this->getValueModel();
            $vResult = $vModel->load(['grv_study_id' => $studyId, 'grv_value_label' => $val]);
            
            if (! $vResult) {
                $vValues = [
                    'grv_study_id' => $studyId,
                    'grv_value' => $val,
                    'grv_value_label' => $val,
                ];

                $vResult = $vModel->save($vValues);
                // \MUtil_Echo::track($vResult, $vValues);
            }
            
            $this->_valueIds[$vResult['grv_value_id']] = $val;
            $this->addMultiOption('grb_value_id', $vResult['grv_value_id'], $val);
            $this->cache->invalidateTags(['randomvalues']);
        }

        $row = parent::translateRowValues($row, $rowId);

        if (!$row) {
            return false;
        }

        // \MUtil_Echo::track($row);

        return $row;
    }

    private function getConditionModel(): ConditionModel
    {
        if (!$this->conditionModel) {
            $this->conditionModel = $this->container->get(ConditionModel::class);
        }
        return $this->conditionModel;
    }

    private function getStudyModel(): RandomizationStudyModel
    {
        if (!$this->studyModel) {
            $this->studyModel = $this->container->get(RandomizationStudyModel::class);
            $this->studyModel->applySettings(true);
        }
        return $this->studyModel;
    }

    private function getValueModel(): RandomizationValueModel
    {
        if (!$this->valueModel) {
            $this->valueModel = $this->container->get(RandomizationValueModel::class);
            $this->valueModel->applySettings(true);
        }
        return $this->valueModel;
    }
}