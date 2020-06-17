<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
require_once JPATH_COMPONENT . '/lib/functions.php';

class FoxContactViewInvalid extends JViewLegacy
{
	
	public function display($tpl = null)
	{
		echo '<h2>' . JText::_('COM_FOXCONTACT_ERR_PROVIDE_VALID_URL') . '</h2>';
		$application = JFactory::getApplication('site');
		$menu = $application->getMenu();
		$valid_items = $menu->getItems('component', 'com_foxcontact');
		echo '<ul>';
		foreach ($valid_items as $valid_item)
		{
			echo '<li><a href="' . FGetLink($valid_item->id) . '">' . $valid_item->title . '</a></li>';
		}
		
		echo '</ul>';
		JFactory::getLanguage()->load('com_foxcontact', JPATH_ADMINISTRATOR);
		echo '<p><a href="http://www.fox.ra.it/forum/22-how-to/1574-hide-the-contact-page-menu-item.html">' . JText::_('COM_FOXCONTACT_DOCUMENTATION') . '</a></p>';
	}

}