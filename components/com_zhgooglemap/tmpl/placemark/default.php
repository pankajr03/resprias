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
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri; 

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Input\Cookie; 

	$mapInitTag = "";
	$mapDivSuffix = "";

	$scripttext = "";
	$scripttext_slide1 = "";
	$scripttext_slide2 = "";
	
	$scripttextBegin = '';
	$scripttextEnd = '';

	$this->current_custom_js_path = URI::root() .'components/com_zhgooglemap/assets/js/';    
	$current_custom_js_path = $this->current_custom_js_path;


	$loadjquery = $this->loadjquery;

	$apikey4map = $this->mapapikey4map;
	$loadtype = $this->loadtype;
	$load_by_script = "";

	$apiversion = $this->mapapiversion;

	$apitype = $this->mapapitype;


	$currentmarker = $this->item;
	$main_lang = $this->item->maplang;

	$this->urlProtocol = "http";
	if ($this->httpsprotocol != "")
	{
		if ((int)$this->httpsprotocol == 0)
		{
			$this->urlProtocol = 'https';
		}
	}    

	$urlProtocol = $this->urlProtocol;


	$enable_map_gpdr = $this->enable_map_gpdr;
	$map_gpdr_buttonlabel = $this->map_gpdr_buttonlabel;
	$map_gpdr_header = $this->map_gpdr_header;
	$map_gpdr_footer = $this->map_gpdr_footer;
	$map_gpdr_buttonc = $this->map_gpdr_buttonc;
	$map_gpdr_buttonclabel = $this->map_gpdr_buttonclabel;
	$map_gpdr_buttoncexp = $this->map_gpdr_buttoncexp;

	$document    = Factory::getDocument();	
	$app = Factory::getApplication();
	
	$wa  = $document->getWebAssetManager();
	
    $load_bs = (int)$this->state->get('placemark.load_bootstrap');
		
    if ($load_bs == 0
     || $load_bs == 1)
    {
        $wa->registerAndUseStyle('zhgooglemaps.bootstrap_css', URI::root().'components/com_zhgooglemap/assets/bootstrap/css/bootstrap.min.css');
		// in new version there is no this style
        //$wa->registerAndUseStyle('zhgooglemaps.bootstrap_css_responsive', URI::root().'components/com_zhgooglemap/assets/bootstrap/css/bootstrap-responsive.min.css');
    }
    $wa->registerAndUseStyle('zhgooglemaps.common', URI::root() .'components/com_zhgooglemap/assets/css/placemark-common.css');

    
    if ($load_bs == 0)
    {
        $wa->registerAndUseScript('zhgooglemaps.bootstrap', URI::root().'components/com_zhgooglemap/assets/bootstrap/js/bootstrap.min.js');
    }
    
	if (isset($loadjquery))
	{
		if ((int)$loadjquery == 1) {
			$wa->useScript('jquery');
		}
	}

	if ((int)$enable_map_gpdr == 1)
	{
		$wa->registerAndUseScript('zhgooglemaps.cookie', $current_custom_js_path.'js-cookie/3.0.5/js.cookie.min.js');
	}
	
	$do_map_load = 0;
	$cookieName = 'zhgm-gpdr-enabled';
	$cookieNameH = ApplicationHelper::getHash($cookieName);
		
	if ((int)$enable_map_gpdr == 0)
	{
		$do_map_load = 1;
	} 
	else
	{
		
		$input = $app->getInput();
		
		$cookieValue = $input->cookie->getString($cookieNameH);
		$cookieExists = ($cookieValue !== null);
		
		if ($cookieExists) {
			if ((int)$cookieValue == 1) {
				$do_map_load = 1;
			}
		}
	}

	$scripttextBegin .= '<script type="text/javascript" >' ."\n";
	$scripttextEnd .= '</script>' ."\n";
	
    $current_image_idx = 0; 
    $current_indicator_idx = 0; 
    
    function get_Slide($pi_idx, $pi_image, $pi_title, $pi_descr, &$scripttext_slide1) {		
		
        $cur_image_idx = $pi_idx;
        if ( $pi_image != "") 
        {
            $cur_image_idx += 1;

            $scripttext_slide1 .= "\n"."<div class=\"carousel-item zhgm-placemarkCarouselImg";
            
            if ($cur_image_idx == 1) 
            $scripttext_slide1 .= " active";
            $scripttext_slide1 .= "\">"."\n";
            
            $scripttext_slide1 .= "<img alt=\"\" src=\"";
            $scripttext_slide1 .= $pi_image; 
            $scripttext_slide1 .= "\">"."\n";
            if ($scripttext_slide1 != "" || $pi_descr != "")
            {
                $scripttext_slide1 .= "<div class=\"carousel-caption d-none d-md-block\">";
                if ($pi_title != "")
                {
                    $scripttext_slide1 .= "<h4>";
                    $scripttext_slide1 .= $pi_title; 
                $scripttext_slide1 .= "</h4>"."\n";
                }
                if ($pi_descr != "")
                {
                    $scripttext_slide1 .= $pi_descr;
                }
                $scripttext_slide1 .= "</div>"."\n";
            }
            $scripttext_slide1 .= "</div>"."\n";
        }  

		return $cur_image_idx;
    }


    function get_SlideIndex($pi_idx, $pi_image, $pi_title, $pi_descr, &$scripttext_slide2) {
		
        $cur_image_idx = $pi_idx;
        if ( $pi_image != "") 
        {
            $cur_image_idx += 1;

            $scripttext_slide2 .= "\n"."<button type=\"button\" class=\"";
            
            if ($cur_image_idx == 1)
            {            
                $scripttext_slide2 .= "active\"";
            }
            else
            {
                $scripttext_slide2 .= "\"";
            }
            $scripttext_slide2 .= " data-bs-slide-to=\"".($cur_image_idx-1)."\" data-bs-target=\"#zhgmCarousel\"";

            $scripttext_slide2 .= ">";
            $scripttext_slide2 .= "</button>"."\n";
        }      		
		
		return $cur_image_idx;
    }    
    
    function get_SlideNeed($pi_image, $pi_title, $pi_descr) {
        if ( $pi_image != "") 
        {
            $ret_val = 1;
        }    
        else
        {
            $ret_val = 0;
        }
        return $ret_val;
    }    
      
                        
    $scripttext .= '<div class="row">'."\n";
	$scripttext .= '<div class="col-md-12">'."\n";
	$scripttext .= '<h2>'."\n";
    $scripttext .= htmlspecialchars(str_replace('\\', '/', ($this->item->title)), ENT_QUOTES, 'UTF-8');
    $scripttext .= '</h2>'."\n";
    $scripttext .= '</div>'."\n";
    $scripttext .= '</div>'."\n";
    $scripttext .= '<div class="row">'."\n";
    if ($this->item->hrefimagethumbnail == ""
              || (int)$this->state->get('placemark.thumbnail') == 0)
    {
        
        $scripttext .= '<div class="col-md-12">'."\n";
        $scripttext .= '<h3>'."\n";
        $scripttext .= htmlspecialchars(str_replace('\\', '/', ($this->item->description)), ENT_QUOTES, 'UTF-8'); 
        $scripttext .= '</h3>'."\n";
        $scripttext .= '</div>'."\n";
        
    }
	else
	{
        $scripttext .= '<div class="col-md-8">'."\n";
        $scripttext .= '<h3>'."\n";
        $scripttext .= htmlspecialchars(str_replace('\\', '/',($this->item->description)), ENT_QUOTES, 'UTF-8');
        $scripttext .= '</h3>'."\n";
        $scripttext .= '</div>'."\n";
        $scripttext .= '<div class="col-md-4">'."\n";
            
            if ($this->item->hrefimagethumbnail!="")
            {
                $tmp_image_path = strtolower($this->item->hrefimagethumbnail);
                
                if (substr($tmp_image_path,0,5) == "http:"
                || substr($tmp_image_path,0,6) == "https:"
                || substr($tmp_image_path,0,1) == "/"
                || substr($tmp_image_path,0,1) == ".")
                {
                    $tmp_image_path_add = "";
                }
                else
                {
                    $tmp_image_path_add = "/";
                }
                $scripttext .= '<img src="'.$tmp_image_path_add . $this->item->hrefimagethumbnail.'" alt="" class="zhgm-placemarkDetailImg" />'."\n";
            }
        $scripttext .= '</div>'."\n";
        
	}       
    $scripttext .= '</div>'."\n";  
  
    $scripttext .= '<div class="row">'."\n";  
        if (((int)$this->state->get('placemark.imagegalery') == 1 )
         &&
          ( get_SlideNeed($this->item->tab1image, $this->item->tab1title, $this->item->tab1)
            || get_SlideNeed($this->item->tab2image, $this->item->tab2title, $this->item->tab2)
            || get_SlideNeed($this->item->tab3image, $this->item->tab3title, $this->item->tab3)
            || get_SlideNeed($this->item->tab4image, $this->item->tab4title, $this->item->tab4)
            || get_SlideNeed($this->item->tab5image, $this->item->tab5title, $this->item->tab5)
            || get_SlideNeed($this->item->tab6image, $this->item->tab6title, $this->item->tab6)
            || get_SlideNeed($this->item->tab7image, $this->item->tab7title, $this->item->tab7)
            || get_SlideNeed($this->item->tab8image, $this->item->tab8title, $this->item->tab8)
            || get_SlideNeed($this->item->tab9image, $this->item->tab9title, $this->item->tab9)
            || get_SlideNeed($this->item->tab10image, $this->item->tab10title, $this->item->tab10)
            || get_SlideNeed($this->item->tab11image, $this->item->tab11title, $this->item->tab11)
            || get_SlideNeed($this->item->tab12image, $this->item->tab12title, $this->item->tab12)
            || get_SlideNeed($this->item->tab13image, $this->item->tab13title, $this->item->tab13)
            || get_SlideNeed($this->item->tab14image, $this->item->tab14title, $this->item->tab14)
            || get_SlideNeed($this->item->tab15image, $this->item->tab15title, $this->item->tab15)
            || get_SlideNeed($this->item->tab16image, $this->item->tab16title, $this->item->tab16)
            || get_SlideNeed($this->item->tab17image, $this->item->tab17title, $this->item->tab17)
            || get_SlideNeed($this->item->tab18image, $this->item->tab18title, $this->item->tab18)
            || get_SlideNeed($this->item->tab19image, $this->item->tab19title, $this->item->tab19)))
        {    
        $scripttext .= '<div class="col-md-8">'."\n";
        $scripttext .= '<div id="zhgmCarousel" class="carousel slide" data-bs-ride="carousel">'."\n";
        $scripttext .= '<div  class="carousel-indicators">'."\n";  
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab1image, $this->item->tab1title, $this->item->tab1, $scripttext_slide2);			
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab2image, $this->item->tab2title, $this->item->tab2, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab3image, $this->item->tab3title, $this->item->tab3, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab4image, $this->item->tab4title, $this->item->tab4, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab5image, $this->item->tab5title, $this->item->tab5, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab6image, $this->item->tab6title, $this->item->tab6, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab7image, $this->item->tab7title, $this->item->tab7, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab8image, $this->item->tab8title, $this->item->tab8, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab9image, $this->item->tab9title, $this->item->tab9, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab10image, $this->item->tab10title, $this->item->tab10, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab11image, $this->item->tab11title, $this->item->tab11, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab12image, $this->item->tab12title, $this->item->tab12, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab13image, $this->item->tab13title, $this->item->tab13, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab14image, $this->item->tab14title, $this->item->tab14, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab15image, $this->item->tab15title, $this->item->tab15, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab16image, $this->item->tab16title, $this->item->tab16, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab17image, $this->item->tab17title, $this->item->tab17, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab18image, $this->item->tab18title, $this->item->tab18, $scripttext_slide2);
			$current_indicator_idx = get_SlideIndex($current_indicator_idx, $this->item->tab19image, $this->item->tab19title, $this->item->tab19, $scripttext_slide2);
		
		$scripttext .= $scripttext_slide2;
			
		$scripttext .= '</div >'."\n";
		$scripttext .= '<div class="carousel-inner">'."\n";  
                        
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab1image, $this->item->tab1title, $this->item->tab1, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab2image, $this->item->tab2title, $this->item->tab2, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab3image, $this->item->tab3title, $this->item->tab3, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab4image, $this->item->tab4title, $this->item->tab4, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab5image, $this->item->tab5title, $this->item->tab5, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab6image, $this->item->tab6title, $this->item->tab6, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab7image, $this->item->tab7title, $this->item->tab7, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab8image, $this->item->tab8title, $this->item->tab8, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab9image, $this->item->tab9title, $this->item->tab9, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab10image, $this->item->tab10title, $this->item->tab10, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab11image, $this->item->tab11title, $this->item->tab11, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab12image, $this->item->tab12title, $this->item->tab12, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab13image, $this->item->tab13title, $this->item->tab13, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab14image, $this->item->tab14title, $this->item->tab14, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab15image, $this->item->tab15title, $this->item->tab15, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab16image, $this->item->tab16title, $this->item->tab16, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab17image, $this->item->tab17title, $this->item->tab17, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab18image, $this->item->tab18title, $this->item->tab18, $scripttext_slide1);
			$current_image_idx = get_Slide($current_image_idx, $this->item->tab19image, $this->item->tab19title, $this->item->tab19, $scripttext_slide1);   
			
		$scripttext .= $scripttext_slide1;
		
		$scripttext .= '</div>'."\n";  
		$scripttext .= '<button class="carousel-control-prev" type="button" data-bs-target="#zhgmCarousel" data-bs-slide="prev">'."\n";  
		$scripttext .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>'."\n";  
		$scripttext .= '</button>'."\n";  
		$scripttext .= '<button class="carousel-control-next" type="button" data-bs-target="#zhgmCarousel" data-bs-slide="next">'."\n";  
		$scripttext .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>'."\n";  
		$scripttext .= '</button>'."\n";  
        $scripttext .= '</div>'."\n";  
        $scripttext .= '</div>'."\n";  
        $scripttext .= '<div class="col-md-4">'."\n";  
        }
        else
        { 
			$scripttext .= '<div class="col-md-12">'."\n";  
        }   
            $scripttext .= '<div id="GMapsID'.$mapDivSuffix.'" class="zhgm-placemarkDetailMap">'."\n";
            $scripttext .= '</div>'."\n";
            $scripttext .= '<div id="GMapsCredit'.$mapDivSuffix.'" class="zhgm-credit">'."\n";
            $scripttext .= '</div>'."\n";

			$scripttext .= '</div>'."\n";
			$scripttext .= '</div>'."\n";
    
        if ((int)$this->state->get('placemark.hidedescriptionhtml') == 0 ) {
			$scripttext .= '<div class="row">'."\n";
			$scripttext .= '<div class="col-md-12">'."\n";
			
            $pluginPrepContentText = $this->item->descriptionhtml;
				if (isset($this->item->preparecontent) && (int)$this->item->preparecontent != 0)
				{
					$pluginPrepContentText = HTMLHelper::_('content.prepare', $pluginPrepContentText);                               
				}

				$scripttext .= $pluginPrepContentText; 
			
			$scripttext .= '</div>'."\n";
			$scripttext .= '</div>'."\n";   
    
        }
   
        
  
        if ((int)$this->state->get('placemark.showdescriptionfullhtml') == 1 ) {
   
			$scripttext .= '<div class="row">'."\n";
			$scripttext .= '<div class="col-md-12">'."\n";
			
            $pluginPrepContentText = $this->item->descriptionfullhtml;
			if (isset($this->item->preparecontent) && (int)$this->item->preparecontent != 0)
			{
				$pluginPrepContentText = HTMLHelper::_('content.prepare', $pluginPrepContentText);                               
			}

			$scripttext .= $pluginPrepContentText;
			
			$scripttext .= '</div>'."\n";
			$scripttext .= '</div>'."\n";   
   
        }


	if ($apitype != "")
    {
        if ((int)$apitype == 1)
        {
            $apiURL = 'ditu.google.cn';
        }
        else
        {
            $apiURL = 'maps.googleapis.com';
        }
    }
    else
    {
        $apiURL = 'maps.googleapis.com';
    }

    $mainScriptBegin = $urlProtocol.'://'.$apiURL.'/maps/api/js?';
    
    $mainScriptMiddle = "";
    $scriptParametersExist = 0;

    if ($apiversion != "")
    {
        $scriptParametersExist = 1;
        if ($mainScriptMiddle == "")
        {
            $mainScriptMiddle = 'v='.$apiversion;
        }
        else
        {
            $mainScriptMiddle .= '&v='.$apiversion;
        }
        
    }

    if ($map_region !="")
    {
        $scriptParametersExist = 1;
        if ($mainScriptMiddle == "")
        {
            $mainScriptMiddle = 'region='.$map_region;
        }
        else
        {
            $mainScriptMiddle .= '&region='.$map_region;
        }        
    }

    if ($apikey4map != "")
    {
        $scriptParametersExist = 1;
        if ($mainScriptMiddle == "")
        {
            $mainScriptMiddle = 'key='.$apikey4map;
        }
        else
        {
            $mainScriptMiddle .= '&key='.$apikey4map;
        }        
        
    }


    $mainScriptBegin .= $mainScriptMiddle;
    

    $mainScriptAdd ="";
    $mainScriptLibrary ="";

    $mainLang = "";

    if (isset($main_lang) && $main_lang != "")
    {
        $mainLang = substr($main_lang,0, strpos($main_lang, '-'));

        if ($mainLang != "")
        {
            $scriptParametersExist = 1;
            $mainScriptAdd .= '&language='.$mainLang;
        }
        
    }



    $mainScriptAdd .= $mainScriptLibrary;	

	$retval = "";
        

	if ($do_map_load == 1) {
		if ($loadtype == "1")
		{
			$retval .= ' window.addEvent(\'domready\', initialize'.$mapInitTag.');' ."\n";
		}
		else if ($loadtype == "2")
		{
			$retval .= 'var tmpJQ'.$mapDivSuffix.' = jQuery.noConflict();'."\n";
			$retval .= ' tmpJQ'.$mapDivSuffix.'(document).ready(function() {initialize'.$mapInitTag.'();});' ."\n";
		}
		else if ($loadtype == "3")
		{
			// passed to script        
			if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
			{
					$this->load_by_script = 'initialize'.$mapInitTag;
					$load_by_script = $this->load_by_script;
			}
			else
			{
					$load_by_script = 'initialize'.$mapInitTag;
			}
					
		}
		else
		{
			$retval .= ' function addLoadEvent(func) {' ."\n";
			$retval .= '  var oldonload = window.onload;' ."\n";
			$retval .= '  if (typeof window.onload != \'function\') {' ."\n";
			$retval .= '    window.onload = func;' ."\n";
			$retval .= '  } else {' ."\n";
			$retval .= '    window.onload = function() {' ."\n";
			$retval .= '      if (oldonload) {' ."\n";
			$retval .= '        oldonload();' ."\n";
			$retval .= '      }' ."\n";
			$retval .= '      func();' ."\n";
			$retval .= '    }' ."\n";
			$retval .= '  }' ."\n";
			$retval .= '}    ' ."\n";    

			$retval .= 'addLoadEvent(initialize'.$mapInitTag.');' ."\n";
		}
	
	}
	else 
	{
		$retval .= 'ShowGPDRMessage'.$mapDivSuffix.'();' ."\n";
	}


	if ((int)$enable_map_gpdr == 0 || (int)$do_map_load == 1)
	{
        if (isset($load_by_script) && $load_by_script != "")
        {
            $mainScriptAdd .= '&callback='.$load_by_script;
            $wa->registerAndUseScript('zhgooglemaps.main', $mainScriptBegin . $mainScriptAdd, [], ['defer' => true]);
        }
        else
        {
			$mainScriptAdd .= '&callback=Function.prototype';
            $wa->registerAndUseScript('zhgooglemaps.main', $mainScriptBegin . $mainScriptAdd);
        }
		
	}   
	else
	{
		$mainScriptAdd .= '&callback='.'initialize'.$mapInitTag;
		$retval .= 'function HideGPDRMessage'.$mapDivSuffix.'(p_exp) {'."\n";
		
		$retval .= 'var tmpJQ'.$mapDivSuffix.' = jQuery.noConflict();'."\n";
		$retval .= ' tmpJQ'.$mapDivSuffix.'.getScript("'.$mainScriptBegin . $mainScriptAdd.'");' ."\n";
		$retval .= ' var CookiesMap = Cookies.noConflict();' ."\n";
		$retval .= ' if (p_exp == 0) {';
	    $retval .= ' CookiesMap.set("'.$cookieNameH.'", 1, { sameSite: \'strict\' });' ."\n";
		$retval .= '} else {';
		$retval .= ' CookiesMap.set("'.$cookieNameH.'", 1, { sameSite: \'strict\', expires: p_exp });' ."\n";
		$retval .= '}';
		
		$retval .= '};' ."\n"; 
		
		$retval .= 'function HideGPDRMessageCookies'.$mapDivSuffix.'(p_exp) {'."\n";		
		$retval .= ' document.getElementById("zhgm-display-gpdr-'.$mapDivSuffix.'").setAttribute(\'onclick\', \'HideGPDRMessage'.$mapDivSuffix.'(\'+p_exp+\');\');'."\n";
		$retval .= ' document.getElementById("zhgm-display-gpdr-cookie-'.$mapDivSuffix.'").style.display = \'none\'';
		$retval .= '};' ."\n"; 
	}
	
	// Google Maps JS API initialization - end


	if (1==1)
	{		
		$imgpathIcons = URI::root() .'components/com_zhgooglemap/assets/icons/';
		$imgpathUtils = URI::root() .'components/com_zhgooglemap/assets/utils/';
		$directoryIcons = 'components/com_zhgooglemap/assets/icons/';
		  

		$retval .= 'var icoIcon=\''.$imgpathIcons.'\';'."\n";
		$retval .= 'var icoUtils=\''.$imgpathUtils.'\';'."\n";
		$retval .= 'var icoDir=\''.$directoryIcons.'\';'."\n";
		
		$retval .= 'function initialize() {'."\n";

		$retval .= '    routeaddress = "";'."\n";
		
		if (($currentmarker->latitude != "" && $currentmarker->longitude != "")
		   ||($currentmarker->addresstext != ""))
		{
				if ($currentmarker->latitude != "" && $currentmarker->longitude != "")
				{
					$retval .= 'latlng = new google.maps.LatLng('.$currentmarker->latitude.', ' .$currentmarker->longitude.');' ."\n";
					$retval .= '    var myOptions = {'."\n";
					$retval .= '        center: latlng,'."\n";
					$retval .= '        zoom: 14,'."\n";
					$retval .= '    };'."\n";

					$retval .= '    map = new google.maps.Map(document.getElementById("GMapsID'.$mapDivSuffix.'"), myOptions);'."\n";

					$retval .= '    var marker = new google.maps.Marker({'."\n";
					$retval .= '          position: latlng, '."\n";
					$retval .= '      map: map, ' ."\n";
					if ((int)$currentmarker->overridemarkericon == 0)
					{
						$retval .= '      icon: icoIcon + "'.str_replace("#", "%23", $currentmarker->icontype).'.png" ' ."\n";
					}    
					else
					{
						$retval .= '      icon: icoIcon + "'.str_replace("#", "%23", $currentmarker->groupicontype).'.png" ' ."\n";
					}
					$retval .= '    });'."\n";
					
				}            
				else
				{
					// Begin marker creation with address by geocoding
					$retval .= 'var geocoder = new google.maps.Geocoder();'."\n";
					$retval .= '  geocoder.geocode( { \'address\': "'.$currentmarker->addresstext.'"}, function(results, status) {'."\n";
					$retval .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
					$retval .= '    var latlng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";
					$retval .= '    var myOptions = {'."\n";
					$retval .= '        center: latlng,'."\n";
					$retval .= '        zoom: 14,'."\n";
					$retval .= '    };'."\n";

					$retval .= '    map = new google.maps.Map(document.getElementById("GMapsID'.$mapDivSuffix.'"), myOptions);'."\n";

					$retval .= '    var marker = new google.maps.Marker({'."\n";
					$retval .= '          position: latlng, '."\n";
					$retval .= '      map: map, ' ."\n";
					if ((int)$currentmarker->overridemarkericon == 0)
					{
						$retval .= '      icon: icoIcon + "'.str_replace("#", "%23", $currentmarker->icontype).'.png" ' ."\n";
					}    
					else
					{
						$retval .= '      icon: icoIcon + "'.str_replace("#", "%23", $currentmarker->groupicontype).'.png" ' ."\n";
					}
					$retval .= '    });'."\n";
				    $retval .= '  }'."\n";
					$retval .= '  else'."\n";
					$retval .= '  {'."\n";
					$retval .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': '.$currentmarker->addresstext.'" + "\n"+"id:'. $currentmarker->id.'");'."\n";
					$retval .= '  }'."\n";
					$retval .= '});'."\n";                    
				}
		}                


		$retval .= '}'."\n";
		
		
		if ($do_map_load == 0)
		{
			$gpdr_text = '';
			$gpdr_text .= '<div class="zhgm-map-gpdr">';
			$gpdr_text .= '<div class="zhgm-map-gpdr-content">';
			$gpdr_text .= '<div class="zhgm-map-gpdr-header">';
			if ($map_gpdr_header != "")
			{
				$gpdr_text .= $map_gpdr_header;
			}
			$gpdr_text .= '</div>'."\n";
			$gpdr_text .= '<div class="zhgm-map-gpdr-buttons">';

			if ((int)$map_gpdr_buttonc == 0) {
				$gpdr_text .= '<button id="zhgm-display-gpdr-cookie-'.$mapDivSuffix.'" class="btn btn-danger" type="button" onclick="HideGPDRMessageCookies'.$mapDivSuffix.'('.(int)$map_gpdr_buttoncexp.');">';
				if ($map_gpdr_buttonclabel != "")
				{
					$gpdr_text .= $map_gpdr_buttonclabel;
				}
				else
				{
					$gpdr_text .= Text::_('COM_ZHGOOGLEMAP_MAP_SHOW_MAP_BUTTON_COOKIE');
				}
				$gpdr_text .= '</button>';
			}
			
			if ((int)$map_gpdr_buttonc == 0) {
				$gpdr_text .= '<button id="zhgm-display-gpdr-'.$mapDivSuffix.'" class="btn btn-danger zhgm-map-gpdr-button" type="button" onclick="HideGPDRMessage'.$mapDivSuffix.'(0);">';
			} else {
				$gpdr_text .= '<button id="zhgm-display-gpdr-'.$mapDivSuffix.'" class="btn btn-danger zhgm-map-gpdr-button" type="button" onclick="HideGPDRMessage'.$mapDivSuffix.'('.(int)$map_gpdr_buttoncexp.');">';
			}
			if ($map_gpdr_buttonlabel != "")
			{
				$gpdr_text .= $map_gpdr_buttonlabel;
			}
			else
			{
				$gpdr_text .= Text::_('COM_ZHGOOGLEMAP_MAP_SHOW_MAP_BUTTON');
			}
			$gpdr_text .= '</button>';
							
			$gpdr_text .= '</div>';
			$gpdr_text .= '<div class="zhgm-map-gpdr-footer">';
			if ($map_gpdr_footer != "")
			{
				$gpdr_text .= $map_gpdr_footer;
			}
			$gpdr_text .= '</div>';
			$gpdr_text .= '</div>';
			$gpdr_text .= '</div>';
			
			$retval .= 'function ShowGPDRMessage'.$mapDivSuffix.'() {'."\n";
			$retval .= ' document.getElementById("GMapsID'.$mapDivSuffix.'").innerHTML = \''.str_replace(array("\r", "\r\n", "\n"), '', str_replace('\'', '\\\'', $gpdr_text)).'\';' ."\n";
			
			$retval .= '};' ."\n"; 
		}

		$scripttext .= $scripttextBegin . $retval. $scripttextEnd;

	}
		
	
	echo $scripttext;