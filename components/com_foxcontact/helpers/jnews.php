<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fnewsletter.php';

class FJNewsSubscriber extends FNewsletter
{
	
	public function __construct(&$params, &$messageboard, &$fieldsbuilder)
	{
		parent::__construct($params, $messageboard, $fieldsbuilder);
		$this->Name = 'FJNews';
		$this->prefix = 'jnews';
	}
	
	
	public function Process()
	{
		if (!$this->enabled)
		{
			return true;
		}
		
		$config = new jNews_Config();
		$subscriber = new stdClass();
		$cumulative = $this->JInput->post->get('jnews_subscribe_cumulative', NULL, 'int');
		$checkboxes = $this->JInput->post->get('jnews_subscribe', array(), 'array');
		$subscriber->list_id = $cumulative ? $checkboxes : array();
		if (empty($subscriber->list_id))
		{
			return true;
		}
		
		$subscriber->name = isset($this->FieldsBuilder->Fields['sender0']) ? $this->FieldsBuilder->Fields['sender0']['Value'] : '';
		$subscriber->email = empty($this->FieldsBuilder->Fields['sender1']['Value']) ? NULL : JMailHelper::cleanAddress($this->FieldsBuilder->Fields['sender1']['Value']);
		if (empty($subscriber->email))
		{
			$this->logger->Write(get_class($this) . ' Process(): Email address empty. User save aborted.');
			return true;
		}
		
		$subscriber->confirmed = !(bool) $config->get('require_confirmation');
		$subscriber->receive_html = 1;
		$subscriber->ip = jNews_Subscribers::getIP();
		$subscriber->subscribe_date = jnews::getNow();
		$subscriber->language_iso = 'eng';
		$subscriber->timezone = '00:00:00';
		$subscriber->blacklist = 0;
		$subscriber->user_id = JFactory::getUser()->id;
		$sub_id = null;
		jNews_Subscribers::saveSubscriber($subscriber, $sub_id, true);
		if (empty($sub_id))
		{
			$this->logger->Write(get_class($this) . ' Process(): User save failed');
			return true;
		}
		
		$this->SaveSubscription($subscriber);
		$this->logger->Write(get_class($this) . ' Process(): subscribed ' . $this->FieldsBuilder->Fields['sender0']['Value'] . ' (' . $this->FieldsBuilder->Fields['sender1']['Value'] . ') to lists ' . implode(',', $subscriber->list_id));
		return true;
	}
	
	
	protected function load_newsletter_config()
	{
		if (!(bool) $this->Params->get('jnews'))
		{
			return $this->enabled = false;
		}
		
		defined('JNEWS_JPATH_ROOT') or define('JNEWS_JPATH_ROOT', JPATH_ROOT);
		$mainAdminPathDefined = JPATH_ROOT . '/components/com_jnews/defines.php';
		$this->enabled = (bool) @(include_once $mainAdminPathDefined);
		$jnews_include = JNEWS_JPATH_ROOT . '/administrator/components/' . JNEWS_OPTION . '/classes/class.jnews.php';
		$this->enabled &= (bool) @(include_once $jnews_include);
		$found = $this->enabled ? ' ' : ' not ';
		$this->logger->Write(get_class($this) . ' Newsletter component' . $found . 'found');
	}
	
	
	protected function load_newsletter_lists()
	{
		$this->lists = array();
		if (!$this->enabled)
		{
			return;
		}
		
		if (!$this->extension_exists('jnews'))
		{
			return;
		}
		
		$lists = $this->Params->get('jnews_lists', array('NULL'));
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id') . ',' . $db->quoteName('hidden') . ' as ' . $db->quoteName('visible') . ',' . $db->quoteName('list_name') . ' as ' . $db->quoteName('name'));
		$query->from($db->quoteName('#__jnews_lists'));
		$query->where($db->quoteName('published') . '=' . $db->quote('1'));
		$query->where($db->quoteName('id') . ' IN (' . implode(',', $lists) . ')');
		$db->setQuery($query);
		$this->lists = $db->loadAssocList() or $this->lists = array();
	}
	
	
	public function SaveSubscription($subscriber)
	{
		if (empty($subscriber->list_id) || empty($subscriber->id))
		{
			return false;
		}
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		foreach ($subscriber->list_id as $listid)
		{
			$query->clear();
			$query->update($db->quoteName('#__jnews_listssubscribers'));
			$query->set($db->quoteName('subdate') . '=' . time());
			$query->set($db->quoteName('unsubdate') . '= 0');
			$query->set($db->quoteName('unsubscribe') . '= 0');
			$query->where($db->quoteName('list_id') . '=' . (int) $listid);
			$query->where($db->quoteName('subscriber_id') . '=' . (int) $subscriber->id);
			$db->setQuery($query);
			try
			{
				$result = $db->execute();
				$affected = $db->getAffectedRows();
				switch ($affected)
				{
					case -1:
						JFactory::getApplication()->enqueueMessage($db->getErrorMsg(true), 'error');
						break;
					case 0:
						$query->clear();
						$query->insert($db->quoteName('#__jnews_listssubscribers'));
						$query->set($db->quoteName('list_id') . '=' . (int) $listid);
						$query->set($db->quoteName('subscriber_id') . '=' . (int) $subscriber->id);
						$query->set($db->quoteName('subdate') . '=' . time());
						$query->set($db->quoteName('unsubdate') . '= 0');
						$query->set($db->quoteName('unsubscribe') . '= 0');
						$db->setQuery($query);
						try
						{
							$result = $db->execute();
							if (!$result)
							{
								JFactory::getApplication()->enqueueMessage($db->getErrorMsg(true), 'error');
							}
						
						}
						catch (RuntimeException $e)
						{
							JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
						}
						
						break;
				}
			
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		
		}
		
		return true;
	}

}