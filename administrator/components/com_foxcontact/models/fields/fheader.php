<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class JFormFieldFHeader extends JFormField
{
	protected $type = 'FHeader';
	
	protected function getInput()
	{
		$cn = basename(realpath(dirname(__FILE__) . '/../..'));
		$direction = intval(JFactory::getLanguage()->get('rtl', 0));
		$left = $direction ? 'right' : 'left';
		$right = $direction ? 'left' : 'right';
		$image = '';
		$icon = (string) $this->element['icon'];
		if (!empty($icon))
		{
			$image .= '<img style="margin:0; float:' . $left . ';" src="' . JUri::base() . '../media/' . $cn . '/images/' . $icon . '">';
		}
		
		$helpurl = (string) $this->element['helpurl'];
		if (!empty($helpurl))
		{
			$image .= '<a href="' . $helpurl . '" target="_blank"><img style="margin:0; float:' . $right . ';" src="' . JUri::base() . '../media/' . $cn . '/images/question-button-16.png"></a>';
		}
		
		$style = 'background:#f4f4f4; color:#025a8d; border:1px solid silver; padding:5px; margin:16px 0;';
		return '<div style="' . $style . '">' . $image . '<span style="padding-' . $left . ':5px; font-weight:bold; line-height:16px;">' . JText::_($this->element['default']) . '</span>' . '</div>';
	}

}