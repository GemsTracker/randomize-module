<?php

/**
 *
 * @package    GemsRandomize
 * @subpackage Snippers\Randomizer
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace GemsRandomizer\Snippets\Randomizer;

/**
 *
 * @package    GemsRandomize
 * @subpackage Snippers\Randomizer
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class RandomizerSearchSnippet extends \Gems_Snippets_AutosearchFormSnippet
{
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
     * Returns a text element for autosearch. Can be overruled.
     *
     * The form / html elements to search on. Elements can be grouped by inserting null's between them.
     * That creates a distinct group of elements
     *
     * @param array $data The $form field values (can be usefull, but no need to set them)
     * @return array Of \Zend_Form_Element's or static tekst to add to the html or null for group breaks.
     */
    protected function getAutoSearchElements(array $data)
    {
        $conditons = $this->loader->getConditions();

        $elements = parent::getAutoSearchElements($data);

        $elements['grb_study_name'] = $this->_createSelectElement('grb_study_name',  $this->randomUtil->getRandomStudies(), $this->_('(all studies)'));
        $elements['grb_condition'] = $this->_createSelectElement('grb_condition',  $this->model, $this->_('(all strata)'));
        $usages = [
            'unused'    => $this->_('Unused'),
            'used'      => $this->_('Used'),
            // '' => $this->_(''),
            'maxed'     => $this->_('Maxed out'),
            'unlimited' => $this->_('Unlimited use'),
            ];
        $elements['usage'] = $this->_createSelectElement('usage', $usages, $this->_('(any usage)'));
        $elements['grb_active'] = $this->_createSelectElement('grb_active', $this->model, $this->_('(any active)'));

        return $elements;
    }
}