<?php

declare(strict_types=1);

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace GemsRandomizer\Handlers;

use Gems\Handlers\CsrfHandlerTrait;
use Gems\SnippetsActions\Form\CreateAction;
use Gems\SnippetsActions\Import\ImportAction;
use GemsRandomizer\Handlers\RandomizationHandlerAbstract;
use GemsRandomizer\Model\BlockRandomizationModel;
use Psr\Cache\CacheItemPoolInterface;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\MetaModellerInterface;
use Zalt\Model\MetaModelLoader;
use Zalt\SnippetsActions\PostActionInterface;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\SnippetsLoader\SnippetResponderInterface;

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @since      Class available since version 2.0
 */
class RandomizationAssignmentHandler extends RandomizationHandlerAbstract
{
    use CsrfHandlerTrait;

    protected bool $appliedModelSettings = false;

    public static array $parameters = [
        'id' => '[a-zA-Z0-9-_]+',
    ];

    public function __construct(
        SnippetResponderInterface $responder,
        MetaModelLoader $metaModelLoader,
        TranslatorInterface $translate,
        CacheItemPoolInterface $cache,
        protected readonly BlockRandomizationModel $model,
    ) {
        parent::__construct($responder, $metaModelLoader, $translate, $cache);
    }

    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
        if (!$this->appliedModelSettings) {
            $showChanged = !($action instanceof CreateAction);

            $this->model->applySettings($action->isDetailed(), $showChanged);
            $this->appliedModelSettings = true;
        }
        return $this->model;
    }

    public function prepareAction(SnippetActionInterface $action): void
    {
        parent::prepareAction($action);

        if ($action instanceof PostActionInterface) {
            // Check if properties exist like we do in gemstracker/src/Handlers/GemsHandler.php
            if (property_exists($action, 'csrfName') && property_exists($action, 'csrfToken')) {
                $action->csrfName = $this->getCsrfTokenName();
                $action->csrfToken = $this->getCsrfToken($action->csrfName);
            }
        }
    }
}
