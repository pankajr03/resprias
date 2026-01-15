<?php
/*------------------------------------------------------------------------
# plg_zhgooglemap - Zh GoogleMap Plugin
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


abstract class plgZhGoogleMapPlacemarksHelper
{
    
    public static function parseZoom($pi_zoom) 
    {
        if ($pi_zoom != "")
        {
            switch ($pi_zoom)
            {
                case "0":
                  $currentZoom = "0";
                break;
                case "1":
                  $currentZoom = "1";
                break;
                case "2":
                  $currentZoom = "2";
                break;
                case "3":
                  $currentZoom = "3";
                break;
                case "4":
                  $currentZoom = "4";
                break;
                case "5":
                  $currentZoom = "5";
                break;
                case "6":
                  $currentZoom = "6";
                break;
                case "7":
                  $currentZoom = "7";
                break;
                case "8":
                  $currentZoom = "8";
                break;
                case "9":
                  $currentZoom = "9";
                break;
                case "10":
                  $currentZoom = "10";
                break;
                case "11":
                  $currentZoom = "11";
                break;
                case "12":
                  $currentZoom = "12";
                break;
                case "13":
                  $currentZoom = "13";
                break;
                case "14":
                  $currentZoom = "14";
                break;
                case "15":
                  $currentZoom = "15";
                break;
                case "16":
                  $currentZoom = "16";
                break;
                case "17":
                  $currentZoom = "17";
                break;
                case "18":
                  $currentZoom = "18";
                break;
                case "19":
                  $currentZoom = "19";
                break;
                case "20":
                  $currentZoom = "20";
                break;
                case "21":
                  $currentZoom = "21";
                break;
                case "22":
                  $currentZoom = "22";
                break;
                case "23":
                  $currentZoom = "23";
                break;
                case "24":
                  $currentZoom = "24";
                break;
                case "25":
                  $currentZoom = "25";
                break;
                default:
                    $currentZoom = "do not change";
                break;
            }
            
        }
        else
        {
            $currentZoom = "do not change";
        }

        return $currentZoom;
    }    

    public static function parseMapType($pi_map_type) 
    {
        if ($pi_map_type != "")
        {
            switch ($pi_map_type)
            {
                case "ROADMAP":
                  $currentMapType = "1";
                break;
                case "SATELLITE":
                  $currentMapType = "2";
                break;
                case "HYBRID":
                  $currentMapType = "3";
                break;
                case "TERRAIN":
                  $currentMapType = "4";
                break;
                case "OSM":
                  $currentMapType = "5";
                break;
                case "NZTOPOMAPS":
                  $currentMapType = "6";
                break;
                case "NZTOPOMAP":
                  $currentMapType = "6";
                break;
                case "OPENTOPOMAP":
                  $currentMapType = "8";
                break;
                default:
                  $currentMapType = "do not change";
                break;
            }        
        }
        else
        {
            $currentMapType = "do not change";
        }

        return $currentMapType;
    }    




    
}