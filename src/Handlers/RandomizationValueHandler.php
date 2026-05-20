<?php

declare(strict_types=1);

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Handlers;

use Gems\SnippetsActions\Form\CreateAction;
use Gems\SnippetsActions\Import\ImportAction;
use GemsRandomizer\Model\RandomizationValueModel;
use Psr\Cache\CacheItemPoolInterface;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\MetaModellerInterface;
use Zalt\Model\MetaModelLoader;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\SnippetsLoader\SnippetResponderInterface;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Controller
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationValueHandler extends RandomizationHandlerAbstract
{
    protected bool $appliedModelSettings = false;


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
            'grs_study_name' => SORT_ASC,
            'grv_value_label' => SORT_ASC,
        ],
    ];

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    public array $cacheTags = ['randomvalues'];

    /**
     * Model level parameters used for all actions, overruled by any values set in any other
     * parameters array except the private $_defaultParamters values in this module.
     *
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected $defaultParameters = ['randomizationStep' => 'values'];

    public function __construct(
        SnippetResponderInterface $responder,
        MetaModelLoader $metaModelLoader,
        TranslatorInterface $translate,
        CacheItemPoolInterface $cache,
        protected RandomizationValueModel $model,
    ) {
        parent::__construct($responder, $metaModelLoader, $translate, $cache);
    }

    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
        if (!$this->appliedModelSettings) {
            $addUsage = !($action instanceof CreateAction || $action instanceof ImportAction);
            $this->model->applySettings($action->isDetailed(), $addUsage);
            $this->appliedModelSettings = true;
        }
        return $this->model;
    }

    /**
     * Helper function to get the title for the index action.
     *
     * @return string
     */
    public function getIndexTitle(): string
    {
        return $this->_('Randomization values');
    }

    /**
     * Helper function to allow generalized statements about the items in the model.
     *
     * @param int $count
     * @return string
     */
    public function getTopic(int $count = 1): string
    {
        return $this->plural('randomization value', 'randomization values', $count);
    }
}