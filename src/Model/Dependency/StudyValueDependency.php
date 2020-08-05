<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model\Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Model\Dependency;

use GemsRandomizer\Util\RandomUtil;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model\Dependency
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class StudyValueDependency extends \MUtil\Model\Dependency\DependencyAbstract
{
    /**
     * Array of setting => setting of setting changed by this dependency
     *
     * The settings array for those effecteds that don't have an effects array
     *
     * @var array
     */
    protected $_defaultEffects = ['multiOptions'];

    /**
     * Array of name => name of items dependency depends on.
     *
     * Can be overriden in sub class, when set to only field names this class will
     * change the array to the correct structure.
     *
     * @var array Of name => name
     */
    protected $_dependentOn = ['grb_study_id'];

    /**
     * Array of name => array(setting => setting) of fields with settings changed by this dependency
     *
     * Can be overriden in sub class, when set to only field names this class will use _defaultEffects
     * to change the array to the correct structure.
     *
     * @var array of name => array(setting => setting)
     */
    protected $_effecteds = ['grb_value_id'];

    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;

    /**
     * Constructor checks any subclass set variables
     *
     * @param \GemsRandomizer\Util\RandomUtil $randomUtil
     */
    public function __construct(RandomUtil $randomUtil)
    {
        $this->randomUtil = $randomUtil;
        
        parent::__construct();
    }    
    
    /**
     * @inheritDoc
     */
    public function getChanges(array $context, $new)
    {
        $studyId = isset($context['grb_study_id']) ? $context['grb_study_id'] : null;
        return ['grb_value_id' => ['multiOptions' => $this->randomUtil->getRandomValues($studyId)]]; 
    }
}