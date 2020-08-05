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

use GemsRandomizer\Model\Dependency\StudyValueDependency;
use GemsRandomizer\Model\Dependency\UseCountDependency;

use Gems\Conditions;
use MUtil\Model\Dependency\ValueSwitchDependency;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @since      Class available since version 1.8.8
 */
class BlockRandomizationModel extends \Gems_Model_JoinModel
{
    /**
     * @var \Gems_Loader
     */
    protected $loader;

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;

    /**
     * @var \Gems_Util
     */
    protected $util;

    /**
     * Create a model that joins two or more tables
     */
    public function __construct()
    {
        parent::__construct('gemsrnd__randomization_blocks', 'gemsrnd__randomization_blocks', 'grb', true);

        $this->addColumn(new \Zend_Db_Expr("CASE WHEN grb_active = 1 THEN '' ELSE 'DELETED' END"), 'row_class');
        $this->setDeleteValues(['grb_active' => 0]);
    }

    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return BlockRandomizationModel
     */
    public function applySettings($detailed, $action)
    {
        if (! $detailed) {
            $this->addLeftTable('gems__conditions', ['grb_condition' => 'gcon_id'], 'grb', false);
            $this->addLeftTable('gemsrnd__randomization_studies', ['grb_study_id' => 'grs_study_id'], 'grs', false);
            $this->addLeftTable('gemsrnd__randomization_values', ['grb_value_id' => 'grv_value', 'grb_study_id' => 'grv_study_id'], 'grv', false);
        }
        $this->resetOrder();
        if ($detailed) {
            $this->copyKeys();
        }

        if ($detailed) {
            $this->set('grb_study_id', 'label', $this->_('Study name'),
                       'description', $this->_('The study name is used to group blocks.'),
                       'import_descr', $this->_('The study name is used to group blocks.'),
                       'multiOptions', $this->randomUtil->getRandomStudies()
            );
        } else {
            $this->set('grs_study_name', 'label', $this->_('Study name'),
                       'description', $this->_('The study name is used to group blocks.')
            );
        }

        $this->set('grb_condition',
                   'multiOptions', $this->loader->getConditions()->getConditionsFor(Conditions::TRACK_CONDITION, false));
        if ($detailed) {
            $this->set('grb_condition', 'label', $this->_('Stratum / condition'),
                       'description', $this->_('A stratum is a track level condition.'),
                       'import_descr', $this->_('A stratum is a track level condition.') . ' ' .
                       $this->_('If it does not exist it will be created as an inactive condition.'));
        } else {
            $this->set('gcon_name', 'label', $this->_('Stratum / condition'),
                       'description', $this->_('A stratum is a track level condition.') . ' ' .
                       $this->_('See Track builder: Conditions.'));
        }

        $this->set('grb_block_id', 'label', $this->_('Assignment id'),
            'description', $this->_('A unique name identifying the randomization value.'),
           'import_descr', $this->_('A unique name identifying the randomization value.'),
           'validators[unique]', $this->createUniqueValidator('grb_block_id')
        );
        $this->set('grb_value_order', 'label', $this->_('Selection order'),
                   'default', '',
                   'description', $this->_('The order of use within a study, leave empty to add to end of stack.'),
                   'import_descr', $this->_('The order of use within a study, leave empty to add by order of import.'),
                   'required', false,
                   'validators[int]', 'Int',
                   'validators[unique]', $this->createUniqueValidator(['grb_value_order', 'grb_study_id'], ['grb_block_id']));

        $this->set('grb_value_id', 'label', $this->_('Assign value'),
                   'description', $this->_('The outcome value assigned to a randomization.'),
                    'multiOptions', $this->randomUtil->getRandomValues()
        );

        $this->set('grb_block_description', 'label', $this->_('Block Description'),
                   'description', $this->_('Optional block description, not used by GemsTracker'),
                   'import_descr', $this->_('Optional extra information, not used by GemsTracker'));
        $this->set('grb_block_info', 'label', $this->_('Block Info'),
                   'description', $this->_('Optional extra information, not used by GemsTracker'),
                   'import_descr', $this->_('Optional extra information, not used by GemsTracker'));

        $this->set('grb_active', 'label', $this->_('Active'),
            'elementClass', 'Checkbox',
            'multiOptions', $this->util->getTranslated()->getYesNo()
            );

        $this->set('grb_use_count', 'label', $this->_('Usage'),
            'filters[digits]', 'Digits');
        $this->set('grb_use_max', 'label', $this->_('Maximum'),
           'description', $this->_('0 means unlimited use'),
           'import_descr',  $this->_('0 means unlimited use'),
           'filters[digits]', 'Digits');

        $elementClass = ($action == 'create' ? 'None' : 'Exhibitor');
        $this->set('grb_changed', 'label', $this->_('Changed on'),
            'elementClass', $elementClass,
            'formatFunction', array($this->util->getTranslated(), 'formatDateTime'));
        $this->set('grb_changed_by', 'label', $this->_('Changed by'),
            'elementClass', $elementClass,
            'multiOptions', $this->util->getDbLookup()->getStaff());

        if ($detailed) {
            $this->addDependency(new StudyValueDependency($this->randomUtil));
            $this->addDependency(new UseCountDependency());
        }

        return $this;
    }

    /**
     * Save a single model item.
     *
     * @param array $newValues The values to store for a single model item.
     * @param array $filter If the filter contains old key values these are used
     * to decide on update versus insert.
     * @param array $saveTables Optional array containing the table names to save,
     * otherwise the tables set to save at model level will be saved.
     * @return array The values as they are after saving (they may change).
     */
    protected function _save(array $newValues, array $filter = null, array $saveTables = null)
    {
        if (! (isset($newValues['grb_value_order']) && $newValues['grb_value_order'])) {
            if ( isset($newValues['grb_study_id'])) {
                $db  = $this->getAdapter();
                $sql = "SELECT COALESCE(MAX(grb_value_order), 0) + 10  FROM gemsrnd__randomization_blocks WHERE grb_study_id = ?";
                $newValues['grb_value_order'] = $db->fetchOne($sql,  $newValues['grb_study_id']);
                // \MUtil_Echo::track($newValues['grb_value_order']);
            }
        }

        return parent::_save($newValues, $filter, $saveTables);
    }
}