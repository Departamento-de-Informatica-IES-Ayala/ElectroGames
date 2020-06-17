<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

abstract class FoxDocument
{
	protected $Document;
	protected $Prefix;
	
	protected abstract function addCss($slug);
	
	protected abstract function addJs($slug);
	
	public abstract function addStyleSheet($url);
	
	public abstract function addScript($url);
	
	public static function getInstance()
	{
		$config = JComponentHelper::getParams('com_foxcontact');
		$mode = $config->get('resources_loading', 'Performance');
		$class = 'FoxDocument' . $mode;
		return new $class();
	}
	
	
	public function __construct()
	{
		$application = JFactory::getApplication();
		$this->Document = JFactory::getDocument();
		$this->Prefix = 'index.php?option=com_foxcontact' . '&view=loader' . '&owner=' . $application->owner . '&id=' . $application->oid;
	}
	
	
	public function addResource($values)
	{
		$slug = '';
		foreach ($values as $key => $value)
		{
			$slug .= '&' . $key . '=' . $value;
		}
		
		$method = 'add' . ucwords($values['type']);
		$this->{$method}($slug);
	}

}


class FoxDocumentPerformance extends FoxDocument
{
	
	protected function addCss($slug)
	{
		$this->Document->addStyleSheet(JRoute::_($this->Prefix . $slug));
	}
	
	
	protected function addJs($slug)
	{
		$this->Document->addScript(JRoute::_($this->Prefix . $slug));
	}
	
	
	public function addStyleSheet($url)
	{
		$this->Document->addStyleSheet($url);
	}
	
	
	public function addScript($url)
	{
		$this->Document->addScript($url);
	}

}


class FoxDocumentCompatibility extends FoxDocument
{
	
	protected function addCss($slug)
	{
		$this->Document->addCustomTag('<link rel="stylesheet" href="' . JRoute::_($this->Prefix . $slug) . '" type="text/css" />');
	}
	
	
	protected function addJs($slug)
	{
		$this->Document->addCustomTag('<script src="' . JRoute::_($this->Prefix . $slug) . '" type="text/javascript"></script>');
	}
	
	
	public function addStyleSheet($url)
	{
		$this->Document->addCustomTag('<link rel="stylesheet" href="' . $url . '" type="text/css" />');
	}
	
	
	public function addScript($url)
	{
		$this->Document->addCustomTag('<script src="' . $url . '" type="text/javascript"></script>');
	}

}