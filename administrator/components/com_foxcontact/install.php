<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class com_foxcontactInstallerScript
{
	private $InstallLog;
	private $results = array();
	private $component_name;
	private $extension_name;
	private $event;
	
	public function __construct($parent)
	{
	}
	
	
	public function install($parent)
	{
		$this->initialize($parent);
		$this->InstallLog->Write('Running ' . $this->event . ' on: ' . PHP_OS . ' | ' . $_SERVER['SERVER_SOFTWARE'] . ' | php ' . PHP_VERSION . ' | safe_mode: ' . intval(ini_get('safe_mode')) . ' | interface: ' . php_sapi_name());
		$this->chain_install($parent);
		$this->logo($parent);
	}
	
	
	public function uninstall($parent)
	{
	}
	
	
	public function update($parent)
	{
		@unlink(JPATH_ADMINISTRATOR . '/components/com_foxcontact/css/selext.css');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fsession.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fdispatcher.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fadminmailer.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fsubmittermailer.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fjmessenger.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fuploader.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/jtext.js');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/fileuploader.js');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/fileuploader.min.js');
		@unlink(JPATH_ROOT . '/modules/mod_foxcontact/mod_foxcontact.inc');
		@unlink(JPATH_ROOT . '/media/com_foxcontact/css/chosen.css');
		@unlink(JPATH_ROOT . '/media/com_foxcontact/css/bootstrap.css');
		$this->install($parent);
	}
	
	
	public function preflight($type, $parent)
	{
		$j_version = new JVersion();
		$j_min = (string) $parent->get('manifest')->attributes()->{'version'};
		$j_max = (string) $parent->get('manifest')->{'version'};
		if (version_compare($j_version->RELEASE, $j_min, '<') || version_compare($j_version->RELEASE, $j_max, '>'))
		{
			$j_max = preg_replace('/\\.[0-9]+$/', '', $j_max);
			JFactory::getApplication()->enqueueMessage('<h3><i class="icon-warning"></i> Fox Contact ' . $j_max . ' has not been tested on Joomla ' . $j_version->RELEASE . '</h3>' . 'The installation process will continue, but the current version may result incompatible with your current Joomla version.<br/>' . '<b>You should upgrade to Fox Contact ' . $j_version->RELEASE . ' series as soon as possible.</b><br/>' . 'If you have an active subscription, download now the latest release through the <a href="http://www.fox.ra.it/account-recovery.html">account recovery form</a>.<br/>' . 'Please do it before <a href="http://www.fox.ra.it/account-recovery.html">asking for technical support</a>.', 'error');
		}
		
		$this->component_name = $parent->get('element');
		$this->extension_name = substr($this->component_name, 4);
		$this->event = $type;
	}
	
	
	public function postflight($type, $parent)
	{
		$this->check_environment($parent);
		$this->InstallLog->Write('Component action \'' . $type . '\' completed.');
	}
	
	
	protected function initialize(&$parent)
	{
		(include_once JPATH_ROOT . '/components/' . $parent->get('element') . '/helpers/flogger.php') or die(JText::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', 'flogger.php'));
		$this->InstallLog = new FLogger('install script', 'install');
	}
	
	
	private function chain_install(&$parent)
	{
		$manifest = $parent->get('manifest');
		$extensions = isset($manifest->chain->extension) ? $manifest->chain->extension : array();
		$this->InstallLog->Write('Found ' . count($extensions) . ' chained extensions.');
		$result = array();
		foreach ($extensions as $extension)
		{
			$attributes = $extension->attributes();
			$item = $parent->getParent()->getPath('source') . '/' . $attributes['directory'] . '/' . $attributes['name'];
			if (is_dir($item))
			{
				$installer = new JInstaller();
				$result['type'] = strtoupper((string) $attributes['type']);
				$result['result'] = $installer->install($item) ? 'SUCCESS' : 'ERROR';
				$this->results[(string) $attributes['name']] = $result;
				$this->InstallLog->Write('Installing ' . $result['type'] . '... [' . $result['result'] . ']');
			}
		
		}
		
		$result['type'] = 'COMPONENT';
		$result['result'] = 'SUCCESS';
		$this->results[$this->component_name] = $result;
	}
	
	
	private function check_environment(&$parent)
	{
		$this->check_permissions($parent);
		$files = JFolder::files(JPATH_ROOT . '/components/' . $parent->get('element') . '/helpers', '.php') or $files = array();
		foreach ($files as $file)
		{
			(include_once JPATH_ROOT . '/components/' . $parent->get('element') . '/helpers/' . $file) or $this->InstallLog->Write(JText::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', $file));
			$name = JFile::stripExt($file);
			$class = $name . 'CheckEnvironment';
			if (class_exists($class))
			{
				new $class();
			}
		
		}
	
	}
	
	
	private function check_permissions(&$parent)
	{
		$permissions = fileperms(JPATH_ADMINISTRATOR . '/index.php');
		$buffer = sprintf('Determining correct file permissions...  [%o]', $permissions);
		$this->InstallLog->Write($buffer);
		if ($permissions)
		{
			$files = JFolder::files(JPATH_ROOT . '/components/' . $parent->get('element') . '/lib', '.php', false, true);
			foreach ($files as $file)
			{
				$this->set_permissions($file, $permissions);
			}
		
		}
		
		$permissions = fileperms(JPATH_ADMINISTRATOR);
		$buffer = sprintf('Determining correct directory permissions...  [%o]', $permissions);
		$this->InstallLog->Write($buffer);
		if ($permissions)
		{
			$this->set_permissions(JPATH_ROOT . '/components', $permissions);
			$this->set_permissions(JPATH_ROOT . '/components/' . $parent->get('element'), $permissions);
			$this->set_permissions(JPATH_ROOT . '/components/' . $parent->get('element') . '/lib', $permissions);
		}
	
	}
	
	
	private function set_permissions($filename, $permissions)
	{
		jimport('joomla.client.helper');
		$ftp_config = JClientHelper::getCredentials('ftp');
		if ($ftp_config['enabled'])
		{
			jimport('joomla.client.ftp');
			jimport('joomla.filesystem.path');
			$filename = JPath::clean(str_replace(JPATH_ROOT, $ftp_config['root'], $filename), '/');
			$ftp = new JFTP($ftp_config);
			$result = intval($ftp->chmod($filename, $permissions));
		}
		else
		{
			$result = intval(@chmod($filename, $permissions));
		}
		
		$this->InstallLog->Write("setting permissions for [{$filename}]... [{$result}]");
		return $result;
	}
	
	
	private function logo(&$parent)
	{
		$manifest = $parent->get('manifest');
		echo '<style type="text/css">' . '@import url("' . JUri::base(true) . '/components/' . $this->component_name . '/css/install.css' . '");' . '</style>' . '<img ' . 'class="install_logo" width="128" height="128" ' . 'src="' . (string) $manifest->authorUrl . 'logo/' . $this->extension_name . '-' . $this->event . '-logo.jpg" ' . 'alt="' . JText::_((string) $manifest->name) . ' Logo" ' . '/>' . '<div class="install_container">' . '<div class="install_row">' . '<h2 class="install_title">' . JText::_((string) $manifest->name) . '</h2>' . '</div>';
		foreach ($this->results as $name => $extension)
		{
			echo '<div class="install_row">' . '<div class="install_' . strtolower($extension['type']) . ' install_icon">' . JText::_('COM_INSTALLER_TYPE_' . $extension['type']) . '</div>' . '<div class="install_icon">' . $name . '</div>' . '<div class="install_' . strtolower($extension['result']) . ' install_icon">' . JText::sprintf('COM_INSTALLER_INSTALL_' . $extension['result'], '') . '</div>' . '</div>';
		}
		
		echo '</div>';
	}

}