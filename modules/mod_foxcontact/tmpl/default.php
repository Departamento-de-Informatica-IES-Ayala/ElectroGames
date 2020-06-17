<?php defined("_JEXEC") or die(file_get_contents("index.html"));

/**
 * @package   Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license   Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see       Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

?>
<a name="<?php echo("mid_" . $module->id) ?>"></a>

<div
	id="foxcontainer_m<?php echo $module->id ?>"
	class="foxcontainer<?php echo $params->get("moduleclass_sfx") ?>">

	<?php if (!empty($page_subheading)) : ?>
		<h2><?php echo $page_subheading ?></h2>
	<?php endif ?>

	<?php $messageboard->Display() ?>

	<?php if (!empty($form_text)) : ?>
		<form enctype="multipart/form-data"
				id="fox_form_m<?php echo $module->id ?>"
				name="fox_form_m<?php echo $module->id ?>"
				class="fox_form foxform-<?php echo $params->get("form_layout", "extended") ?>"
				method="post"
				action="<?php echo $action ?>">
			<!-- <?php echo $app->scope . " " . (string)$xml->version . " " . (string)$xml->license ?> -->
			<?php echo $form_text ?>
		</form>
	<?php endif ?>
</div>
