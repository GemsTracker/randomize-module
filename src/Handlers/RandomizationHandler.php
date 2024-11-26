<?php

declare(strict_types=1);

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace GemsRandomizer\Handlers;

use Gems\Handlers\EmptyHandler;
use Zalt\SnippetsActions\Browse\BrowseTableAction;

/**
 * @package    GemsRandomizer
 * @subpackage GemsRandomizer\Handlers
 * @since      Class available since version 2.0
 */
class RandomizationHandler extends EmptyHandler
{
    public static $actions = [
        'index' => BrowseTableAction::class,
    ];

    public static array $parameters = [];
}
