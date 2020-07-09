<?php
                
/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\FIeld
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Tracker\Field;

use Gems\Tracker\Field\FieldAbstract;

/**
 *
 * @package    GemsRandomize
 * @subpackage Tracker\FIeld
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomizationField extends FieldAbstract
{
    /**
     * Add the model settings like the elementClass for this field.
     *
     * elementClass is overwritten when this field is read only, unless you override it again in getDataModelSettings()
     *
     * @param array $settings The settings set so far
     */
    protected function addModelSettings(array &$settings)
    {
        $settings['elementClass'] = 'Exhibitor';
    }
}