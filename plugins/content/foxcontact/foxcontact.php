<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class plgContentfoxcontact extends JPlugin
{
	
	public function onContentPrepareForm($form, $data)
	{
		$is_module = is_object($data) && isset($data->module) && $data->module == 'mod_foxcontact';
		$is_menu_item = is_array($data) && isset($data['request']['option']) && $data['request']['option'] == 'com_foxcontact';
		$language = JFactory::getLanguage();
		$enGB = $language->get('tag') == $language->getDefault();
		if ($is_module || $is_menu_item && !$enGB)
		{
			$component_name = 'com_' . basename(realpath(dirname(__FILE__)));
			$language->load($component_name, JPATH_ADMINISTRATOR, $language->getDefault(), true);
			$language->load($component_name, JPATH_ADMINISTRATOR, null, true);
		}
		
		return true;
	}

}