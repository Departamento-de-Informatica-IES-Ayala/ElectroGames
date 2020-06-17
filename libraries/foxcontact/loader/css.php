<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
require_once 'loader.php';

class cssLoader extends UncachableLoader
{
	
	public function __construct()
	{
		parent::__construct();
		$this->headers[] = 'Content-Type: text/css; charset=utf-8';
	}
	
	
	protected function type()
	{
		return 'css';
	}
	
	
	protected function content_header()
	{
	}
	
	
	protected function content_footer()
	{
	}

}