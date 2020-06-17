<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdatapump.php';

class FSubmitter extends FDataPump
{
	
	public function __construct($params, &$messageboard)
	{
		parent::__construct($params, $messageboard);
		$this->Name = 'FSubmitter';
		$this->isvalid = count($_POST) > 1 && isset($_POST[$this->GetId()]);
	}
	
	
	public function Show()
	{
		$result = '';
		$field = array();
		if ($this->Params->get('copy_to_submitter', 0) == 2 && (bool) $this->Params->get('sender1display', 0))
		{
			$field['Display'] = 1;
			$field['Type'] = 'checkbox';
			$field['Name'] = JText::_('COM_FOXCONTACT_SEND_ME_A_COPY');
			$field['PostName'] = $this->SafeName('copy_to_submitter' . $this->GetId());
			$field['Value'] = $this->JInput->post->get($field['PostName'], null, 'int');
			$field['IsValid'] = true;
			$result .= $this->BuildCheckboxField('', $field);
		}
		
		$this->CreateSpacerLabel();
		$result .= '<div id="' . $this->GetId() . '-buttons" ' . 'class="control-group">' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls buttons">' . PHP_EOL;
		$result .= '<input type="hidden" name="' . $this->GetId() . '" value="">' . PHP_EOL;
		switch ($this->Params->get('submittype'))
		{
			case 1:
				$result .= '<input class="btn btn-success submit-button" type="submit" value="' . $this->Params->get('submittext') . '"/>' . PHP_EOL;
				break;
			default:
				$icon = $this->Params->get('submiticon');
				$result .= '<button class="btn btn-success submit-button" type="submit">' . PHP_EOL . '<span';
				if ($icon != '-1')
				{
					$result .= ' class="with-icon"';
					$result .= ' style="background-image: url(' . JUri::base(true) . '/media/com_foxcontact/images/submit/' . $icon . ');"';
				}
				
				$result .= '>' . $this->Params->get('submittext') . '</span>' . PHP_EOL . '</button>' . PHP_EOL;
		}
		
		if ($this->Params->get('resetbutton'))
		{
			switch ($this->Params->get('resettype'))
			{
				case 1:
					$result .= '<input class="btn btn-danger reset-button" type="reset" value="' . $this->Params->get('resettext') . '">' . PHP_EOL;
					break;
				default:
					$reseticon = $this->Params->get('reseticon');
					$result .= '<button class="btn btn-danger reset-button" type="reset">' . PHP_EOL . '<span';
					if ($reseticon != '-1')
					{
						$result .= ' class="with-icon"';
						$result .= ' style="background-image: url(' . JUri::base(true) . '/media/com_foxcontact/images/reset/' . $reseticon . ');"';
					}
					
					$result .= '>' . $this->Params->get('resettext') . '</span>' . PHP_EOL . '</button>' . PHP_EOL;
			}
		
		}
		
		$result .= '</div>' . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	protected function LoadFields()
	{
	}
	
	
	private function BuildCheckboxField($key, &$field)
	{
		if ($field['Value'] == JText::_('JYES'))
		{
			$checked = 'checked=""';
		}
		else
		{
			$checked = '';
		}
		
		$this->CreateSpacerLabel();
		$result = '<div class="control-group fox_copy_to_sender">' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<label class="checkbox">' . '<input ' . 'type="checkbox" ' . 'value="' . JText::_('JYES') . '" ' . $checked . 'name="' . $field['PostName'] . '" ' . 'id="c' . $field['PostName'] . '" ' . '/>' . $field['Name'] . '</label>' . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}

}