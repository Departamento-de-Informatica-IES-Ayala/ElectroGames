<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 * Contributions by Graeme Moffat
 */
$inc_dir = realpath(__DIR__ . '/..');
require_once $inc_dir . '/fdatapump.php';
require_once $inc_dir . '/flogger.php';
require_once $inc_dir . '/emailhelper.php';
jimport('joomla.mail.helper');
jimport('foxcontact.foxhtml.foxhtml');
require_once JPATH_ADMINISTRATOR . '/components/com_foxcontact/helpers/template.php';

abstract class FDispatcher extends FDataPump
{
	protected $FieldsBuilder;
	protected $FileList;
	
	public abstract function Process();
	
	protected function LoadFields()
	{
	}
	
	
	public function __construct($params, &$messageboard, &$fieldsbuilder)
	{
		parent::__construct($params, $messageboard);
		$this->FieldsBuilder = $fieldsbuilder;
		$this->Logger = new FLogger();
		$this->FileList = $this->session->get('filelist', array(), $this->namespace);
	}
	
	
	protected function submittername()
	{
		return isset($this->FieldsBuilder->Fields['sender0']) ? $this->FieldsBuilder->Fields['sender0']['Value'] : JFactory::getConfig()->get('fromname');
	}
	
	
	protected function submitteraddress()
	{
		$addr = isset($this->FieldsBuilder->Fields['sender1']['Value']) && !empty($this->FieldsBuilder->Fields['sender1']['Value']) ? $this->FieldsBuilder->Fields['sender1']['Value'] : JFactory::getConfig()->get('mailfrom');
		return JMailHelper::cleanAddress($addr);
	}
	
	
	protected function subject($field_name)
	{
		$subject = JMailHelper::cleanSubject($this->Params->get($field_name));
		$expanded = preg_replace_callback('/{(.*?)}/', array('FDispatcher', 'expand_variables'), $subject);
		return JMailHelper::cleanSubject($expanded);
	}
	
	
	protected function body($def_body_name)
	{
		$html = $this->body_no_style($def_body_name);
		$css = FoxHtml::parseCss(FTmlHelper::renderTemplate('user_email_tmpl.css'));
		return FoxHtml::embedClassToStyles($html, $css);
	}
	
	
	protected function body_no_style($name)
	{
		$body = JMailHelper::cleanBody($this->Params->get($name));
		if (empty($body))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_foxcontact/helpers/template.php';
			$body = FTmlHelper::renderTemplate('admin_email_tmpl.php');
		}
		
