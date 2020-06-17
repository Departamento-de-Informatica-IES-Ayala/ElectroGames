<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
jimport('foxcontact.constants');
$inc_dir = realpath(dirname(__FILE__));
require_once $inc_dir . '/fdatapump.php';
require_once JPATH_SITE . '/components/com_foxcontact/lib/functions.php';

class FAjaxUploader extends FDataPump
{
	
	public function __construct($params, &$messageboard)
	{
		parent::__construct($params, $messageboard);
		$this->Name = 'FAjaxFilePump';
		$this->isvalid = intval($this->Validate());
	}
	
	
	protected function LoadFields()
	{
	}
	
	
	public function Show()
	{
		if (!(bool) $this->Params->get('uploaddisplay'))
		{
			return '';
		}
		
		static $resources = true;
		if ($resources)
		{
			$document = JFactory::getDocument();
			$document->addScript(JUri::base(true) . '/media/com_foxcontact/js/upload.min.js');
			$resources = false;
		}
		
		$id = $this->GetId();
		JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function () {' . "CreateUploadButton('foxupload_{$id}'," . '\'' . $this->Application->owner . '\',' . $this->Application->oid . ',' . '\'' . JRoute::_('index.php?option=com_foxcontact&view=loader&root=none&filename=none&type=uploader&owner=' . $this->Application->owner . '&id=' . $this->Application->oid) . '\');' . '});' . PHP_EOL);
		$label = '';
		$span = '';
		if ((bool) $this->Params->get('labelsdisplay'))
		{
			$label = '<label class="control-label">' . $this->Params->get('upload') . '</label>';
		}
		else
		{
			$span = '<span class="help-block">' . $this->Params->get('upload') . '</span>';
		}
		
		$result = '<div class="control-group">' . $label . '<div class="controls">' . $span . '<div id="foxupload_' . $id . '"></div>' . '<span class="help-block">' . JText::_('COM_FOXCONTACT_FILE_SIZE_LIMIT') . ' ' . HumanReadable(constant($this->Params->get('upload_max_file_size', 'MB100'))) . '</span>' . '<span><noscript>' . JText::_('COM_FOXCONTACT_JAVASCRIPT_REQUIRED') . '</noscript></span>' . '</div>' . PHP_EOL . '</div>' . PHP_EOL;
		$filelist = $this->session->get('filelist', array(), $this->namespace);
		$result .= '<div class="control-group">' . '<div class="controls">';
		$result .= '<ul id="uploadlist-' . $this->Application->owner . $this->Application->oid . '" class="qq-upload-list">';
		foreach ($filelist as $index => $file)
		{
			$result .= '<li class="qq-upload-success">' . '<span class="qq-upload-file">' . $this->format_filename($file['realname']) . '</span>' . '<span class="qq-upload-size">' . HumanReadable($file['size']) . '</span>' . '<span class="qq-upload-success-text">' . JText::_('COM_FOXCONTACT_SUCCESS') . '</span>' . '<span class="qq-upload-remove" title="' . JText::_('COM_FOXCONTACT_REMOVE_TITLE') . '" onclick="deletefile(this,' . $index . ',\'' . JRoute::_('index.php?option=com_foxcontact&view=loader&root=none&filename=none&type=uploader&owner=' . $this->Application->owner . '&id=' . $this->Application->oid) . '\')">' . JText::_('COM_FOXCONTACT_REMOVE_ALT') . '</span>' . '</li>';
		}
		
		$result .= '</ul>' . PHP_EOL;
		$result .= '</div>' . '</div>' . PHP_EOL;
		if (!$this->isvalid)
		{
			$this->MessageBoard->Add(JText::sprintf('COM_FOXCONTACT_ERR_MIN_UPLOAD_FILES_NOT_REACHED', $this->Params->get('upload_min_files', 0)), FoxMessageBoard::error);
		}
		
		return $result;
	}
	
	
	protected function format_filename($value)
	{
		if (strlen($value) > 33)
		{
			if (function_exists('mb_substr'))
			{
				$substr = 'mb_substr';
			}
			else
			{
				$substr = 'substr';
			}
			
			$value = $substr($value, 0, 19) . '...' . $substr($value, -13);
		}
		
		return $value;
	}
	
	
	protected function Validate()
	{
		$min_files = $this->Params->get('upload_min_files', 0);
		$isrequired = (bool) $min_files;
		$filelist = $this->session->get('filelist', array(), $this->namespace);
		$this->isvalid = count($filelist) >= $min_files;
		return !($this->Submitted && $isrequired && !$this->isvalid);
	}

}