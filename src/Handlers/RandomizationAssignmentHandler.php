<?php

declare(strict_types=1);

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace GemsRandomizer\Handlers;

use Gems\Handlers\CsrfHandlerTrait;
use GemsRandomizer\Handlers\RandomizationHandlerAbstract;
use Zalt\Model\MetaModellerInterface;
use Zalt\SnippetsActions\PostActionInterface;
use Zalt\SnippetsActions\SnippetActionInterface;

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @since      Class available since version 2.0
 */
class RandomizationAssignmentHandler extends RandomizationHandlerAbstract
{
    use CsrfHandlerTrait;

    public static array $parameters = [
        'id' => '[a-zA-Z0-9-_]+',
    ];

    /**
     * @inheritDoc
     */
    protected function createModel($detailed, $action)
    {
        return $this->randomRepository->createBlockModel($detailed, $action);
    }

    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
        if (!$this->model) {
            $this->model = $this->createModel(false, $action);
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
