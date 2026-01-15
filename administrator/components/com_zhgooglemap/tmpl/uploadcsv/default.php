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

$urlProtocol = "http";

if ($this->httpsprotocol != "")
{
    if ((int)$this->httpsprotocol == 0)
    {
        $urlProtocol = 'https';
    }
}

$imgpath = URI::root() .'components/com_zhgooglemap/assets/icons/';
$utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';

?>
<div class="container-popup">
    <form
        class="form-horizontal form-validate"
        id="upload-form"
        name="adminForm"
        action="<?php echo Route::_('index.php?option=com_zhgooglemap&amp;task=mapbufmrks.file_load&amp;tmpl=component&amp;'. Session::getFormToken().'=1'); ?>"
        method="post"
                enctype="multipart/form-data">

        <?php 
                    foreach ($this->form->getFieldset() as $field) : 
                    if ($field->id == 'jform_icontype')
                    {
                            ?>
                            <div class="control-label">
                            <?php 
                                    echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 

                                    $iconTypeJS = " onchange=\"javascript:
                                    if (document.forms.adminForm.jform_icontype.options[selectedIndex].value!='') 
                                    {document.image.src='".$imgpath."' + document.forms.adminForm.jform_icontype.options[selectedIndex].value.replace(/#/g,'%23') + '.png'}
                                    else 
                                    {document.image.src=''}\"";


                                    $scriptPosition = ' name=';

                                    echo str_replace($scriptPosition, $iconTypeJS.$scriptPosition, $field->input);
                                    //echo '<img name="image" src="'.$imgpath .str_replace("#", "%23", $this->item->icontype).'.png" alt="" />';
                                    echo '<img name="image" src="" alt="" />';

                                    //echo '<div class="clr"></div>';
                                    echo '<a class="btn btn-primary" href="'.$urlProtocol.'://wiki.zhuk.cc/index.php?title=Zh_GoogleMap_Credits_Icons" target="_blank">'.JText::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).' <img src="'.$utilspath.'info.png" alt="'.JText::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).'" style="margin: 0;" /></a>';
                                    echo '<div class="clr"></div>';
                                    echo '<br />';
                            ?>
                            </div>
                            <?php 
                    }
                    else 
                    {
                        echo $this->form->renderField($field->fieldname); 
                    }
                    
                    endforeach; ?>

        <button class="hidden"
            id="closeBtn"
            type="button"
            onclick="window.parent.Joomla.Modal.getCurrent().close();">
        </button>
        <button class="hidden"
            id="uploadBtn"
            type="button"
            onclick="this.form.submit();">
        </button>
            
		<div>
				<?php echo HTMLHelper::_('form.token'); ?>
		</div>    
    </form>
</div>
