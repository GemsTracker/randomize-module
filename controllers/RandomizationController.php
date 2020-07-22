<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     mjong
 * @license    Not licensed, do not copy
 */

use GemsRandomizer\Model\BlockRandomizationModel;
use GemsRandomizer\Model\Translator\BlockImportTranslator;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @since      Class available since version 1.8.8
 */
class RandomizationController extends \Gems_Controller_ModelSnippetActionAbstract
{
    /**
     * The parameters used for the autofilter action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialisation
     */
    protected $autofilterParameters = [
        'extraSort' => [
            'grb_study_name' => SORT_ASC,
            'grb_value_order' => SORT_ASC,
            ],
        ];

    /**
     * @var \MUtil_Registry_SourceInterface
     */
    public $source;

    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return \MUtil_Model_ModelAbstract
     */
    protected function createModel($detailed, $action)
    {
        $model = new BlockRandomizationModel();
        $this->source->applySource($model);

        $model->applySettings($detailed, $action);

        return $model;
    }

    /**
     * Get the possible translators for the import snippet.
     *
     * @return array of \MUtil_Model_ModelTranslatorInterface objects
     */
    public function getImportTranslators()
    {
        $trs = new BlockImportTranslator($this->_('Direct import'));
        $this->applySource($trs);

        return array('default' => $trs);
    }

    public function getIndexTitle()
    {
        return $this->_('Block randomization');
    }


    /**
     * Helper function to allow generalized statements about the items in the model.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
        return $this->plural('randomization block', 'randomization blocks', $count);
    }
}
