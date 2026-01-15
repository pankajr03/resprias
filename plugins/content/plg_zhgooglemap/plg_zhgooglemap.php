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

jimport( 'joomla.plugin.plugin' );

JLoader::register('comZhGoogleMapData', JPATH_SITE.'/components/com_zhgooglemap/helpers/map_data.php'); 


class plgContentPlg_ZhGoogleMap extends JPlugin
{    
    
    var $scripthead;
    var $scripttext;
    var $scriptinitialize;    
    var $scriptfulltext;

    var $MapXsuffix = "ZhGMPLG";    

    var $markercluster;
    var $markermanager;
    var $places;
    var $weather;
    var $loadVisualisation;
    var $loadVisualisationKML;
    var $infobubble;
    var $featureMarkerWithLabel;
    var $main_lang;
    var $use_object_manager;
    var $urlProtocol;
    
    
    
    var $apikey4map;
    var $apikey4map_nz;
    var $compatiblemodersf;
    var $compatiblemode;
    var $httpsprotocol;
    var $apiversion;
    var $apitype;
    var $placemarktitletag;
    
    var $geotag_link;
    var $geotag_css;
    var $geotag_hide_marker;
    var $licenseinfo;
    
        var $load_by_script;
    
    
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {
        $parameterDefaultLine = ';;;;;;;;;;;;;;;;;;;;';

        $app = JFactory::getApplication();

        $comparams = JComponentHelper::getParams( 'com_zhgooglemap' );

        $this->apikey4map = $comparams->get( 'map_map_key');
        $this->apikey4map_nz = $comparams->get( 'map_map_key_nz');
        $this->compatiblemode = $comparams->get( 'map_compatiblemode');
        $this->compatiblemodersf = $comparams->get( 'map_compatiblemode_rsf');
        $this->httpsprotocol = $comparams->get('httpsprotocol');
        $this->loadtype = $comparams->get('loadtype');
        $this->apiversion = $comparams->get('map_api_version');
        $this->apitype = $comparams->get('api_type');
        
        $this->geotag_link = $comparams->get('geotag_link');
        $this->geotag_css = $comparams->get('geotag_css');
        $this->geotag_hide_marker = $comparams->get('geotag_hide_marker');

        $this->licenseinfo = $comparams->get('licenseinfo');
        $this->placemarktitletag = $comparams->get('placemarktitletag');
        
        $this->urlProtocol = "http";
        if ($this->httpsprotocol != "")
        {
            if ((int)$this->httpsprotocol == 0)
            {
                $this->urlProtocol = 'https';
            }
        }        
        $document    = JFactory::getDocument();

        // Load default language
        JPlugin::loadLanguage();
        
        $this->current_custom_js_path = JURI::root() .'components/com_zhgooglemap/assets/js/';
        $current_custom_js_path = $this->current_custom_js_path;    
        
        $this->useObjectStructure = 1;

        require_once JPATH_SITE . '/plugins/content/plg_zhgooglemap/helpers/placemarks.php';

                
        $regexLght        = '/({zhgooglemap-lightbox:\s*)(.*?)(})/is';
        $matchesLght         = array();
        $count_matches_Lght    = preg_match_all($regexLght, $article->text, $matchesLght, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexMrList        = '/({zhgooglemap-markerlist:\s*)(.*?)(})/is';
        $matchesMrList         = array();
        $count_matches_MrList    = preg_match_all($regexMrList, $article->text, $matchesMrList, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexMrk        = '/({zhgooglemap-marker:\s*)(.*?)(})/is';
        $matchesMrk         = array();
        $count_matches_Mrk    = preg_match_all($regexMrk, $article->text, $matchesMrk, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexGrp        = '/({zhgooglemap-group:\s*)(.*?)(})/is';
        $matchesGrp         = array();
        $count_matches_Grp    = preg_match_all($regexGrp, $article->text, $matchesGrp, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexCategory        = '/({zhgooglemap-category:\s*)(.*?)(})/is';
        $matchesCategory         = array();
        $count_matches_Category    = preg_match_all($regexCategory, $article->text, $matchesCategory, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexMap        = '/({zhgooglemap:\s*)(.*?)(})/is';
        $matchesMap         = array();
        $count_matches_Map    = preg_match_all($regexMap, $article->text, $matchesMap, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexGeoTag        = '/({zhgooglemap-geotag:\s*)(.*?)(})/is';
        $matchesGeoTag         = array();
        $count_matches_GeoTag    = preg_match_all($regexGeoTag, $article->text, $matchesGeoTag, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexRt        = '/({zhgooglemap-route:\s*)(.*?)(})/is';
        $matchesRt         = array();
        $count_matches_Rt    = preg_match_all($regexRt, $article->text, $matchesRt, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $regexPth        = '/({zhgooglemap-path:\s*)(.*?)(})/is';
        $matchesPth         = array();
        $count_matches_Pth    = preg_match_all($regexPth, $article->text, $matchesPth, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        
        $this->markercluster = 0;
        $this->markermanager = 0;
        $this->places = 0;
        $this->weather = 0;
        $this->loadVisualisation = 0;
        $this->loadVisualisationKML = 0;
        $this->main_lang = "";
        $this->infobubble = 0;
        $this->featureMarkerWithLabel = 0;
        $this->use_object_manager = 0;
        $this->map_region = "";
                $this->load_by_script = "";
        
        // 11.03.2016 to fix a few articles or modules on page
        $this->scriptfulltext = "";
        $this->scriptinitialize = "";
        

        // Local variables - part 1 - begin
        // set it in any time
        $apikey4map = $this->apikey4map;
        $apikey4map_nz = $this->apikey4map_nz;
        $compatiblemodersf = $this->compatiblemodersf;
        $compatiblemode = $this->compatiblemode;
        $loadtype = $this->loadtype;
        $apiversion = $this->apiversion;
        $apitype = $this->apitype;
        $licenseinfo = $this->licenseinfo;
        $placemarkTitleTag = $this->placemarktitletag;
        $urlProtocol = $this->urlProtocol;
        
        $main_lang = $this->main_lang;

        $current_custom_js_path = $this->current_custom_js_path;
        if ($compatiblemodersf == 0)
        {
            $imgpathIcons = JURI::root() .'administrator/components/com_zhgooglemap/assets/icons/';
            $imgpathUtils = JURI::root() .'administrator/components/com_zhgooglemap/assets/utils/';
            $directoryIcons = 'administrator/components/com_zhgooglemap/assets/icons/';
        }
        else
        {
            $imgpathIcons = JURI::root() .'components/com_zhgooglemap/assets/icons/';
            $imgpathUtils = JURI::root() .'components/com_zhgooglemap/assets/utils/';
            $directoryIcons = 'components/com_zhgooglemap/assets/icons/';
        }            
        // Local variables - part 1 - end

                

        if (($count_matches_Map > 0) ||
            ($count_matches_Mrk > 0) ||
            ($count_matches_Pth > 0) ||
            ($count_matches_Rt > 0) ||
            ($count_matches_MrList > 0) ||
            ($count_matches_Grp > 0) ||
            ($count_matches_Category > 0)
            /* There is no need to load API
             || ($count_matches_Lght > 0)
            */
            )
        {

            // Begin loop for Map
            for($i = 0; $i < $count_matches_Map; $i++) 
            {
              //$article->text .= "\n" .'<br />-1-'. $matches[0][$i][0];
               //$article->text .= "\n" .'<br />-2-'. $matches[1][$i][0];
              //$article->text .= "\n" .'<br />-3-'. $matches[2][$i][0];
              //$article->text .= "\n" .'<br />-4-'. $matches[3][$i][0];
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesMap[2][$i][0].$parameterDefaultLine);
                $basicID = $pars[0];
                $compoundID .= '_'.$basicID.'_'.'map';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap($matchesMap[2][$i][0], $compoundID, "0", "0", "0", "0", "0", "0"))
                {
                    $patternsMap = '/'.$matchesMap[0][$i][0].'/';
                    $replacementsMap = $this->scripthead ; //'call='.$i ;
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsMap, $replacementsMap, $article->text, 1);
                }
            }
            // End loop for Map
                        
            // Begin loop for Marker
            for($i = 0; $i < $count_matches_Mrk; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesMrk[2][$i][0].$parameterDefaultLine);
                $basicID = $pars[0];
                $compoundID .= '_'.$basicID.'_'.'mrk';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, $matchesMrk[2][$i][0], "0", "0", "0", "0", "0"))
                {
                    $patternsMrk = '/'.$matchesMrk[0][$i][0].'/';
                    $replacementsMrk = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsMrk, $replacementsMrk, $article->text, 1);
                }
            }
            // End loop for Marker

            // Begin loop for Group
            for($i = 0; $i < $count_matches_Grp; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesGrp[2][$i][0].$parameterDefaultLine);
                $basicID = 0; //$pars[0]; -- this is list now
                $compoundID .= '_'.$basicID.'_'.'grp';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, "0", $matchesGrp[2][$i][0], "0", "0", "0", "0"))
                {
                    $patternsGrp = '/'.$matchesGrp[0][$i][0].'/';
                    $replacementsGrp = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsGrp, $replacementsGrp, $article->text, 1);
                }
            }
            // End loop for Group

