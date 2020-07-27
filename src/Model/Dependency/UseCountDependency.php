<?php

/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\Model\Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Model\Dependency;

use MUtil\Model\Dependency\DependencyAbstract;

/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\Model\Dependency
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class UseCountDependency extends DependencyAbstract
{
    /**
     * Array of setting => setting of setting changed by this dependency
     *
     * The settings array for those effecteds that don't have an effects array
     *
     * @var array
     */
    protected $_defaultEffects = ['readonly'];

    /**
     * Array of name => name of items dependency depends on.
     *
     * Can be overriden in sub class, when set to only field names this class will
     * change the array to the correct structure.
     *
     * @var array Of name => name
     */
    protected $_dependentOn = ['grb_use_count'];

    /**
     * Array of name => array(setting => setting) of fields with settings changed by this dependency
     *
     * Can be overriden in sub class, when set to only field names this class will use _defaultEffects
     * to change the array to the correct structure.
     *
     * @var array of name => array(setting => setting)
     */
    protected $_effecteds = ['grb_value_id', 'grb_value', 'grb_use_count'];

    /**
     * @inheritDoc
     */
    public function getChanges(array $context, $new)
    {
        $output = [];
        if ($context['grb_use_count'] > 0) {
            foreach ($this->getEffecteds() as $fieldname => $effects) {
                $output[$fieldname] = ['readonly' => 'readonly'];
            }
        }
        return $output;
    }
}