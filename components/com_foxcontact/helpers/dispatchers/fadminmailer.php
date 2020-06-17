<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 * Contributions by Graeme Moffat
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdispatcher.php';

class FAdminMailer extends FDispatcher
{
	
	public function Process()
	{
		$mail = JFactory::getMailer();
		$this->set_from($mail);
		$this->set_to($mail, 'to_address', 'addRecipient');
		$this->set_to($mail, 'cc_address', 'addCC');
		$this->set_to($mail, 'bcc_address', 'addBCC');
		$mail->setSubject($this->subject('email_subject'));
		$mail->Encoding = 'quoted-printable';
		$mail->setBody($this->body('email_body'));
		$html2text = JPATH_ROOT . '/libraries/vendor/phpmailer/phpmailer/extras/class.html2text.php';
		if (file_exists($html2text))
		{
			require_once $html2text;
			$html2text = new Html2Text($mail->Body);
			$mail->AltBody = $html2text->get_text();
		}
		else
		{
			$mail->isHtml(true);
		}
		
		$this->addAttachments($mail);
		$sent = $this->send($mail);
		if ($sent)
		{
			$msg = preg_replace_callback('/{(.*?)}/', array($this, 'expand_variables_http_safe'), $this->Params->get('email_sent_text'));
			$this->MessageBoard->Add($msg, FoxMessageBoard::success);
			$this->Logger->Write('Notification email sent.');
		}
		
		return $sent;
	}
	
	
	private function set_from(&$mail)
	{
		$emailhelper = new FoxEmailHelper($this->Params);
		$config = JComponentHelper::getParams('com_foxcontact');
		$default = (object) array('select' => 'submitter', 'email' => '', 'name' => '');
		$adminemailfrom = $config->get('adminemailfrom', $default);
		$from = $emailhelper->convert($adminemailfrom);
		$mail->setSender($from);
		$adminemailreplyto = $config->get('adminemailreplyto', $default);
		$replyto = $emailhelper->convert($adminemailreplyto);
		$mail->ClearReplyTos();
		$mail->addReplyTo($replyto[0], $replyto[1]);
	}
	
	
	private function set_to(&$mail, $param_name, $method)
	{
		if ($this->Params->get($param_name, null))
		{
			$recipients = explode(',', $this->Params->get($param_name, ''));
		}
		else
		{
			$recipients = array();
		}
		
		foreach ($recipients as $recipient)
		{
			if (empty($recipient))
			{
				continue;
			}
			
			$mail->{$method}($recipient);
		}
	
	}
	
	
	protected function addAttachments(&$mail)
	{
		$sum = 0;
		foreach ($this->FileList as $file)
		{
			$sum += filesize($this->getFileName($file));
		}
		
		if ($sum < constant($this->Params->get('email_size_limit', 'MB2')))
		{
			foreach ($this->FileList as $file)
			{
				$mail->addAttachment($this->getFileName($file), $file['realname']);
			}
		
		}
	
	}
	
	
	protected function getFileName($file)
	{
		return JPATH_SITE . '/components/com_foxcontact/uploads/' . $file['filename'];
	}

}