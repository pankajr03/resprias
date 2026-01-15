<?php
/**
 * ------------------------------------------------------------------------
 * JA Mason Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;
?>

<?php 
$app = JFactory::getApplication();
$input = $app->input;
$option = $input->getCmd('option');
$view = $input->getCmd('view');
$id     = $input->getInt('id');
if ($option != 'com_content' || $view != 'article' || in_array($id, [5, 15, 17, 19, 78])) :
if ($this->countModules('slideshow')) : ?>
	<!-- SLIDESHOW -->
	<div class="wrap t3-slideshow <?php $this->_c('slideshow') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('slideshow') ?>" />
	</div>
	<!-- //SLIDESHOW -->
<?php endif ?>
<?php endif ?>
