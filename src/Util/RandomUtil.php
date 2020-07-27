<?php

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Util;

use Gems\Util\UtilAbstract;

/**
 *
 * @package    GemsRandomize
 * @subpackage Util
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomUtil extends UtilAbstract
{
    /**
     * @return array study => study with description
     */
    public function getRandomStudies()
    {
        $sql = sprintf(
            "SELECT grb_study_name, CONCAT(grb_study_name, '%s', SUM(grb_use_max) - SUM(grb_use_count))  FROM gemsrnd__randomization_blocks GROUP BY grb_study_name;",
             $this->_(' - outcomes: ')
            );

        return $this->_getSelectPairsCached(__FUNCTION__, $sql);
    }
}