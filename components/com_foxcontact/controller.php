<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
jimport('joomla.application.component.controller');

class FoxContactController extends JControllerLegacy
{
	
	public function display($cachable = false, $urlparams = false)
	{
		$application = JFactory::getApplication('site');
		$menu = $application->getMenu();
		$activemenu = $menu->getActive();
		$view = $application->input->get('view', $this->default_view);
		if ($view == 'foxcontact' && !$activemenu)
		{
			JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_foxcontact&view=invalid'));
		}
		
		return parent::display($cachable, $urlparams);
	}

}