<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

abstract class Loader
{
	public $headers = array();
	
	protected abstract function type();
	
	protected abstract function content_header();
	
	protected abstract function content_footer();
	
	public function Show()
	{
		$this->headers();
		$this->content_header();
		$this->load();
		$this->content_footer();
		JFactory::getApplication()->close();
	}
	
	
	private function headers()
	{
		foreach ($this->headers as $header)
		{
			header($header);
		}
	
	}
	
	
	protected function load()
	{
		$input = JFactory::getApplication()->input;
		$owner = $input->get('owner', 'component');
		$id = $input->get('id', '0');
		$uid = '_' . $owner[0] . $id;
		$language = JFactory::getLanguage();
		$direction = intval($language->get('rtl', 0));
		$left = $direction ? 'right' : 'left';
		$right = $direction ? 'left' : 'right';
		$juri_root = JURI::root(true);
		$filename = $input->get('filename', '');
		preg_match('/^[A-Z0-9-_]+$/i', $filename) or $filename = 'invalid';
		$filename = $this->IncludePath . '/' . $this->type() . '/' . $filename . '.' . $this->type();
		if (is_file($filename) && is_readable($filename))
		{
			require_once $filename;
		}
		else
		{
			echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND');
		}
	
	}

}


abstract class CachableLoader extends Loader
{
	
	public function __construct()
	{
		$this->headers[] = 'Cache-Control: max-age=604800, public';
		$this->headers[] = 'Connection: keep-alive, Keep-Alive';
		$this->headers[] = 'Date: ' . gmdate('D, d M Y H:i:s') . ' GMT';
		$this->headers[] = 'Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT';
	}

}


abstract class UncachableLoader extends Loader
{
	
	public function __construct()
	{
		$this->headers[] = 'Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT';
		$this->headers[] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT';
		$this->headers[] = 'Cache-Control: no-cache, private, must-revalidate, max-age=0';
		$this->headers[] = 'Pragma: no-cache';
	}

}