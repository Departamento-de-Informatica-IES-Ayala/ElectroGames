<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
if (isset($GLOBALS['foxcontact_mid_' . $module->id]))
{
	return;
}
else
{
	$GLOBALS['foxcontact_mid_' . $module->id] = true;
}

$cache = JFactory::getCache('com_modules', '');
$cache->setCaching(false);
$cache = @JFactory::getCache('com_content', 'view');
$cache->setCaching(false);
$app->owner = 'module';
$app->oid = $module->id;
$app->cid = 0;
$app->mid = $module->id;
$app->submitted = (bool) count($_POST) && isset($_POST["mid_{$app->mid}"]);
$helpdir = JPATH_BASE . '/components/com_foxcontact/helpers/';
require_once $helpdir . 'fieldsbuilder.php';
include_once $helpdir . 'fsubmitter.php';
include_once $helpdir . 'fajaxuploader.php';
include_once $helpdir . 'fcaptcha.php';
include_once $helpdir . 'fantispam.php';
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
$libsdir = JPATH_BASE . '/components/com_foxcontact/lib/';
include_once $libsdir . 'functions.php';
if (isset($scope) && $scope == 'com_content')
{
	echo '<!--{emailcloak=off}-->';
}

$foxDocument = FoxDocument::getInstance();
$foxDocument->addResource(array('root' => 'media', 'filename' => 'foxcontact', 'type' => 'css'));
$stylesheet = $params->get('css', 'bootstrap.css');
$stylesheet = preg_replace('/\\.[^.\\s]{3,4}$/', '', $stylesheet);
$foxDocument->addResource(array('root' => 'components', 'filename' => $stylesheet, 'type' => 'css'));
$action = $app->input->server->get('REQUEST_URI', '', 'string') . '#mid_' . $module->id;
$language = JFactory::getLanguage();
$language->load('com_foxcontact', JPATH_SITE, $language->getDefault(), true);
$language->load('com_foxcontact', JPATH_SITE, null, true);
$body = JResponse::getBody();
if (!empty($body))
{
	echo JText::_('COM_FOXCONTACT_ADDITIONAL_SETTINGS_REQUIRED') . ' <a href="http://www.fox.ra.it/forum/22-how-to/10274-nested-modules.html">' . JText::_('COM_FOXCONTACT_SEE_DOCUMENTATION') . '</a>';
	return;
}

$page_subheading = $params->get('page_subheading', '');
$xml = new SimpleXMLElement(JPATH_SITE . '/modules/' . $app->scope . '/' . $app->scope . '.xml', 0, true);
$messageboard = new FoxMessageBoard();
$submitter = new FSubmitter($params, $messageboard);
$fieldsBuilder = new FieldsBuilder($params, $messageboard);
$ajax_uploader = new FAjaxUploader($params, $messageboard);
$fcaptcha = new FCaptcha($params, $messageboard);
$antispam = new FAntispam($params, $messageboard, $fieldsBuilder);
$jMessenger = new FJMessenger($params, $messageboard, $fieldsBuilder);
$DatabaseDispatcher = new DatabaseDispatcher($params, $messageboard, $fieldsBuilder);
$newsletter = new FNewsletter($params, $messageboard, $fieldsBuilder);
$acymailing = new FAcyMailing($params, $messageboard, $fieldsBuilder);
$jnews = new FJNewsSubscriber($params, $messageboard, $fieldsBuilder);
$adminMailer = new FAdminMailer($params, $messageboard, $fieldsBuilder);
$submitterMailer = new FSubmitterMailer($params, $messageboard, $fieldsBuilder);
$form_text = '';
$form_text .= $fieldsBuilder->Show();
$form_text .= $ajax_uploader->Show();
$form_text .= $acymailing->Show();
$form_text .= $jnews->Show();
$form_text .= $fcaptcha->Show();
$form_text .= $antispam->Show();
$form_text .= $submitter->Show();
switch (0)
{
	case $submitter->IsValid():
		break;
	case $fieldsBuilder->IsValid():
		break;
	case $ajax_uploader->IsValid():
		break;
	case $fcaptcha->IsValid():
		break;
	case $antispam->IsValid():
		break;
	case $jMessenger->Process():
		break;
	case $DatabaseDispatcher->Process():
		break;
	case $newsletter->Process():
		break;
	case $acymailing->Process():
		break;
	case $jnews->Process():
		break;
	case $adminMailer->Process():
		break;
	case $submitterMailer->Process():
		break;
	default:
		$form_text = '';
		$jsession = JFactory::getSession();
		$namespace = 'foxcontact_module_' . $module->id;
		$jsession->clear('captcha_answer', $namespace);
		HeaderRedirect($params);
}

require JModuleHelper::getLayoutPath($app->scope, $params->get('layout', 'default'));