            // Begin loop for Category
            for($i = 0; $i < $count_matches_Category; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesCategory[2][$i][0].$parameterDefaultLine);
                $basicID = 0; // $pars[0]; - this is list now
                $compoundID .= '_'.$basicID.'_'.'cat';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, "0", "0", $matchesCategory[2][$i][0], "0", "0", "0" ))
                {
                    $patternsCategory = '/'.$matchesCategory[0][$i][0].'/';
                    $replacementsCategory = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsCategory, $replacementsCategory, $article->text, 1);
                }
            }
            // End loop for Category

            // Begin loop for MarkerList
            for($i = 0; $i < $count_matches_MrList; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesMrList[2][$i][0].$parameterDefaultLine);
                $basicID = 0; //$pars[0] - this is a placemark list;
                $compoundID .= '_'.$basicID.'_'.'mrlist';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, "0", "0", "0", $matchesMrList[2][$i][0], "0", "0"))
                {
                    $patternsMrList = '/'.$matchesMrList[0][$i][0].'/';
                    $replacementsMrList = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsMrList, $replacementsMrList, $article->text, 1);
                }
            }
            // End loop for MarkerList

            // Begin loop for Route
            for($i = 0; $i < $count_matches_Rt; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesRt[2][$i][0].$parameterDefaultLine);
                $basicID = $pars[0];
                $compoundID .= '_'.$basicID.'_'.'rt';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, "0", "0", "0", "0", $matchesRt[2][$i][0], "0"))
                {
                    $patternsRt = '/'.$matchesRt[0][$i][0].'/';
                    $replacementsRt = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsRt, $replacementsRt, $article->text, 1);
                }
            }
            // End loop for Route


                        
            // Begin loop for Path
            for($i = 0; $i < $count_matches_Pth; $i++) 
            {
                if (property_exists($article, "id"))
                {
                    $cur_article_id = $article->id;
                }
                else
                {
                    $cur_article_id ="";
                }
                
                $cur_article_id = str_replace(array("{", "}"), '_', $cur_article_id);
                $contextFormatted =  str_replace(array("{", "}"), '_', $context);
                $compoundID = str_replace('#','_', str_replace('.', '_', $contextFormatted.'#'.$cur_article_id .'#'.$i));
                $pars = explode(";", $matchesPth[2][$i][0].$parameterDefaultLine);
                $basicID = $pars[0];
                $compoundID .= '_'.$basicID.'_'.'pth';
                                // 12.11.2018 fix -extrafields from K2
                                $compoundID = str_replace('-','_', $compoundID);


                if ($this->getMap("0", $compoundID, "0", "0", "0", "0", "0", $matchesPth[2][$i][0]))
                {
                    $patternsPth = '/'.$matchesPth[0][$i][0].'/';
                    $replacementsPth = $this->scripthead ; 
                    $this->scriptfulltext .= "\n" . $this->scripttext;
                    $article->text = preg_replace($patternsPth, $replacementsPth, $article->text, 1);
                }
            }
            // End loop for Path
            
            $article->text .= '<script type="text/javascript" >/*<![CDATA[*/' ."\n";
            $article->text .= $this->scriptfulltext ."\n";

            if ($this->loadtype == "1")
            {
                $article->text .= 'function initialize'.$this->MapXsuffix . $cur_article_id .'() {' ."\n";
                $article->text .= $this->scriptinitialize;
                $article->text .= '};' ."\n";
                
                $article->text .= ' window.addEvent(\'domready\', initialize'.$this->MapXsuffix . $cur_article_id .');' ."\n";
            }
            else if ($this->loadtype == "2")
            {
                $article->text .= 'function initialize'.$this->MapXsuffix . $cur_article_id .'() {' ."\n";
                $article->text .= $this->scriptinitialize;
                $article->text .= '};' ."\n";
                
                $article->text .= 'var tmpJQ'.$this->MapXsuffix . $cur_article_id.' = jQuery.noConflict();'."\n";
                $article->text .= ' tmpJQ'.$this->MapXsuffix . $cur_article_id.'(document).ready(function() {initialize'.$this->MapXsuffix . $cur_article_id .'();});' ."\n";
            }            
            else
            {
                $article->text .= ' function addLoadEvent(func) {' ."\n";
                $article->text .= '  var oldonload = window.onload;' ."\n";
                $article->text .= '  if (typeof window.onload != \'function\') {' ."\n";
                $article->text .= '    window.onload = func;' ."\n";
                $article->text .= '  } else {' ."\n";
                $article->text .= '    window.onload = function() {' ."\n";
                $article->text .= '      if (oldonload) {' ."\n";
                $article->text .= '        oldonload();' ."\n";
                $article->text .= '      }' ."\n";
                $article->text .= '      func();' ."\n";
                $article->text .= '    }' ."\n";
                $article->text .= '  }' ."\n";
                $article->text .= '}    ' ."\n";    

                
                $article->text .= 'function initialize'.$this->MapXsuffix . $cur_article_id .'() {' ."\n";
                $article->text .= $this->scriptinitialize;
                $article->text .= '};' ."\n";


                $article->text .= 'addLoadEvent(initialize'.$this->MapXsuffix . $cur_article_id .');' ."\n";
            }

            //$article->text .= 'window.onload = initialize;' ."\n";
            $article->text .= '/*]]>*/</script>' ."\n";

            // add local variables for common script
            //   because module doesn't use object model
            
            $featureMarkerWithLabel = $this->featureMarkerWithLabel;
            $places = $this->places;
            $weather = $this->weather;
            $markercluster = $this->markercluster;
            $markermanager = $this->markermanager;
            $map_region = $this->map_region;

            $loadVisualisation = $this->loadVisualisation;
            $loadVisualisationKML = $this->loadVisualisationKML;

            $infobubble = $this->infobubble;
            $use_object_manager = $this->use_object_manager;
                        $load_by_script = $this->load_by_script;

            // do it for each article, because it can be different scripts to load
            require (JPATH_SITE . '/components/com_zhgooglemap/views/zhgooglemap/tmpl/display_script.php');            
        
        }

        if ($count_matches_Lght > 0)
        {
            // Begin loop for Lightbox
            for($i = 0; $i < $count_matches_Lght; $i++) 
            {

                $pars = explode(";", $matchesLght[2][$i][0].$parameterDefaultLine);
                $mapid = $pars[0];
                $popupTitle = htmlspecialchars($pars[1], ENT_QUOTES, 'UTF-8');
                $mapwidth = $pars[2];
                $mapheight = $pars[3];
                $mapimage = $pars[4];
                $placemarkListIds = $pars[5];
                $lbMapZoom = $pars[6];
                $lbPlacemarkCenter = $pars[7];
                $lbPlacemarkAction = $pars[8];

                if ($lbPlacemarkAction != "")
                {
                    $lbPlacemarkAction = str_replace("#", "%23", $lbPlacemarkAction);
                }
                
                JHTML::_('behavior.modal', 'a.zhgm-modal-button');

                if ((!isset($mapwidth)) || (isset($mapwidth) && (int)$mapwidth < 1)) 
                {
                    $popupWidth = 700;
                }
                else
                {
                    $popupWidth = (int)$mapwidth;
                }
                
                if ((!isset($mapheight)) || (isset($mapheight) && (int)$mapheight < 1)) 
                {
                    $popupHeight = 500;
                }
                else
                {
                    $popupHeight = (int)$mapheight;
                }

                if ((!isset($popupTitle) || (isset($popupTitle) && $popupTitle ==""))
                 && (!isset($mapimage) || (isset($mapimage) && $mapimage =="")))
                {
                    $popupTitle = JText::_('PLG_ZHGOOGLEMAP_MAP_LIGHTBOX_SHOW_MAP');
                    //$popupTitle = 'Show map';
                }
                
                if (isset($mapimage) && $mapimage !="")
                {
                    if ($this->compatiblemodersf == 0)
                    {                    
                        $imgpath = JURI::root() .'administrator/components/com_zhgooglemap/assets/lightbox/';
                    }
                    else
                    {
                        $imgpath = JURI::root() .'components/com_zhgooglemap/assets/lightbox/';
                    }
                    $popupImage = '<img src="'.$imgpath.$mapimage.'" alt="" />';
                }
                else
                {
                    $popupImage = '';
                }

                if (isset($mapid) && (int)$mapid != 0)
                {
                    $popupOptions = "{handler: 'iframe', size: {x: ".$popupWidth.", y: ".$popupHeight."} }";
                    $popupCall = JRoute::_('index.php?option=com_zhgooglemap&amp;view=zhgooglemap&amp;tmpl=component'.
                                            '&amp;id='.(int)$mapid.
                                            '&amp;placemarklistid='.$placemarkListIds.
                                            '&amp;mapzoom='.$lbMapZoom.
                                            '&amp;centerplacemarkid='.$lbPlacemarkCenter.
                                            '&amp;centerplacemarkaction='.$lbPlacemarkAction.
                                            ''
                                            );

                    $replacementsLght = '<a class="zhgm-modal-button" title="'.$popupTitle.'" href="'.$popupCall.'" rel="'.$popupOptions.'">'.$popupImage.$popupTitle.'</a>';
                    
                    $patternsLght = '/'.$matchesLght[0][$i][0].'/';
                    
                    $article->text = preg_replace($patternsLght, $replacementsLght, $article->text, 1);
                }
                
            }
            // End loop for Lightbox
        }
            
        if ($count_matches_GeoTag > 0)
        {


            $document->addStyleSheet(JURI::root() .'components/com_zhgooglemap/assets/css/common.css');  
            
                    // Begin loop for GeoTag
            for($i = 0; $i < $count_matches_GeoTag; $i++) 
            {

                $patternsGeoTagMain = $matchesGeoTag[0][$i][0];
                $patternsGeoTag = '/'.$patternsGeoTagMain.'/';
            
                $pars = explode(";", $matchesGeoTag[2][$i][0].$parameterDefaultLine);
                $pars_cnt = count($pars);
                $cur_geotag_lat = "";
                $cur_geotag_lng = "";
                $cur_geotag_tags = "";
                $cur_geotag_zoom = "";
                $cur_geotag_maptype = "";
                
                $replacementsGeoTag = "";
                //$replacementsGeoTag .= '<br />x:' . $matchesGeoTag[2][$i][0].$parameterDefaultLine;
                for($j = 0; $j < $pars_cnt; $j++) 
                {
                    if ($pars[$j] != "")
                    {
                        //$replacementsGeoTag .= '<br />par val:' .$j.' - '. $pars[$j];
                        $cur_par = explode("=", $pars[$j]);
                        if (count($cur_par) == 2)
                        {
                            $cur_par_name = strtolower(trim($cur_par[0]));
                            $cur_par_val = trim($cur_par[1]);
                            
                            //$replacementsGeoTag .= '<br />tag val:' .$j.' - '. $cur_par[1];
                            
                            if ($cur_par_name != ""
                             && $cur_par_val != "")
                            {
                                if ($cur_par_name == "latitude"
                                 || $cur_par_name == "lat")
                                {
                                    $cur_geotag_lat = $cur_par_val;
                                }
                                else if ($cur_par_name == "longitude"
                                 || $cur_par_name == "lng"
                                 || $cur_par_name == "lon")
                                {
                                    $cur_geotag_lng = $cur_par_val;
                                }
                                else if ($cur_par_name == "tag"
                                 || $cur_par_name == "tags")
                                {
                                    $cur_geotag_tags = $cur_par_val;
                                }
                                else if ($cur_par_name == "zoom"
                                || $cur_par_name == "z")
                                {
                                    $cur_geotag_zoom = $cur_par_val;
                                }
                                else if ($cur_par_name == "ll")
                                {
                                    $tmp_par_val = explode(',', $cur_par_val);
                                    if (count($tmp_par_val) == 2)
                                    {
                                        $cur_geotag_lat = trim($tmp_par_val[0]);
                                        $cur_geotag_lng = trim($tmp_par_val[1]);
                                    }
                                }
                                else if ($cur_par_name == "maptype")
                                {
                                    $cur_geotag_maptype = strtolower($cur_par_val);
                                }
                                
                            }
                        }
                    }
                }

                if ($cur_geotag_lat != ""
                 && $cur_geotag_lng != ""
                 && $cur_geotag_tags != "")
                {
                    /*
                    $replacementsGeoTag .= '<br />Correct!';
                    $replacementsGeoTag .= '<br />cur_geotag_lat:' . $cur_geotag_lat;
                    $replacementsGeoTag .= '<br />cur_geotag_lng:'. $cur_geotag_lng;
                    $replacementsGeoTag .= '<br />cur_geotag_tags:' . $cur_geotag_tags;
                    $replacementsGeoTag .= '<br />x:' . $matchesGeoTag[2][$i][0].$parameterDefaultLine;
                    */

                    
                    $returnText  = "";

                    if ((int)$this->geotag_css == 10
                    || (int)$this->geotag_css == 11)
                    {
                        $geotag_css_suffix = '-external';
                    }
                    else
                    {
                        $geotag_css_suffix = '';
                    }
                    
                    // addition parameters
                    $cur_geotag_add = '';
                    
                    if ((int)$this->geotag_css == 0
                     || (int)$this->geotag_css == 10)
                    {
                        $returnText .= '<div id="GMapsGeoTagDIV" class="zhgm-geotag-wrapping-div-advanced'.$geotag_css_suffix.'">'."\n";
                        $returnText .= '<div id="GMapsGeoTagBAR" class="zhgm-geotag-wrapping-tagbar-advanced'.$geotag_css_suffix.'">'."\n";
                        if ((int)$this->geotag_link == 0)
                        {
                            $returnText .= '<div id="GMapsGeoTagITEM" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'">';
                            
                            if ($cur_geotag_maptype == 'google')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.google.com/?ll='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'yandex')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;pt='.$cur_geotag_lng.','.$cur_geotag_lat;
                                }
                                $returnText .= '<a href="http://maps.yandex.ru/?ll='.$cur_geotag_lng.','.$cur_geotag_lat.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'osm')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;zoom='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;mlat='.$cur_geotag_lat.'&amp;mlon='.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://openstreetmap.org/?lat='.$cur_geotag_lat.'&amp;lon='.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'yahoo')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;zoom='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.yahoo.com/#lat='.$cur_geotag_lat.'&amp;lon='.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'nokia')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= ','.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '/title='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://here.com/map='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'bing')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;lvl='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;where1='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://bing.com/maps/?cp='.$cur_geotag_lat.'~'.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.google.com/?ll='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-advanced'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-advanced'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            
                            $returnText .= '</div>'."\n";
                        }
                        $taglist = explode(",", $cur_geotag_tags);
                        for($j = 0; $j < count($taglist); $j++) 
                        {
                            $returnText .= '<div id="GMapsGeoTagITEM" class="zhgm-geotag-wrapping-tagbar-item-advanced'.$geotag_css_suffix.'">';
                            $returnText .= trim($taglist[$j]);
                            $returnText .= '</div>'."\n";
                        }
                        $returnText .= '</div>'."\n";
                        $returnText .= '</div>'."\n";
                    }
                    else if ((int)$this->geotag_css == 1
                     || (int)$this->geotag_css == 11)
                    {
                        $returnText .= '<div id="GMapsGeoTagDIV" class="zhgm-geotag-wrapping-div-simple'.$geotag_css_suffix.'">'."\n";
                        $returnText .= '<div id="GMapsGeoTagBAR" class="zhgm-geotag-wrapping-tagbar-simple'.$geotag_css_suffix.'">'."\n";
                        $returnText .= '<div id="GMapsGeoTagITEM" class="zhgm-geotag-wrapping-tagbar-item-simple'.$geotag_css_suffix.'">'."\n";
                        if ((int)$this->geotag_link == 0)
                        {
                            if ($cur_geotag_maptype == 'google')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.google.com/?ll='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'yandex')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;pt='.$cur_geotag_lng.','.$cur_geotag_lat;
                                }
                                $returnText .= '<a href="http://maps.yandex.com/?ll='.$cur_geotag_lng.','.$cur_geotag_lat.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'osm')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;zoom='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;mlat='.$cur_geotag_lat.'&amp;mlon='.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://openstreetmap.org/?lat='.$cur_geotag_lat.'&amp;lon='.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'yahoo')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;zoom='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.yahoo.com/#lat='.$cur_geotag_lat.'&amp;lon='.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'nokia')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= ','.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '/title='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://here.com/map='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else if ($cur_geotag_maptype == 'bing')
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;lvl='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;where1='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://bing.com/maps/?cp='.$cur_geotag_lat.'~'.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                            else
                            {
                                if ($cur_geotag_zoom != "")
                                {
                                    $cur_geotag_add .= '&amp;z='.$cur_geotag_zoom;
                                }
                                
                                if ((int)$this->geotag_hide_marker == 0)
                                {
                                    $cur_geotag_add .= '&amp;q='.$cur_geotag_lat.','.$cur_geotag_lng;
                                }
                                $returnText .= '<a href="http://maps.google.com/?ll='.$cur_geotag_lat.','.$cur_geotag_lng.$cur_geotag_add.'" class="zhgm-geotag-wrapping-tagbar-url-simple'.$geotag_css_suffix.'"><img id="GMapsGeoTagURL" src="'.$imgpathUtils.'earth1.png" class="zhgm-geotag-wrapping-tagbar-image-simple'.$geotag_css_suffix.'" /></a>'."\n";
                            }
                        }
                        $taglist = explode(",", $cur_geotag_tags);
                        for($j = 0; $j < count($taglist); $j++) 
                        {
                            if ($j == 0)
                            {
                                $returnText .= trim($taglist[$j]);
                            }
                            else
                            {
                                $returnText .= ', '.trim($taglist[$j]);
                            }
                        }
                        $returnText .= '</div>'."\n";
                        $returnText .= '</div>'."\n";
                        $returnText .= '</div>'."\n";
                    }
                    
                    $replacementsGeoTag .= $returnText;

                }
                else
                {
                    $replacementsGeoTag = 'Incorrect call:<br />'.$patternsGeoTagMain;
                    $replacementsGeoTag .= '<br />Latitude: ' . $cur_geotag_lat;
                    $replacementsGeoTag .= '<br />Longitude: ' . $cur_geotag_lng;
                    $replacementsGeoTag .= '<br />GeoTags: ' . $cur_geotag_tags;
                    $replacementsGeoTag .= '<br />Zoom: ' . $cur_geotag_zoom;
                    $replacementsGeoTag .= '<br />MapType: ' . $cur_geotag_maptype;
                }
                
                
                $article->text = preg_replace($patternsGeoTag, $replacementsGeoTag, $article->text, 1);
                
            }
            // End loop for GeoTag
        }
            
            
        return true;

    }
    
    function getMap($mapWithPars, 
                    $currentArticleId, 
            $placemarkIdWithPars, 
            $groupIdWithPars, 
            $categoryIdWithPars, 
            $placemarkListWithPars, 
            $routeIdWithPars, 
            $pathIdWithPars)
{      
        $parameterDefaultLine = ';;;;;;;;;;;;;;;;;;;;';
        
        
        // Center Value in (placemark, map)
        $currentCenter = "map";
        $currentPlacemarkCenter = "do not change";
        $currentPlacemarkAction = "do not change";
        $currentPlacemarkActionID = "do not change";
        
        // Zoom Value in (1.., do not change)
        $currentZoom = "do not change";
    
        // Map Type Value
        $currentMapType ="do not change";

        // Size Value 
        $currentMapWidth ="do not change";
        $currentMapHeight ="do not change";
        
        
        
    if (($mapWithPars == "0") &&
        ($placemarkIdWithPars == "0") &&
        ($routeIdWithPars == "0") &&
        ($pathIdWithPars == "0") &&
        ($placemarkListWithPars == "0") &&
        ($groupIdWithPars == "0") &&
        ($categoryIdWithPars == "0") 
        ) 
    {
        return false;
    }

    $db = JFactory::getDBO();

        if ($mapWithPars != "0")
        {
            $pars = explode(";", $mapWithPars.$parameterDefaultLine);
            $mapId = $pars[0];
            $mapZoom = $pars[1];
            $mapMapType = $pars[2];
            $mapMapWidth = $pars[3];
            $mapMapHeight = $pars[4];

            if ($mapZoom != "")
            {
                $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($mapZoom);
            }

            if ($mapMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($mapMapType);
            }

            if ($mapMapWidth != "")
            {
                $currentMapWidth = $mapMapWidth;
            }
            
            if ($mapMapHeight != "")
            {
                $currentMapHeight = $mapMapHeight;
            }
            
            if ((int)$mapId == 0)
            {
                return false;
            }
            else
            {
                $placemarklistid = "";
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = "";
                $taglistid = "";

                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                $externalmarkerlink = "";
                                        
                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";
                $pathtaglistid = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";                                  

                $map = comZhGoogleMapData::getMap((int)$mapId);
                
                if (isset($map) && (int)$map->id != 0)
                {

                    $usermarkersfilter = "";

                    // addition parameters
                    if ($usermarkersfilter == "")
                    {
                        $usermarkersfilter = (int)$map->usermarkersfilter;
                    }
                    else
                    {
                        $usermarkersfilter = (int)$usermarkersfilter;
                    }
                                
                    if ($map->useajaxobject == 0)
                    {
                        $markers = comZhGoogleMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);
                        $paths = comZhGoogleMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    }
                    else
                    {
                        unset($markers);
                        unset($paths);
                    }
                    
                    $routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    $markergroups = comZhGoogleMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                        $map->markergrouporder);
                    $mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage($map->id, 
                                                                              $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                              $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                                              $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                }
                else
                {
                    return false;
                }
            }
            
        }
        else if ($placemarkIdWithPars != "0")
        {

            $pars = explode(";", $placemarkIdWithPars.$parameterDefaultLine);
            $placemarkId = $pars[0];
            $placemarkCenter = $pars[1];
            $placemarkZoom = $pars[2];
            $placemarkMapType = $pars[3];
            $placemarkMapWidth = $pars[4];
            $placemarkMapHeight = $pars[5];
            $placemarkAction = $pars[6];
            
            
            if ($placemarkCenter != "")
            {
                switch ($placemarkCenter)
                {
                    case "map":
                        $currentCenter = "map";

                        $mapCenterLatLng = comZhGoogleMapData::getMarkerCoordinatesLatLngObject((int)$placemarkId);
                        if ($mapCenterLatLng != "")
                        {
                            $currentPlacemarkActionID = (int)$placemarkId;
                            $currentPlacemarkAction = $placemarkAction;    
                        }

                    break;
                    case "placemark":
                        $currentCenter = "placemark";
                            
                        $mapCenterLatLng = comZhGoogleMapData::getMarkerCoordinatesLatLngObject((int)$placemarkId);
                        if ($mapCenterLatLng != "")
                        {
                            $currentPlacemarkCenter = (int)$placemarkId;
                            $currentPlacemarkActionID = (int)$placemarkId;
                            $currentPlacemarkAction = $placemarkAction;    
                            if ($mapCenterLatLng == "geocode")
                            {
                                $currentCenter = "map";
                            }
                            else
                            {
                                $currentCenter = $mapCenterLatLng;
                            }
                        }
                        else
                        {
                            $currentCenter = "map";
                        }
                            
                    break;
                    default:
                        $currentCenter = "map";
                    break;
                }


                
            }

            if ($placemarkZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($placemarkZoom);
            }

            if ($placemarkMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($placemarkMapType);
            }
            
            if ($placemarkMapWidth != "")
            {
                $currentMapWidth = $placemarkMapWidth;
            }
            
            if ($placemarkMapHeight != "")
            {
                $currentMapHeight = $placemarkMapHeight;
            }
            
            if ((int)$placemarkId == 0)
            {
                return false;
            }
            else
            {

                $query = $db->getQuery(true);
                $query->select('h.*')
                    ->from('#__zhgooglemaps_maps as h')
                    ->leftJoin('#__zhgooglemaps_markers as m ON h.id=m.mapid')
                    ->where('m.id = '.(int) $placemarkId);

                $nullDate = $db->Quote($db->getNullDate());
                $nowDate = $db->Quote(JFactory::getDate()->toSQL());
                $query->where('(m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')');
                $query->where('(m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')');

                $db->setQuery($query);        
                $map = $db->loadObject();

                $placemarklistid = (int) $placemarkId;
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = "";
                $taglistid = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";     
                                
                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";
                $pathtaglistid = "";

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
                
                if (isset($map) && (int)$map->id != 0)
                {
                    // 13.11.2014 - disable placemark list
                    $map->markerlistpos = 0;
                    // 12.08.2015 - disable group management
                    $map->markergroupcontrol = 0;
                    
                    $usermarkersfilter = "";

                    // addition parameters
                    if ($usermarkersfilter == "")
                    {
                        $usermarkersfilter = (int)$map->usermarkersfilter;
                    }
                    else
                    {
                        $usermarkersfilter = (int)$usermarkersfilter;
                    }

                    if ($map->useajaxobject == 0)
                    {
                        $markers = comZhGoogleMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);
                    }
                    else
                    {
                        unset($markers);
                    }
                    // change comments to unset
                    unset($paths);
                    //$paths = comZhGoogleMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    unset($routers);
                    //$routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    $markergroups = comZhGoogleMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                        $map->markergrouporder);
                    $mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage($map->id, 
                                                                              $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                              $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                                              $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                }
                else
                {
                    return false;
                }
                
                
                
            }
        } 
        else if ($placemarkListWithPars !="0")
        {
            $pars = explode(";", $placemarkListWithPars.$parameterDefaultLine);
            $placemarkListIds = $pars[0];
            $mapId = $pars[1];
            $placemarkListZoom = $pars[2];
            $placemarkListMapType = $pars[3];
            $placemarkListMapWidth = $pars[4];
            $placemarkListMapHeight = $pars[5];
            $mapCenter = $pars[6];
            $placemarkListAction = $pars[7];

            if ($mapCenter != "")
            {
                if ($mapCenter == "do not change")
                {
                    $currentCenter = "map";
                }
                else if ((int)$mapCenter != 0)
                {
                    $mapCenterLatLng = comZhGoogleMapData::getMarkerCoordinatesLatLngObject((int)$mapCenter);
                    if ($mapCenterLatLng != "")
                    {
                        $currentPlacemarkCenter = (int)$mapCenter;
                        $currentPlacemarkActionID = (int)$mapCenter;
                        $currentPlacemarkAction = $placemarkListAction;    
                        if ($mapCenterLatLng == "geocode")
                        {
                            $currentCenter = "map";
                        }
                        else
                        {
                            $currentCenter = $mapCenterLatLng;
                        }
                    }
                    else
                    {
                        $currentCenter = "map";
                    }
                }
                else
                {
                    $currentCenter = "map";
                }
            }

            if ($placemarkListZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($placemarkListZoom);
            }
            
            
            if ($placemarkListMapWidth != "")
            {
                $currentMapWidth = $placemarkListMapWidth;
            }
            
            if ($placemarkListMapHeight != "")
            {
                $currentMapHeight = $placemarkListMapHeight;
            }


            if ($placemarkListMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($placemarkListMapType);
            }
                        
            if (((int)$mapId == 0) || ($placemarkListIds == ""))
            {
                return false;
            }
            else
            {

                $placemarklistid = $placemarkListIds;
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = "";
                $taglistid = "";

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";    
                                
                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";
                $pathtaglistid = "";
                                
                $map = comZhGoogleMapData::getMap((int)$mapId);
                                                    
                if (isset($map) && (int)$map->id != 0)
                {
                                        
                    $usermarkersfilter = "";

                    // addition parameters
                    if ($usermarkersfilter == "")
                    {
                        $usermarkersfilter = (int)$map->usermarkersfilter;
                    }
                    else
                    {
                        $usermarkersfilter = (int)$usermarkersfilter;
                    }

                    if ($map->useajaxobject == 0)
                    {
                        $markers = comZhGoogleMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);
                        $paths = comZhGoogleMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    }
                    else
                    {
                        unset($markers);
                        unset($paths);
                    }                    
                    $routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    $markergroups = comZhGoogleMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                        $map->markergrouporder);
                    $mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage($map->id, 
                                                                              $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                              $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                                              $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                }
                else
                {
                    return false;
                }
                
            }
            
        }
        else if ($groupIdWithPars !="0")
        {
            $pars = explode(";", $groupIdWithPars.$parameterDefaultLine);
            $groupId = $pars[0];
            $mapId = $pars[1];
            $groupZoom = $pars[2];
            $groupMapType = $pars[3];
            $groupMapWidth = $pars[4];
            $groupMapHeight = $pars[5];
            $mapCenter = $pars[6];
            $groupAction = $pars[7];
                        
                        $groupObjectType = $pars[8];   
                        if ($groupObjectType == "")
                        {
                            $groupObjectType = "placemark";
                        }
                        $groupObjectTypeList = explode(",", $groupObjectType);

                        for($i = 0; $i < count($groupObjectTypeList); $i++) 
                        {
                            $groupObjectTypeList[$i] = strtolower(trim($groupObjectTypeList[$i]));
                        }
                        
            if ($mapCenter != "")
            {
                if ($mapCenter == "do not change")
                {
                    $currentCenter = "map";
                }
                else if ((int)$mapCenter != 0)
                {
                    $mapCenterLatLng = comZhGoogleMapData::getMarkerCoordinatesLatLngObject((int)$mapCenter);
                    if ($mapCenterLatLng != "")
                    {
                        $currentPlacemarkCenter = (int)$mapCenter;
                        $currentPlacemarkActionID = (int)$mapCenter;
                        $currentPlacemarkAction = $groupAction;
                        if ($mapCenterLatLng == "geocode")
                        {
                            $currentCenter = "map";
                        }
                        else
                        {
                            $currentCenter = $mapCenterLatLng;
                        }
                    }
                    else
                    {
                        $currentCenter = "map";
                    }
                }
                else
                {
                    $currentCenter = "map";
                }
            }

            if ($groupZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($groupZoom);
            }
            
            
            if ($groupMapWidth != "")
            {
                $currentMapWidth = $groupMapWidth;
            }
            
            if ($groupMapHeight != "")
            {
                $currentMapHeight = $groupMapHeight;
            }


            if ($groupMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($groupMapType);
            }
            
            if ((int)$mapId == 0)
            {
                return false;
            }
            else
            {
                $placemarklistid = "";
                $explacemarklistid = "";
                $grouplistid = "";   // 23.01.2018 set it in loop to support map objects
                $categorylistid = "";
                $taglistid = "";

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";        
                                
                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = ""; // 23.01.2018 set it in loop to support map objects - NEW
                $pathcategorylistid = "";
                $pathtaglistid = "";

                                for($i = 0; $i < count($groupObjectTypeList); $i++) 
                                {
                                    if ($groupObjectTypeList[$i] == "placemark" 
                                        || $groupObjectTypeList[$i] == "all")
                                    {
                                        $grouplistid = $groupId;
                                    }
                                    if ($groupObjectTypeList[$i] == "path" 
                                        || $groupObjectTypeList[$i] == "all")
                                    {
                                        $pathgrouplistid = $groupId;
                                    }
                                    
                                }

                $map = comZhGoogleMapData::getMap((int)$mapId);
                        
                if (isset($map) && (int)$map->id != 0)
                {

                    $usermarkersfilter = "";

                    // addition parameters
                    if ($usermarkersfilter == "")
                    {
                        $usermarkersfilter = (int)$map->usermarkersfilter;
                    }
                    else
                    {
                        $usermarkersfilter = (int)$usermarkersfilter;
                    }

                    if ($map->useajaxobject == 0)
                    {
                        $markers = comZhGoogleMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);
                        $paths = comZhGoogleMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    }
                    else
                    {
                        unset($markers);
                        unset($paths);
                    }                    
                    $routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    $markergroups = comZhGoogleMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                        $map->markergrouporder);
                    $mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage($map->id, 
                                                                              $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                              $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                                              $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                }
                else
                {
                    return false;
                }
                
            }
            
        }
        else if ($categoryIdWithPars !="0")
        {
            $pars = explode(";", $categoryIdWithPars.$parameterDefaultLine);
            $categoryId = $pars[0];
            $mapId = $pars[1];
            $categoryZoom = $pars[2];
            $categoryMapType = $pars[3];
            $categoryMapWidth = $pars[4];
            $categoryMapHeight = $pars[5];
            $mapCenter = $pars[6];
            $categoryAction = $pars[7];
                        $categoryObjectType = $pars[8];   
                        if ($categoryObjectType == "")
                        {
                            $categoryObjectType = "placemark";
                        }
                        $categoryObjectTypeList = explode(",", $categoryObjectType);

                        for($i = 0; $i < count($categoryObjectTypeList); $i++) 
                        {
                            $categoryObjectTypeList[$i] = strtolower(trim($categoryObjectTypeList[$i]));
                        }
                        
            if ($mapCenter != "")
            {
                if ($mapCenter == "do not change")
                {
                    $currentCenter = "map";
                }
                else if ((int)$mapCenter != 0)
                {
                    $mapCenterLatLng = comZhGoogleMapData::getMarkerCoordinatesLatLngObject((int)$mapCenter);
                    if ($mapCenterLatLng != "")
                    {
                        $currentPlacemarkCenter = (int)$mapCenter;
                        $currentPlacemarkActionID = (int)$mapCenter;
                        $currentPlacemarkAction = $categoryAction;    
                        if ($mapCenterLatLng == "geocode")
                        {
                            $currentCenter = "map";
                        }
                        else
                        {
                            $currentCenter = $mapCenterLatLng;
                        }
                    }
                    else
                    {
                        $currentCenter = "map";
                    }
                }
                else
                {
                    $currentCenter = "map";
                }
            }

            if ($categoryZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($categoryZoom);
            }

            if ($categoryMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($categoryMapType);
            }

            if ($categoryMapWidth != "")
            {
                $currentMapWidth = $categoryMapWidth;
            }
            
            if ($categoryMapHeight != "")
            {
                $currentMapHeight = $categoryMapHeight;
            }
            
            if ((int)$mapId == 0)
            {
                return false;
            }
            else
            {
                $placemarklistid = "";
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = ""; // 23.01.2018 set it in loop to support map objects
                $taglistid = "";

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";  // 23.01.2018 set it in loop to support map objects - NEW                                

                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";  // 23.01.2018 set it in loop to support map objects - NEW
                $pathtaglistid = "";


                                for($i = 0; $i < count($categoryObjectTypeList); $i++) 
                                {
                                    if ($categoryObjectTypeList[$i] == "placemark" 
                                        || $categoryObjectTypeList[$i] == "all")
                                    {
                                        $categorylistid = $categoryId;
                                    }
                                    if ($categoryObjectTypeList[$i] == "path" 
                                        || $categoryObjectTypeList[$i] == "all")
                                    {
                                        $pathcategorylistid = $categoryId;
                                    }
                                    if ($categoryObjectTypeList[$i] == "route" 
                                        || $categoryObjectTypeList[$i] == "all")
                                    {
                                        $routecategorylistid = $categoryId;
                                    }
                                }
                                
                                $map = comZhGoogleMapData::getMap((int)$mapId);
                
                if (isset($map) && (int)$map->id != 0)
                {
                    
                    $usermarkersfilter = "";

                    // addition parameters
                    if ($usermarkersfilter == "")
                    {
                        $usermarkersfilter = (int)$map->usermarkersfilter;
                    }
                    else
                    {
                        $usermarkersfilter = (int)$usermarkersfilter;
                    }
                                                            
                    if ($map->useajaxobject == 0)
                    {
                        $markers = comZhGoogleMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);
                        $paths = comZhGoogleMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    }
                    else
                    {
                        unset($markers);
                        unset($paths);
                    }
                    $routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    $markergroups = comZhGoogleMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid, 
                                                                        $map->markergrouporder);
                    $mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage($map->id, 
                                                                              $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                                              $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                                              $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                }
                else
                {
                    return false;
                }
                
            }
        }
        else if ($routeIdWithPars != "0")
        {

            $pars = explode(";", $routeIdWithPars.$parameterDefaultLine);
            $routeId = $pars[0];
            $routeCenter = $pars[1];
            $routeZoom = $pars[2];
            $routeMapType = $pars[3];
            $routeMapWidth = $pars[4];
            $routeMapHeight = $pars[5];
            $routeAction = $pars[6];
            
            
            if ($routeCenter != "")
            {
                switch ($routeCenter)
                {
                    case "map":
                        $currentCenter = "map";

                    break;
                    default:
                        $currentCenter = "map";
                    break;
                }


                
            }

            if ($routeZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($routeZoom);
            }

            if ($routeMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($routeMapType);
            }
            
            if ($routeMapWidth != "")
            {
                $currentMapWidth = $routeMapWidth;
            }
            
            if ($routeMapHeight != "")
            {
                $currentMapHeight = $routeMapHeight;
            }
            
            if ((int)$routeId == 0)
            {
                return false;
            }
            else
            {

                $query = $db->getQuery(true);
                $query->select('h.*')
                    ->from('#__zhgooglemaps_maps as h')
                    ->leftJoin('#__zhgooglemaps_routers as m ON h.id=m.mapid')
                    ->where('m.id = '.(int) $routeId);

                /*
                $nullDate = $db->Quote($db->getNullDate());
                $nowDate = $db->Quote(JFactory::getDate()->toSQL());
                $query->where('(m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')');
                $query->where('(m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')');
                */

                $db->setQuery($query);        
                $map = $db->loadObject();

                $placemarklistid = "";
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = "";
                $taglistid = "";
                
                $routelistid = (int) $routeId;
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";

                $pathlistid = "";
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";  
                $pathtaglistid = "";                

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
				
				$usermarkersfilter = "";
                
                if (isset($map) && (int)$map->id != 0)
                {
                    // 13.11.2014 - disable placemark list
                    $map->markerlistpos = 0;
                    // 12.08.2015 - disable group management
                    $map->markergroupcontrol = 0;
                    
                    unset($markers);
                    //$markers = comZhGoogleMapData::getMarkers();
                    unset($paths);
                    //$paths = comZhGoogleMapData::getPaths("", $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    $routers = comZhGoogleMapData::getRouters("", $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    unset($markergroups);
                    //$markergroups = comZhGoogleMapData::getMarkerGroups();
                    unset($mgrgrouplist);
                    //$mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage();
                }
                else
                {
                    return false;
                }
                
                
                
            }
        } 
        else if ($pathIdWithPars != "0")
        {

            $pars = explode(";", $pathIdWithPars.$parameterDefaultLine);
            $pathId = $pars[0];
            $pathCenter = $pars[1];
            $pathZoom = $pars[2];
            $pathMapType = $pars[3];
            $pathMapWidth = $pars[4];
            $pathMapHeight = $pars[5];
            $pathAction = $pars[6];
            
            
            if ($pathCenter != "")
            {
                switch ($pathCenter)
                {
                    case "map":
                        $currentCenter = "map";

                    break;
                    default:
                        $currentCenter = "map";
                    break;
                }


                
            }

            if ($pathZoom != "")
            {
                  $currentZoom = plgZhGoogleMapPlacemarksHelper::parseZoom($pathZoom);
            }

            if ($pathMapType != "")
            {
              $currentMapType = plgZhGoogleMapPlacemarksHelper::parseMapType($pathMapType);
            }
            
            if ($pathMapWidth != "")
            {
                $currentMapWidth = $pathMapWidth;
            }
            
            if ($pathMapHeight != "")
            {
                $currentMapHeight = $pathMapHeight;
            }
            
            if ((int)$pathId == 0)
            {
                return false;
            }
            else
            {

                $query = $db->getQuery(true);
                $query->select('h.*')
                    ->from('#__zhgooglemaps_maps as h')
                    ->leftJoin('#__zhgooglemaps_paths as m ON h.id=m.mapid')
                    ->where('m.id = '.(int) $pathId);

                /*
                $nullDate = $db->Quote($db->getNullDate());
                $nowDate = $db->Quote(JFactory::getDate()->toSQL());
                $query->where('(m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')');
                $query->where('(m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')');
                */

                $db->setQuery($query);        
                $map = $db->loadObject();

                $placemarklistid = "";
                $explacemarklistid = "";
                $grouplistid = "";
                $categorylistid = "";
                $taglistid = "";

                $pathlistid = (int) $pathId;
                $expathlistid = "";
                $pathgrouplistid = "";
                $pathcategorylistid = "";
                $pathtaglistid = "";
                                
                $routelistid = "";
                $exroutelistid = "";
                $routegrouplistid = "";
                $routecategorylistid = "";                               

                // it will be recalculated later -- begin
                $centerplacemarkid = "";
                $centerplacemarkaction = "";
                $centerplacemarkactionid = "";
                // it will be recalculated later -- end
                $externalmarkerlink = "";
				
				$usermarkersfilter = "";
                
                if (isset($map) && (int)$map->id != 0)
                {
                    // 13.11.2014 - disable placemark list
                    $map->markerlistpos = 0;
                    // 12.08.2015 - disable group management
                    $map->markergroupcontrol = 0;
                    
                    unset($markers);
                    //$markers = comZhGoogleMapData::getMarkers();
                    if ($map->useajaxobject == 0)
                    {
                        $paths = comZhGoogleMapData::getPaths("", $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
                    }
                    else
                    {
                        unset($paths);
                    }
                    unset($routers);
                    //$routers = comZhGoogleMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
                    $maptypes = comZhGoogleMapData::getMapTypes();

                    unset($markergroups);
                    //$markergroups = comZhGoogleMapData::getMarkerGroups();
                    unset($mgrgrouplist);
                    //$mgrgrouplist = comZhGoogleMapData::getMarkerGroupsManage();
                }
                else
                {
                    return false;
                }
                
                
                
            }
        } 
        else 
        {
            return false;
        }
        
    // Change translation language and load translation
    if (isset($map->lang) && $map->lang != "")
    {
        $currentLanguage = JFactory::getLanguage();
        $currentLangTag = $currentLanguage->getTag();
        $currentLanguage->setLanguage($map->lang);         
        JPlugin::loadLanguage();

        $currentLanguage->setLanguage($currentLangTag);         
    }

    $MapXArticleId = $currentArticleId;
    $MapXdoLoad = 0;
    $MapXsuffix = $this->MapXsuffix;
    
    $useObjectStructure = $this->useObjectStructure;
    
    $apikey4map = $this->apikey4map;
    $apikey4map_nz = $this->apikey4map_nz;
    $compatiblemodersf = $this->compatiblemodersf;
    $compatiblemode = $this->compatiblemode;
    $loadtype = $this->loadtype;
    $apiversion = $this->apiversion;
    $apitype = $this->apitype;
    
        $main_lang = $this->main_lang;
        
    $licenseinfo = $this->licenseinfo;
    $placemarkTitleTag = $this->placemarktitletag;

    $urlProtocol = $this->urlProtocol;
    
    if (($currentPlacemarkCenter != "") && ($currentPlacemarkCenter != "do not change"))
    {
        $centerplacemarkid = $currentPlacemarkCenter;
    }
    else
    {
        $centerplacemarkid = "";
    }

    if (($currentPlacemarkAction != "") && ($currentPlacemarkAction != "do not change"))
    {
        $centerplacemarkaction = $currentPlacemarkAction;
    }
    else
    {
        $centerplacemarkaction = "";
    }

    if (($currentPlacemarkActionID != "") && ($currentPlacemarkActionID != "do not change"))
    {
        $centerplacemarkactionid = $currentPlacemarkActionID;
    }
    else
    {
        $centerplacemarkaction = "";
    }
    
    if (($currentZoom != "") && ($currentZoom != "do not change"))
    {
        $mapzoom = $currentZoom;
    }
    else
    {
        $mapzoom = "";
    }

    
    if (($currentMapWidth != "") && ($currentMapWidth != "do not change"))
    {
        $mapMapWidth = $currentMapWidth;
    }
    else
    {
        $mapMapWidth = "";
    }

    if (($currentMapHeight != "") && ($currentMapHeight != "do not change"))
    {
        $mapMapHeight = $currentMapHeight;
    }
    else
    {
        $mapMapHeight = "";
    }
        
    $current_custom_js_path = $this->current_custom_js_path;

    // -- -- extending ------------------------------------------
    // class suffix, for example for module use
    $cssClassSuffix = "";
    


    
/*
// ***** Settings Begin *************************************

// ***** Settings End ***************************************
*/

    
    require (JPATH_SITE . '/components/com_zhgooglemap/views/zhgooglemap/tmpl/display_map_data.php');

    return true;
    }


}
