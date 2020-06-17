<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdatapump.php';
require_once $inc_dir . '/flanghandler.php';
require_once $inc_dir . '/conflicting.php';
require_once $inc_dir . '/flogger.php';

class FieldsBuilder extends FDataPump
{
	
	public function __construct($params, &$messageboard)
	{
		parent::__construct($params, $messageboard);
		$this->ValidateEmail();
		static $resources = true;
		if ($resources)
		{
			JHtml::_('jquery.framework');
			$foxDocument = FoxDocument::getInstance();
			$foxDocument->addResource(array('root' => 'components', 'filename' => 'foxtext', 'type' => 'js'));
			$resources = false;
		}
		
		$this->isvalid = intval($this->ValidateForm());
		$lang_handler = new FLangHandler();
		if ($lang_handler->HasMessages())
		{
			$messageboard->Append($lang_handler->GetMessages(), FoxMessageBoard::warning);
		}
		
		$conflict = new FoxConflicting();
		if ($conflict->HasMessages())
		{
			$messageboard->Append($conflict->GetMessages(), FoxMessageBoard::warning);
		}
	
	}
	
	
	public function Show()
	{
		$result = '';
		uasort($this->Fields, 'sort_fields');
		foreach ($this->Fields as $key => $field)
		{
			switch ($field['Type'])
			{
				case 'customhtml':
					$result .= $this->BuildCustomHtmlField($key, $field);
					break;
				case 'sender':
				case 'text':
					$result .= $this->BuildTextField($key, $field);
					break;
				case 'dropdown':
					$result .= $this->BuildDropdownField($key, $field);
					break;
				case 'textarea':
					$result .= $this->BuildTextareaField($key, $field);
					break;
				case 'checkbox':
					$result .= $this->BuildCheckboxField($key, $field);
					break;
				case 'calendar':
					$result .= $this->BuildCalendarField($key, $field);
					break;
				case 'clientinfo':
					$result .= $this->BuildClientInfoField($key, $field);
					break;
			}
			
			if (!$field['IsValid'])
			{
				$this->MessageBoard->Add(JText::sprintf('COM_FOXCONTACT_ERR_INVALID_VALUE', $field['Name']), FoxMessageBoard::error);
			}
		
		}
		
		return $result;
	}
	
	
	protected function LoadFields()
	{
		$all_params = $this->Params->toArray();
		foreach ($all_params as $name => $value)
		{
			if (array_key_exists($name . 'order', $all_params))
			{
				list($type, $number) = preg_split('/([a-z]+)/i', $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				$this->LoadField($type, $number);
			}
		
		}
	
	}
	
	
	protected function LoadField($type, $number)
	{
		$name = $type . (string) $number;
		if (!parent::LoadField($type, $name))
		{
			return false;
		}
		
		$this->Fields[$name]['Value'] = $this->JInput->post->get($this->Fields[$name]['PostName'], NULL, 'string');
		if ($this->Fields[$name]['Value'] == $this->Fields[$name]['Name'])
		{
			$this->Fields[$name]['Value'] = '';
		}
		
		$this->Fields[$name]['IsValid'] = intval($this->ValidateField($this->Fields[$name]['Value'], $this->Fields[$name]['Display']));
		if ($type == 'checkbox' && $this->Fields[$name]['Value'] == '')
		{
			$this->Fields[$name]['Value'] = JText::_('JNO');
		}
		
		return true;
	}
	
	
	private function BuildCustomHtmlField($key, &$field)
	{
		if (empty($field['Name']))
		{
			return '';
		}
		
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group" ' . '>' . PHP_EOL . '<div class="controls">' . '<div>' . PHP_EOL . $field['Name'] . PHP_EOL . '</div>' . '</div>' . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	private function BuildClientInfoField($key, $field)
	{
		static $resources = true;
		if ($resources)
		{
			$basename = '/media/com_foxcontact/js/clientinfo';
			$min = JDEBUG && file_exists(JPATH_ROOT . $basename . '.js') ? '' : '.min';
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . "{$basename}{$min}.js");
			$resources = false;
		}
		
		$result = '<input ' . 'type="hidden" ' . 'value="" ' . 'name="' . $field['PostName'] . '" ' . 'class="' . $key . '" ' . '/>' . PHP_EOL;
		return $result;
	}
	
	
	private function BuildTextField($key, &$field)
	{
		$this->CreateStandardLabel($field);
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group' . $this->TextStyleByValidation($field) . '" ' . '>' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<input ' . 'type="text" ' . 'value="' . $field['Value'] . '" ' . 'title="' . $field['Name'] . '" ' . $this->placeholder . 'name="' . $field['PostName'] . '" ' . '/>' . $this->DescriptionByValidation($field) . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	private function BuildCalendarField($key, &$field)
	{
		static $resources = true;
		if ($resources)
		{
			$basename = '/media/com_foxcontact/js/calendar';
			$min = JDEBUG && file_exists(JPATH_ROOT . $basename . '.js') ? '' : '.min';
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . "{$basename}{$min}.js");
			$document->addStyleSheet(JUri::root(true) . '/media/com_foxcontact/css/calendar.css');
			$resources = false;
		}
		
		$mode = (int) $field['Mode'];
		$have_date = (bool) ($mode & 2);
		$have_time = (bool) ($mode & 1);
		$have_both = $have_date && $have_time;
		$format = ($have_date ? JText::_('DATE_FORMAT_LC') : '') . ($have_time ? ' H:i' : '');
		$this->CreateStandardLabel($field);
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group' . $this->TextStyleByValidation($field) . '" ' . '>' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<input ' . 'type="text" ' . 'class="foxcalendar" ' . 'value="' . $field['Value'] . '" ' . 'title="' . $field['Name'] . '" ' . $this->placeholder . 'name="' . $field['PostName'] . '" ' . 'data-datepicker="' . ($have_date ? 'true' : 'false') . '" ' . 'data-timepicker="' . ($have_time ? 'true' : 'false') . '" ' . 'data-close-on-date-select="' . (!$have_both ? 'true' : 'false') . '" ' . 'data-format="' . $format . '" ' . 'readonly="" ' . '/>' . $this->DescriptionByValidation($field) . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	private function BuildDropdownField($key, &$field)
	{
		static $resources = true;
		if ($resources)
		{
			$uncompressed = JDEBUG ? '-uncompressed' : '';
			$min = JDEBUG ? '' : '.min';
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . '/media/system/js/core' . $uncompressed . '.js');
			$document->addScript(JUri::root(true) . '/media/jui/js/chosen.jquery' . $min . '.js');
			$document->addScript(JUri::root(true) . '/media/com_foxcontact/js/chosen.min.js');
			$document->addStyleSheet(JUri::root(true) . '/media/jui/css/chosen.css');
			$resources = false;
		}
		
		$this->CreateStandardLabel($field);
		$placeholder = $this->Params->get('labelsdisplay') ? ' ' : $field['Name'];
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group' . $this->TextStyleByValidation($field) . '" ' . '>' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<select ' . 'class="fox_select" ' . 'data-placeholder="' . $placeholder . '" ' . 'name="' . $field['PostName'] . '" ' . '>' . PHP_EOL;
		$result .= '<option value=""></option>';
		$options = explode(',', $field['Values']);
		foreach ($options as $option)
		{
			$result .= '<option value="' . $option . '"';
			if ($field['Value'] === $option && !empty($option))
			{
				$result .= ' selected ';
			}
			
			$result .= '>' . $option . '</option>';
		}
		
		$result .= PHP_EOL . '</select>' . $this->DescriptionByValidation($field) . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	private function BuildCheckboxField($key, &$field)
	{
		if ($field['Value'] == JText::_('JYES'))
		{
			$checked = 'checked="" ';
		}
		else
		{
			$checked = '';
		}
		
		$this->CreateSpacerLabel();
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group' . $this->TextStyleByValidation($field) . '" ' . '>' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<label class="checkbox">' . '<input ' . 'type="checkbox" ' . 'value="' . JText::_('JYES') . '" ' . $checked . 'name="' . $field['PostName'] . '" ' . 'id="c' . $field['PostName'] . '" ' . '/>' . $this->AdditionalDescription($field['Display']) . $field['Name'] . $this->DescriptionByValidation($field) . '</label>' . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	private function BuildTextareaField($key, &$field)
	{
		$this->CreateStandardLabel($field);
		$result = '<div ' . 'id="' . $this->GetId() . '-' . $key . '" ' . 'class="control-group' . $this->TextStyleByValidation($field) . '" ' . '>' . PHP_EOL . $this->LabelHtmlCode . PHP_EOL . '<div class="controls">' . '<textarea ' . 'rows="10" ' . 'cols="30" ' . 'name="' . $field['PostName'] . '" ' . 'title="' . $field['Name'] . '" ' . $this->placeholder . '>' . $field['Value'] . '</textarea>' . $this->DescriptionByValidation($field) . '</div>' . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
		return $result;
	}
	
	
	public function DescriptionByValidation(&$field)
	{
		return $field['IsValid'] ? '' : ' <span class="asterisk"></span>';
	}
	
	
	public function CheckboxStyleByValidation(&$field)
	{
		if (!$this->Submitted)
		{
			return 'foxcheckbox';
		}
		
		return $field['IsValid'] ? 'validcheckbox' : 'invalidcheckbox';
	}
	
	
	protected function TextStyleByValidation(&$field)
	{
		if (!$this->Submitted)
		{
			return '';
		}
		
		return $field['IsValid'] ? ' success' : ' error';
	}
	
	
	public function ValidateForm()
	{
		$result = true;
		$result &= $this->ValidateGroup('sender');
		$result &= $this->ValidateGroup('text');
		$result &= $this->ValidateGroup('dropdown');
		$result &= $this->ValidateGroup('checkbox');
		$result &= $this->ValidateGroup('textarea');
		$result &= $this->ValidateGroup('calendar');
		return $result;
	}
	
	
	public function ValidateGroup($family)
	{
		$result = true;
		for ($l = 0; $l < 10; ++$l)
		{
			if (isset($this->Fields[$family . $l]) && $this->Fields[$family . $l]['Display'])
			{
				$result &= $this->Fields[$family . $l]['IsValid'];
			}
		
		}
		
		return $result;
	}
	
	
	public function ValidateField($fieldvalue, $fieldtype)
	{
		return !($this->Submitted && $fieldtype == 2 && empty($fieldvalue));
	}
	
	
	public function ValidateEmail()
	{
		if (!isset($_POST[$this->GetId()]))
		{
			return;
		}
		
		if (!isset($this->Fields['sender1']))
		{
			return;
		}
		
		if (empty($this->Fields['sender1']['Value']) && $this->Fields['sender1']['Display'] == 1)
		{
			return;
		}
		
		if (!isset($this->Fields['sender1']['Value']))
		{
			return;
		}
		
		$this->Fields['sender1']['IsValid'] &= preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,63}$/', strtolower($this->Fields['sender1']['Value'])) == 1;
		$config = JComponentHelper::getParams('com_foxcontact');
		$mode = $config->get('use_dns', 'disabled');
		if (method_exists('FieldsBuilder', $mode))
		{
			$this->{$mode}();
		}
	
	}
	
	
	public function dns_check()
	{
		if (empty($this->Fields['sender1']['Value']))
		{
			return;
		}
		
		$parts = explode('@', $this->Fields['sender1']['Value']);
		$domain = array_pop($parts);
		if (!empty($domain))
		{
			$this->Fields['sender1']['IsValid'] &= checkdnsrr($domain, 'MX');
		}
	
	}

}

function sort_fields($a, $b)
{
	return $a['Order'] - $b['Order'];
}

class fieldsbuilderCheckEnvironment
{
	protected $InstallLog;
	
	public function __construct()
	{
		$this->InstallLog = new FLogger('fieldsbuilder', 'install');
		$params = JComponentHelper::getParams('com_foxcontact')->toObject();
		$this->test_addresses($params);
		$this->test_dns($params);
		$table = JTable::getInstance('extension');
		$table->load(array('element' => 'com_foxcontact', 'client_id' => 1));
		$table->bind(array('params' => json_encode($params)));
		$result = $table->check() && $table->store();
		return $result;
	}
	
	
	private function test_dns($params)
	{
		$this->InstallLog->Write('--- Determining if this system is able to query DNS records ---');
		if (!function_exists('checkdnsrr'))
		{
			$this->InstallLog->Write('checkdnsrr function doesn\'t exist.');
			$params->use_dns = '0';
			return;
		}
		
		$this->InstallLog->Write('checkdnsrr function found. Let\'s see if it works.');
		$record_found = checkdnsrr('fox.ra.it', 'MX');
		$this->InstallLog->Write('testing function [checkdnsrr]... [' . intval($record_found) . ']');
		$result = $record_found ? 'dns_check' : '0';
		$this->InstallLog->Write("--- Method choosen to query DNS records is [{$result}] ---");
		$params->use_dns = $result;
	}
	
	
	private function test_addresses(&$params)
	{
		$config = JFactory::getConfig();
		if ($config->get('mailer') == 'smtp' && (bool) $config->get('smtpauth') && strpos($config->get('smtpuser'), '@') !== false)
		{
			isset($params->adminemailfrom) or $params->adminemailfrom = new stdClass();
			$params->adminemailfrom->select = 'custom';
			$params->adminemailfrom->name = $config->get('fromname');
			$params->adminemailfrom->email = $config->get('smtpuser');
			isset($params->submitteremailfrom) or $params->submitteremailfrom = new stdClass();
			$params->submitteremailfrom->select = 'custom';
			$params->submitteremailfrom->name = $config->get('fromname');
			$params->submitteremailfrom->email = $config->get('smtpuser');
		}
	
	}

}