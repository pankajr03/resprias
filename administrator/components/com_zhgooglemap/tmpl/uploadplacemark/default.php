<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;


?>
<div class="container-popup">
    <form
        class="form-horizontal form-validate"
        id="import-form"
        name="adminForm"
        action="<?php echo Route::_('index.php?option=com_zhgooglemap&amp;task=mapbufmrks.marker_load_all&amp;tmpl=component&amp;'. Session::getFormToken().'=1'); ?>"
        method="post">

        <?php 
		foreach ($this->form->getFieldset() as $field) : 
		
			echo $this->form->renderField($field->fieldname); 

		endforeach; ?>

        <button class="hidden"
            id="closeBtn"
            type="button"
            onclick="window.parent.Joomla.Modal.getCurrent().close();">
        </button>
        <button class="hidden"
            id="importBtn"
            type="button"
            onclick="this.form.submit();">
        </button>
            
		<div>
				<?php echo HTMLHelper::_('form.token'); ?>
		</div>    
    </form>
</div>
