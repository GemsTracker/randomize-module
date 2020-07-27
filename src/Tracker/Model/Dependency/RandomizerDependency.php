<?php
                
/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\Model\Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Tracker\Model\Dependency;

use Gems\Conditions;
use MUtil\Model\Dependency\DependencyAbstract;

/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\Model\Dependency
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomizerDependency extends DependencyAbstract
{
    /**
     * Array of setting => setting of setting changed by this dependency
     *
     * The settings array for those effecteds that don't have an effects array
     *
     * @var array
     */
    protected $_defaultEffects = array('description', 'elementClass', 'label', 'multiOptions', 'onchange', 'onclick',
        'filters', 'validators',
    );

    /**
     * Array of name => name of items dependency depends on.
     *
     * Can be overriden in sub class
     *
     * @var array Of name => name
     */
    protected $_dependentOn = ['gtf_field_type', 'gtf_field_values'];

    /**
     * Array of name => array(setting => setting) of fields with settings changed by this dependency
     *
     * Can be overriden in sub class
     *
     * @var array of name => array(setting => setting)
     */
    protected $_effecteds = [
        'gtf_required' => ['elementClass', 'value'],
        'gtf_readonly' => ['elementClass', 'value'],
        'htmlCalc'     => ['elementClass', 'label'],
        'gtf_calculate_using' => ['description', 'elementClass', 'label', 'multiOptions'],
        ];

    /**
     *
     * @var \Gems_Loader
     */
    protected $loader;

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;

    /**
     *
     * @var \Gems_Util
     */
    protected $util;

    /**
     * Returns the changes that must be made in an array consisting of
     *
     * <code>
     * array(
     *  field1 => array(setting1 => $value1, setting2 => $value2, ...),
     *  field2 => array(setting3 => $value3, setting4 => $value4, ...),
     * </code>
     *
     * By using [] array notation in the setting name you can append to existing
     * values.
     *
     * Use the setting 'value' to change a value in the original data.
     *
     * When a 'model' setting is set, the workings cascade.
     *
     * @param array $context The current data this object is dependent on
     * @param boolean $new True when the item is a new record not yet saved
     * @return array name => array(setting => value)
     */
    public function getChanges(array $context, $new)
    {
        $output['gtf_required'] = [
            'elementClass' => 'Hidden',
            'value'        => 0,
        ];
        $output['gtf_readonly'] = [
            'elementClass' => 'Hidden',
            'value'        => 1,
        ];
        $output['htmlCalc'] = [
            'label'        => ' ',
            'elementClass' => 'Exhibitor',
        ];
        $output['gtf_calculate_using'] = [
            'label' => $this->_('Study Blocks'),
            'description'  => $this->_('Select the study name for this randomization'),
            'elementClass' => 'MultiCheckbox',
            'multiOptions' => $this->randomUtil->getRandomStudies(),
            'validators[ranfge]' => ['CheckedItemsRange', false, ['gtf_calculate_using', 1, 1]],
        ];;
        // \MUtil_Echo::track($options);

        return $output;
    }
}