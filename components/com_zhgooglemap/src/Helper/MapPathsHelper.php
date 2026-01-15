<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap Component
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
namespace ZhukDL\Component\ZhGoogleMap\Site\Helper;
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Zh GoogleMap Helper
 */
abstract class MapPathsHelper
{

    public static function get_path_content_string(
                        $currentArticleId,
                        $currentpath, 
                        $imgpathIcons, $imgpathUtils, $directoryIcons, $lang, $titleTag)
    {

        $currentLanguage = Factory::getLanguage();
        $currentLangTag = $currentLanguage->getTag();
        
        if (isset($titleTag) && $titleTag != "")
        {
            if ($titleTag == "h2"
             || $titleTag == "h3")
            {
                $currentTitleTag = $titleTag;
            }
            else
            {
                $currentTitleTag ='h2';
            }
        }
        else
        {
            $currentTitleTag ='h2';
        }
        
        if (isset($lang) && $lang != "")
        {
            $currentLanguage->load('com_zhgooglemap', JPATH_SITE, $lang, true);    
            $currentLanguage->load('com_zhgooglemap', JPATH_COMPONENT, $lang, true);    
            $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap' , $lang, true);    
        }
        else
        {
            $currentLanguage->load('com_zhgooglemap', JPATH_SITE, $currentLangTag, true);    
            $currentLanguage->load('com_zhgooglemap', JPATH_COMPONENT, $currentLangTag, true);        
            $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap', $currentLangTag, true);        
        }
        
        $returnText = '';

        $returnText .= '\'<div id="pathContent'. $currentpath->id.'" class="pathContent">\' +    ' ."\n";
        if (isset($currentpath->infowincontent) &&
            (((int)$currentpath->infowincontent == 0) ||
             ((int)$currentpath->infowincontent == 1))
            )
        {
            $returnText .= '\'<'.$currentTitleTag.' id="headContent'. $currentpath->id.'" class="pathHead">'.'\'+' ."\n";
            $returnText .= '\''.htmlspecialchars(str_replace('\\', '/', $currentpath->title), ENT_QUOTES, 'UTF-8').'\'+'."\n";
            $returnText .= '\'</'.$currentTitleTag.'>\'+' ."\n";
        }
        $returnText .= '\'<div id="bodyContent'. $currentpath->id.'" class="pathBody">\'+'."\n";


        if (isset($currentpath->infowincontent) &&
            (((int)$currentpath->infowincontent == 0) ||
             ((int)$currentpath->infowincontent == 2))
            )
        {
            $returnText .= '\''.htmlspecialchars(str_replace('\\', '/', $currentpath->description), ENT_QUOTES, 'UTF-8').'\'+'."\n";
        }
        $returnText .= '\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentpath->descriptionhtml)).'\'+'."\n";

        if ($currentpath->hrefsite!="")
        {
            $returnText .= '\'<p><a class="pathHREF" href="'.$currentpath->hrefsite.'" target="_blank">';
            if ($currentpath->hrefsitename != "")
            {
                $returnText .= htmlspecialchars($currentpath->hrefsitename, ENT_QUOTES, 'UTF-8');
            }
            else
            {
                $returnText .= $currentpath->hrefsite;
            }
            $returnText .= '</a></p>\'+'."\n";
        }

        
        $returnText .= '\'</div>\'+'."\n";
        $returnText .= '\'</div>\';'."\n";
                                
        return $returnText;
    }
    
    public static function get_path_hover_string(
                        $currentpath)
    {

        $returnText = '';
      
            $returnText .= '\'<div id="pathHoverContent'. $currentpath->id.'">\' +    ' ."\n";

            $returnText .= '\'<div id="bodyHoverContent'. $currentpath->id.'"  class="pathHoverBody">\'+'."\n";

            $returnText .= '\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentpath->hoverhtml)).'\'+'."\n";


            $returnText .= '\'</div>\'+'."\n";
            
            
            $returnText .= '\'</div>\';'."\n";

        return $returnText;
    }
    

}
