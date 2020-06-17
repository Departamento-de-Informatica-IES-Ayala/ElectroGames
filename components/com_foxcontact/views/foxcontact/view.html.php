<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$helpdir = JPATH_BASE . '/components/com_foxcontact/helpers/';
require_once $helpdir . 'fsubmitter.php';
require_once $helpdir . 'fieldsbuilder.php';
require_once $helpdir . 'fajaxuploader.php';
require_once $helpdir . 'fantispam.php';
require_once $helpdir . 'fcaptcha.php';
require_once $helpdir . 'fnewsletter.php';
require_once $helpdir . 'acymailing.php';
require_once $helpdir . 'jnews.php';
require_once $helpdir . 'messageboard.php';
require_once $helpdir . 'document.php';
$dispatchers_dir = $helpdir . 'dispatchers/';
require_once $dispatchers_dir . 'fadminmailer.php';
require_once $dispatchers_dir . 'fsubmittermailer.php';
require_once $dispatchers_dir . 'fjmessenger.php';
require_once $dispatchers_dir . 'database.php';
require_once JPATH_COMPONENT . '/lib/functions.php';

class FoxContactViewFoxcontact extends JViewLegacy
{
	public $document;
	protected $Application;
	protected $cparams;
	protected $Submitter;
	protected $FieldsBuilder;
	protected $AjaxUploader;
	protected $Antispam;
	protected $JMessenger;
	protected $DatabaseDispatcher;
	protected $AdminMailer;
	protected $SubmitterMailer;
	protected $Newsletter;
	protected $AcyMailing;
	protected $JNews;
	protected $FoxCaptcha;
	protected $MessageBoard;
	protected $menu_id;
	protected $page_subheading;
	protected $xml;
	public $FormText = '';
	
	public function display($tpl = null)
	{
		$this->Application = JFactory::getApplication();
		$this->cparams = $this->Application->getMenu()->getActive()->params;
		if ($description = $this->cparams->get('menu-meta_description'))
		{
			$this->document->setDescription($description);
		}
		
		if ($keywords = $this->cparams->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $keywords);
		}
		
		if ($robots = $this->cparams->get('robots'))
		{
			$this->document->setMetadata('robots', $robots);
		}
		
		$foxDocument = FoxDocument::getInstance();
		$foxDocument->addResource(array('root' => 'media', 'filename' => 'foxcontact', 'type' => 'css'));
		$stylesheet = $this->cparams->get('css', 'bootstrap.css');
		$stylesheet = preg_replace('/\\.[^.\\s]{3,4}$/', '', $stylesheet);
		$foxDocument->addResource(array('root' => 'components', 'filename' => $stylesheet, 'type' => 'css'));
		$this->MessageBoard = new FoxMessageBoard();
		$this->Submitter = new FSubmitter($this->cparams, $this->MessageBoard);
		$this->FieldsBuilder = new FieldsBuilder($this->cparams, $this->MessageBoard);
		$this->AjaxUploader = new FAjaxUploader($this->cparams, $this->MessageBoard);
		$this->FoxCaptcha = new FCaptcha($this->cparams, $this->MessageBoard);
		$this->JMessenger = new FJMessenger($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->DatabaseDispatcher = new DatabaseDispatcher($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->Antispam = new FAntispam($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->Newsletter = new FNewsletter($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->AcyMailing = new FAcyMailing($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->JNews = new FJNewsSubscriber($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->AdminMailer = new FAdminMailer($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->SubmitterMailer = new FSubmitterMailer($this->cparams, $this->MessageBoard, $this->FieldsBuilder);
		$this->FormText .= $this->FieldsBuilder->Show();
		$this->FormText .= $this->AjaxUploader->Show();
		$this->FormText .= $this->AcyMailing->Show();
		$this->FormText .= $this->JNews->Show();
		$this->FormText .= $this->FoxCaptcha->Show();
		$this->FormText .= $this->Antispam->Show();
		$this->FormText .= $this->Submitter->Show();
		switch (0)
		{
			case $this->Submitter->IsValid():
				break;
			case $this->FieldsBuilder->IsValid():
				break;
			case $this->AjaxUploader->IsValid():
				break;
			case $this->FoxCaptcha->IsValid():
				break;
			case $this->Antispam->IsValid():
				break;
			case $this->JMessenger->Process():
				break;
			case $this->DatabaseDispatcher->Process():
				break;
			case $this->Newsletter->Process():
				break;
			case $this->AcyMailing->Process():
				break;
			case $this->JNews->Process():
				break;
			case $this->AdminMailer->Process():
				break;
			case $this->SubmitterMailer->Process():
				break;
			default:
				$this->FormText = '';
				$jsession = JFactory::getSession();
				$namespace = 'foxcontact_component_' . $this->Application->cid;
				$jsession->clear('captcha_answer', $namespace);
				HeaderRedirect($this->cparams);
		}
		
		$this->menu_id = $this->Application->getMenu()->getActive()->id;
		$this->page_subheading = $this->cparams->get('page_subheading', '');
		$this->xml = new SimpleXMLElement(JPATH_ADMINISTRATOR . '/components/com_foxcontact/foxcontact.xml', 0, true);
		parent::display($tpl);
	}

}