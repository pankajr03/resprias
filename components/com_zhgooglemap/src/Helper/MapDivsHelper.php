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

/**
 * Zh GoogleMap Helper
 */
abstract class MapDivsHelper
{

    public static function get_MapPanelDIV( $do_creation,
                                            $height,
                                                $width,
                                                $mapDivSuffix,
                                                $mapPANDivCSSClassName,
                                                $managePanelContent)
    {
        if ((int)$do_creation == 1)
        {
            $scripttext = '<div id="GMapsMainPanel'.$mapDivSuffix.'" '.$mapPANDivCSSClassName.' style="margin:0;padding:0;display:none;width:'.$width.';height:'.$height.';overflow:auto;"><div>'.$managePanelContent.'</div></div>';
            
        }
        else
        {
            $scripttext = '';
        }

        return $scripttext;
    }


}
