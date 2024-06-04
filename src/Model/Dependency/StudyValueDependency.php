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
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Dependency\DependencyAbstract;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Model\Dependency
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class StudyValueDependency extends DependencyAbstract
{
    /**
     * Array of setting => setting of setting changed by this dependency
     *
     * The settings array for those effecteds that don't have an effects array
     *
     * @var array
     */
    protected array $_defaultEffects = ['multiOptions'];

    /**
     * Array of name => name of items dependency depends on.
     *
     * Can be overriden in sub class, when set to only field names this class will
     * change the array to the correct structure.
     *
     * @var array Of name => name
     */
    protected array $_dependentOn = ['grb_study_id'];

    /**
     * Array of name => array(setting => setting) of fields with settings changed by this dependency
     *
     * Can be overriden in sub class, when set to only field names this class will use _defaultEffects
     * to change the array to the correct structure.
     *
     * @var array of name => array(setting => setting)
     */
    protected array $_effecteds = ['grb_value_id'];

    /**
     * Constructor checks any subclass set variables
     *
     * @param RandomUtil $randomUtil
     */
    public function __construct(
        TranslatorInterface $translate,
        protected readonly RandomUtil $randomUtil)
    {
        parent::__construct($translate);
    }    
    
    /**
     * @inheritDoc
     */
    public function getChanges(array $context, bool $new = false): array
    {
        $studyId = isset($context['grb_study_id']) ? $context['grb_study_id'] : null;
        return ['grb_value_id' => ['multiOptions' => $this->randomUtil->getRandomValues($studyId)]]; 
    }
}