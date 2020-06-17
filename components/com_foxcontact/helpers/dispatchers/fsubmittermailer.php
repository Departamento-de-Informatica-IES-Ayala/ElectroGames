<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdispatcher.php';

class FSubmitterMailer extends FDispatcher
{
	
	public function Process()
	{
		$copy_to_submitter = JFactory::getApplication()->input->post->get($this->SafeName('copy_to_submitter' . $this->GetId()), false, 'bool') || $this->Params->get('copy_to_submitter', null) == 1;
		if (!$copy_to_submitter || !isset($this->FieldsBuilder->Fields['sender1']) || empty($this->FieldsBuilder->Fields['sender1']['Value']))
		{
			$this->session->clear('filelist', $this->namespace);
			return true;
		}
		
		$mail = JFactory::getMailer();
		$this->set_from($mail);
		$this->set_to($mail);
		$mail->setSubject($this->subject('email_copy_subject'));
		$mail->Encoding = 'quoted-printable';
		$mail->setBody($this->body('email_copy_body'));
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
		
		$this->session->clear('filelist', $this->namespace);
		$sent = $this->send($mail);
		if ($sent)
		{
			$this->Logger->Write('Copy email sent.');
		}
		
		return $sent;
	}
	
	
	private function set_from(&$mail)
	{
		$emailhelper = new FoxEmailHelper($this->Params);
		$config = JComponentHelper::getParams('com_foxcontact');
		$default = (object) array('select' => 'admin', 'email' => '', 'name' => '');
		$submitteremailfrom = $config->get('submitteremailfrom', $default);
		$from = $emailhelper->convert($submitteremailfrom);
		$mail->setSender($from);
		$submitteremailreplyto = $config->get('submitteremailreplyto', $default);
		$replyto = $emailhelper->convert($submitteremailreplyto);
		$mail->ClearReplyTos();
		$mail->addReplyTo($replyto[0], $replyto[1]);
	}
	
	
	private function set_to(&$mail)
	{
		$addr = $this->FieldsBuilder->Fields['sender1']['Value'];
		$mail->addRecipient(JMailHelper::cleanAddress($addr));
	}
	
	
	protected function getAttachmentList()
	{
		$html = '';
		if (count($this->FileList))
		{
			foreach ($this->FileList as $file)
			{
				if (!empty($html))
				{
					$html .= ', ';
				}
				
				$html .= htmlspecialchars($file['realname']);
			}
		
		}
		
		return $html;
	}

}