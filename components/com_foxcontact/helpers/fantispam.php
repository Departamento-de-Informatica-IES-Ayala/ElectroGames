<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdatapump.php';
require_once $inc_dir . '/flogger.php';

class FAntispam extends FDataPump
{
	protected $FieldsBuilder;
	
	public function __construct($params, &$messageboard, $fieldsbuilder)
	{
		parent::__construct($params, $messageboard);
		$this->Name = 'FAntispam';
		$this->FieldsBuilder = $fieldsbuilder;
		$this->isvalid = intval($this->ValidateForSpam($fieldsbuilder));
	}
	
	
	public function Show()
	{
		if (!$this->isvalid)
		{
			$this->MessageBoard->Add($this->Params->get('spam_detected_text'), FoxMessageBoard::warning);
		}
		
		return '';
	}
	
	
	protected function LoadFields()
	{
	}
	
	
	protected function ValidateForSpam(&$fieldsbuilder)
	{
		$message = '';
		foreach ($fieldsbuilder->Fields as $key => $field)
		{
			if (strpos($field['Type'], 'textarea') !== 0)
			{
				continue;
			}
			
			$message .= $field['Value'];
		}
		
		$spam_words = $this->Params->get('spam_words', '');
		if (!(bool) $this->Params->get('spam_check', 0) && !(bool) $this->Params->get('copy_to_submitter', 0))
		{
			return true;
		}
		
		if (empty($spam_words))
		{
			return true;
		}
		
		$arr_spam_words = explode(',', $spam_words);
		foreach ($arr_spam_words as $word)
		{
			if (stripos($message, $word) !== false)
			{
				$this->log($fieldsbuilder);
				return false;
			}
		
		}
		
		return true;
	}
	
	
	protected function log($fieldsbuilder)
	{
		if ((bool) $this->Params->get('spam_log', true))
		{
			$dump = array();
			foreach ($fieldsbuilder->Fields as $field)
			{
				if ($field['Value'])
				{
					$dump[$field['Name']] = $field['Value'];
				}
			
			}
			
			$logger = new FLogger();
			$logger->Write('Spam attempt blocked: ' . json_encode($dump));
		}
	
	}

}