<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
require_once 'fdispatcher.php';

class DatabaseDispatcher extends FDispatcher
{
	
	public function Process()
	{
		if (!(bool) $this->Params->get('delivery_db', true))
		{
			return true;
		}
		
		$application = JFactory::getApplication();
		$prefix = $application->owner == 'module' ? '-' : '';
		$oid = $prefix . $application->oid;
		$fileds_json = $this->getFiledsJson();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__foxcontact_enquiries'));
		$query->set($db->quoteName('form_id') . '=' . $db->quote($oid));
		$query->set($db->quoteName('date') . '=' . $db->quote(JFactory::getDate()->toSql()));
		$query->set($db->quoteName('ip') . '=' . $db->quote($application->input->server->get('REMOTE_ADDR', '', 'string')));
		$query->set($db->quoteName('url') . '=' . $db->quote(JUri::current()));
		$query->set($db->quoteName('fields') . '=' . $db->quote($fileds_json));
		$db->setQuery((string) $query);
		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->MessageBoard->Add(JText::_('COM_FOXCONTACT_ERR_DATABASE'), FoxMessageBoard::error);
			$this->Logger->Write($e->getMessage());
			return false;
		}
		
		$this->Logger->Write('Enquiry saved to the database.');
		return true;
	}
	
	
	protected function getFiledsJson()
	{
		$body = array();
		foreach ($this->FieldsBuilder->Fields as $field)
		{
			if ($field['Type'] !== 'customhtml' && !empty($field['Value']))
			{
				$body[] = array($field['Type'], $field['Name'], $field['Value']);
			}
		
		}
		
		return json_encode($body);
	}

}