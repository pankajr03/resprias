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

    $imgpath = URI::root() .'administrator/components/com_zhgooglemap/assets/icons/';
    $utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';

  
?>

<div class="card-columns zhgm-panel-container">
    <div class="col-md-12 module-wrapper">    

        <div class="card mb-3">
            <div class="card-header">
                <div class="zhgm-header-title">
                    <h2><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_MAIN'); ?></h2>
                </div>
            </div>
            <div class="card-body">
                <div class="row-striped">
                    <div>
                        
                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapmaps'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_map.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPS'); ?></span> 
                            </a>
                        </div>        
                    </div>   

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapmarkers'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_placemark.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERS'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapmarkergroups'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_tag.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERGROUPS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERGROUPS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPMARKERGROUPS'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=maprouters'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_route.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPROUTERS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPROUTERS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPROUTERS'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mappaths'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_path.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPPATHS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPPATHS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPPATHS'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=maptypes'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_map_type.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPTYPES'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPTYPES'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPTYPES'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapinfobubbles'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_infowin.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPINFOBUBBLES'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPINFOBUBBLES'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPINFOBUBBLES'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapstreetviews'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_streetview.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPSTREETVIEWS'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPSTREETVIEWS'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPSTREETVIEWS'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapoverrides'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_override.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPOVERRIDES'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPOVERRIDES'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_MAPOVERRIDES'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_categories&extension=com_zhgooglemap&view=categories'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_category.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_CATEGORIES'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_CATEGORIES'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_CATEGORIES'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=utils'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_config_tool.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_UTIL'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_UTIL'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_UTIL'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        

                    <div class="zhgm-panel-icon-wrapper">
                        <div class="zhgm-panel-icon">
                            <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&view=abouts'); ?>"> 
                                <img src="<?php echo $utilspath ?>img_about.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_ABOUT'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_ABOUT'); ?>"> 
                                <span><?php echo Text::_('COM_ZHGOOGLEMAP_SUBMENU_ABOUT'); ?></span> 
                            </a>
                        </div>        
                    </div>   
                        
                    </div>            
                </div>   
            </div>              
        </div>
    
        <div class="card mb-3">
            <div class="card-header">
                <div class="zhgm-header-title">
                    <h2><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_SUPPORT'); ?></h2>
                </div>
            </div>
            <div class="card-body">
                <p class="zhgm-panel-comment"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_SUPPORT_COMMENT'); ?></p>
                <div> 
                    <div>
                        <ul class="zhgm-panel-ul">
                            <li><i class="icon icon-question"></i><a href="http://forum.zhuk.cc/index.php/zh-googlemap" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_SUPPORT_FORUM'); ?></a></li>
                            <li><i class="icon icon-book"></i><a href="http://wiki.zhuk.cc/index.php/Zh_GoogleMap" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_SUPPORT_DOC'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>        
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                <div class="zhgm-header-title">
                    <h2><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_FEEDBACK'); ?></h2>
                </div>
            </div>
            <div class="card-body">
                <p class="zhgm-panel-comment"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_FEEDBACK_COMMENT'); ?></p>
                <div>
                    <div> 
                        <div>
                            <ul class="zhgm-panel-ul">
                                <li><i class="icon icon-thumbs-up"></i><a href="https://extensions.joomla.org/extensions/extension/maps-a-weather/maps-a-locations/zh-googlemap/" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_FEEDBACK_RATE'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>  
                
                <div> 
                    <div>
                        <ul class="zhgm-panel-ul">
                            <li><i class="icon icon-loop"></i><a href="https://www.transifex.com/dmitryzhuk/zh-googlemap/dashboard/" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_TRANSLATE'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>                
    </div>
      
    
    <div class="col-md-12 module-wrapper">
        <div class="card mb-3">
            <div class="card-header">
                <div class="zhgm-header-title">
                    <h2><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO'); ?></h2>
                </div>
            </div>
            <div class="card-body">
                <div> 
                    <div><img class="zhgm-panel-image" src="<?php echo $utilspath ?>img_main_gm.png" title="<?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO'); ?>" alt="<?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO'); ?>"> 
                    </div>
                    <table class="table zhgm-panel-table">
                    <tbody>    
                        <tr>
                        <td><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_JED_DOWNLOAD'); ?></td>
                        <td colspan="2"><a href="https://extensions.joomla.org/extensions/extension/maps-a-weather/maps-a-locations/zh-googlemap/" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_LAST_VERSION'); ?></a></td>
                        </tr>
                        <tr>
                        <td></td>
                        <td colspan="2"><a href="http://joomla.zhuk.cc/index.php/zhgooglemap-main" target="_blank"><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO_DEMO'); ?></a></td>
                        </tr>
                        <tr>
                        <td><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO_VERSION'); ?></td>
                        <td colspan="3"></td>
                        </tr>
                        <tr>
                        <?php
                        foreach($this->extList as $i => $item) {   
                          echo "<tr>";
                          echo "<td class=\"zhgm-panel-horizontal-type\">" . Text::_('COM_INSTALLER_TYPE_' . strtoupper($item->type)) . "</td>";   
                          $manifest = json_decode($item->manifest_cache, true);
                          echo "<td class=\"zhgm-panel-horizontal-value\">";   
                          echo "<span class=\"zhgm-panel-horizontal-value-version\">" . $manifest['version'] . "</span>";   
                          if ((int)$item->enabled == 1)
                          {
                            echo '<img src="'.$utilspath.'published1.png" title="'.Text::_("JSTATUS").'" alt="'.Text::_("JSTATUS").'">';
                          }
                          elseif ((int)$item->enabled == 0)
                          {
                            echo '<img src="'.$utilspath.'published0.png" title="'.Text::_("JSTATUS").'" alt="'.Text::_("JSTATUS").'">';
                          }
                          else 
                          {
                              echo Text::_("JSTATUS"). ": " . $item->enabled;
                          }
                          echo "</td>";   
                          echo "<td class=\"zhgm-panel-horizontal-desc\">";   
                          echo "" . Text::_($item->name);                          
                          echo "</td>";   
                          echo "</tr>";                          
                        }
                        
                        ?>
                        </td>
                        </tr>
                        <tr>
                        <td><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO_AUTHOR'); ?></td>
                        <td colspan="2"><a href="http://zhuk.cc" target="_blank">Dmitry Zhuk</a></td>
                        </tr>
                        <tr>
                        <td><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO_COPYRIGHT'); ?></td>
                        <td colspan="2"><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU GPLv2 or later</a><br /><br /><?php echo Text::_("COM_ZHGOOGLEMAP_DASHBOARD_OTHER_LICENSE"); ?></td>
                        </tr>
                        <tr>
                        <td><?php echo Text::_('COM_ZHGOOGLEMAP_DASHBOARD_INFO_DONATE'); ?></td>
                        <td colspan="2"><p><a href="http://joomla.zhuk.cc/index.php/donate" target="_blank"><img src="<?php echo $utilspath ?>btn_donate_CC_LG.gif" alt="Donate" width="147" height="47" /></a></p></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div>

</div>
