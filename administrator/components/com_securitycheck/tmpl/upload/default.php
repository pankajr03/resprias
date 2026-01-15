<?php 
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */


// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

Session::checkToken( 'get' ) or die( 'Invalid Token' );

// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
HTMLHelper::stylesheet($media_url);
?>



<form enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-horizontal">

	<div class="alert alert-warning">
		<?php echo Text::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS_ALERT'); ?>
	</div>
	
	<fieldset class="uploadform">
		<legend><?php echo Text::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></legend>
		<div class="control-group">
			<label for="install_package" class="control-label"><?php echo Text::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></label>
			<div class="controls">
				<input class="input_box" id="file_to_import" name="file_to_import" type="file" size="57" />
			</div>
			</div>
			<div class="form-actions">
				<input class="btn btn-primary" type="button" value="<?php echo Text::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('read_file')" />
		</div>
	</fieldset>


<input type="hidden" name="option" value="com_securitycheck" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="upload" />

</form>