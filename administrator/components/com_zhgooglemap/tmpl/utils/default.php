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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


    $wa  = $this->document->getWebAssetManager();

    $wa->registerAndUseStyle('zhgooglemaps.dashboard.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/utils.css');

    $imgpath = URI::root() .'components/com_zhgooglemap/assets/icons/';
    $utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';


?>

<div class="zhgm-panel-container">
    <div class="row">
        <div class="col-md-12 module-wrapper">
        
            <div class="card mb-12">
                <div class="card-header">
                    <div class="zhgm-header-title">
                        <h2><?php echo Text::_('COM_ZHGOOGLEMAP_UTILITIES_IMPORT'); ?></h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row-striped">
                        <div>
                            
                        <div class="zhgm-panel-icon-wrapper">
                            <div class="zhgm-panel-icon">
								<div class="zhgm-panel-icon">
									<a href="<?php echo Route::_('index.php?option=com_zhgooglemap&amp;view=mapbufmrks'); ?>"> 
										<img src="<?php echo $utilspath ?>import_csv.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPBUFMRKS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPBUFMRKS'); ?>"> 
										<span><?php echo Text::_('COM_ZHGOOGLEMAP_UTILITIES_IMPORT_CSV'); ?></span> 
									</a>
								</div>   
                            </div>        
                        </div>  
                        </div>                     
                    </div>
                </div>        
            </div>
         
        </div>
    </div>
    
</div>