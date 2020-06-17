<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class FoxMessageBoard
{
	const success = 1;
	const info = 2;
	const warning = 4;
	const error = 8;
	protected $Level = 0;
	protected $Messages = array();
	public static $Levels = array(FoxMessageBoard::success => 'success', FoxMessageBoard::info => 'info', FoxMessageBoard::warning => 'warning', FoxMessageBoard::error => 'error');
	
	public function Add($message, $level = 0)
	{
		$this->Messages[] = $message;
		$this->RaiseLevel($level);
	}
	
	
	public function Append($messages, $level = 0)
	{
		$this->Messages += $messages;
		$this->RaiseLevel($level);
	}
	
	
	public function Clear()
	{
		$this->Messages[] = array();
		$this->Level = 0;
	}
	
	
	public function RaiseLevel($level)
	{
		if ($level > $this->Level)
		{
			$this->Level = $level;
		}
	
	}
	
	
	public function Display()
	{
		echo $this->__toString();
	}
	
	
	public function __toString()
	{
		$result = '';
		if (!count($this->Messages))
		{
			return $result;
		}
		
		$result .= '<div class="alert alert-' . FoxMessageBoard::$Levels[$this->Level] . '">' . '<ul class="fox_messages">';
		foreach ($this->Messages as $message)
		{
			$result .= '<li>' . $message . '</li>';
		}
		
		$result .= '</ul>' . '</div>';
		return $result;
	}

}