		$body = preg_replace_callback('/{(.*?)}/', array('FDispatcher', 'expand_variables_http_safe'), $body);
		return JMailHelper::cleanBody('<div dir="' . (JFactory::getLanguage()->isRTL() ? 'rtl' : 'ltr') . '">' . $body . '</div>');
	}
	
	
	protected function SiteName()
	{
		return JFactory::getConfig()->get('sitename');
	}
	
	
	protected function send(&$mail)
	{
		if (($error = $mail->Send()) !== true)
		{
			if (is_object($error))
			{
				$info = $error->getMessage();
			}
			else
			{
				if (!empty($mail->ErrorInfo))
				{
					$info = $mail->ErrorInfo;
				}
				else
				{
					$info = JText::_('JLIB_MAIL_FUNCTION_OFFLINE');
				}
			
			}
			
			$msg = JText::_('COM_FOXCONTACT_ERR_SENDING_MAIL') . '. ' . $info;
			$this->MessageBoard->Add($msg, FoxMessageBoard::error);
			$this->Logger->Write($msg);
			return false;
		}
		
		return true;
	}
	
	
	protected function expand_variables($matches)
	{
		return $this->_expand_variables($matches, false);
	}
	
	
	protected function expand_variables_http_safe($matches)
	{
		return $this->_expand_variables($matches, true);
	}
	
	
	protected function _expand_variables($matches, $http_safe)
	{
		$keyword = strtolower($matches[1]);
		$replace = '';
		$encode = true;
		switch ($keyword)
		{
			case 'user':
				$replace = JFactory::getUser()->get('username') or $replace = JText::_('COM_FOXCONTACT_NOT_LOGGED_IN');
				break;
			case 'name':
				$replace = $this->submittername();
				break;
			case 'email':
				$replace = $this->submitteraddress();
				break;
			case 'date':
				$replace = JHtml::date('now', 'Y-m-d', false);
				break;
			case 'time':
				$replace = JHtml::date('now', 'H:i', false);
				break;
			case 'date-utc':
				$replace = JHtml::date('now', 'Y-m-d', 'UTC');
				break;
			case 'time-utc':
				$replace = JHtml::date('now', 'H:i', 'UTC') . ' UTC';
				break;
			case 'field-table-full':
				$replace = $this->getFieldTable(true, true);
				$encode = false;
				break;
			case 'field-table-without-attachments':
				$replace = $this->getFieldTable(false, true);
				$encode = false;
				break;
			case 'field-table-without-client-info':
				$replace = $this->getFieldTable(true, false);
				$encode = false;
				break;
			case 'field-table':
				$replace = $this->getFieldTable(false, false);
				$encode = false;
				break;
			case 'attachments':
				$replace = $this->getAttachmentList();
				$encode = false;
				break;
			case 'client-ip-address':
				$replace = JFactory::getApplication()->input->server->get('REMOTE_ADDR', '', 'string');
				break;
			case 'sitename':
				$replace = $this->SiteName();
				break;
			case 'current-url':
				$replace = JUri::current();
				break;
			case 'page-title':
				$replace = JFactory::getDocument()->getTitle();
				break;
			default:
				if (isset($this->FieldsBuilder->Fields[$keyword]))
				{
					$replace = $this->FieldsBuilder->Fields[$keyword]['Value'];
				}
		
		}
		
		if (!$encode && !$http_safe)
		{
			return '';
		}
		
		return $encode && $http_safe ? nl2br(htmlspecialchars($replace)) : $replace;
	}
	
	
	protected function getFieldTable($include_attachment, $include_client_info)
	{
		$html = '';
		foreach ($this->FieldsBuilder->Fields as $key => $field)
		{
			if ($field['Type'] !== 'customhtml' && !empty($field['Value']))
			{
				$html .= $this->getFieldRow($field);
			}
		
		}
		
		if ($include_attachment && count($this->FileList) > 0)
		{
			$html .= '<dt class=\'field-title\'>' . JText::_('COM_FOXCONTACT_ATTACHMENTS') . '</dt><dd class=\'field-content\'>' . $this->getAttachmentList() . '</dd>';
		}
		
		if ($include_client_info)
		{
			$html .= '<dt class=\'field-title\'>' . JText::_('COM_FOXCONTACT_SITE_NAME') . '</dt><dd class=\'field-content\'>' . htmlspecialchars($this->SiteName()) . '</dd>';
			$html .= '<dt class=\'field-title\'>' . JText::_('COM_FOXCONTACT_CURRENT_URL') . '</dt><dd class=\'field-content\'>' . '<a href=\'' . JUri::current() . '\' class=\'field-table-href\'>' . htmlspecialchars(JFactory::getDocument()->getTitle()) . '</a>' . '</dd>';
			if ($this->Params->get('client_ip', false, 'bool'))
			{
				$ip = JFactory::getApplication()->input->server->get('REMOTE_ADDR', '', 'string');
				if (function_exists('geoip_record_by_name'))
				{
					$record = geoip_record_by_name($ip) or $record = array('country_name' => JText::_('JLIB_UNKNOWN'), 'city' => JText::_('JLIB_UNKNOWN'));
					$ip .= ' - ' . JText::sprintf('COM_FOXCONTACT_LOCATION_ORIGIN', utf8_encode($record['country_name']) . ', ' . utf8_encode($record['city']));
				}
				
				$html .= '<dt class=\'field-title\'>' . JText::_('COM_FOXCONTACT_CLIENT_IP_ADDRESS') . '</dt><dd class=\'field-content\'>' . $ip . '</dd>';
			}
		
		}
		
		return "<dl class='fields-list'>{$html}</dl>";
	}
	
	
	protected function getFieldRow($field)
	{
		$name = JFilterInput::getInstance()->clean($field['Name'], '');
		$hname = htmlspecialchars($name);
		$value = JFilterInput::getInstance()->clean($field['Value'], '');
		$hvalue = nl2br(htmlspecialchars($value));
		return "<dt class='field-title'>{$hname}</dt><dd class='field-content'>{$hvalue}</dd>";
	}
	
	
	protected function getAttachmentList()
	{
		$html = '';
		if (count($this->FileList))
		{
			foreach ($this->FileList as $file)
			{
				$url = JUri::base() . 'components/com_foxcontact/uploads/' . $file['filename'];
				$name = htmlspecialchars($file['realname']);
				if (!empty($html))
				{
					$html .= ', ';
				}
				
				$html .= "<a href='{$url}' class='field-table-href'>{$name}</a>";
			}
		
		}
		
		return $html;
	}

}