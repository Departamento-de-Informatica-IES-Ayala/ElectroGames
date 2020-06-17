<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$application = JFactory::getApplication('site');
$menu = $application->getMenu();
$activemenu = $menu->getActive() or $activemenu = new stdClass();
$application->owner = 'component';
$application->oid = isset($activemenu->id) ? $activemenu->id : 0;
$application->cid = isset($activemenu->id) ? $activemenu->id : 0;
$application->mid = 0;
$application->submitted = (bool) count($_POST) && isset($_POST["cid_{$application->cid}"]);
$language = JFactory::getLanguage();
if ($language->get('tag') != $language->getDefault())
{
	$language->load('com_foxcontact', JPATH_SITE, $language->getDefault(), true);
	$language->load('com_foxcontact', JPATH_SITE, null, true);
}

jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('FoxContact');
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));
$controller->redirect();