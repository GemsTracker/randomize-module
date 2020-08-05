<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Snippets\Randomizer
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Snippets\Randomizer;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Snippets\Randomizer
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class AddRandomizerInformation extends \MUtil_Snippets_SnippetAbstract
{
    /**
     * @var string 
     */
    protected $randomizationStep;
    
    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil_Html_HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $seq = $this->getHtmlSequence();
        $seq->br();
        $divMain = $seq->div(['class' => 'alert alert-info', 'role' => "alert"]);

        $divMain->h2($this->_('Block randomization explanation'), ['style' => 'margin-top: 5px;']);

        $p = $divMain->pInfo($this->_('Block randomization is used to automatically assign randomization outcomes to track fields.'));
        $p->append(' ' . $this->_('The randomization outcome is the free assignment with the lowest selection order, if the track is valid for the assigment stratum.'));

        $div = $divMain->div(['class' => ('study' == $this->randomizationStep ? 'mailpreview' : null)]);
        $div->h3($this->_('Step 1: Create a study'));
        $ul = $div->ul();
        $ul->li($this->_('Studies are just labels to allow multiple randomizations in a project.'));
        $ul->li($this->_('If an assignment import contains a new study it will automatically be created.'));

        $div = $divMain->div(['class' => ('strata' == $this->randomizationStep ? 'mailpreview' : null)]);
        $div->h3($this->_('Step 2: Create / design strata'));
        $ul = $div->ul();
        $ul->li($this->_('Strata are track-level conditions.'));
        $ul->li($this->_('You can create these conditions both here and in the Track builder Conditions page.'));
        $ul->li($this->_('Create a track condition on age, location, etc...'));
        $ul->li($this->_('If an assignment import contains a new stratum an inactive skeleton stratum will automatically be created.'));

        $div = $divMain->div(['class' => ('values' == $this->randomizationStep ? 'mailpreview' : null)]);
        $div->h3($this->_('Step 3: Determine the outcome values'));
        $ul = $div->ul();
        $ul->li($this->_('Per study you can define the outcome values used.'));
        $ul->li($this->_('The export values must be unique per study.'));
        $ul->li($this->_('The label value is what is shown to the users who are allowed to see the the outcomes.'));
        $ul->li($this->_('If an assignment import contains a new value, this value will be created.'));

        $div = $divMain->div(['class' => ('assignments' == $this->randomizationStep ? 'mailpreview' : null)]);
        $div->h3($this->_('Step 4: Create assignments'));
        $ul = $div->ul();
        $ul->li($this->_('Randomization values are assigned from the assignments table.'));
        $ul->li($this->_('Assignments have to satisfy a stratum to be used.'));
        $ul->li($this->_('The assignment with the lowest order that has free uses is used first.'));
        $ul->li($this->_('The id and value of a used assignment cannot be changed.'));
        $ul->li($this->_('During assignment import you can fill this table without first taking the other steps.'));

        $div = $divMain->div(['class' => ('fields' == $this->randomizationStep ? 'mailpreview' : null)]);
        $div->h3($this->_('Step 5: Create the track fields'));
        $ul = $div->ul();
        $ul->li($this->_('To use randomization values with a track, add a Randomization type track field.'));
        $ul->li($this->_('Make sure all fields used by strata are placed before the Randomization field.'));
        $ul->li($this->_('Select a study to use, you may use one study in multiple fields.'));
        
        // $divMain->pInfo($this->_('These checks are run automatically every time an appointment is created or changed.'));

        return $seq;
    }
}