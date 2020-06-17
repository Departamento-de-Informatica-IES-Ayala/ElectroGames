<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class JFormFieldFConditionalList extends JFormField
{
	protected $type = 'FConditionalList';
	
	protected function getInput()
	{
		$html = array();
		$attr = '';
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}
		
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		$options = (array) $this->getOptions();
		$db = JFactory::getDBO();
		$sql = 'SELECT value FROM #__' . substr(basename(realpath(dirname(__FILE__) . '/../..')), 4) . '_settings WHERE name = \'' . $this->element['triggerkey'] . '\';';
		$db->setQuery($sql);
		$method = $db->loadResult();
		if (!$method || $method == $this->element['triggervalue'])
		{
			$attr .= ' disabled="disabled"';
			$this->value = (string) $this->element['triggerdata'];
		}
		
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}
		
		return implode($html);
	}
	
	
	protected function getOptions()
	{
		$options = array();
		foreach ($this->element->children() as $option)
		{
			if ($option->getName() != 'option')
			{
				continue;
			}
			
			$tmp = JHtml::_('select.option', (string) $option['value'], JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\\-]/', '_', $this->fieldname)), 'value', 'text', (string) $option['disabled'] == 'true');
			$tmp->class = (string) $option['class'];
			$tmp->onclick = (string) $option['onclick'];
			$options[] = $tmp;
		}
		
		reset($options);
		return $options;
	}

}