<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdatapump.php';

class FCaptcha extends FDataPump
{
	
	public function __construct($params, &$messageboard)
	{
		parent::__construct($params, $messageboard);
		$this->Name = 'FCaptcha';
		$this->Fields['Value'] = $this->FaultTolerance(JRequest::getVar('fcaptcha', null, 'POST'));
		$this->Fields['Secret'] = $this->FaultTolerance($this->session->get('captcha_answer', '', $this->namespace));
		$this->isvalid = intval($this->Validate());
	}
	
	
	protected function LoadFields()
	{
	}
	
	
	protected function LoadField($type, $number)
	{
	}
	
	
	public function Show()
	{
		if (!(bool) $this->Params->get('stdcaptchadisplay'))
		{
			return '';
		}
		
		$captcha_width = (int) $this->Params->get('stdcaptchawidth', '');
		$captcha_height = (int) $this->Params->get('stdcaptchaheight', '');
		$valid = !empty($this->Fields['Secret']) && $this->Fields['Value'] == $this->Fields['Secret'];
		$this->Fields['Name'] = $this->Params->get('stdcaptcha', '');
		$this->Fields['Display'] = 2;
		$this->CreateStandardLabel($this->Fields);
		$result = '<div class="control-group' . $this->TextStyleByValidation() . '"';
		if ($valid)
		{
			$result .= ' style="display:none !important;"';
		}
		
		$result .= '>' . PHP_EOL . $this->LabelHtmlCode . '<div ' . 'class="controls" ' . '>' . PHP_EOL;
		if (!$valid)
		{
			$src = JRoute::_('index.php?option=com_foxcontact' . '&view=loader' . '&owner=' . $this->Application->owner . '&id=' . $this->Application->oid . '&root=none' . '&filename=none' . '&type=captcha');
			if (strpos($src, '?') === false)
			{
				$src .= '?';
			}
			else
			{
				$src .= '&';
			}
			
			$src .= 'uniqueid=00000000';
			$result .= '<div class="fcaptchafieldcontainer">' . '<img src="' . $src . '" ' . 'class="fox_captcha_img" ' . 'alt="captcha" ' . 'id="fcaptcha_' . $this->GetId() . '" width="' . $captcha_width . '" height="' . $captcha_height . '"/>' . '</div>';
		}
		
		$result .= '<div class="fcaptchainputcontainer">' . $this->DescriptionByValidation() . '<input ' . 'type="text" ' . 'name="' . 'fcaptcha' . '" ' . 'style="width:' . ($captcha_width - 40) . 'px !important;" ' . 'value="' . $this->Fields['Value'] . '" ' . 'title="' . $this->Params->get('stdcaptcha', '') . '" ' . $this->placeholder;
		if ($valid)
		{
			$result .= 'readonly="readonly" ';
		}
		
		$result .= '/>' . '</div>';
		if (!$valid)
		{
			$result .= '<div class="fcaptcha-reload-container">' . '<img src="' . JUri::base(true) . '/media/com_foxcontact/images/transparent.gif" ' . 'id="reloadbtn_' . $this->GetId() . '" ' . 'alt="' . JText::_('COM_FOXCONTACT_RELOAD_ALT') . '" ' . 'title="' . JText::_('COM_FOXCONTACT_RELOAD_TITLE') . '" ' . 'width="16" height="16" ' . 'onclick="javascript:ReloadFCaptcha(\'fcaptcha_' . $this->GetId() . '\')" />' . '</div>' . '<script language="javascript" type="text/javascript">BuildReloadButton(\'reloadbtn_' . $this->GetId() . '\');</script>';
		}
		
		$result .= '</div>' . '</div>' . PHP_EOL;
		if (!$this->isvalid)
		{
			$this->MessageBoard->Add(JText::sprintf('COM_FOXCONTACT_ERR_INVALID_VALUE', JText::_('COM_FOXCONTACT_SECURITY_CODE')), FoxMessageBoard::error);
		}
		
		return $result;
	}
	
	
	private function build_label(&$field)
	{
		return '<label ' . 'class="control-label"' . '>' . $this->Params->get('stdcaptcha', '&nbsp;') . '</label>' . PHP_EOL;
	}
	
	
	protected function TextStyleByValidation()
	{
		if (!$this->Submitted)
		{
			return '';
		}
		
		return $this->isvalid ? ' success' : ' error';
	}
	
	
	public function Validate()
	{
		$isrequired = (bool) $this->Params->get('stdcaptchadisplay');
		$this->isvalid = !empty($this->Fields['Secret']) && $this->Fields['Value'] == $this->Fields['Secret'];
		return !($this->Submitted && $isrequired && !$this->isvalid);
	}
	
	
	private function DescriptionByValidation()
	{
		return $this->isvalid ? '' : ' <span class="asterisk"></span>';
	}
	
	
	private function FaultTolerance($string)
	{
		if ($string == $this->Params->get('stdcaptcha', ''))
		{
			return $string;
		}
		
		$string = strtolower($string);
		$string = preg_replace('/[l1]/', 'i', $string);
		$string = preg_replace('/[0]/', 'o', $string);
		$string = preg_replace('/[q9]/', 'g', $string);
		$string = preg_replace('/[5]/', 's', $string);
		$string = preg_replace('/[8]/', 'b', $string);
		return $string;
	}

}


class fcaptchaCheckEnvironment
{
	protected $InstallLog;
	
	public function __construct()
	{
		$this->InstallLog = new FLogger('fcaptchaimage', 'install');
		$this->InstallLog->Write('--- Determining if this system is able to draw captcha images ---');
		switch (true)
		{
			case $this->gd_usable():
				$value = 'use_gd';
				break;
			default:
				$value = 'disabled';
		}
		
		$db = JFactory::getDBO();
		$sql = "REPLACE INTO #__foxcontact_settings (name, value) VALUES ('captchadrawer', '{$value}');";
		$db->setQuery($sql);
		$result = $db->query();
		$this->InstallLog->Write("--- Method choosen to draw captcha images is [{$value}] ---");
		return $result;
	}
	
	
	private function gd_usable()
	{
		if (!extension_loaded('gd') || !function_exists('gd_info'))
		{
			$this->InstallLog->Write('gd extension not found');
			return false;
		}
		
		$this->InstallLog->Write('gd extension found. Let\'s see if it works.');
		$gdinfo = gd_info();
		foreach ($gdinfo as $key => $line)
		{
			$this->InstallLog->Write($key . '... [' . $line . ']');
		}
		
		$result = true;
		$result &= $this->testfunction('imagecreate');
		$result &= $this->testfunction('imagecolorallocate');
		$result &= $this->testfunction('imagefill');
		$result &= $this->testfunction('imageline');
		$result &= $this->testfunction('imagettftext');
		$result &= $this->testfunction('imagejpeg');
		$result &= $this->testfunction('imagedestroy');
		return $result;
	}
	
	
	private function testfunction($function)
	{
		$result = function_exists($function);
		$this->InstallLog->Write("testing function [{$function}]... [" . intval($result) . ']');
		return $result;
	}

}