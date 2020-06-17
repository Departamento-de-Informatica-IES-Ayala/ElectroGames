<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

abstract class FDataPump
{
	public $Params;
	public $Application;
	public $Name;
	public $Fields = array();
	public $Style = array();
	protected $Submitted;
	protected $Logger;
	protected $isvalid;
	protected $MessageBoard;
	protected $JInput;
	protected $LabelHtmlCode;
	protected $placeholder;
	protected $session;
	protected $namespace;
	
	protected abstract function LoadFields();
	
	public function __construct($params, &$messageboard)
	{
		$this->Params = $params;
		$this->MessageBoard =& $messageboard;
		$this->Application = JFactory::getApplication();
		$this->Submitted = (bool) count($_POST) && isset($_POST[$this->GetId()]);
		$this->JInput = JFactory::getApplication()->input;
		$this->session = JFactory::getSession();
		$this->namespace = 'foxcontact_' . $this->Application->owner . '_' . $this->Application->oid;
		$this->LoadFields();
	}
	
	
	public function IsValid()
	{
		return $this->isvalid;
	}
	
	
	protected function LoadField($type, $name)
	{
		$enabled = intval($this->Params->get($name . 'display', '0'));
		if (!$enabled)
		{
			return false;
		}
		
		$this->Fields[$name]['Display'] = intval($this->Params->get($name . 'display', '0'));
		$this->Fields[$name]['Type'] = $type;
		$this->Fields[$name]['Name'] = $this->Params->get($name, '');
		$this->Fields[$name]['PostName'] = $this->SafeName($this->Fields[$name]['Name'] . $this->Application->cid . $this->Application->mid);
		$this->Fields[$name]['Values'] = $this->Params->get($name . 'values', '');
		$this->Fields[$name]['Width'] = (int) $this->Params->get($type . 'width', '');
		$this->Fields[$name]['Height'] = (int) $this->Params->get($type . 'height', '');
		$this->Fields[$name]['Unit'] = $this->Params->get($type . 'unit', '');
		$this->Fields[$name]['Order'] = (int) $this->Params->get($name . 'order', 0);
		$this->Fields[$name]['Mode'] = (int) $this->Params->get($name . 'mode', 0);
		return true;
	}
	
	
	protected function MakeText($key)
	{
		$text = $this->Params->get($key, '');
		if (empty($text))
		{
			return '';
		}
		
		return '<div class="foxmessage" style="clear:both;">' . $text . '</div>';
	}
	
	
	protected function AdditionalDescription($display)
	{
		return $display == 2 ? '<span class="required"></span>' : '';
	}
	
	
	protected function SafeName($name)
	{
		return '_' . md5($name);
	}
	
	
	protected function GetComponentId()
	{
		global $app;
		if (strpos($app->scope, 'com_') !== 0)
		{
			return 0;
		}
		
		$wholemenu = $this->Application->getMenu();
		$targetmenu = $wholemenu->getActive();
		return $targetmenu->id;
	}
	
	
	protected function GetId($separator = '_')
	{
		$id = substr($this->Application->scope, 0, 1);
		switch ($id)
		{
			case 'c':
				$wholemenu = $this->Application->getMenu();
				$activemenu = $wholemenu->getActive();
				$id .= 'id' . $separator . $activemenu->id;
				break;
			case 'm':
				$id .= 'id' . $separator . $this->Application->mid;
				break;
			default:
				$id = '';
		}
		
		return $id;
	}
	
	
	protected function CreateStandardLabel($field)
	{
		if ((bool) $this->Params->get('labelsdisplay'))
		{
			$this->LabelHtmlCode = '<label class="control-label">' . $field['Name'] . $this->AdditionalDescription($field['Display']) . '</label>';
			$this->placeholder = '';
		}
		else
		{
			$this->LabelHtmlCode = '';
			$this->placeholder = 'placeholder="' . $field['Name'] . '" ';
		}
	
	}
	
	
	protected function CreateSpacerLabel()
	{
		$layout = $this->Params->get('form_layout', 'extended');
		if ((bool) $this->Params->get('labelsdisplay') && ($layout == 'compact' || $layout == 'extended'))
		{
			$this->LabelHtmlCode = '<label class="control-label">&nbsp;</label>';
		}
		else
		{
			$this->LabelHtmlCode = '';
		}
	
	}

}