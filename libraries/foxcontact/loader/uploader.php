<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
jimport('foxcontact.constants');
require_once JPATH_COMPONENT . '/helpers/flogger.php';
require_once JPATH_COMPONENT . '/helpers/fmimetype.php';
require_once 'loader.php';

class uploaderLoader extends UncachableLoader
{
	
	protected function type()
	{
		return 'uploader';
	}
	
	
	protected function content_header()
	{
	}
	
	
	protected function content_footer()
	{
	}
	
	
	protected function load()
	{
		switch (true)
		{
			case isset($_GET['qqfile']):
				$um = new XhrUploadManager();
				break;
			case isset($_FILES['qqfile']):
				$um = new FileFormUploadManager();
				break;
			case JFactory::getApplication()->input->get('action', 0) == 'deletefile':
				$um = new XhrDeleteManager();
				break;
			default:
				$result = array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_NO_FILE'));
				die(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
		}
		
		$um->Params =& $this->Params;
		$result = $um->HandleUpload(JPATH_COMPONENT . '/uploads/');
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	}

}


abstract class FUploadManager
{
	protected $Log;
	protected $session;
	protected $namespace;
	
	protected abstract function save_file($path);
	
	protected abstract function get_file_name();
	
	protected abstract function get_file_size();
	
	public function __construct()
	{
		$this->Log = new FLogger();
		$this->session = JFactory::getSession();
		$owner = JFactory::getApplication()->input->get('owner', null);
		$id = JFactory::getApplication()->input->get('id', null);
		$this->namespace = 'foxcontact_' . $owner . '_' . $id;
	}
	
	
	public function HandleUpload($uploadDirectory)
	{
		if (!(bool) $this->Params->get('uploaddisplay', 0))
		{
			return array('error' => ' [upload disabled]');
		}
		
		if (!is_writable($uploadDirectory))
		{
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_DIR_NOT_WRITABLE'));
		}
		
		$size = $this->get_file_size();
		if ($size == 0)
		{
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_FILE_EMPTY'));
		}
		
		if ($size > constant($this->Params->get('upload_max_file_size', 'MB100')))
		{
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_FILE_TOO_LARGE'));
		}
		
		$realname = $this->get_file_name();
		$filename = JFilterInput::getInstance()->clean($realname, 'cmd');
		$filename = basename($filename);
		$realname = trim($this->get_file_name(), '.');
		$forbidden_extensions = '/ph(p[345st]?|t|tml|ar)/i';
		if (preg_match($forbidden_extensions, $filename))
		{
			$this->Log->Write('Blocked a file upload attempt [forbidden extension]: ' . $filename);
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_MIME') . ' [forbidden extension]');
		}
		
		$filename = uniqid() . '-' . $filename;
		$full_filename = $uploadDirectory . $filename;
		if (!$this->save_file($full_filename))
		{
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_SAVE_FILE'));
		}
		
		$mimetype = new FMimeType();
		if (!$mimetype->Check($full_filename, $this->Params))
		{
			unlink($full_filename);
			return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_MIME') . ' [' . $mimetype->Mimetype . ']');
		}
		
		$filelist = $this->session->get('filelist', array(), $this->namespace);
		if (count($filelist) >= $this->Params->get('upload_max_files', 10))
		{
			return array('error' => JText::_('COM_FOXCONTACT_ERR_MAX_UPLOAD_FILES_EXCEEDED'));
		}
		
		$chunk_size = MB1;
		$back_step = -6;
		$handle = fopen($full_filename, 'rb');
		do
		{
			$content = fread($handle, $chunk_size);
			fseek($handle, $back_step, SEEK_CUR);
			if (strpos($content, '<?php') !== false || strpos($content, '<script') !== false)
			{
				fclose($handle);
				unlink($full_filename);
				$this->Log->Write('Blocked a file upload attempt [forbidden content]: ' . $filename);
				return array('error' => JFactory::getLanguage()->_('COM_FOXCONTACT_ERR_MIME') . ' [forbidden content]');
			}
		
		} while (strlen($content) == $chunk_size);
		
		fclose($handle);
		$filelist[] = array('filename' => $filename, 'realname' => $realname, 'size' => $size);
		$this->session->set('filelist', $filelist, $this->namespace);
		end($filelist);
		$last = key($filelist);
		$this->Log->Write('File ' . $filename . ' uploaded succesful.');
		return array('success' => true, 'index' => $last);
	}

}


class XhrUploadManager extends FUploadManager
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	protected function save_file($path)
	{
		$input = fopen('php://input', 'r');
		$target = fopen($path, 'w');
		$realSize = stream_copy_to_stream($input, $target);
		fclose($input);
		fclose($target);
		return $realSize == $this->get_file_size();
	}
	
	
	protected function get_file_name()
	{
		return $_GET['qqfile'];
	}
	
	
	protected function get_file_size()
	{
		if (isset($_SERVER['CONTENT_LENGTH']))
		{
			return (int) $_SERVER['CONTENT_LENGTH'];
		}
		
		return 0;
	}

}


class FileFormUploadManager extends FUploadManager
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	protected function save_file($path)
	{
		return move_uploaded_file($_FILES['qqfile']['tmp_name'], $path);
	}
	
	
	protected function get_file_name()
	{
		return $_FILES['qqfile']['name'];
	}
	
	
	protected function get_file_size()
	{
		return $_FILES['qqfile']['size'];
	}

}


class XhrDeleteManager
{
	
	public function HandleUpload($uploadDirectory)
	{
		$fileindex = JFactory::getApplication()->input->get('fileindex', 0);
		$owner = JFactory::getApplication()->input->get('owner', null);
		$id = JFactory::getApplication()->input->get('id', null);
		$namespace = 'foxcontact_' . $owner . '_' . $id;
		$jsession = JFactory::getSession();
		$filelist = $jsession->get('filelist', array(), $namespace);
		if (!isset($filelist[$fileindex]))
		{
			return array('error' => 'Index not found');
		}
		
		$deleted = @unlink($uploadDirectory . $filelist[$fileindex]['filename']);
		if (!$deleted)
		{
			return array('error' => 'Unable to delete the file');
		}
		
		unset($filelist[$fileindex]);
		$jsession->set('filelist', $filelist, $namespace);
		return array('success' => true);
	}

}