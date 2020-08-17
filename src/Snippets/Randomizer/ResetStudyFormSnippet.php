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

use Gems\Snippets\FormSnippetAbstract;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Snippets\Randomizer
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class ResetStudyFormSnippet extends FormSnippetAbstract
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;
    
    /**
     * @var \GemsRandomizer\Util\RandomUtil
     */
    protected $randomUtil;

    /**
     * @var int|false The study id or false if none exists 
     */
    protected $studyId = false;
    
    /**
     * @inheritDoc
     */
    protected function addFormElements(\Zend_Form $form)
    {
        $this->saveLabel = $this->_('Reset the study NOW!');
        
        $element = $form->createElement('text', 'study_name');
        $element->setLabel($this->_('Enter the name of the study'));
        $element->setDescription($this->_('Resetting a study clears all randomization assignments to track fields.'));
        $element->setAttrib('size', 30);
        $element->setRequired(true);
        
        $inArray = new \Zend_Validate_InArray(['haystack' => $this->randomUtil->getRandomStudies()]);
        $inArray->setMessage($this->_("'%value%' is not an existing study!"), \Zend_Validate_InArray::NOT_IN_ARRAY);
            
        $element->addValidator($inArray);

        $form->addElement($element);
    }
    
    /**
     * Retrieve the header title to display
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->_('Do you want to reset a study?');
    }

    /**
     * @inheritDoc
     */
    protected function saveData()
    {
        $studyName     = $this->formData['study_name'];
        $this->studyId = array_search($studyName, $this->randomUtil->getRandomStudies());
        
        if (! $this->studyId) {
            return 0;
        }
        
        $sql1  = "UPDATE gems__respondent2track2field
                    SET gr2t2f_value = null
                    WHERE gr2t2f_id_field IN (
                        SELECT gtf_id_field FROM gems__track_fields WHERE gtf_field_type = 'randomization' AND gtf_calculate_using = ?
                    );";
        $stmt1 = $this->db->query($sql1, [$this->studyId]);
        $fieldCount = $stmt1->rowCount();
        
        $sql2  = "UPDATE gemsrnd__randomization_blocks SET grb_use_count = 0 WHERE grb_study_id = ?";
        $stmt2 = $this->db->query($sql2, [$this->studyId]);
        $assignCount = $stmt2->rowCount();
        
        $this->addMessage(sprintf($this->_('Reset all randomization values for study %s.'), $studyName));
        $this->addMessage(sprintf($this->_('Reset %d assignment(s) and %d track field(s).'), $assignCount, $fieldCount));
        
        return 1;        
    }
    
    /**
     * Set what to do when the form is 'finished'.
     *
     * #param array $params Url items to set for this route
     * @return MUtil_Snippets_ModelFormSnippetAbstract (continuation pattern)
     */
    protected function setAfterSaveRoute(array $params = array())
    {
        // Only reroute when it is to a different url
        if ($this->studyId) {

            if ($this->routeController) {
                $controllerName = $this->routeController;
            } else {
                $controllerName = $this->request->getControllerName();
            }

            $this->afterSaveRouteUrl = $params + array(
                    $this->request->getControllerKey() => $controllerName,
                    $this->request->getActionKey() => $this->routeAction,
                    \MUtil_Model::REQUEST_ID => $this->studyId,
                    'RouteReset' => true,
                );
        }

        return $this;
    }
}