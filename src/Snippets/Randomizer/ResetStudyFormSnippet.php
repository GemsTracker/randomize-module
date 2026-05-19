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

use Gems\Audit\AuditLog;
use Gems\Db\ResultFetcher;
use Gems\Menu\MenuSnippetHelper;
use Gems\Model;
use Gems\Snippets\FormSnippetAbstract;
use GemsRandomizer\Repository\RandomRepository;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslatorInterface;
use Zalt\Message\MessengerInterface;
use Zalt\SnippetsLoader\SnippetOptions;
use Zalt\Validator\InArray;

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
     * @var int|false The study id or false if none exists 
     */
    protected int|bool $studyId = false;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo,
        TranslatorInterface $translate,
        MessengerInterface $messenger,
        AuditLog $auditLog,
        MenuSnippetHelper $menuHelper,
        protected readonly ResultFetcher $resultFetcher,
        protected readonly RandomRepository $randomRepository,
    )
    {
        parent::__construct($snippetOptions, $requestInfo, $translate, $messenger, $auditLog, $menuHelper);
    }
    /**
     * @inheritDoc
     */
    protected function addFormElements(mixed $form): void
    {
        $this->saveLabel = $this->_('Reset the study NOW!');
        
        $element = $form->createElement('text', 'study_name');
        $element->setLabel($this->_('Enter the name of the study'));
        $element->setDescription($this->_('Resetting a study clears all randomization assignments to track fields.'));
        $element->setAttrib('size', 30);
        $element->setRequired(true);
        
        $inArray = new InArray(['haystack' => $this->randomRepository->getRandomStudies()]);
        $inArray->setMessage($this->_("'%value%' is not an existing study!"), InArray::NOT_IN_ARRAY);
            
        $element->addValidator($inArray);

        $form->addElement($element);
    }
    
    /**
     * Retrieve the header title to display
     *
     * @return string
     */
    protected function getTitle(): string
    {
        return $this->_('Do you want to reset a study?');
    }

    /**
     * @inheritDoc
     */
    protected function saveData(): int
    {
        $studyName     = $this->formData['study_name'];
        $this->studyId = array_search($studyName, $this->randomRepository->getRandomStudies());
        
        if (! $this->studyId) {
            return 0;
        }

        $subQuery = new Select();
        $subQuery->from('gems__track_fields')->columns(['gtf_id_field'])->where(['gtf_field_type' => 'randomization', 'gtf_calculate_using' => $this->studyId]);
        $where = new Where();
        $where->in('gr2t2f_id_field', $subQuery);
        $fieldCount = $this->resultFetcher->updateTable('gems__respondent2track2field', ['gr2t2f_value' => null], $where);

        $assignCount = $this->resultFetcher->updateTable('gemsrnd__randomization_blocks', ['grb_use_count' => 0], ['grb_study_id' => $this->studyId]);
        
        $this->addMessage(sprintf($this->_('Reset all randomization values for study %s.'), $studyName));
        $this->addMessage(sprintf($this->_('Reset %d assignment(s) and %d track field(s).'), $assignCount, $fieldCount));
        
        return 1;        
    }

    /**
     * Set what to do when the form is 'finished'.
     *
     * #param array $params Url items to set for this route
     * @return FormSnippetAbstract (continuation pattern)
     */
    protected function setAfterSaveRoute(array $params = array())
    {
        // Only reroute when it is to a different url
        if ($this->studyId) {

            /*if ($this->routeController) {
                $controllerName = $this->routeController;
            } else {
                $controllerName = $this->request->getControllerName();
            }

            $this->afterSaveRouteUrl = $params + array(
                    $this->request->getControllerKey() => $controllerName,
                    $this->request->getActionKey() => $this->routeAction,
                    Model::REQUEST_ID => $this->studyId,
                    'RouteReset' => true,
                );
            */
        }

        return $this;
    }
}