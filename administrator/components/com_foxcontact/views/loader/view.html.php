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
		$type = $this->Input->get('type', '');
		preg_match('/^[a-z_-]+$/', $type) or $type = '';
		jimport('foxcontact.loader.' . $type) or die(JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'));
		$view = $this->Input->get('v', '');
		preg_match('/^[a-z_-]+$/', $view) or $view = '';
		$view = $view ? '/views/' . $view : '';
		$option = $this->Input->get('option', '');
		$classname = $type . 'Loader';
		$loader = new $classname();
		$loader->IncludePath = JPATH_ADMINISTRATOR . "/components/{$option}" . $view;
		$loader->Show();
	}

}