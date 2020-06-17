<?php defined("_JEXEC") or die(file_get_contents("index.html"));

/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

?>
<a name="cid_<?php echo $this->menu_id ?>"></a>

<div
	id="foxcontainer_c<?php echo $this->menu_id ?>"
	class="foxcontainer<?php echo $this->cparams->get('pageclass_sfx') ?>">
	
	<?php if ($this->cparams->get('show_page_heading')) : ?>
		<h1><?php echo $this->escape($this->cparams->get('page_heading')) ?></h1>
	<?php endif ?>

	<?php if (!empty($this->page_subheading)) : ?>
		<h2><?php echo $this->page_subheading ?></h2>
	<?php endif ?>

	<?php $this->MessageBoard->Display() ?>

	<?php if (!empty($this->FormText)) : ?>
		<form enctype="multipart/form-data"
				id="fox_form_c<?php echo $this->menu_id ?>"
				name="fox_form_c<?php echo $this->menu_id ?>"
				class="fox_form foxform-<?php echo $this->cparams->get("form_layout", "extended") ?>"
				method="post"
				action="<?php echo(JFactory::getApplication()->input->server->get("REQUEST_URI", "", "string") . "#cid_" . $this->menu_id) ?>">
			<!-- <?php echo "com_" . $this->_name . " " . (string)$this->xml->version . " " . (string)$this->xml->license ?> -->
			<?php echo $this->FormText ?>
		</form>
	<?php endif ?>
</div>
