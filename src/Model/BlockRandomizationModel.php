<?php

/**
 *
 *
 * @package    GemsRandomizer
 * @subpackage Model
 * @author     mjong
 * @license    Not licensed, do not copy
 */

namespace GemsRandomizer\Model;

use Gems\Conditions;

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
        }
        $this->resetOrder();

        $this->set('grb_study', 'label', $this->_('Study name'),
            'description', $this->_('A study name use to bundle a group of outcome blocks.')
            );
        $this->set('grb_value_label', 'label', $this->_('Value label'),
            'description', $this->_('A unique name identifying this value within the study.')
            );

        $this->set('grb_description', 'label', $this->_('Description'),
            'description', $this->_('Optional block description'));
        $this->set('grb_info', 'label', $this->_('Block Info'),
            'description', $this->_('Optional extra information'));

        if ($detailed) {
            $this->set('grb_condition', 'label', $this->_('Stratum'),
                'multiOptions', $this->loader->getConditions()->getConditionsFor(Conditions::TRACK_CONDITION)
            );
        } else {
            $this->set('gcon_name', 'label', $this->_('Stratum'));
        }

        $this->set('grb_value', 'label', $this->_('Block value'));

        if ($detailed) {
            $this->set('grb_active', 'label', $this->_('Active'),
                'elementClass', 'Checkbox',
                'multiOptions', $this->util->getTranslated()->getYesNo()
                );
        }

        $this->set('grb_use_count', 'label', $this->_('Usage'),
            'filters[digits]', 'Digits');
        $this->set('grb_use_max', 'label', $this->_('Maximum'),
            'description', $this->_('0 means unlimited use'),
            'filters[digits]', 'Digits');

        $elementClass = ($action == 'create' ? 'None' : 'Exhibitor');
        $this->set('grb_changed', 'label', $this->_('Changed on'),
            'elementClass', $elementClass,
            'formatFunction', array($this->util->getTranslated(), 'formatDateTime'));
        $this->set('grb_changed_by', 'label', $this->_('Changed by'),
            'elementClass', $elementClass,
            'multiOptions', $this->util->getDbLookup()->getStaff());

        return $this;
    }
}