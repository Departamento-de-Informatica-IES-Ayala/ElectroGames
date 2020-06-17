<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
require_once 'fdispatcher.php';

class FJMessenger extends FDispatcher
{
	
	public function Process()
	{
		$uid = $this->Params->get('jmessenger_user', NULL);
		if (!$uid)
		{
			return true;
		}
		
		$body = $this->body('email_body');
		$subject = htmlspecialchars($this->submittername() . ' (' . $this->submitteraddress() . ') ' . $this->subject('email_subject'));
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__messages'));
		$query->set($db->quoteName('user_id_from') . '=' . $db->quote($uid));
		$query->set($db->quoteName('user_id_to') . '=' . $db->quote($uid));
		$query->set($db->quoteName('date_time') . '=' . $db->quote(JFactory::getDate()->toSql()));
		$query->set($db->quoteName('subject') . '=' . $db->quote($subject));
		$query->set($db->quoteName('message') . '=' . $db->quote($body));
		$db->setQuery((string) $query);
		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->MessageBoard->Add(JText::_('COM_FOXCONTACT_ERR_SENDING_MESSAGE'), FoxMessageBoard::error);
			$this->Logger->Write($e->getMessage());
			return false;
		}
		
		$this->Logger->Write('Private message sent to Joomla messenger.');
		return true;
	}

}