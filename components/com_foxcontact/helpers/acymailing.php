<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fnewsletter.php';

class FAcyMailing extends FNewsletter
{
	const subscribe = 1;
	const unsubscribe = -1;
	
	public function __construct(&$params, &$messageboard, &$fieldsbuilder)
	{
		parent::__construct($params, $messageboard, $fieldsbuilder);
		$this->Name = 'FAcyMailing';
		$this->prefix = 'acymailing';
	}
	
	
	public function Process()
	{
		if (!$this->enabled)
		{
			return true;
		}
		
		$cumulative = $this->JInput->post->get('acymailing_subscribe_cumulative', NULL, 'int');
		$checkboxes = array(FAcyMailing::subscribe => $this->JInput->post->get('acymailing_subscribe', array(), 'array'));
		$lists = $cumulative ? $checkboxes : array();
		if (empty($lists[FAcyMailing::subscribe]))
		{
			return true;
		}
		
		$subscriber = new stdClass();
		$subscriber->name = isset($this->FieldsBuilder->Fields['sender0']) ? $this->FieldsBuilder->Fields['sender0']['Value'] : '';
		$subscriber->email = empty($this->FieldsBuilder->Fields['sender1']['Value']) ? NULL : JMailHelper::cleanAddress($this->FieldsBuilder->Fields['sender1']['Value']);
		$userClass = acymailing_get('class.subscriber');
		$userClass->checkVisitor = false;
		$sub_id = $userClass->save($subscriber);
		if (empty($sub_id))
		{
			$this->logger->Write(get_class($this) . ' Process(): User save failed');
			return true;
		}
		
		$newSubscription = array();
		foreach ($lists[FAcyMailing::subscribe] as $listId)
		{
			$newList = array();
			$newList['status'] = FAcyMailing::subscribe;
			$newSubscription[$listId] = $newList;
		}
		
		if (!empty($newSubscription))
		{
			$userClass->saveSubscription($sub_id, $newSubscription);
		}
		
		@$lists[FAcyMailing::subscribe] or $lists[FAcyMailing::subscribe] = array();
		$this->logger->Write(get_class($this) . ' Process(): subscribed ' . $this->FieldsBuilder->Fields['sender0']['Value'] . ' (' . $this->FieldsBuilder->Fields['sender1']['Value'] . ') to lists ' . implode(',', $lists[FAcyMailing::subscribe]));
		return true;
	}
	
	
	protected function load_newsletter_config()
	{
		if (!(bool) $this->Params->get('acymailing'))
		{
			return $this->enabled = false;
		}
		
		$include = JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
		$this->enabled = (bool) @(include_once $include);
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
		
		if (!$this->extension_exists('acymailing'))
		{
			return;
		}
		
		$lists = $this->Params->get('acymailing_lists', array('NULL'));
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('listid') . ' as ' . $db->quoteName('id') . ',' . $db->quoteName('visible') . ',' . $db->quoteName('name'));
		$query->from($db->quoteName('#__acymailing_list'));
		$query->where($db->quoteName('published') . '=' . $db->quote('1'));
		$query->where($db->quoteName('listid') . ' IN (' . implode(',', $lists) . ')');
		$query->where('(' . $db->quoteName('languages') . ' LIKE ' . $db->quote('%' . JFactory::getLanguage()->getTag() . '%') . ' OR ' . $db->quoteName('languages') . ' LIKE ' . $db->quote('%all%') . ')');
		$query->where($db->quoteName('type') . '=' . $db->quote('list'));
		$query->order($db->quoteName('ordering'));
		$db->setQuery($query);
		$this->lists = $db->loadAssocList() or $this->lists = array();
	}

}