<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class FoxContactViewLoader extends JViewLegacy
{
	protected $Input;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->Input = JFactory::getApplication()->input;
	}
	
	
	public function display($tpl = null)
	{
		$owner = $this->Input->get('owner', '');
		preg_match('/^[a-z_-]+$/', $owner) or $owner = '';
		$method = '_get_' . $owner . '_params_';
		$params = $this->{$method}();
		$type = $this->Input->get('type', '');
		preg_match('/^[a-z_-]+$/', $type) or $type = '';
		$root = $this->Input->get('root', '');
		preg_match('/^components|media$/', $root) or $root = 'components';
		$option = $this->Input->get('option', '');
		preg_match('/^[a-z_-]+$/', $option) or $option = '';
		$view = $this->Input->get('v', '');
		preg_match('/^[a-z_-]+$/', $view) or $view = '';
		$view = $view ? '/views/' . $view : '';
		jimport('foxcontact.loader.' . $type) or die(JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'));
		$classname = $type . 'Loader';
		$loader = new $classname();
		$loader->IncludePath = JPATH_SITE . "/{$root}/{$option}" . $view;
		$loader->Params =& $params;
		$loader->Show();
	}
	
	
	private function _get__params_()
	{
		return new Joomla\Registry\Registry();
	}
	
	
	private function _get_component_params_()
	{
		$application = @JFactory::getApplication('site');
		$menu = @$application->getMenu();
		$params = $menu->getParams(intval($this->Input->get('id', 0)));
		return $params;
	}
	
	
	private function _get_module_params_()
	{
		$db = JFactory::getDbo();
		jimport('joomla.database.databasequery');
		$query = $db->getQuery(true);
		$query->select($db->quoteName('params'));
		$query->from($db->quoteName('#__modules'));
		$query->where($db->quoteName('id') . '=' . intval($this->Input->get('id', 0)));
		$query->where($db->quoteName('module') . '=' . $db->quote('mod_foxcontact'));
		$db->setQuery($query);
		$json = $db->loadResult();
		$params = new Joomla\Registry\Registry($json);
		return $params;
	}

}