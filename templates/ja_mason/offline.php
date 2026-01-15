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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$app = Factory::getApplication();

// Add JavaScript Frameworks
HTMLHelper::_('bootstrap.framework');

require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

$twofactormethods = UsersHelper::getTwoFactorMethods();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/offline.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/fonts/EdoSZ/fonts.css" type="text/css" />
	<link href='https://fonts.googleapis.com/css?family=Archivo:400,700|IBM+Plex+Sans+Condensed:400,700' rel='stylesheet' type='text/css'>
	<?php if ($this->direction == 'rtl') : ?>
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/offline_rtl.css" type="text/css" />
	<?php endif; ?>
  
</head>
<body>
<jdoc:include type="message" />
	<div id="frame" class="outline">
		<?php if ($app->get('offline_image')) : ?>
          <div class="offline-image">
            <?php echo HTMLHelper::_('image', $app->get('offline_image'), $app->get('sitename'), [], false, 0); ?>
          </div>
        <?php endif; ?>
		<h1>
			<?php echo htmlspecialchars($app->get('sitename'), ENT_COMPAT, 'UTF-8'); ?>
		</h1>
	<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
		<p class="message">
			<?php echo $app->get('offline_message'); ?>
		</p>
	<?php elseif ($app->get('display_offline_message', 1) == 2 && str_replace(' ', '', Text::_('JOFFLINE_MESSAGE')) != '') : ?>
		<p class="message">
			<?php echo Text::_('JOFFLINE_MESSAGE'); ?>
		</p>
	<?php endif; ?>
	<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
	<fieldset class="input">
		<p id="form-login-username">
			<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
			<input name="username" id="username" type="text" class="inputbox" alt="<?php echo Text::_('JGLOBAL_USERNAME'); ?>" autocomplete="off" autocapitalize="none" />
		</p>
		<p id="form-login-password">
			<label for="passwd"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
			<input type="password" name="password" class="inputbox" alt="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
		</p>
		<?php if (count($twofactormethods) > 1) : ?>
			<p id="form-login-secretkey">
				<label for="secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
				<input type="text" name="secretkey" class="inputbox" alt="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
			</p>
		<?php endif; ?>
		<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
		<p id="form-login-remember">
			<input type="checkbox" name="remember" class="inputbox" value="yes" alt="<?php echo Text::_('JGLOBAL_REMEMBER_ME') ?>" id="remember" />
			<label for="remember"><?php echo Text::_('JGLOBAL_REMEMBER_ME') ?></label>
		</p>
		<?php  endif; ?>
		<p id="submit-buton">
			<input type="submit" name="Submit" class="button login" value="<?php echo Text::_('JLOGIN'); ?>" />
		</p>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
	</form>
	</div>
</body>
</html>
