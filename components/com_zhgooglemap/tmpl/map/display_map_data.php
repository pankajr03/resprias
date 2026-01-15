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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\CMS\HTML\HTMLHelper;

use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapDivsHelper;
use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapPlacemarksHelper;
use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapPathsHelper;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Input\Cookie;

$document    = Factory::getDocument();
$app = Factory::getApplication();

$wa  = $document->getWebAssetManager();


$allowUserMarker = 0;
$scripttext = '';
$scripttextBegin = '';
$scripttextEnd = '';

$divmapheader ="";
$divmapfooter ="";
$currentUserInfo ="";
$currentUserID = 0;

$scripthead ="";

// Change translation language and load translation
$currentLanguage = Factory::getLanguage();
$currentLangTag = $currentLanguage->getTag();
$main_lang_little = "";

if (isset($map->lang) && $map->lang != "")
{
    $main_lang = $map->lang;
    $main_lang_little = substr($main_lang,0, strpos($main_lang, '-'));

      
    $currentLanguage->load('com_zhgooglemap', JPATH_SITE, $map->lang, true);    
    $currentLanguage->load('com_zhgooglemap', JPATH_COMPONENT, $map->lang, true);    
    
    // fix translation problem on plugin call
    $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap' , $map->lang, true);    

    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
    {
        $this->main_lang = $main_lang;                
    }
    
}
else
{
    $currentLanguage->load('com_zhgooglemap', JPATH_SITE, $currentLangTag, true);    
    $currentLanguage->load('com_zhgooglemap', JPATH_COMPONENT, $currentLangTag, true);        
    $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap' , $currentLangTag, true);    
    
}

if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
    // all OK
    if ((int)$MapXdoLoad == 0)
    {   // ***** Plugin call *****
        //   hide loading call
        //   but passing composite ID
        if (isset($MapXArticleId) && ($MapXArticleId != ""))
        {
            $mapInitTag = $MapXArticleId;
            // Map DIV suffix
            $mapDivSuffix = '_'.$MapXArticleId;
        }
        else
        {
            if (isset($MapXsuffix) && ($MapXsuffix != ""))
            {
                $mapInitTag = $MapXsuffix;
                $mapDivSuffix = "";
            }
            else
            {
                $mapInitTag = "";
                $mapDivSuffix = "";
            }
        }
    }
    else
    {
        if (isset($MapXsuffix) && ($MapXsuffix != ""))
        {
            $mapInitTag = $MapXsuffix;
            $mapDivSuffix = "";
        }
        else
        {
            $mapInitTag = "";
            $mapDivSuffix = "";
        }
    }

}
else
{
    $MapXdoLoad = 1;

    if (isset($MapXsuffix) && ($MapXsuffix != ""))
    {
        $mapInitTag = $MapXsuffix;
        $mapDivSuffix = "";
    }
    else
    {
        $mapInitTag = "";
        $mapDivSuffix = "";
    }
}


if (isset($map->usermarkers) 
  && ((int)$map->usermarkers == 1
      ||(int)$map->usermarkers == 2)) 
{
    $currentUser = Factory::getUser();

    if ($currentUser->id == 0)
    {
        if ((int)$map->usermarkers == 1)
        {
            $currentUserInfo .= '<div id="GMapsLogin'.$mapDivSuffix.'" class="zhgm-login">';
            $currentUserInfo .= Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_NOTLOGIN' );
            $currentUserInfo .= '</div>';
        }
        $allowUserMarker = 0;
        $currentUserID = 0;
    }
    else
    {
        if ((int)$map->usermarkers == 1)
        {
            $currentUserInfo .= '<div id="GMapsLogin'.$mapDivSuffix.'" class="zhgm-login">';
            $currentUserInfo .= Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_LOGIN' ) .' '. $currentUser->name;
            $currentUserInfo .= '</div>';
        }
        $allowUserMarker = 1;
        $currentUserID = $currentUser->id;
    }
    
} 
else
{
    $allowUserMarker = 0;
    $currentUserID = 0;
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


// if post data to load
if ($allowUserMarker == 1
 && isset($_POST['marker_action']))
{        
$scripttext .= '<script type="text/javascript">';
    
    $db = Factory::getDBO();

    if (isset($_POST['marker_action']) && 
        ($_POST['marker_action'] == "insert") ||
        ($_POST['marker_action'] == "update") 
        )
    {

        $title = substr($_POST["markername"], 0, 249);
        if ($title == "")
        {
            $title = 'Placemark';
        }

        $markericon = substr($_POST["markerimage"], 0, 249);
        if ($markericon == "")
        {
            $markericon ='default#';
        }
        
        $description = $_POST["markerdescription"];
        $latitude = substr($_POST["markerlat"], 0, 100);
        $longitude = substr($_POST["markerlng"], 0, 100);
        $group = substr($_POST["markergroup"], 0, 100);
        $markercatid = substr($_POST["markercatid"], 0, 100);
        $markerbaloon = substr($_POST["markerbaloon"], 0, 100);
        $markermarkercontent = substr($_POST["markermarkercontent"], 0, 100);
        if (isset($_POST['markerid']))
        {
            $markerid = (int)substr($_POST["markerid"], 0, 100);
        }
        else
        {
            $markerid = '';
        }
        $markerhrefimage = substr($_POST["markerhrefimage"], 0, 500);
        
        if (isset($map->usercontact) && (int)$map->usercontact == 1) 
        {
            $contactid = substr($_POST["contactid"], 0, 100);
        }
        else
        {
            $contactid = '';
        }
        
        $contactDoInsert = 0;
        
        if (isset($map->usercontact) && (int)$map->usercontact == 1) 
        {
            $contact_name = substr($_POST["contactname"], 0, 250);
            $contact_position = substr($_POST["contactposition"], 0, 250);
            $contact_phone = substr($_POST["contactphone"], 0, 250);
            $contact_mobile = substr($_POST["contactmobile"], 0, 250);
            $contact_fax = substr($_POST["contactfax"], 0, 250);
            $contact_address = substr($_POST["contactaddress"], 0, 250);
            $contact_email = substr($_POST["contactemail"], 0, 250);
            
            if (($contact_name != "") 
              ||($contact_position != "")
              ||($contact_phone != "")
              ||($contact_mobile != "")
              ||($contact_fax != "")
              ||($contact_email != "")
              ||($contact_address != "")
                )
            {
                $contactDoInsert = 1;
            }
        }

        $newRow = new stdClass;
        
        if ($_POST['marker_action'] == "insert")
        {
            $newRow->id = NULL;
            $newRow->userprotection = 0;
            $newRow->openbaloon = 0;
            $newRow->actionbyclick = 1;
            $newRow->access = 1;
            
            if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
             &&($contactDoInsert == 1))
            {                
                $newRow->showcontact = 2;
            }
            else
            {                
                $newRow->showcontact = 0;
            }
        }
        else
        {
            $newRow->id = $markerid;

            if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
             &&($contactDoInsert == 1) && ((int)$contactid == 0))
            {                
                $newRow->showcontact = 2;
            }
            
        }
        
        // Data for Contacts - begin
        if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
          &&($contactDoInsert == 1))
        {
            $newContactRow = new stdClass;
            
            if ($_POST['marker_action'] == "insert")
            {
                $newContactRow->id = NULL;
                $newContactRow->published = (int)$map->usercontactpublished;
                $newContactRow->language = '*';
                $newContactRow->access = 1;
            }
            else
            {
                if ((int)$contactid == 0)
                {
                    $newContactRow->id = NULL;
                    $newContactRow->published = (int)$map->usercontactpublished;
                    $newContactRow->language = '*';
                    $newContactRow->access = 1;
                }
                else
                {
                    $newContactRow->id = $contactid;
                }
            }
            
        }            
        // Data for Contacts - end
        
        // because it (quotes) escaped
        $newRow->title = str_replace('\\','', htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8'));
        $newRow->description = str_replace('\\','', htmlspecialchars($description, ENT_NOQUOTES, 'UTF-8'));
        // because it escaped
        $newRow->latitude = htmlspecialchars($latitude, ENT_QUOTES, 'UTF-8');
        $newRow->longitude = htmlspecialchars($longitude, ENT_QUOTES, 'UTF-8');
        $newRow->mapid = $map->id;
        $newRow->icontype = htmlspecialchars($markericon, ENT_QUOTES, 'UTF-8');
                
		if ($_POST['marker_action'] == "insert") {
			$newRow->published = (int)$map->usermarkerspublished;
			$newRow->createdbyuser = $currentUserID;
		} else {
			// do not change state
		}

        
        $newRow->markergroup = htmlspecialchars($group, ENT_QUOTES, 'UTF-8');
		if ($newRow->markergroup == '') {
			$newRow->markergroup = 0;
		}
        $newRow->catid = htmlspecialchars($markercatid, ENT_QUOTES, 'UTF-8');
		if ($newRow->catid == '') {
			$newRow->catid = 0;
		}

        $newRow->baloon = htmlspecialchars($markerbaloon, ENT_QUOTES, 'UTF-8');
        $newRow->markercontent = htmlspecialchars($markermarkercontent, ENT_QUOTES, 'UTF-8');
        $newRow->hrefimage = htmlspecialchars($markerhrefimage, ENT_QUOTES, 'UTF-8');
        

        if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
          &&($contactDoInsert == 1))
        {
            $newContactRow->name = str_replace('\\','', htmlspecialchars($contact_name, ENT_NOQUOTES, 'UTF-8'));
            if ($newContactRow->name == "")
            {
                $newContactRow->name = $newRow->title;
            }
            $newContactRow->con_position = str_replace('\\','', htmlspecialchars($contact_position, ENT_NOQUOTES, 'UTF-8'));
            $newContactRow->telephone = str_replace('\\','', htmlspecialchars($contact_phone, ENT_NOQUOTES, 'UTF-8'));
            $newContactRow->mobile = str_replace('\\','', htmlspecialchars($contact_mobile, ENT_NOQUOTES, 'UTF-8'));
            $newContactRow->fax = str_replace('\\','', htmlspecialchars($contact_fax, ENT_NOQUOTES, 'UTF-8'));
            $newContactRow->email_to = str_replace('\\','', htmlspecialchars($contact_email, ENT_NOQUOTES, 'UTF-8'));
            $newContactRow->address = str_replace('\\','', htmlspecialchars($contact_address, ENT_NOQUOTES, 'UTF-8'));
        }
        
        if ($_POST['marker_action'] == "insert")
        {
            if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
              &&($contactDoInsert == 1))
            {
                $dml_contact_result = $db->insertObject( '#__contact_details', $newContactRow, 'id' );
                
                $newRow->contactid = $newContactRow->id;
            }

            // 9.03.2015 set creation date
            $newRow->createddate = Factory::getDate()->toSQL();
            
            $dml_result = $db->insertObject( '#__zhgooglemaps_markers', $newRow, 'id' );
        }
        else
        {
            if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
              &&($contactDoInsert == 1))
            {
                if (isset($newContactRow->id))
                {
                    $dml_contact_result = $db->updateObject( '#__contact_details', $newContactRow, 'id' );
                }
                else
                {
                    $dml_contact_result = $db->insertObject( '#__contact_details', $newContactRow, 'id' );
                    $newRow->contactid = $newContactRow->id;
                }
            }

            $dml_result = $db->updateObject( '#__zhgooglemaps_markers', $newRow, 'id' );
            //$scripttext .= 'alert("Updated");'."\n";
        }
        
        if ((!$dml_result) || 
            (isset($map->usercontact) && (int)$map->usercontact == 1 && ($contactDoInsert == 1) && (!$dml_result))
            )
        {
            //$this->setError($db->getErrorMsg());
            $scripttext .= 'alert("Error (Insert New Marker or Update): " + "' . $db->escape($db->getErrorMsg()).'");';
        }
        else
        {
            //$scripttext .= 'alert("Complete, redirect");'."\n";
            $scripttext .= 'window.location = "'.URI::current().'";'."\n";
            
            $new_id = $newRow->id;

        }
    }
    else if (isset($_POST['marker_action']) && $_POST['marker_action'] == "delete") 
    {

        $contactid = substr($_POST["contactid"], 0, 100);
        $markerid = substr($_POST["markerid"], 0, 100);
    
        if (isset($map->usercontact) && (int)$map->usercontact == 1) 
        {
        
            if ((int)$contactid != 0)
            {
                $query = $db->getQuery(true);

                $db->setQuery( 'DELETE FROM `#__contact_details` '.
                'WHERE `id`='.(int)$contactid);

				try {
					$db->execute();
				} catch (ExecutionFailureException $e) {
					throw new \Exception("Error (Delete Exist Marker Contact): " . $e->getMessage(), 500);
				}                

            }
        }


        $query = $db->getQuery(true);

        $db->setQuery( 'DELETE FROM `#__zhgooglemaps_markers` '.
        'WHERE `createdbyuser`='.$currentUserID.
        ' and `id`='.(int)$markerid);


		try {
			$db->execute();
			$scripttext .= 'window.location = "'.URI::current().'";'."\n";
		} catch (ExecutionFailureException $e) {
			throw new \Exception("Error (Delete Exist Marker): " . $e->getMessage(), 500);
		} 
				
    }
$scripttext .= '</script>';


    echo $scripttext;

}
else
{
// main part where not post data


// Process API version for map controls
// 3.22 - ZoomControlStyle is not available, there is no longer a slider
//      - Overview Map control (deprecated)
//      - Pan control (deprecated)
//      - Full Screen control (new)
//
if ($apiversion != "")
{
    if (($apiversion == '3') 
        ||($apiversion == '3.exp'))
    {
        $feature4control = 2;
    }
    else
    {
        if (version_compare($apiversion, '3.22') >= 0)
        {
            $feature4control = 2;
        }
        else
        {
            $feature4control = 1;
        }
    }
}
else
{
    $feature4control = 2;
}    

$credits ='';

if ($licenseinfo == "")
{
  $licenseinfo = 8;
}

if ($compatiblemode == "")
{
  $compatiblemode = 0;
}

if (isset($placemarkTitleTag) && $placemarkTitleTag != "")
{
    if ($placemarkTitleTag == "h2"
     || $placemarkTitleTag == "h3")
    {
        // it's OK. Do not change it
        //$placemarkTitleTag = $placemarkTitleTag;
    }
    else
    {
        $placemarkTitleTag ='h2';
    }
}
else
{
    $placemarkTitleTag ='h2';
}

$imgpathIcons = URI::root() .'components/com_zhgooglemap/assets/icons/';
$imgpathUtils = URI::root() .'components/com_zhgooglemap/assets/utils/';
$directoryIcons = 'components/com_zhgooglemap/assets/icons/';
    
$imgpath4size = JPATH_SITE .'/components/com_zhgooglemap/assets/icons/';


$currentPlacemarkCenter = "do not change";
$currentPlacemarkAction = "do not change";
$currentPlacemarkActionID = "do not change";

if ($centerplacemarkid != "")
{
    $currentPlacemarkCenter = $centerplacemarkid;
        
}

if ($centerplacemarkactionid != "")
{
    $currentPlacemarkActionID = $centerplacemarkactionid;
}

if ($centerplacemarkaction != "")
{
    $currentPlacemarkAction = str_replace(',', ';', $centerplacemarkaction);
}


if (isset($loadjquery))
{
	if ((int)$loadjquery == 1) {
		$wa->useScript('jquery');
	}
}

$wa->registerAndUseStyle('zhgooglemaps.common', URI::root() .'components/com_zhgooglemap/assets/css/common.css');


if (isset($map->css2load) && ($map->css2load != ""))
{
    $loadCSSList = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $map->css2load));


    for($i = 0; $i < count($loadCSSList); $i++) 
    {
        $currCSS = trim($loadCSSList[$i]);
        if ($currCSS != "")
        {
            $wa->registerAndUseStyle('zhgooglemaps.css2load_' . $i, $currCSS);
        }
    }
}

if (isset($map->js2load) && ($map->js2load != ""))
{
    $loadJSList = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $map->js2load));


    for($i = 0; $i < count($loadJSList); $i++) 
    {
        $currJS = trim($loadJSList[$i]);
        if ($currJS != "")
        {
            $wa->registerAndUseScript('zhgooglemaps.js2load_' . $i, $currJS);
        }
    }
}



// Overrides - begin
if (isset($map->override_id) && (int)$map->override_id != 0) 
{
    $fv_override = MapPlacemarksHelper::get_MapOverride($map->override_id);
    if (isset($fv_override) && (int)$fv_override->published == 1)
    {
        if ((isset($fv_override->placemark_list_title) && $fv_override->placemark_list_title != ""))
        {
            $fv_override_placemark_title = $fv_override->placemark_list_title;
        }
        else
        {
            $fv_override_placemark_title = Text::_( 'COM_ZHGOOGLEMAP_MARKERLIST_SEARCH_FIELD');
        }
        if ((isset($fv_override->placemark_list_button_title) && $fv_override->placemark_list_button_title != ""))
        {
            $fv_override_placemark_button_title = $fv_override->placemark_list_button_title;
        }
        else
        {
            $fv_override_placemark_button_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
        }
        if ((isset($fv_override->placemark_list_button_hint) && $fv_override->placemark_list_button_hint != ""))
        {
            $fv_override_placemark_button_tooltip = $fv_override->placemark_list_button_hint;
        }
        else
        {
            $fv_override_placemark_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
        }
        
        // panel
        if ((isset($fv_override->panelcontrol_hint) && $fv_override->panelcontrol_hint != ""))
        {
            $fv_override_panel_button_tooltip = $fv_override->panelcontrol_hint;
        }
        else
        {
            $fv_override_panel_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANELCONTROL_LABEL');
        }
        if ((isset($fv_override->panel_detail_title) && $fv_override->panel_detail_title != ""))
        {
            $fv_override_panel_detail_title = $fv_override->panel_detail_title;
        }
        else
        {
            $fv_override_panel_detail_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_DETAIL_TITLE');
        }
        if ((isset($fv_override->panel_placemarklist_title) && $fv_override->panel_placemarklist_title != ""))
        {
            $fv_override_panel_placemarklist_title = $fv_override->panel_placemarklist_title;
        }
        else
        {
            $fv_override_panel_placemarklist_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
        }
        if ((isset($fv_override->panel_route_title) && $fv_override->panel_route_title != ""))
        {
            $fv_override_panel_route_title = $fv_override->panel_route_title;
        }
        else
        {
            $fv_override_panel_route_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_ROUTE_TITLE');
        }        
        if ((isset($fv_override->panel_group_title) && $fv_override->panel_group_title != ""))
        {
            $fv_override_panel_group_title = $fv_override->panel_group_title;
        }
        else
        {
            $fv_override_panel_group_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_GROUP_TITLE');
        }

        if ((isset($fv_override->group_list_title) && $fv_override->group_list_title != ""))
        {
            $fv_override_group_title = $fv_override->group_list_title;
        }
        else
        {
            $fv_override_group_title = Text::_( 'COM_ZHGOOGLEMAP_GROUPLIST_SEARCH_FIELD');
        }                
 
        if ((isset($fv_override->gogoogle_text) && $fv_override->gogoogle_text != ""))
        {
            $fv_override_gogoogle_text = $fv_override->gogoogle_text;
        }
        else
        {
            $fv_override_gogoogle_text = Text::_( 'COM_ZHGOOGLEMAP_MAP_GOGOOGLE_TITLE');
        }                  
                
        if ((isset($fv_override->placemark_list_search) && $fv_override->placemark_list_search != ""))
        {
            $fv_override_placemark_list_search = (int)$fv_override->placemark_list_search;
        }
        else
        {
            $fv_override_placemark_list_search = 0;
        }               
                                
        if ((isset($fv_override->placemark_list_mapping_type) && $fv_override->placemark_list_mapping_type != ""))
        {
            $fv_override_placemark_list_mapping_type = (int)$fv_override->placemark_list_mapping_type;
        }
        else
        {
            $fv_override_placemark_list_mapping_type = 0;
        }      

        if ((isset($fv_override->placemark_list_accent_side) && $fv_override->placemark_list_accent_side != ""))
        {
            $fv_override_placemark_list_accent_side = (int)$fv_override->placemark_list_accent_side;
        }
        else
        {
            $fv_override_placemark_list_accent_side = 0;
        } 
                                             

        if ((isset($fv_override->placemark_list_mapping) && $fv_override->placemark_list_mapping != ""))
        {
            $fv_override_placemark_list_mapping = $fv_override->placemark_list_mapping;
        }
        else
        {
            $fv_override_placemark_list_mapping = ""; 
        }  
 
        if ((isset($fv_override->placemark_list_accent) && $fv_override->placemark_list_accent != ""))
        {
            $fv_override_placemark_list_accent = $fv_override->placemark_list_accent;
        }
        else
        {
            $fv_override_placemark_list_accent = ""; 
        }    
                
        //
        if ((isset($fv_override->group_list_search) && $fv_override->group_list_search != ""))
        {
            $fv_override_group_list_search = (int)$fv_override->group_list_search;
        }
        else
        {
            $fv_override_group_list_search = 0;
        } 
                
        if ((isset($fv_override->group_list_mapping_type) && $fv_override->group_list_mapping_type != ""))
        {
            $fv_override_group_list_mapping_type = (int)$fv_override->group_list_mapping_type;
        }
        else
        {
            $fv_override_group_list_mapping_type = 0;
        }      

        if ((isset($fv_override->group_list_accent_side) && $fv_override->group_list_accent_side != ""))
        {
            $fv_override_group_list_accent_side = (int)$fv_override->group_list_accent_side;
        }
        else
        {
            $fv_override_group_list_accent_side = 0;
        } 
                                             

        if ((isset($fv_override->group_list_mapping) && $fv_override->group_list_mapping != ""))
        {
            $fv_override_group_list_mapping = $fv_override->group_list_mapping;
        }
        else
        {
            $fv_override_group_list_mapping = ""; 
        }  
 
        if ((isset($fv_override->group_list_accent) && $fv_override->group_list_accent != ""))
        {
            $fv_override_group_list_accent = $fv_override->group_list_accent;
        }
        else
        {
            $fv_override_group_list_accent = ""; 
        }  
        
        if ((isset($fv_override->placemark_date_fmt) && $fv_override->placemark_date_fmt != ""))
        {
            $fv_placemark_date_fmt = $fv_override->placemark_date_fmt;
        }
        else
        {
            $fv_placemark_date_fmt = "";
        }
        
        if ((isset($fv_override->circle_radius) && $fv_override->circle_radius != ""))
        {
            $fv_override_circle_radius = $fv_override->circle_radius;
        }
        else
        {
            $fv_override_circle_radius = "";
        }

        if ((isset($fv_override->circle_stroke_color) && $fv_override->circle_stroke_color != ""))
        {
            $fv_override_circle_stroke_color = $fv_override->circle_stroke_color;
        }
        else
        {
            $fv_override_circle_stroke_color = "";
        }

        if ((isset($fv_override->circle_stroke_opacity) && $fv_override->circle_stroke_opacity != ""))
        {
            $fv_override_circle_stroke_opacity = $fv_override->circle_stroke_opacity;
        }
        else
        {
            $fv_override_circle_stroke_opacity = "";
        }

        if ((isset($fv_override->circle_stroke_weight) && $fv_override->circle_stroke_weight != ""))
        {
            $fv_override_circle_stroke_weight = $fv_override->circle_stroke_weight;
        }
        else
        {
            $fv_override_circle_stroke_weight = "";
        }

        if ((isset($fv_override->circle_fill_color) && $fv_override->circle_fill_color != ""))
        {
            $fv_override_circle_fill_color = $fv_override->circle_fill_color;
        }
        else
        {
            $fv_override_circle_fill_color = "";
        }

        if ((isset($fv_override->circle_fill_opacity) && $fv_override->circle_fill_opacity != ""))
        {
            $fv_override_circle_fill_opacity = $fv_override->circle_fill_opacity;
        }
        else
        {
            $fv_override_circle_fill_opacity = "";
        }
        
        if ((isset($fv_override->circle_draggable) && $fv_override->circle_draggable != ""))
        {
            $fv_override_circle_draggable = $fv_override->circle_draggable;
        }
        else
        {
            $fv_override_circle_draggable = "";
        }
        if ((isset($fv_override->circle_editable) && $fv_override->circle_editable != ""))
        {
            $fv_override_circle_editable = $fv_override->circle_editable;
        }
        else
        {
            $fv_override_circle_editable = "";
        }
        if ((isset($fv_override->circle_info) && $fv_override->circle_info != ""))
        {
            $fv_override_circle_info = $fv_override->circle_info;
        }
        else
        {
            $fv_override_circle_info = "";
        }
            
        //
           
                
                
    }
    else
    {
        $fv_override_placemark_title = Text::_( 'COM_ZHGOOGLEMAP_MARKERLIST_SEARCH_FIELD');
        $fv_override_placemark_button_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
        $fv_override_placemark_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
        
        $fv_override_panel_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANELCONTROL_LABEL');
        $fv_override_panel_detail_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_DETAIL_TITLE');
        $fv_override_panel_placemarklist_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
        $fv_override_panel_route_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_ROUTE_TITLE');
        $fv_override_panel_group_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_GROUP_TITLE');
                
        $fv_override_group_title = Text::_( 'COM_ZHGOOGLEMAP_GROUPLIST_SEARCH_FIELD');

        $fv_override_gogoogle_text = Text::_( 'COM_ZHGOOGLEMAP_MAP_GOGOOGLE_TITLE');

        $fv_override_placemark_list_search = 0;
        $fv_override_placemark_list_mapping_type = 0;
        $fv_override_placemark_list_mapping = "";       
        $fv_override_placemark_list_accent = "";
        $fv_override_placemark_list_accent_side = 0;

        $fv_override_group_list_search = 0;
        $fv_override_group_list_mapping_type = 0;
        $fv_override_group_list_mapping = ""; 
        $fv_override_group_list_accent = ""; 
        $fv_override_group_list_accent_side = 0;
        
        $fv_placemark_date_fmt = "";
        
        $fv_override_circle_radius = "";
        $fv_override_circle_stroke_color = "";
        $fv_override_circle_stroke_opacity = "";
        $fv_override_circle_stroke_weight = "";
        $fv_override_circle_fill_color = "";
        $fv_override_circle_fill_opacity = "";
        $fv_override_circle_draggable = "";
        $fv_override_circle_editable = "";
        $fv_override_circle_info = "";
                
    }    
}
else
{
    $fv_override_placemark_title = Text::_( 'COM_ZHGOOGLEMAP_MARKERLIST_SEARCH_FIELD');
    $fv_override_placemark_button_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
    $fv_override_placemark_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PLACEMARKLIST');
    
    $fv_override_panel_button_tooltip = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANELCONTROL_LABEL');
    $fv_override_panel_detail_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_DETAIL_TITLE');
    $fv_override_panel_placemarklist_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
    $fv_override_panel_route_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_ROUTE_TITLE');
    $fv_override_panel_group_title = Text::_( 'COM_ZHGOOGLEMAP_MAP_PANEL_GROUP_TITLE');
        
    $fv_override_group_title = Text::_( 'COM_ZHGOOGLEMAP_GROUPLIST_SEARCH_FIELD');

    $fv_override_gogoogle_text = Text::_( 'COM_ZHGOOGLEMAP_MAP_GOGOOGLE_TITLE');

    $fv_override_placemark_list_search = 0;
    $fv_override_placemark_list_mapping_type = 0;
    $fv_override_placemark_list_mapping = "";       
    $fv_override_placemark_list_accent = "";
    $fv_override_placemark_list_accent_side = 0;

    $fv_override_group_list_search = 0;
    $fv_override_group_list_mapping_type = 0;
    $fv_override_group_list_mapping = ""; 
    $fv_override_group_list_accent = ""; 
    $fv_override_group_list_accent_side = 0;       
    
    $fv_placemark_date_fmt = "";
    
    $fv_override_circle_radius = "";
    $fv_override_circle_stroke_color = "";
    $fv_override_circle_stroke_opacity = "";
    $fv_override_circle_stroke_weight = "";
    $fv_override_circle_fill_color = "";
    $fv_override_circle_fill_opacity = "";
    $fv_override_circle_draggable = "";
    $fv_override_circle_editable = "";
    $fv_override_circle_info = "";

        
}
// Overrides - end

$map_street_view_content = 0;

if ((int)$map->maptype == 10)
{
    $map_street_view_content = 1;
}
else
{
    $map_street_view_content = 0;
}

if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{
    $placemarkSearch = (int)$map->markerlistsearch;    
}
else
{
    $placemarkSearch = 0;
}

if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{
    $groupSearch = (int)$map->markergroupsearch;    
}
else
{
    $groupSearch = 0;
}



$managePanelFeature = 0;

if ((isset($map->panelinfowin) && (int)$map->panelinfowin != 0))
{
    $managePanelInfowin = 1;
}
else
{
    $managePanelInfowin = 0;
}


if (($managePanelInfowin ==1)
||((isset($map->markerlistpos) && (int)$map->markerlistpos == 120))
||((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120))
)
{
    $managePanelFeature = 1;
}

if ((isset($map->trafficcontrol) && (int)$map->trafficcontrol > 1) 
 || (isset($map->transitcontrol) && (int)$map->transitcontrol > 1) 
 || (isset($map->bikecontrol) && (int)$map->bikecontrol > 1) 
 || (isset($map->mapcentercontrol) && (int)$map->mapcentercontrol != 0) 
 || (isset($map->markerlistpos) && (int)$map->markerlistpos != 0 && isset($map->markerlistbuttontype) && (int)$map->markerlistbuttontype != 0) 
 ||($managePanelFeature == 1)
)
{
    $layersButtons = 1;
}
else
{
    $layersButtons = 0;
}



$custMapTypeList = explode(";", $map->custommaptypelist);
if (count($custMapTypeList) != 0)
{
    $custMapTypeFirst = $custMapTypeList[0];
}
else
{
    $custMapTypeFirst = 0;
}

$needOverlayControl = 0;

if ((int)$map->overlayopacitycontrol != 0)
{
    if ($needOverlayControl == 0)
    {    
        if ((int)$map->custommaptype != 0)
        {
			if (isset($maptypes) && !empty($maptypes)) {
				foreach ($maptypes as $key => $currentmaptype)     
				{               
                for ($i=0; $i < count($custMapTypeList); $i++)
                {
                    if ($currentmaptype->id == (int)$custMapTypeList[$i]
                    && $currentmaptype->gettileurl != "")
                    {                              
                        if ((int)$currentmaptype->layertype == 1)
                        {
                            if ((int)$currentmaptype->opacitymanage == 1)
                            {
                                $needOverlayControl = 1;
                                break;
                            }
                        }
                    }
                }
            }
			}
		}
    }
    
    if ($needOverlayControl == 0)
    {
    if (isset($mappaths) && !empty($mappaths)) 
    {
            foreach ($mappaths as $key => $currentpath) 
            {
                if ($currentpath->imgurl != ""
                    && $currentpath->imgbounds != "") 
                {
                    if ((int)$currentpath->imgopacitymanage == 1)
                    {
                        $needOverlayControl = 1;
                        break;
                    }
                }
            }
        }    
    }
    
}

if ($needOverlayControl == 0)
{
    if ((int)$map->nztopomaps == 11)
    {
        $needOverlayControl = 1;
    }
}
    
if ($placemarkSearch != 0
    || $groupSearch != 0
    || $managePanelFeature != 0)
{
    $wa->registerAndUseStyle('zhgooglemaps.jquery-ui', URI::root() .'components/com_zhgooglemap/assets/jquery-ui/1.13.2/jquery-ui.min.css');
    $wa->registerAndUseScript('zhgooglemaps.jquery-ui', URI::root() .'components/com_zhgooglemap/assets/jquery-ui/1.13.2/jquery-ui.min.js');
}


if (isset($map->usermarkers) 
  && ((int)$map->usermarkers == 1
      ||(int)$map->usermarkers == 2)) 
{
    $wa->registerAndUseStyle('zhgooglemaps.usermarkers', URI::root() .'components/com_zhgooglemap/assets/css/usermarkers.css');    
}


// Extra checking - begin

    $featurePathElevation = 0;
    $featurePathElevationKML = 0;
    // Do you need Elevation feature
    if (isset($mappaths) && !empty($mappaths)) 
    {
        foreach ($mappaths as $key => $currentpath) 
        {
            if (($currentpath->path != ""
             && (int)$currentpath->objecttype == 0
             && (int)$currentpath->elevation != 0))
            {
                $featurePathElevation = 1;
                break;
            }
        }
        foreach ($mappaths as $key => $currentpath) 
        {
            if (($currentpath->kmllayer != ""
             && (int)$currentpath->elevation != 0))
            {
                $featurePathElevationKML = 1;
                break;
            }
        }
    }

// Extra checking - begin



$fullWidth = 0;
$fullHeight = 0;

// Size Value 
$currentMapWidth ="do not change";
$currentMapHeight ="do not change";

// Map Type Value 
//   add parameter to redefine (passed from plugin)
if (isset($currentMapType) && $currentMapType != "")
{
    $currentMapTypeValue ="";
}
else
{
    $currentMapType ="do not change";
    $currentMapTypeValue ="";
}
        
if ($mapMapWidth != "")
{
    $currentMapWidth = $mapMapWidth;
}

if ($mapMapHeight != "")
{
    $currentMapHeight = $mapMapHeight;
}

if ($map->headerhtml != "")
{
        $divmapheader .= '<div id="GMapInfoHeader'.$mapDivSuffix.'" class="zhgm-map-header">'.$map->headerhtml;
        if (isset($map->headersep) && (int)$map->headersep == 1) 
        {
            $divmapheader .= '<hr id="mapHeaderLine" />';
        }
        $divmapheader .= '</div>';
}

if ($map->footerhtml != "")
{
       $divmapfooter .= '<div id="GMapInfoFooter'.$mapDivSuffix.'" class="zhgm-map-footer">';
        if (isset($map->footersep) && (int)$map->footersep == 1) 
        {
            $divmapfooter .= '<hr id="mapFooterLine" />';
        }
       $divmapfooter .= $map->footerhtml.'</div>';
}

if ($currentMapWidth == "do not change")
{
    $currentMapWidthValue = (int)$map->width;
}
else
{
    $currentMapWidthValue = (int)$currentMapWidth;
}

if ($currentMapHeight == "do not change")
{
    $currentMapHeightValue = (int)$map->height;
}
else
{
    $currentMapHeightValue = (int)$currentMapHeight;
}


if ((!isset($currentMapWidthValue)) || (isset($currentMapWidthValue) && (int)$currentMapWidthValue < 1)) 
{
    $fullWidth = 1;
}
if ((!isset($currentMapHeightValue)) || (isset($currentMapHeightValue) && (int)$currentMapHeightValue < 1)) 
{
    $fullHeight = 1;
}



if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{
    $wa->registerAndUseStyle('zhgooglemaps.markergroups', URI::root() .'components/com_zhgooglemap/assets/css/markergroups.css');
    
    
    switch ((int)$map->markergroupcss) 
    {
        
        case 0:
            $markergroupcssstyle = '-simple';
        break;
        case 1:
            $markergroupcssstyle = '-advanced';
        break;
        case 2:
            $markergroupcssstyle = '-external';
        break;
        default:
            $markergroupcssstyle = '-simple';
        break;
    }


           $divmarkergroup =  '<div id="GMapsMenu'.$markergroupcssstyle.'" class= "zhgm-mapsmenu'.$markergroupcssstyle.'" style="margin:0;padding:0;width=100%;">'."\n";
        if ($map->markergrouptitle != "")
        {
            $divmarkergroup .= '<div id="groupList"><h2 id="groupListHeadTitle" class="groupListHead">'.htmlspecialchars($map->markergrouptitle , ENT_QUOTES, 'UTF-8').'</h2></div>';
        }
        
        if ($map->markergroupdesc1 != "")
        {
            $divmarkergroup .= '<div id="groupListBodyTopContent" class="groupListBodyTop">'.$map->markergroupdesc1.'</div>';
        }

        if (isset($map->markergroupsep1) && (int)$map->markergroupsep1 == 1) 
        {
            $divmarkergroup .= '<hr id="groupListLineTop" />';
        }

        
        $divmarkergroup .= '<ul id="zhgm-menu'.$markergroupcssstyle.'" class="zhgm-markergroup-group-ul-menu'.$markergroupcssstyle.'">'."\n";

        /* 19.02.2013 
           for flexible support group management 
           and have ability to set off placemarks from group managenent 
           markergroups changed to mgrgrouplist
           */
        
        if (isset($mgrgrouplist) && !empty($mgrgrouplist)) 
        {

                if (isset($map->markergroupshowiconall) && ((int)$map->markergroupshowiconall!= 100))
                {
                        $imgimg1 = $imgpathUtils.'checkbox1.png';
                        $imgimg0 = $imgpathUtils.'checkbox0.png';

                        switch ((int)$map->markergroupshowiconall) 
                        {

                                case 0:
                                        $divmarkergroup .= '<li id="li-all" class="zhgm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-all" class="zhgm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhgm-markergroup-search'.$mapDivSuffix.'" class="zhgm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="GMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                case 1:
                                        $divmarkergroup .= '<li id="li-all" class="zhgm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-all" class="zhgm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-img-all" class="zhgm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg1.'" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'" /></div><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-img-all" class="zhgm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg0.'" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'" /></div><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";

                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhgm-markergroup-search'.$mapDivSuffix.'" class="zhgm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="GMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }

                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                case 2:
                                        $divmarkergroup .= '<li id="li-all" class="zhgm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-all" class="zhgm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-img-all" class="zhgm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg1.'" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'" /></div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-img-all" class="zhgm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg0.'" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'" /></div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhgm-markergroup-search'.$mapDivSuffix.'" class="zhgm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="GMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                default:
                                        $divmarkergroup .= '<li id="li-all" class="zhgm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-all" class="zhgm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-div-all" class="zhgm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhgm-markergroup-a-all" class="zhgm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhgm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhgm-markergroup-text-all" class="zhgm-markergroup-text-all'.$markergroupcssstyle.'">'.Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhgm-markergroup-search'.$mapDivSuffix.'" class="zhgm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="GMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                        }
                }
                else
                {
                    if ($groupSearch != 0)
                    {
                        $divmarkergroup .= '<li id="li-all" class="zhgm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                        $divmarkergroup .= '<div id="zhgm-markergroup-search'.$mapDivSuffix.'" class="zhgm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                        $divmarkergroup .= '<input id="GMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                        $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                        $divmarkergroup .='>';                                   
                        $divmarkergroup .= '</div>'."\n";
                        $divmarkergroup .= '</li>'."\n";                    
                    }                                                
                }


				if (isset($mgrgrouplist) && !empty($mgrgrouplist)) {
					foreach ($mgrgrouplist as $key => $currentmarkergroup) 
					{
							if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
							{
									$imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarkergroup->icontype).'.png';

									$markergroupname ='';
									$markergroupname = 'markergroup'. $currentmarkergroup->id;

									$markergroupname_article = 'markergroup'.$mapDivSuffix.'_'. $currentmarkergroup->id;

									if ((int)$currentmarkergroup->activeincluster == 1)
									{
											$markergroupactive = ' active';
									}
									else
									{
											$markergroupactive = '';
									}



									switch ((int)$map->markergroupshowicon) 
									{

											case 0:
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-text-'.$markergroupname.'" class="zhgm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
											break;
											case 1:
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-img-'.$markergroupname.'" class="zhgm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" /></div><div id="zhgm-markergroup-text-'.$markergroupname.'" class="zhgm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
											break;
											case 2:
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-img-'.$markergroupname.'" class="zhgm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" /></div></a></div></li>'."\n";
											break;
										
											case 10:
											case 20:
											case 30:                                            
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-text-'.$markergroupname.'" class="zhgm-markergroup-text'.$markergroupcssstyle.'"><span id="t-'.$markergroupname_article.'" class="zhgm-markergroup-link-title'.$markergroupcssstyle.$markergroupactive.'">'.htmlspecialchars(str_replace('\\', '/',$currentmarkergroup->title), ENT_QUOTES, 'UTF-8').'</span>'.'<span id="s-'.$markergroupname_article.'" class="zhgm-markergroup-link-span'.$markergroupcssstyle.$markergroupactive.'"></span>'.'</div></a></div></li>'."\n";
											break;
											case 11:
											case 21:
											case 31:                                            
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-img-'.$markergroupname.'" class="zhgm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" /></div><div id="zhgm-markergroup-text-'.$markergroupname.'" class="zhgm-markergroup-text'.$markergroupcssstyle.'"><span id="t-'.$markergroupname_article.'" class="zhgm-markergroup-link-title'.$markergroupcssstyle.$markergroupactive.'">'.htmlspecialchars(str_replace('\\', '/',$currentmarkergroup->title), ENT_QUOTES, 'UTF-8').'</span>'.'<span id="s-'.$markergroupname_article.'" class="zhgm-markergroup-link-span'.$markergroupcssstyle.$markergroupactive.'"></span>'.'</div></a></div></li>'."\n";
											break;
											case 12:
											case 22:
											case 32:                                            
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-img-'.$markergroupname.'" class="zhgm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" />'.'<span id="s-'.$markergroupname_article.'" class="zhgm-markergroup-link-span'.$markergroupcssstyle.$markergroupactive.'"></span>'.'</div></a></div></li>'."\n";
											break;
																					
											default:
													$divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhgm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhgm-markergroup-a-'.$markergroupname.'" class="zhgm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhgm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhgm-markergroup-text-'.$markergroupname.'" class="zhgm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
											break;
									}


							}
					}
				}
		}


        $divmarkergroup .= '</ul>'."\n";

        if (isset($map->markergroupsep2) && (int)$map->markergroupsep2 == 1) 
        {
            $divmarkergroup .= '<hr id="groupListLineBottom" />';
        }
        
        if ($map->markergroupdesc2 != "")
        {
            $divmarkergroup .= '<div id="groupListBodyBottomContent" class="groupListBodyBottom">'.$map->markergroupdesc2.'</div>';
        }
        
        $divmarkergroup .= '</div>'."\n";

}




$zhgmObjectManager = 0;
$ajaxLoadContent = 0;
$ajaxLoadScripts = 0;

$ajaxLoadObjects = (int)$map->useajaxobject;

$ajaxLoadObjectType = (int)$map->ajaxgetplacemark;

$featureSpider = (int)$map->markerspinner;

if (
 (isset($map->useajax) && ((int)$map->useajax !=0))
 || 
 (isset($map->placemark_rating) && ((int)$map->placemark_rating != 0))
)
{
    $ajaxLoadContent = 1;
}

if ($ajaxLoadObjects != 0)
{
    $ajaxLoadScripts = 1;
}

if (  ($ajaxLoadObjects != 0)
   || ($ajaxLoadContent != 0)
   || ($featureSpider != 0)
   || ($placemarkSearch != 0)
   || ($groupSearch != 0)
   || ($needOverlayControl != 0)
   || ($managePanelFeature != 0)
   || ($layersButtons != 0)
   || (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
   || (isset($map->markermanager) && (int)$map->markermanager == 1)
   || (isset($map->markercluster) && (int)$map->markercluster == 1)
   || (isset($map->mapbounds) && $map->mapbounds != "")
   || (((isset($map->elevation) && (int)$map->elevation == 1))
   || $featurePathElevation == 1 || $featurePathElevationKML == 1)
   || (isset($map->hovermarker) && ((int)$map->hovermarker !=0))
   )
{
    $zhgmObjectManager = 1;
}





if (($ajaxLoadScripts != 0)
    ||  
   (isset($map->hovermarker) && ((int)$map->hovermarker == 2)))
{

    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
    {
            $this->infobubble = 1;
    }
    else
    {
            $infobubble = 1;
    }
}
else
{
    if (isset($markers) && !empty($markers)) 
    {
            foreach ($markers as $key => $currentmarker) 
            {
                    if ((int)$currentmarker->actionbyclick == 4)
                    {

                            if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                            {
                                    $this->infobubble = 1;
                            }
                            else
                            {
                                    $infobubble = 1;
                            }
                            break; 
                    }
            }
    }
}


if ($ajaxLoadScripts != 0) 
{
        if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
        {
                $this->featureMarkerWithLabel = 1;
        }
        else
        {
                $featureMarkerWithLabel = 1;
        }
}
else
{
    if (isset($markers) && !empty($markers)) 
    {
        foreach ($markers as $key => $currentmarker) 
        {
            if ((int)$currentmarker->baloon == 21
             || (int)$currentmarker->baloon == 22
             || (int)$currentmarker->baloon == 23
             )
            {
                if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                {
                    $this->featureMarkerWithLabel = 1;
                }
                else
                {
                    $featureMarkerWithLabel = 1;
                }

                break; 
            }
        }
    }
}

$wa->registerAndUseScript('zhgooglemaps.common', $current_custom_js_path.'common-min.js');
if ($zhgmObjectManager != 0)
{
        if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
        {
            $this->use_object_manager = 1;
        }
        else
        {
            $use_object_manager = 1;
        }
}


$divmap = "";


if ((int)$map->routedriving == 2
   || ((int)$map->routewalking == 0 
    && (int)$map->routetransit == 0
    && (int)$map->routebicycling == 0))
{
    $routeSelectedDriving = ' selected="selected"';
    $routeSelectedWalking = '';
    $routeSelectedTransit = '';
    $routeSelectedBicycling = '';
}
else
{
    if ((int)$map->routedriving == 2)
    {
        $routeSelectedDriving = ' selected="selected"';
        $routeSelectedWalking = '';
        $routeSelectedTransit = '';
        $routeSelectedBicycling = '';
    }
    else if ((int)$map->routewalking == 2)
    {
        $routeSelectedDriving = '';
        $routeSelectedWalking = ' selected="selected"';
        $routeSelectedTransit = '';
        $routeSelectedBicycling = '';
    }
    else if ((int)$map->routetransit == 2)
    {
        $routeSelectedDriving = '';
        $routeSelectedWalking = '';
        $routeSelectedTransit = ' selected="selected"';
        $routeSelectedBicycling = '';
    }
    else if ((int)$map->routebicycling == 2)
    {
        $routeSelectedDriving = '';
        $routeSelectedWalking = '';
        $routeSelectedTransit = '';
        $routeSelectedBicycling = ' selected="selected"';
    }
    else
    {
        if ((int)$map->routedriving != 0)
        {
            $routeSelectedDriving = ' selected="selected"';
            $routeSelectedWalking = '';
            $routeSelectedTransit = '';
            $routeSelectedBicycling = '';
        }
        else if ((int)$map->routewalking != 0)
        {
            $routeSelectedDriving = '';
            $routeSelectedWalking = ' selected="selected"';
            $routeSelectedTransit = '';
            $routeSelectedBicycling = '';
        }
        else if ((int)$map->routetransit != 0)
        {
            $routeSelectedDriving = '';
            $routeSelectedWalking = '';
            $routeSelectedTransit = ' selected="selected"';
            $routeSelectedBicycling = '';
        }
        else if ((int)$map->routebicycling != 0)
        {
            $routeSelectedDriving = '';
            $routeSelectedWalking = '';
            $routeSelectedTransit = '';
            $routeSelectedBicycling = ' selected="selected"';
        }
        else
        {
            $routeSelectedDriving = '';
            $routeSelectedWalking = '';
            $routeSelectedTransit = '';
            $routeSelectedBicycling = '';
        }
    }
}

if (isset($map->placesenable) && (int)$map->placesenable == 1) 
{
    // Add autocomplete field only if find control is off
    if ((isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1)
     && (isset($map->findcontrol) && (int)$map->findcontrol == 0)
     )
    {
        $divmap .='<div id="placesAutocomplete'.$mapDivSuffix.'" class="zhgm-autocomplete-main" >';
        if (isset($map->placesdirection) 
          && (int)$map->placesdirection == 1) 
        {
                        $divmap .='<div id="GMapFindAddress'.$mapDivSuffix.'" class="zhgm-find-address-main">';
                        $divmap .='<div id="GMapFindTarget'.$mapDivSuffix.'" class="zhgm-find-target">'."\n";
                        $divmap .='<span id="GMapFindTargetIcon'.$mapDivSuffix.'"></span>'."\n";
                        $divmap .='<span id="GMapFindTargetText'.$mapDivSuffix.'"></span>'."\n";
                        $divmap .='</div>'."\n";
                        $divmap .='<div id="GMapFindPanel'.$mapDivSuffix.'" class="zhgm-find-panel">'."\n";
                        $divmap .='<span id="GMapFindPanelIcon'.$mapDivSuffix.'"></span>'."\n";     
                        
            $divmap .= '<select id="searchTravelMode'.$mapDivSuffix.'" class="zhgm-autocomplete-search-mode" >' ."\n";

            if ((int)$map->routedriving != 0
               || ((int)$map->routewalking == 0 
                && (int)$map->routetransit == 0
                && (int)$map->routebicycling == 0))
            {
                $divmap .= '<option value="google.maps.TravelMode.DRIVING"'.$routeSelectedDriving.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_DRIVING').'</option>' ."\n";
            }
            if ((int)$map->routewalking != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.WALKING"'.$routeSelectedWalking.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_WALKING').'</option>' ."\n";
            }
            if ((int)$map->routebicycling != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.BICYCLING"'.$routeSelectedBicycling.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_BICYCLING').'</option>' ."\n";
            }
            if ((int)$map->routetransit != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.TRANSIT"'.$routeSelectedTransit.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_TRANSIT').'</option>' ."\n";
            }
            $divmap .= '</select>' ."\n";
        }
        $divmap .= '<input id="searchTextField'.$mapDivSuffix.'" type="text" class="zhgm-autocomplete-search-field" '; 
        if (isset($map->placesacwidth) && (int)$map->placesacwidth != 0)
        {
            $divmap .= ' size="'.(int)$map->placesacwidth.'"';
        }
        $divmap .=' />';
                
                if (isset($map->placesdirection) 
          && (int)$map->placesdirection == 1) 
        {       
                    $divmap .='</div>'."\n";
                    $divmap .='</div>'."\n"; 
                }
                
        $divmap .='</div>'."\n";
    }
}


$doShowDivGeo = 0;
if (isset($map->geolocationcontrol) && (int)$map->geolocationcontrol == 1) 
{
    $doShowDivGeo = 1;
    $divmap .='<div id="geoLocation'.$mapDivSuffix.'" style="display: none;" class="zhgm-geolocation-main" >';
    $divmap .= '  <button id="geoLocationButton'.$mapDivSuffix.'" class="zhgm-geolocation-button" >';

    switch ((int)$map->geolocationbutton) 
    {
        
        case 1:
            $divmap .= '<img src="'.$imgpathUtils.'geolocation.png" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATIONBUTTON').'" style="vertical-align: middle" />';
        break;
        case 2:
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATIONBUTTON');
        break;
        case 3:
            $divmap .= '<img src="'.$imgpathUtils.'geolocation.png" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATIONBUTTON').'" style="vertical-align: middle" />';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATIONBUTTON');
        break;
        default:
            $divmap .= '<img src="'.$imgpathUtils.'geolocation.png" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATIONBUTTON').'" style="vertical-align: middle" />';
        break;
    }
    
    $divmap .= '</button>';
    $divmap .='</div>'."\n";
}


$divmapbefore = "";
$divmapafter = "";

if ((isset($map->placesenable) && (int)$map->placesenable == 1)
 && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1)
 && (isset($map->findcontrol) && (int)$map->findcontrol == 0)
 )
{
    if (isset($map->placesdirection) && (int)$map->placesdirection == 1)
    {
                $service_DoDirection = 1;
    }
    else
    {
        $service_DoDirection = 0;
    }
}
else if (isset($map->findcontrol) && (int)$map->findcontrol == 1)
{
    if (isset($map->findroute) && (int)$map->findroute != 0)
    {
        $service_DoDirection = 1;
    }
    else
    {

        $service_DoDirection = 0;

    }
}
else
{
    $service_DoDirection = 0;
}
$doShowDivFind = 0;
if (isset($map->findcontrol) && (int)$map->findcontrol == 1) 
{
    if (isset($map->findpos) && (int)$map->findpos == 101)
    {
        $doShowDivFind = 0;
        $divmapbefore .='<div id="GMapFindAddress'.$mapDivSuffix.'" class="zhgm-find-address-main">';
        $divmapbefore .='<div id="GMapFindTarget'.$mapDivSuffix.'" class="zhgm-find-target">'."\n";
        $divmapbefore .='<span id="GMapFindTargetIcon'.$mapDivSuffix.'"></span>'."\n";
        $divmapbefore .='<span id="GMapFindTargetText'.$mapDivSuffix.'"></span>'."\n";
        $divmapbefore .='</div>'."\n";
        $divmapbefore .='<div id="GMapFindPanel'.$mapDivSuffix.'" class="zhgm-find-panel">'."\n";
        $divmapbefore .='<span id="GMapFindPanelIcon'.$mapDivSuffix.'"></span>'."\n";
        if (((int)$map->findroute != 0) || ((int)$map->placesdirection == 1)) 
        {
            $divmapbefore .= '<select id="findAddressTravelMode'.$mapDivSuffix.'" class="zhgm-find-mode" >' ."\n";
            if ((int)$map->routedriving != 0
               || ((int)$map->routewalking == 0 
                && (int)$map->routetransit == 0
                && (int)$map->routebicycling == 0))
            {
                $divmapbefore .= '<option value="google.maps.TravelMode.DRIVING"'.$routeSelectedDriving.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_DRIVING').'</option>' ."\n";
            }
            if ((int)$map->routewalking != 0)
            {
                $divmapbefore .= '<option value="google.maps.TravelMode.WALKING"'.$routeSelectedWalking.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_WALKING').'</option>' ."\n";
            }
            if ((int)$map->routebicycling != 0)
            {
                $divmapbefore .= '<option value="google.maps.TravelMode.BICYCLING"'.$routeSelectedBicycling.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_BICYCLING').'</option>' ."\n";
            }
            if ((int)$map->routetransit != 0)
            {
                $divmapbefore .= '<option value="google.maps.TravelMode.TRANSIT"'.$routeSelectedTransit.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_TRANSIT').'</option>' ."\n";
            }
            $divmapbefore .= '</select>' ."\n";
        }
        $divmapbefore .= '  <input id="findAddressField'.$mapDivSuffix.'" type="text" class="zhgm-find-field" ';
        if (isset($map->findwidth) && (int)$map->findwidth != 0)
        {
            $divmapbefore .= ' size="'.(int)$map->findwidth.'"';
        }
        $divmapbefore .=' />';
        if (isset($map->findroute) && (int)$map->findroute == 0) 
        {
            $divmapbefore .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapbefore .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapbefore .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 1) 
        {
            $divmapbefore .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapbefore .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmapbefore .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 2) 
        {
            $divmapbefore .= '  <button id="findAddressButtonFind'.$mapDivSuffix.'" class="zhgm-find-find-button" >';
            $divmapbefore .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapbefore .='</button>';
            $divmapbefore .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapbefore .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmapbefore .='</button>';
        }
        else
        {
            $divmapbefore .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapbefore .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapbefore .='</button>';
        }
        $divmapbefore .='</div>'."\n";
        $divmapbefore .='</div>'."\n";
    }
    else if (isset($map->findpos) && (int)$map->findpos == 102)
    {
        $doShowDivFind = 0;
        $divmapafter .='<div id="GMapFindAddress'.$mapDivSuffix.'" class="zhgm-find-address-main">';
        $divmapafter .='<div id="GMapFindTarget'.$mapDivSuffix.'" class="zhgm-find-target">'."\n";
        $divmapafter .='<span id="GMapFindTargetIcon'.$mapDivSuffix.'"></span>'."\n";
        $divmapafter .='<span id="GMapFindTargetText'.$mapDivSuffix.'"></span>'."\n";
        $divmapafter .='</div>'."\n";
        $divmapafter .='<div id="GMapFindPanel'.$mapDivSuffix.'" class="zhgm-find-panel">'."\n";
        $divmapafter .='<span id="GMapFindPanelIcon'.$mapDivSuffix.'"></span>'."\n";
        if (isset($map->findroute) && (int)$map->findroute != 0) 
        {
            $divmapafter .= '<select id="findAddressTravelMode'.$mapDivSuffix.'" class="zhgm-find-mode" >' ."\n";
            if ((int)$map->routedriving != 0
               || ((int)$map->routewalking == 0 
                && (int)$map->routetransit == 0 
                && (int)$map->routebicycling == 0))
            {
                $divmapafter .= '<option value="google.maps.TravelMode.DRIVING"'.$routeSelectedDriving.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_DRIVING').'</option>' ."\n";
            }
            if ((int)$map->routewalking != 0)
            {
                $divmapafter .= '<option value="google.maps.TravelMode.WALKING"'.$routeSelectedWalking.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_WALKING').'</option>' ."\n";
            }
            if ((int)$map->routebicycling != 0)
            {
                $divmapafter .= '<option value="google.maps.TravelMode.BICYCLING"'.$routeSelectedBicycling.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_BICYCLING').'</option>' ."\n";
            }
            if ((int)$map->routetransit != 0)
            {
                $divmapafter .= '<option value="google.maps.TravelMode.TRANSIT"'.$routeSelectedTransit.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_TRANSIT').'</option>' ."\n";
            }
            $divmapafter .= '</select>' ."\n";
        }
        $divmapafter .= '  <input id="findAddressField'.$mapDivSuffix.'" type="text" class="zhgm-find-field" ';
        if (isset($map->findwidth) && (int)$map->findwidth != 0)
        {
            $divmapafter .= ' size="'.(int)$map->findwidth.'"';
        }
        $divmapafter .=' />';
        if (isset($map->findroute) && (int)$map->findroute == 0) 
        {
            $divmapafter .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapafter .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapafter .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 1) 
        {
            $divmapafter .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapafter .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmapafter .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 2) 
        {
            $divmapafter .= '  <button id="findAddressButtonFind'.$mapDivSuffix.'" class="zhgm-find-find-button" >';
            $divmapafter .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapafter .='</button>';
            $divmapafter .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapafter .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmapafter .='</button>';
        }
        else
        {
            $divmapafter .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmapafter .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmapafter .='</button>';
        }
        $divmapafter .='</div>'."\n";
        $divmapafter .='</div>'."\n";
    }
    else
    {
        $doShowDivFind = 1;
        $divmap .='<div id="GMapFindAddress'.$mapDivSuffix.'" class="zhgm-find-address-main" style="display: none;">' ."\n";
        $divmap .='<div id="GMapFindTarget'.$mapDivSuffix.'" class="zhgm-find-target">'."\n";
        $divmap .='<span id="GMapFindTargetIcon'.$mapDivSuffix.'"></span>'."\n";
        $divmap .='<span id="GMapFindTargetText'.$mapDivSuffix.'"></span>'."\n";
        $divmap .='</div>'."\n";
        $divmap .='<div id="GMapFindPanel'.$mapDivSuffix.'" class="zhgm-find-panel">'."\n";
        $divmap .='<span id="GMapFindPanelIcon'.$mapDivSuffix.'"></span>'."\n";
        if (isset($map->findroute) && (int)$map->findroute != 0) 
        {
            $divmap .= '<select id="findAddressTravelMode'.$mapDivSuffix.'" class="zhgm-find-mode" >' ."\n";
            if ((int)$map->routedriving != 0
               || ((int)$map->routewalking == 0 
                && (int)$map->routetransit == 0 
                && (int)$map->routebicycling == 0))
            {
                $divmap .= '<option value="google.maps.TravelMode.DRIVING"'.$routeSelectedDriving.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_DRIVING').'</option>' ."\n";
            }
            if ((int)$map->routewalking != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.WALKING"'.$routeSelectedWalking.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_WALKING').'</option>' ."\n";
            }
            if ((int)$map->routebicycling != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.BICYCLING"'.$routeSelectedBicycling.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_BICYCLING').'</option>' ."\n";
            }
            if ((int)$map->routetransit != 0)
            {
                $divmap .= '<option value="google.maps.TravelMode.TRANSIT"'.$routeSelectedTransit.'>'.Text::_('COM_ZHGOOGLEMAP_ROUTER_TRAVELMODE_TRANSIT').'</option>' ."\n";
            }
            $divmap .= '</select>' ."\n";
        }
        $divmap .= '  <input id="findAddressField'.$mapDivSuffix.'" type="text" class="zhgm-find-field" ';
        if (isset($map->findwidth) && (int)$map->findwidth != 0)
        {
            $divmap .= ' size="'.(int)$map->findwidth.'"';
        }
        $divmap .=' />' ."\n";
        if (isset($map->findroute) && (int)$map->findroute == 0) 
        {
            $divmap .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmap .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 1) 
        {
            $divmap .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmap .='</button>';
        }
        else if (isset($map->findroute) && (int)$map->findroute == 2) 
        {
            $divmap .= '  <button id="findAddressButtonFind'.$mapDivSuffix.'" class="zhgm-find-find-button" >';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmap .='</button>';
            $divmap .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_ROUTE');
            $divmap .='</button>';
        }
        else
        {
            $divmap .= '  <button id="findAddressButton'.$mapDivSuffix.'" class="zhgm-find-address-button" >';
            $divmap .= Text::_('COM_ZHGOOGLEMAP_MAP_DOFINDBUTTON');
            $divmap .='</button>';
        }
        $divmap .='</div>'."\n";
        $divmap .='</div>'."\n";
    }
}

    $divwrapmapstyle = '';
    $divtabcolmapstyle = '';
    
    if ($fullWidth == 1)
    {
        $divwrapmapstyle .= 'width:100%;';
    }
    if ($fullHeight == 1)
    {
        $divwrapmapstyle .= 'height:100%;';
        $divtabcolmapstyle .= 'height:100%;';
    }
    if ($divwrapmapstyle != "")
    {
        $divwrapmapstyle = 'style="'.$divwrapmapstyle.'"';
    }
    if ($divtabcolmapstyle != "")
    {
        $divtabcolmapstyle = 'style="'.$divtabcolmapstyle.'"';
    }

// adding markerlist (div)
$markerlistcssstyle = '';
if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{


    $wa->registerAndUseStyle('zhgooglemaps.markerlists', URI::root() .'components/com_zhgooglemap/assets/css/markerlists.css');
    
    
    switch ((int)$map->markerlist) 
    {
        
        case 0:
            $markerlistcssstyle = 'markerList-simple';
        break;
        case 1:
            $markerlistcssstyle = 'markerList-advanced';
        break;
        case 2:
            $markerlistcssstyle = 'markerList-external';
        break;
        default:
            $markerlistcssstyle = 'markerList-simple';
        break;
    }


    $markerlistAddStyle ='';
    
    if ($map->markerlistbgcolor != "")
    {
        $markerlistAddStyle .= ' background: '.$map->markerlistbgcolor.';';
    }
    
    if ((int)$map->markerlistwidth == 0)
    {
        if ((int)$map->markerlistpos == 113
          ||(int)$map->markerlistpos == 114
          ||(int)$map->markerlistpos == 120
          ||(int)$map->markerlistpos == 121)
        {
            $divMarkerlistWidth = '100%';
        }
        else
        {
            $divMarkerlistWidth = '200px';
        }
    }
    else
    {
        $divMarkerlistWidth = $map->markerlistwidth;
        $divMarkerlistWidth = $divMarkerlistWidth. 'px';
    }


    if ((int)$map->markerlistpos == 111
      ||(int)$map->markerlistpos == 112)
    {
        if ($fullHeight == 1)
        {
            $divMarkerlistHeight = '100%';
        }
        else
        {
            $divMarkerlistHeight = $currentMapHeightValue;
            $divMarkerlistHeight = $divMarkerlistHeight. 'px';
        }
    }
    else
    {
        if ((int)$map->markerlistheight == 0)
        {
            $divMarkerlistHeight = 200;
        }
        else
        {
            $divMarkerlistHeight = $map->markerlistheight;
        }
        $divMarkerlistHeight = $divMarkerlistHeight. 'px';
    }        

    
    if ((int)$map->markerlistcontent < 100) 
    {
        $markerlisttag = '<div id="GMapsMarkerListMain" '.$mapDivSuffix.' class="zhgm-listmain-ul-'.$markerlistcssstyle.'">';
        $markerlisttag .= '<ul id="GMapsMarkerUL'.$mapDivSuffix.'" class="zhgm-ul-'.$markerlistcssstyle.'"></ul>';
        $markerlisttag .= '</div>';
    }
    else 
    {
        $markerlisttag = '<div id="GMapsMarkerListMain" '.$mapDivSuffix.' class="zhgm-listmain-table-'.$markerlistcssstyle.'">';
        $markerlisttag .=  '<table id="GMapsMarkerTABLE'.$mapDivSuffix.'" class="zhgm-ul-table-'.$markerlistcssstyle.'" ';
        if (((int)$map->markerlistpos == 113) 
        || ((int)$map->markerlistpos == 114) 
        || ((int)$map->markerlistpos == 120) 
        || ((int)$map->markerlistpos == 121))
        {
            if ($fullWidth == 1) 
            {
                $markerlisttag .= 'style="width:100%;" ';
            }
        }
        $markerlisttag .= '>';
        $markerlisttag .= '<tbody id="GMapsMarkerTABLEBODY'.$mapDivSuffix.'" class="zhgm-ul-tablebody-'.$markerlistcssstyle.'">';
        $markerlisttag .= '</tbody>';
        $markerlisttag .= '</table>';
        $markerlisttag .= '</div>';
    }

    if ($placemarkSearch != 0)
    {
        if ((int)$map->markerlistpos == 120)
        {
            $markerlistsearch = '<div id="GMapsMarkerListSearch" '.$mapDivSuffix.' class="zhgm-search-panel-'.$markerlistcssstyle.'"';
        }
        else
        {
            $markerlistsearch = '<div id="GMapsMarkerListSearch" '.$mapDivSuffix.' class="zhgm-search-'.$markerlistcssstyle.'"';
        }

        $markerlistsearch .='>';
        $markerlistsearch .= '<input id="GMapsMarkerListSearchAutocomplete'.$mapDivSuffix.'"';
        $markerlistsearch .= ' placeholder="'.$fv_override_placemark_title.'"';
        $markerlistsearch .='>';
        $markerlistsearch .= '</div>';
    }
    else
    {
        $markerlistsearch = "";
    }
    
    // Add Placemark Search 
    $markerlisttag = $markerlistsearch . $markerlisttag;

    if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
    {
        $markerlistPanel = '';
    }    
    
    switch ((int)$map->markerlistpos) 
    {
        case 0:
            // None
        break;
        case 1:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 2:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 3:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 4:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 5:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 6:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 7:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 8:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 9:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 10:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 11:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 12:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 111:
            if ($fullWidth == 1) 
            {
                            if ($fullHeight == 1) 
                            {
                                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" style="width:100%; height:100%;" >';
                            }
                            else
                            {
                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" style="width:100%;" >';
                            }
            }
            else
            {
                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" >';
            }
            $divmap .= '<tbody>';
                        if ($fullHeight == 1) 
                        {
                            $divmap .= '<tr style="height:100%;">';
                        }
                        else
                        {
                            $divmap .= '<tr>';
                        }
            
            $divmap .= '<td style="width:'.$divMarkerlistWidth.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' float: left; padding: 0; margin: 0 10px 0 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
            $divmap .= '</td>';
            $divmap .= '<td>';
        break;
        case 112:
            if ($fullWidth == 1) 
            {
                            if ($fullHeight == 1) 
                            {
                                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" style="width:100%; height:100%;" >';
                            }
                            else
                            {
                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" style="width:100%;" >';
                            }
            }
            else
            {
                $divmap .= '<table id="GMMapTable'.$mapDivSuffix.'" class="zhgm-table-'.$markerlistcssstyle.'" >';
            }
            $divmap .= '<tbody>';
                        if ($fullHeight == 1) 
                        {
                            $divmap .= '<tr style="height:100%;">';
                        }
                        else
                        {
                            $divmap .= '<tr>';
                        }
            $divmap .= '<td>';
        break;
        case 113:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'" >';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
        break;
        case 114:
            $divmap .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-'.$markerlistcssstyle.'" >';
        break;
        case 120:
            // no height
            // new classes
            $markerlistPanel .= '<div id="GMMapWrapper" '.$divwrapmapstyle.' class="zhgm-wrap-panel-'.$markerlistcssstyle.'">';
            $markerlistPanel .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-panel-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0px; width: 100%;">'.$markerlisttag.'</div>';
            $markerlistPanel .='</div>';
        break;
        case 121:
        break;
        default:
        break;
    }

    
}



// SIZE - begin
$mainMapDivContentSize = '';
$mainPanelWrapDivContentSize = '';
$mainStreetViewDivContentSize = '';
$managePanelContentHeight = '';

if ($fullWidth == 1)
{
    if ($fullHeight == 1) 
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:70%;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:30%;';
                                break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:70%;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:30%;';
                                break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:100%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                    $mainPanelWrapDivContentSize = 'width:100%;height:100%;';
                    $managePanelContentHeight = '100%';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                }
            }

    }
    else
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:'.((int)($currentMapHeightValue / 2)).'px;';                                
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:'.((int)($currentMapHeightValue / 2)).'px;';
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                    $managePanelContentHeight = $currentMapHeightValue.'px';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                }
            }

    }        
}
else
{
    if ($fullHeight == 1) 
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:70%;';    
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:30%;';
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:70%;';    
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:30%;';            
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:100%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                                }                              
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:100%;';
                    $managePanelContentHeight = '100%';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';    
                }                    

            }

    }
    else
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.((int)($currentMapHeightValue / 2)).'px;';                                
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.((int)($currentMapHeightValue / 2)).'px;';
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                    $managePanelContentHeight = $currentMapHeightValue.'px';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                }
            }


    }        
}     
// SIZE - end


$mapDivCSSStyle = 'margin:0;padding:0;';
$mapDivCSSStyle0 = $mapDivCSSStyle;

$mapDivCSSClassName = ' class="zhgm-map-default"';
$mapSVDivCSSClassName = ' class="zhgm-map-streetview-default"';
$mapPANWDivCSSClassName = ' class="zhgm-map-mainpanel-wrap-default"';
$mapPANDivCSSClassName = ' class="zhgm-map-mainpanel-default"';

if (isset($map->cssclassname) && ($map->cssclassname != ""))
{
    $mapDivCSSClassName = ' class="'.$map->cssclassname . $cssClassSuffix . '"';
    $mapSVDivCSSClassName = ' class="'.$map->cssclassname.'-streetview'. $cssClassSuffix . '"';
}
else
{
    if (isset($cssClassSuffix) && ($cssClassSuffix != ""))
    {
        $mapDivCSSClassName = ' class="'. $cssClassSuffix . '"';
        $mapSVDivCSSClassName = ' class="'.'-streetview'. $cssClassSuffix . '"';
    }
}

$managePanelContent = '';

if ($managePanelFeature == 1)
{
    if (isset($map->panelwidth) && (int)$map->panelwidth != 0)
    {
        $managePanelContentWidth = (int)$map->panelwidth;
    }
    else
    {
        $managePanelContentWidth = '300';
    }
    


    //$managePanelContent = '<p>Hello world</p>';
    
    $managePanelContent .= '<div id="GMapsPanel'.$mapDivSuffix.'" style="overflow:auto; height:'.$managePanelContentHeight.';">';
    $managePanelContent .= '  <ul>';
    if ($managePanelInfowin == 1)    
    {
        $managePanelContent .= '    <li><a href="#GMapsPanel'.$mapDivSuffix.'tabs-1">'.$fv_override_panel_detail_title.'</a></li>';
    }
    

    if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
    {
        $managePanelContent .= '    <li><a href="#GMapsPanel'.$mapDivSuffix.'tabs-2">'.$fv_override_panel_placemarklist_title.'</a></li>';
    }

    if (1==2) 
    {
        $managePanelContent .= '    <li><a href="#GMapsPanel'.$mapDivSuffix.'tabs-3">'.$fv_override_panel_route_title.'</a></li>';
    }

    if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120) 
    {
        $managePanelContent .= '    <li><a href="#GMapsPanel'.$mapDivSuffix.'tabs-5">'.$fv_override_panel_group_title.'</a></li>';
    }
    
    $managePanelContent .= '  </ul>';
    if ($managePanelInfowin == 1)    
    {
        $managePanelContent .= '  <div id="GMapsPanel'.$mapDivSuffix.'tabs-1">';
        $managePanelContent .= '  </div>';
    }

    if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
    {
        $managePanelContent .= '  <div id="GMapsPanel'.$mapDivSuffix.'tabs-2">';
        $managePanelContent .= $markerlistPanel;
        $managePanelContent .= '  </div>';
    }

    if (1==2) 
    {
        $routePanel ='';
        $managePanelContent .= '  <div id="GMapsPanel'.$mapDivSuffix.'tabs-3">';
        $managePanelContent .= $routePanel;
        $managePanelContent .= '  </div>';
    }    
    
    if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120) 
    {
        $managePanelContent .= '  <div id="GMapsPanel'.$mapDivSuffix.'tabs-5">';
        $managePanelContent .= $divmarkergroup;
        $managePanelContent .= '  </div>';
    }
    
    $managePanelContent .= '</div>';

}
else
{
    $managePanelContentWidth = 0;
}

if ($managePanelFeature == 1)
{
    $managePanelWrapBegin = '<div id="GMapsMainPanelWrap'.$mapDivSuffix.'" '.$mapPANWDivCSSClassName.' style="'.$mapDivCSSStyle;
        $managePanelWrapBegin .= $mainPanelWrapDivContentSize;
        $managePanelWrapBegin .= '">';
    $managePanelDiv = '';
    $managePanelWrapEnd = '</div>';
    $mapDivCSSStyle .= 'display:inline-block;';
}
else
{
    $managePanelWrapBegin = '';
    $managePanelDiv = '';
    $managePanelWrapEnd = '';
}

if ($fullWidth == 1) 
{
    if ($fullHeight == 1) 
    {
        if (isset($map->streetview))
        {
            switch ((int)$map->streetview) 
            {
                case 2:
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
                case 3:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
                break;
                default:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
            }
        }
        else
        {
            $divmap .= $managePanelWrapBegin;
            $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
            $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                    $managePanelFeature,
                                    $managePanelContentHeight,
                                    $managePanelContentWidth.'px',
                                    $mapDivSuffix,
                                    $mapPANDivCSSClassName,
                                    $managePanelContent);
                                    
            $divmap .= $managePanelDiv;
            $divmap .= $managePanelWrapEnd;
        }
        
    }
    else
    {
        if (isset($map->streetview))
        {
            switch ((int)$map->streetview) 
            {
                case 2:
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
                case 3:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
                break;
                default:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
            }
        }
        else
        {
            $divmap .= $managePanelWrapBegin;
            $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
                    
            $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                    $managePanelFeature,
                                    $managePanelContentHeight,
                                    $managePanelContentWidth.'px',
                                    $mapDivSuffix,
                                    $mapPANDivCSSClassName,
                                    $managePanelContent);
                                    
            $divmap .= $managePanelDiv;
            $divmap .= $managePanelWrapEnd;
        }

    }        
}
else
{
    if ($fullHeight == 1) 
    {
        if (isset($map->streetview))
        {
            switch ((int)$map->streetview) 
            {
                case 2:
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';            
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';    
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;                    
                break;
                case 3:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';            
                break;
                default:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
            }
        }
        else
        {
            $divmap .= $managePanelWrapBegin;
            $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
            
            $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                    $managePanelFeature,
                                    $managePanelContentHeight,
                                    $managePanelContentWidth.'px',
                                    $mapDivSuffix,
                                    $mapPANDivCSSClassName,
                                    $managePanelContent);
                                            
            $divmap .= $managePanelDiv;
            $divmap .= $managePanelWrapEnd;
        }

    }
    else
    {
        if (isset($map->streetview))
        {
            switch ((int)$map->streetview) 
            {
                case 2:
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';            
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
                case 3:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                    $divmap .= '<div id="GMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';            
                break;
                default:
                    $divmap .= $managePanelWrapBegin;
                    $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
                    $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                            $managePanelFeature,
                                            $managePanelContentHeight,
                                            $managePanelContentWidth.'px',
                                            $mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
                                            
                    $divmap .= $managePanelDiv;
                    $divmap .= $managePanelWrapEnd;
                break;
            }
        }
        else
        {
            $divmap .= $managePanelWrapBegin;
            $divmap .= '<div id="GMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';            
                    
            $managePanelDiv = MapDivsHelper::get_MapPanelDIV(
                                    $managePanelFeature,
                                    $managePanelContentHeight,
                                    $managePanelContentWidth.'px',
                                    $mapDivSuffix,
                                    $mapPANDivCSSClassName,
                                    $managePanelContent);
                                            
            $divmap .= $managePanelDiv;
            $divmap .= $managePanelWrapEnd;
        }

    }        
}

// adding markerlist (close div)
if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{

    switch ((int)$map->markerlistpos) 
    {
        case 0:
            // None
        break;
        case 1:
            $divmap .='</div>';
        break;
        case 2:
            $divmap .='</div>';
        break;
        case 3:
            $divmap .='</div>';
        break;
        case 4:
            $divmap .='</div>';
        break;
        case 5:
            $divmap .='</div>';
        break;
        case 6:
            $divmap .='</div>';
        break;
        case 7:
            $divmap .='</div>';
        break;
        case 8:
            $divmap .='</div>';
        break;
        case 9:
            $divmap .='</div>';
        break;
        case 10:
            $divmap .='</div>';
        break;
        case 11:
            $divmap .='</div>';
        break;
        case 12:
            $divmap .='</div>';
        break;
        case 111:
            $divmap .= '</td>';
            $divmap .= '</tr>';
            $divmap .= '</tbody>';
            $divmap .='</table>';
        break;
        case 112:
            $divmap .= '</td>';
            $divmap .= '<td style="width:'.$divMarkerlistWidth.'">';
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' float: left; padding: 0; margin: 0 0 0 10px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
            $divmap .= '</td>';
            $divmap .= '</tr>';
            $divmap .= '</tbody>';
            $divmap .='</table>';
        break;
        case 113:
            $divmap .='</div>';
        break;
        case 114:
            $divmap .='<div id="GMapsMarkerList'.$mapDivSuffix.'" class="zhgm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
            $divmap .='</div>';
        break;
        case 120:
        break;        
        case 121:
        break;
        default:
        break;
    }


}

        
$divmap .= '<div id="GMapsCredit'.$mapDivSuffix.'" class="zhgm-credit"></div>';

$divmap .= '<div id="GMapsLoading'.$mapDivSuffix.'" style="display: none;" ><img class="zhgm-image-loading" src="'.$imgpathUtils.'loading.gif" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_LOADING').'" /></div>';

$scripthead .= $divmapheader . $currentUserInfo;

// adding route panel in any case
$divmap4route = '<div id="GMapsMainRoutePanel'.$mapDivSuffix.'" class="zhgm-map-route-main"><div id="GMapsMainRoutePanel_Total'.$mapDivSuffix.'" class="zhgm-map-route-main-total"></div></div>';
$divmap4route .= '<div id="GMapsRoutePanel'.$mapDivSuffix.'" class="zhgm-map-route"><div id="GMapsRoutePanel_Description'.$mapDivSuffix.'" class="zhgm-map-route-description"></div><div id="GMapsRoutePanel_Total'.$mapDivSuffix.'" class="zhgm-map-route-total"></div></div>';

if ($featurePathElevation == 1 || $featurePathElevationKML == 1)
{
    $divmap4route .= '<div id="GMapsPathPanel'.$mapDivSuffix.'" onmouseout="clearMarkerElevation'.$mapDivSuffix.'(); return false;" class="zhgm-map-path"></div>';
}


// adding before and after sections
$divmap = $divmapbefore . $divmap . $divmapafter;


$divTabDivMain = '';

if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{
    switch ((int)$map->markergroupcontrol) 
    {
        
        case 1:
               if ($fullWidth == 1) 
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
                  $divTabDivMain .=  '<tr align="left" >';
                  if ((int)$map->markergroupwidth != 0)
                  {
                      $divTabDivMain .=  '<td valign="top" width="'.(int)$map->markergroupwidth.'%">';
                  }
                  else
                  {
                      $divTabDivMain .=  '<td valign="top" width="20%">';
                  }
                     $divTabDivMain .=  $divmarkergroup;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
                  $divTabDivMain .=  $divmap;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '</tr>';
               }
               else
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
                  $divTabDivMain .=  '<tr>';
                  $divTabDivMain .=  '<td valign="top">';
                  $divTabDivMain .=  $divmarkergroup;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
                  $divTabDivMain .=  $divmap;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '</tr>';
                       }
               $divTabDivMain .=  '</table>';
        break;
        case 2:
               if ($fullWidth == 1) 
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
               }
               else
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
               }
               $divTabDivMain .=  '<tr>';
               $divTabDivMain .=  '<td valign="top">';
               $divTabDivMain .=  $divmarkergroup;
               $divTabDivMain .=  '</td>';
               $divTabDivMain .=  '</tr>';
               $divTabDivMain .=  '<tr>';
               $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
               $divTabDivMain .=  $divmap;
               $divTabDivMain .=  '</td>';
               $divTabDivMain .=  '</tr>';
               $divTabDivMain .=  '</table>';

        break;
        case 3:
               if ($fullWidth == 1) 
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'">';
                  $divTabDivMain .=  '<tr>';
                  $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
                  $divTabDivMain .=  $divmap;
                  $divTabDivMain .=  '</td>';
                  if ((int)$map->markergroupwidth != 0)
                  {
                      $divTabDivMain .=  '<td valign="top" width="'.(int)$map->markergroupwidth.'%">';
                  }
                  else
                  {
                      $divTabDivMain .=  '<td valign="top" width="20%">';
                  }
                  $divTabDivMain .=  $divmarkergroup;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '</tr>';
               }
               else
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
                  $divTabDivMain .=  '<tr>';
                  $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
                  $divTabDivMain .=  $divmap;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '<td valign="top">';
                  $divTabDivMain .=  $divmarkergroup;
                  $divTabDivMain .=  '</td>';
                  $divTabDivMain .=  '</tr>';
               }
               $divTabDivMain .=  '</table>';

        break;
        case 4:
               if ($fullWidth == 1) 
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
               }
               else
               {
                  $divTabDivMain .=  '<table class="zhgm-group-manage" '.$divwrapmapstyle.'>';
               }
               $divTabDivMain .=  '<tr>';
               $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
               $divTabDivMain .=  $divmap;
               $divTabDivMain .=  '</td>';
               $divTabDivMain .=  '</tr>';
               $divTabDivMain .=  '<tr>';
               $divTabDivMain .=  '<td valign="top">';
               $divTabDivMain .=  $divmarkergroup;
               $divTabDivMain .=  '</td>';
               $divTabDivMain .=  '</tr>';
               $divTabDivMain .=  '</table>';
        break;
        case 5:
               $divTabDivMain .=  '<div id="zhgm-wrapper" '.$divwrapmapstyle.'>';
               $divTabDivMain .=  $divmarkergroup;
               $divTabDivMain .=  $divmap;
               $divTabDivMain .=  '</div>';
        break;
        case 6:
               $divTabDivMain .=  '<div id="zhgm-wrapper" '.$divwrapmapstyle.'>';
               $divTabDivMain .=  $divmap;
               $divTabDivMain .=  $divmarkergroup;
               $divTabDivMain .=  '</div>';
        break;
        case 10:
               $divTabDivMain .=  $divmap;
        break;
        case 120:
               $divTabDivMain .=  $divmap;
        break;
        default:
            $divTabDivMain .=  $divmap;
        break;
    }


        $scripthead .= $divTabDivMain;
    
}
else
{
        $scripthead .= $divmap;
}



    $scripthead .= $divmapfooter. $divmap4route;

if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
    {
        // all save at the end    
    }
    else if (isset($useObjectStructure) && (int)$useObjectStructure == 2)
    {
        // for module case
        echo $scripthead;
    }
    else
    {
    }
}
else
{
    echo $scripthead;
}    
    
$scripttext = '';
$scripttextBegin = '';
$scripttextEnd = '';

$script_loadVisualisation = 0;

//Script begin
$scripttextBegin .= '<script type="text/javascript" >' ."\n";


    // Global variable scope (for access from all functions)

    $scripttext .= 'var map'.$mapDivSuffix.', infowindow'.$mapDivSuffix.';' ."\n";
    if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
    {
        $scripttext .= ' var mapCircle'.$mapDivSuffix.';'."\n";
    }
    
    
    $scripttext .= 'var latlng'.$mapDivSuffix.', routeaddress'.$mapDivSuffix.';' ."\n";

    if ((isset($map->streetview) && (int)$map->streetview != 0)
        || (isset($map->maptype) && (int)$map->maptype == 10))
    {
            $scripttext .= 'var panorama'.$mapDivSuffix.';' ."\n";
    }

        if ((int)$map_street_view_content == 0)
        {
            if (isset($map->auto_center_zoom) && ((int)$map->auto_center_zoom !=0))
            {
                    $scripttext .= 'var map_bounds'.$mapDivSuffix.' = new google.maps.LatLngBounds();' ."\n";
            }

            if ($zhgmObjectManager != 0)
            {
                    $scripttext .= 'var zhgmObjMgr'.$mapDivSuffix.';' ."\n";
            }

            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
            {
                    if ((int)$map->hovermarker == 1)
                    {
                            $scripttext .= 'var hoverinfowindow'.$mapDivSuffix.';' ."\n";
                    }
                    else if ((int)$map->hovermarker == 2)
                    {
                            $scripttext .= 'var hoverinfobubble'.$mapDivSuffix.';' ."\n";
                    }
            }

            $scripttext .= 'var routedestination'.$mapDivSuffix.', routedirection'.$mapDivSuffix.';' ."\n";

            $scripttext .= 'var mapzoom'.$mapDivSuffix.';' ."\n";

            $scripttext .= 'var infobubblemarkers'.$mapDivSuffix.' = [];' ."\n";
    
    
            if ($externalmarkerlink == 1)
            {
                    $scripttext .= 'var allPlacemarkArray = [];' ."\n";
            }



            if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
            {
                    if (!isset($this->loadVisualisation))
                    {
                            $tmp_loadVisualisation = 0;
                    }
                    else
                    {
                            $tmp_loadVisualisation = (int)$this->loadVisualisation;
                    }

            }
            else
            {
                    if (!isset($loadVisualisation))
                    {
                            $tmp_loadVisualisation = 0;
                    }
                    else
                    {
                            $tmp_loadVisualisation = (int)$loadVisualisation;
                    }
            }

            // Load the Visualization API and the columnchart package.
            if (($featurePathElevation == 1 || $featurePathElevationKML == 1)
                    && ((int)$tmp_loadVisualisation == 0))
            {
				$script_loadVisualisation = 1;
				if ((int)$do_map_load == 1)
				{
                    $scripttext .= 'google.load("visualization", "1", {packages: ["corechart"]});'."\n";
                    //$scripttext .= 'google.load("visualization", "1", {packages: ["columnchart"]});'."\n";					

                    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                    {
                            $this->loadVisualisation = 1;
                    }
                    else
                    {
                            $loadVisualisation = 1;
                    }
				}

            }

            if ($featurePathElevation == 1 || $featurePathElevationKML == 1)
            {
                    if ($featurePathElevationKML == 1)
                    {

                            if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                            {
                                    $this->loadVisualisationKML = 1;
                            }
                            else
                            {
                                    $loadVisualisationKML = 1;
                            }

                    }
            }

        }

    if (isset($map->usercontactattributes) && $map->usercontactattributes != "")
    {
        $userContactAttrs = str_replace(";", ',',$map->usercontactattributes);
    }
    else
    {
        $userContactAttrs = str_replace(";", ',', 'name;position;address;phone;mobile;fax;email');
    }
    $scripttext .= 'var userContactAttrs = \''.$userContactAttrs.'\';' ."\n";
    

    $scripttext .= 'var icoIcon=\''.$imgpathIcons.'\';'."\n";
    $scripttext .= 'var icoUtils=\''.$imgpathUtils.'\';'."\n";
    $scripttext .= 'var icoDir=\''.$directoryIcons.'\';'."\n";

    if ((int)$map_street_view_content == 0)
        {
            if ($zhgmObjectManager != 0)
            {
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.' = new zhgmMapObjectManager();' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMapID('.$map->id.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkDateFormat("'.$fv_placemark_date_fmt.'");' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkList("'.$placemarklistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setExcludePlacemarkList("'.$explacemarklistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkGroupList("'.$grouplistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkCategotyList("'.$categorylistid.'");' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPathList("'.$pathlistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setExcludePathList("'.$expathlistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPathGroupList("'.$pathgrouplistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPathCategotyList("'.$pathcategorylistid.'");' ."\n";    
        
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkTagList("'.$taglistid.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPathTagList("'.$pathtaglistid.'");' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setUserMarkersFilter("'.$usermarkersfilter.'");' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMapLanguageTag("'.$main_lang.'");' ."\n";


                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setServiceDirection('.$service_DoDirection.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setIcoIcon(icoIcon);' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setIcoUtils(icoUtils);' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setIcoDir(icoDir);' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setArticleID("'.$mapDivSuffix.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkRating('.(int)$map->placemark_rating.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkTitleTag("'.$placemarkTitleTag.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setRequestURL("'.URI::root().'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkCreationInfo('.(int)$map->showcreateinfo.');' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setFeature4Control('.$feature4control.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPanelInfowin('.$managePanelInfowin.');' ."\n";

                    if (isset($map->gogoogle) && ((int)$map->gogoogle != 0))
                    {
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setGoGoogle('.(int)$map->gogoogle.');' ."\n";
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setGoGoogleText("'.$fv_override_gogoogle_text.'");' ."\n";                   
                    }

                    if ($ajaxLoadObjects != 0)
                    {
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizePlacemark('.(int)$map->ajaxbufferplacemark.');' ."\n";
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizePath('.(int)$map->ajaxbufferpath.');' ."\n";
                        //$scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizeRoute('.(int)$map->ajaxbufferroute.');' ."\n";
                    }

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setContactAttrs("'.$userContactAttrs.'");' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setUserContact('.(int)$map->usercontact.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setUserUser('.(int)$map->useruser.');' ."\n";


                    if ($needOverlayControl != 0)
                    {
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enableOpacityOverlayControl();' ."\n";
                    }

                    if ($compatiblemode != 0)
                    {
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setCompatibleMode('.(int)$compatiblemode.');' ."\n";

                    }    

                    // for centering placemarks
                    if ($ajaxLoadObjects != 0) {
                        if ($currentPlacemarkCenter != "do not change") {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setCenterPlacemark('.(int)$currentPlacemarkCenter.');' ."\n";

                        }

                        if ($currentPlacemarkActionID != "do not change")
                        {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setActionPlacemark('.(int)$currentPlacemarkActionID.');' ."\n";

                            if ($currentPlacemarkAction != "do not change")
                            {
                                $currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);

                                for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
                                {
                                    switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
                                    {
                                        case "":
                                           // null
                                        break;
                                        case "do not change":
                                                // do not change
                                        break;
                                        case "click":
                                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("click");' ."\n";
                                        break;
                                        case "bounce":
                                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("bounce");' ."\n";
                                        break;
                                        default:
                                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("'. trim($currentPlacemarkExecuteArray[$i]).'");'."\n";
                                        break;
                                    }
                                }
                            }


                        }                        
                    }

            if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0)
                    {
                            if (isset($map->markerlistsync) && (int)$map->markerlistsync != 0)
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkList();' ."\n";
                            }
                    }


            }

            if ($managePanelFeature == 1)
            {
                $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePanel();' ."\n";
                if ($fullHeight == 1) 
                {
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPanelHeightDeltaFix(5);' ."\n";
                }               
            }

            if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
             || (isset($map->markermanager) && (int)$map->markermanager == 1))
            {
				if (isset($mgrgrouplist) && !empty($mgrgrouplist)) {
					foreach ($mgrgrouplist as $key => $currentmarkergroup) 
					{
						if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
						{
							$scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addManagedGroup('.$currentmarkergroup->id.', "'.str_replace('"', '\\"',str_replace('\\', '\\\\',$currentmarkergroup->title)).'", "'.str_replace('"', '\\"',str_replace('\\', '\\\\',$currentmarkergroup->description)).'", '.$currentmarkergroup->markermanagerminzoom.', '.$currentmarkergroup->markermanagermaxzoom.');' ."\n";
						}
					}
				}
            }
        }
    
    $scripttext .= 'function initialize'.$mapInitTag.'(){' ."\n";

        if ((int)$map_street_view_content == 0)
        {
            //
                // MarkerGroups
                $placemarkGroupArray = array();

                if ($zhgmObjectManager)
                {
                    $scripttext .= 'var markerCluster0;' ."\n";

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.GroupStateDefine(0, 1);' ."\n";

                    if (isset($markergroups) && !empty($markergroups)) 
                    {
                                foreach ($markergroups as $key => $currentmarkergroup) 
                                {
                                        $scripttext .= 'var markerCluster'.$currentmarkergroup->id.';' ."\n";

                                        array_push($placemarkGroupArray, $currentmarkergroup->id);

                                        // 24.11.2015 - bugfix - unpublished groups caused error, because there is no link element
                                        if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
                                        {
                                                $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.GroupStateDefine('.$currentmarkergroup->id.', '.(int)$currentmarkergroup->activeincluster.');' ."\n";
                                        }
                                }
                    }


                        $scripttext .= 'var pathArray0 = [];' ."\n";
                        if (isset($mgrgrouplist) && !empty($mgrgrouplist)) 
                        {
                                foreach ($mgrgrouplist as $key => $currentmarkergroup) 
                                {
                                        if (!in_array($currentmarkergroup->id, $placemarkGroupArray))
                                        {
                                                $scripttext .= 'var markerCluster'.$currentmarkergroup->id.';' ."\n";

                                                // 24.11.2015 - bugfix - unpublished groups caused error, because there is no link element
                                                if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
                                                {
                                                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.GroupStateDefine('.$currentmarkergroup->id.', '.(int)$currentmarkergroup->activeincluster.');' ."\n";
                                                }
                                        }
                                }
                        }
                }

                if (isset($map->useajax) && (int)$map->useajax != 0)
                {
                    $scripttext .= 'var ajaxmarkersLL'.$mapDivSuffix.' = [];' ."\n";
                    $scripttext .= 'var ajaxmarkersADR'.$mapDivSuffix.' = [];' ."\n";

                    $scripttext .= 'var ajaxpaths'.$mapDivSuffix.' = [];' ."\n";
                    $scripttext .= 'var ajaxpathsOVL'.$mapDivSuffix.' = [];' ."\n";

                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                    {
                            $scripttext .= 'var ajaxmarkersLLhover'.$mapDivSuffix.' = [];' ."\n";
                            $scripttext .= 'var ajaxmarkersADRhover'.$mapDivSuffix.' = [];' ."\n";

                    }

                    $scripttext .= 'var ajaxpathshover'.$mapDivSuffix.' = [];' ."\n";                      

                }

            //
        }
    $scripttext .= 'var toShowLoading = document.getElementById("GMapsLoading'.$mapDivSuffix.'");'."\n";
    $scripttext .= '  toShowLoading.style.display = \'block\';'."\n";
    
    $scripttext .= 'latlng'.$mapDivSuffix.' = new google.maps.LatLng('.$map->latitude.', ' .$map->longitude.');' ."\n";
        if ((int)$map_street_view_content == 0)
        {

            $scripttext .= 'routedirection'.$mapDivSuffix.' = 1;'."\n";

            $scripttext .= 'routeaddress'.$mapDivSuffix.' = "'.$map->routeaddress.'";' ."\n";

            if (isset($map->routeaddress) && $map->routeaddress != "")
            {
                    $scripttext .= 'routedestination'.$mapDivSuffix.' = routeaddress'.$mapDivSuffix.';'."\n";
            }
            else
            {
                    $scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
            }



            if (isset($mapzoom) && (int)$mapzoom != 0)
            {
                    $scripttext .= 'mapzoom'.$mapDivSuffix.' ='.$mapzoom.';' ."\n";

                    if (((int)$map->mapcentercontrol == 2)
                      ||((int)$map->mapcentercontrol == 12))
                    {
                            $ctrl_zoom = $mapzoom;
                    }
                    else
                    {
                            $ctrl_zoom = 'do not change';
                    }        
            }
            else
            {
                    $scripttext .= 'mapzoom'.$mapDivSuffix.' ='.$map->zoom.';' ."\n";

                    if (((int)$map->mapcentercontrol == 2)
                      ||((int)$map->mapcentercontrol == 12))
                    {
                            $ctrl_zoom = $map->zoom;
                    }
                    else
                    {
                            $ctrl_zoom = 'do not change';
                    }                
            }




            $scripttext .= 'var myOptions = {' ."\n";

            $scripttext .= ' center: latlng'.$mapDivSuffix.',' ."\n";
            $scripttext .= ' zoom: mapzoom'.$mapDivSuffix.',' ."\n";

            //Scroll Wheel Zoom - changed to gestureHandling

            if (isset($map->scrollwheelzoom))
            {
                switch ((int)$map->scrollwheelzoom) 
                {            
                    case 0:
                            $scripttext .= ' gestureHandling: \'none\',' ."\n";
                    break;
                    case 1:
                            $scripttext .= ' gestureHandling: \'cooperative\',' ."\n";
                    break;
                    case 2:
                            $scripttext .= ' gestureHandling: \'greedy\',' ."\n";
                    break;
                    case 3:
                            $scripttext .= ' gestureHandling: \'auto\',' ."\n";
                    break;
                    default:
                            $scripttext .= '';
                    break;
                }

            }

            /* 
            if (isset($map->scrollwheelzoom) && (int)$map->scrollwheelzoom == 1) 
            {
                    $scripttext .= ' scrollwheel: true,' ."\n";
            } 
            else 
            {
                    $scripttext .= ' scrollwheel: false,' ."\n";
            }
            */


            if (isset($map->minzoom) && (int)$map->minzoom != 0)
            {
                    $scripttext .= ' minZoom: '.(int)$map->minzoom.',' ."\n";
            }
            if (isset($map->maxzoom) && (int)$map->maxzoom != 0)
            {
                    $scripttext .= ' maxZoom: '.(int)$map->maxzoom.',' ."\n";
            }
            if (isset($map->draggable) && (int)$map->draggable == 0)
            {
                    $scripttext .= ' draggable: false ,' ."\n";
            }
            else
            {
                    $scripttext .= ' draggable: true ,' ."\n";
            }

            // Map type
            // Change $map->maptype to $currentMapType

            // Map type
            if (isset($currentMapType)) 
            {

                    if ($currentMapType == "do not change")
                    {
                            $currentMapTypeValue = $map->maptype;
                    }
                    else
                    {
                            $currentMapTypeValue = $currentMapType;
                    }

                    switch ($currentMapTypeValue) 
                    {

                            case 1:
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;
                            case 2:
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.SATELLITE,' ."\n";
                            break;
                            case 3:
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.HYBRID,' ."\n";
                            break;
                            case 4:
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.TERRAIN,' ."\n";
                            break;
                            case 5: 
                                    // set it later (OSM, OpenStreetMap)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;
                            case 6: 
                                    // set it later (NZ Topomaps)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;
                            case 7: 
                                    // set it later (First custom map type)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;
                            case 8: 
                                    // set it later (OpenTopoMap)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;
                            case 9: 
                                    // set it later (StreetView)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;                    
                            case 10: 
                                    // set it later (StreetView)
                                    $scripttext .= ' mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                            break;                    
                            default:
                                    $scripttext .= '' ."\n";
                            break;
                    }
            }


            //Zoom Control
            if (isset($map->zoomcontrol) && (int)$map->zoomcontrol != 0)
            {
                    $scripttext .= ' zoomControl: true,' ."\n";
                    $scripttext .= '   zoomControlOptions: {' ."\n";
                    if (isset($map->poszoom)) 
                    {
                            switch ($map->poszoom) 
                            {
                                    case 0:
                                    break;
                                    case 1:
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_CENTER ' ."\n";
                                    break;
                                    case 2:
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_LEFT ' ."\n";
                                    break;
                                    case 3:
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_RIGHT ' ."\n";
                                    break;
                                    case 4:
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_TOP ' ."\n";
                                    break;
                                    case 5:
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_TOP ' ."\n";
                                    break;
                                    case 6:
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_CENTER ' ."\n";
                                    break;
                                    case 7:
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_CENTER ' ."\n";
                                    break;
                                    case 8:
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_BOTTOM ' ."\n";
                                    break;
                                    case 9:
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_BOTTOM ' ."\n";
                                    break;
                                    case 10:
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_CENTER ' ."\n";
                                    break;
                                    case 11:
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_LEFT ' ."\n";
                                    break;
                                    case 12:
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_RIGHT ' ."\n";
                                    break;
                                    default:
                                            $scripttext .= '' ."\n";
                                    break;
                            }
                    }

                    if ($feature4control == 1)
                    {
                            if ((int)$map->zoomcontrol == 1)
                            {
                                    $scripttext .= '    , style: google.maps.ZoomControlStyle.SMALL' ."\n";
                            }
                            else if ((int)$map->zoomcontrol == 2)
                            {
                                    $scripttext .= '    , style: google.maps.ZoomControlStyle.LARGE' ."\n";
                            }
                            else if ((int)$map->zoomcontrol == 3)
                            {
                                    $scripttext .= '    , style: google.maps.ZoomControlStyle.DEFAULT' ."\n";
                            }
                            else 
                            {
                                    $scripttext .= '    , style: google.maps.ZoomControlStyle.DEFAULT' ."\n";
                            }            
                    }

                    $scripttext .= '   },' ."\n";
            } else 
            {
                    $scripttext .= ' zoomControl: false,' ."\n";
            }


            // Map type Control

            if (isset($map->maptypecontrol) && (int)$map->maptypecontrol != 0) 
            {

                    $maptypeseparator = '';
                    $maptypelist = '';
                    $maptypelist_result = '';

                    $scripttext .= ' mapTypeControl: true,' ."\n";
                    $scripttext .= ' mapTypeControlOptions: {' ."\n";
                    $scripttext .= '    mapTypeIds: [' ."\n";
                    // Add Predefined MapTypes        
                    // OSM
                    if ((int)$map->openstreet == 1)
                    {
                            $maptypelist .= $maptypeseparator. '\'osm\'' ."\n";
                            $maptypeseparator  = ',';
                    }
                    if ((int)$map->opentopomap == 1)
                    {
                            $maptypelist .= $maptypeseparator. '\'opentopomap\'' ."\n";
                            $maptypeseparator  = ',';
                    }                
                    // NZ Topomaps
                    if ((int)$map->nztopomaps != 0)
                    {
                            if ((int)$map->nztopomaps == 1
                            || (int)$map->nztopomaps == 11)
                            {
                            }
                            else
                            {
                                    $maptypelist .= $maptypeseparator.'\'nztopomaps\'' ."\n";
                                    $maptypeseparator  = ',';
                            }
                    }
                    // Add Custom MapTypes - Begin
                    if ((int)$map->custommaptype == 1)
                    {
						if (isset($maptypes) && !empty($maptypes)) {
                            foreach ($maptypes as $key => $currentmaptype) 
                            {
                                    for ($i=0; $i < count($custMapTypeList); $i++)
                                    {
                                            if ($currentmaptype->id == (int)$custMapTypeList[$i]
                                            && $currentmaptype->gettileurl != "")
                                            {
                                                    if ((int)$currentmaptype->layertype == 1)
                                                    {
                                                    }
                                                    else
                                                    {
                                                            $maptypelist .= $maptypeseparator.'\'customMapType'.$currentmaptype->id.'\'' ."\n";
                                                            $maptypeseparator  = ',';
                                                    }
                                            }
                                    }
                                    // End loop by Enabled CustomMapTypes

                            }
                            // End loop by All CustomMapTypes
						}
                    }
                    // Add Custom MapTypes - End

                    if ((isset($map->defaultmaptypes) 
                        && (((int)$map->defaultmaptypes == 0 && $maptypeseparator == "") 
                            || ((int)$map->defaultmaptypes != 0)))
                    || (!isset($map->defaultmaptypes))
                    )
                    {
                            $maptypelist_result = '        google.maps.MapTypeId.ROADMAP,' ."\n";
                            $maptypelist_result .= '        google.maps.MapTypeId.TERRAIN,' ."\n";
                            $maptypelist_result .= '        google.maps.MapTypeId.SATELLITE,' ."\n";
                            $maptypelist_result .= '        google.maps.MapTypeId.HYBRID' ."\n";

                            $maptypelist_result .= $maptypeseparator . $maptypelist;
                            $maptypeseparator  = ',';
                    }
                    else
                    {
                            $maptypelist_result = $maptypelist;
                            $maptypeseparator  = ',';
                    }

                    $scripttext .= $maptypelist_result;


                    $scripttext .= '    ],' ."\n";
                    if (isset($map->posmaptype)) 
                    {
                            switch ($map->posmaptype) 
                            {
                                    case 0:
                                    break;
                                    case 1:
                                            $scripttext .= '    position: google.maps.ControlPosition.TOP_CENTER ,' ."\n";
                                    break;
                                    case 2:
                                            $scripttext .= '    position: google.maps.ControlPosition.TOP_LEFT ,' ."\n";
                                    break;
                                    case 3:
                                            $scripttext .= '    position: google.maps.ControlPosition.TOP_RIGHT ,' ."\n";
                                    break;
                                    case 4:
                                            $scripttext .= '    position: google.maps.ControlPosition.LEFT_TOP ,' ."\n";
                                    break;
                                    case 5:
                                            $scripttext .= '    position: google.maps.ControlPosition.RIGHT_TOP ,' ."\n";
                                    break;
                                    case 6:
                                            $scripttext .= '    position: google.maps.ControlPosition.LEFT_CENTER ,' ."\n";
                                    break;
                                    case 7:
                                            $scripttext .= '    position: google.maps.ControlPosition.RIGHT_CENTER ,' ."\n";
                                    break;
                                    case 8:
                                            $scripttext .= '    position: google.maps.ControlPosition.LEFT_BOTTOM ,' ."\n";
                                    break;
                                    case 9:
                                            $scripttext .= '    position: google.maps.ControlPosition.RIGHT_BOTTOM ,' ."\n";
                                    break;
                                    case 10:
                                            $scripttext .= '    position: google.maps.ControlPosition.BOTTOM_CENTER ,' ."\n";
                                    break;
                                    case 11:
                                            $scripttext .= '    position: google.maps.ControlPosition.BOTTOM_LEFT ,' ."\n";
                                    break;
                                    case 12:
                                            $scripttext .= '    position: google.maps.ControlPosition.BOTTOM_RIGHT ,' ."\n";
                                    break;
                                    default:
                                            $scripttext .= '' ."\n";
                                    break;
                            }
                    }

                    if ((int)$map->maptypecontrol == 1)
                    {
                            $scripttext .= '    style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR' ."\n";
                    }
                    else if ((int)$map->maptypecontrol == 2)
                    {
                            $scripttext .= '    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU' ."\n";
                    }
                    else if ((int)$map->maptypecontrol == 3)
                    {
                            $scripttext .= '    style: google.maps.MapTypeControlStyle.DEFAULT' ."\n";
                    }
                    else 
                    {
                            $scripttext .= '    style: google.maps.MapTypeControlStyle.DEFAULT' ."\n";
                    }


                    $scripttext .= ' },' ."\n";
            } else {
                    $scripttext .= ' mapTypeControl: false,' ."\n";
            }

            //Double Click Zoom
            if (isset($map->doubleclickzoom) && (int)$map->doubleclickzoom == 0) 
            {
                    $scripttext .= ' disableDoubleClickZoom: true,' ."\n";
            } 
            else 
            {
                    $scripttext .= ' disableDoubleClickZoom: false,' ."\n";
            }


            // Pan Control
            if ($feature4control == 1)
            {
                    if (isset($map->pancontrol) && (int)$map->pancontrol == 1) 
                    {
                            $scripttext .= ' panControl: true,' ."\n";
                                    if (isset($map->pospan)) 
                                    {
                                            switch ($map->pospan) 
                                            {
                                            case 0:
                                            break;
                                            case 1:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.TOP_CENTER },' ."\n";
                                            break;
                                            case 2:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.TOP_LEFT },' ."\n";
                                            break;
                                            case 3:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.TOP_RIGHT },' ."\n";
                                            break;
                                            case 4:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.LEFT_TOP },' ."\n";
                                            break;
                                            case 5:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.RIGHT_TOP },' ."\n";
                                            break;
                                            case 6:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.LEFT_CENTER },' ."\n";
                                            break;
                                            case 7:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.RIGHT_CENTER },' ."\n";
                                            break;
                                            case 8:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.LEFT_BOTTOM },' ."\n";
                                            break;
                                            case 9:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.RIGHT_BOTTOM },' ."\n";
                                            break;
                                            case 10:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_CENTER },' ."\n";
                                            break;
                                            case 11:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_LEFT },' ."\n";
                                            break;
                                            case 12:
                                                    $scripttext .= '   panControlOptions: {' ."\n";
                                                    $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_RIGHT },' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '' ."\n";
                                            break;
                                            }
                                    }
                    } 
                    else 
                    {
                            $scripttext .= ' panControl: false,' ."\n";
                    }
            }    



            //Scale Control
            if (isset($map->scalecontrol) && (int)$map->scalecontrol == 1) 
            {
                    $scripttext .= ' scaleControl: true,' ."\n";
                    if (isset($map->posscale)) 
                    {
                            switch ($map->posscale) 
                            {
                            case 0:
                            break;
                            case 1:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_CENTER },' ."\n";
                            break;
                            case 2:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_LEFT },' ."\n";
                            break;
                            case 3:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_RIGHT },' ."\n";
                            break;
                            case 4:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_TOP },' ."\n";
                            break;
                            case 5:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_TOP },' ."\n";
                            break;
                            case 6:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_CENTER },' ."\n";
                            break;
                            case 7:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_CENTER },' ."\n";
                            break;
                            case 8:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_BOTTOM },' ."\n";
                            break;
                            case 9:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_BOTTOM },' ."\n";
                            break;
                            case 10:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_CENTER },' ."\n";
                            break;
                            case 11:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_LEFT },' ."\n";
                            break;
                            case 12:
                                    $scripttext .= '   scaleControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_RIGHT },' ."\n";
                            break;
                            default:
                                    $scripttext .= '' ."\n";
                            break;
                            }
                    }
            } 
            else 
            {
                    $scripttext .= ' scaleControl: false,' ."\n";
            }

            if ($feature4control == 1)
            {
                    if (isset($map->overviewmapcontrol) && (int)$map->overviewmapcontrol != 0) 
                    {
                            $scripttext .= ' overviewMapControl: true,' ."\n";
                            if ((int)$map->overviewmapcontrol == 1)
                            {
                                    $scripttext .= '   overviewMapControlOptions: { opened: false },' ."\n";            
                            }
                            else if ((int)$map->overviewmapcontrol == 2)
                            {
                                    $scripttext .= '   overviewMapControlOptions: { opened: true },' ."\n";            
                            }
                    } 
                    else 
                    {
                            $scripttext .= ' overviewMapControl: false,' ."\n";
                    }        
            }


            if (
                            (isset($map->streetviewcontrol) && (int)$map->streetviewcontrol == 1) 
                    ||
                            (isset($map->streetview) && (int)$map->streetview != 0)
                    )
            {
                    $scripttext .= ' streetViewControl: true,' ."\n";
                    if (isset($map->posstreet)) 
                    {
                            switch ($map->posstreet) 
                            {
                            case 0:
                            break;
                            case 1:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_CENTER },' ."\n";
                            break;
                            case 2:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_LEFT },' ."\n";
                            break;
                            case 3:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.TOP_RIGHT },' ."\n";
                            break;
                            case 4:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_TOP },' ."\n";
                            break;
                            case 5:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_TOP },' ."\n";
                            break;
                            case 6:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_CENTER },' ."\n";
                            break;
                            case 7:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_CENTER },' ."\n";
                            break;
                            case 8:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.LEFT_BOTTOM },' ."\n";
                            break;
                            case 9:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.RIGHT_BOTTOM },' ."\n";
                            break;
                            case 10:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_CENTER },' ."\n";
                            break;
                            case 11:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_LEFT },' ."\n";
                            break;
                            case 12:
                                    $scripttext .= '   streetViewControlOptions: {' ."\n";
                                            $scripttext .= '      position: google.maps.ControlPosition.BOTTOM_RIGHT },' ."\n";
                            break;
                            default:
                                    $scripttext .= '' ."\n";
                            break;
                            }
                    }
            } 
            else 
            {
                    $scripttext .= ' streetViewControl: false,' ."\n";
            }



            if (isset($map->rotatecontrol) && (int)$map->rotatecontrol == 1) 
            {
                    $scripttext .= ' rotateControl: true' ."\n";
            } 
            else 
            {
                    $scripttext .= ' rotateControl: false' ."\n";
            }



            //end of options
            $scripttext .= '};' ."\n";


            if (isset($map->openstreet) && (int)$map->openstreet == 1)
            {

                $scripttext .= ' var openStreetType = new google.maps.ImageMapType({' ."\n";
                $scripttext .= '  getTileUrl: function(ll, z) {' ."\n";
                $scripttext .= '    var X = ll.x % (1 << z);  /* wrap */' ."\n";
                $scripttext .= '    return "'.$urlProtocol.'://tile.openstreetmap.org/" + z + "/" + X + "/" + ll.y + ".png";' ."\n";
                $scripttext .= '  },' ."\n";
                $scripttext .= '  tileSize: new google.maps.Size(256, 256),' ."\n";
                $scripttext .= '  isPng: true,' ."\n";
                $scripttext .= '  maxZoom: 18,' ."\n";
                $scripttext .= '  name: "OSM",' ."\n";
                $scripttext .= '  alt: "'.Text::_('COM_ZHGOOGLEMAP_MAP_OPENSTREETLAYER').'"' ."\n";
                $scripttext .= '}); ' ."\n";

            }

            if (isset($map->opentopomap) && (int)$map->opentopomap == 1)
            {

                $scripttext .= ' var openTopoMapType = new google.maps.ImageMapType({' ."\n";
                $scripttext .= '  getTileUrl: function(ll, z) {' ."\n";
                $scripttext .= '    var X = ll.x % (1 << z);  /* wrap */' ."\n";
                $scripttext .= '    return "'.$urlProtocol.'://tile.opentopomap.org/" + z + "/" + X + "/" + ll.y + ".png";' ."\n";
                $scripttext .= '  },' ."\n";
                $scripttext .= '  tileSize: new google.maps.Size(256, 256),' ."\n";
                $scripttext .= '  isPng: true,' ."\n";
                $scripttext .= '  maxZoom: 18,' ."\n";
                $scripttext .= '  name: "OpenTopoMap",' ."\n";
                $scripttext .= '  alt: "'.Text::_('COM_ZHGOOGLEMAP_MAP_OPENTOPOMAPLAYER').'"' ."\n";
                $scripttext .= '}); ' ."\n";

            }

            if (isset($map->nztopomaps) && (int)$map->nztopomaps != 0)
            {

                $scripttext .= ' var NZTopomapsType = new google.maps.ImageMapType({' ."\n";
                $scripttext .= '  getTileUrl: function(ll, z) {' ."\n";
                $scripttext .= '    var X = ll.x % (1 << z);  /* wrap */' ."\n";
                $scripttext .= '    return "'.$urlProtocol.'://tiles-a.data-cdn.linz.govt.nz/services;key='.$apikey4map_nz.'/tiles/v4/layer=50767/EPSG:3857/"+z+"/"+X+"/"+ll.y+".png";' ."\n";
                $scripttext .= '  },' ."\n";
                $scripttext .= '  tileSize: new google.maps.Size(256, 256),' ."\n";
                $scripttext .= '  isPng: true,' ."\n";
                $scripttext .= '  maxZoom: 17,' ."\n";
                $scripttext .= '  name: "NZ Topo50",' ."\n";
                $scripttext .= '  alt: "'.Text::_('COM_ZHGOOGLEMAP_MAP_NZTOPOMAPSLAYER').'"' ."\n";
                $scripttext .= '}); ' ."\n";                

            }


            if ((int)$map->custommaptype != 0)
            {
				if (isset($maptypes) && !empty($maptypes)) {
                    foreach ($maptypes as $key => $currentmaptype)     
                    {

                            for ($i=0; $i < count($custMapTypeList); $i++)
                            {
                                    if ($currentmaptype->id == (int)$custMapTypeList[$i]
                                    && $currentmaptype->gettileurl != "")
                                    {

                                            $scripttext .= ' var customMapType'.$currentmaptype->id.' = new google.maps.ImageMapType({' ."\n";
                                            $scripttext .= '  getTileUrl: '.$currentmaptype->gettileurl.',' ."\n";
                                            $scripttext .= '  tileSize: new google.maps.Size('.$currentmaptype->tilewidth.', '.$currentmaptype->tileheight.'),' ."\n";
                                            if ((int)$currentmaptype->ispng == 1)
                                            {
                                                    $scripttext .= '  isPng: true,' ."\n";
                                            }
                                            else
                                            {
                                                    $scripttext .= '  isPng: false,' ."\n";
                                            }
                                            if ((int)$currentmaptype->minzoom != 0)
                                            {
                                                    $scripttext .= '  minZoom: '.(int)$currentmaptype->minzoom.',' ."\n";
                                            }
                                            if ((int)$currentmaptype->maxzoom != 0)
                                            {
                                                    $scripttext .= '  maxZoom: '.(int)$currentmaptype->maxzoom.',' ."\n";
                                            }
                                            if ($currentmaptype->opacity != "")
                                            {
                                                    $scripttext .= '  opacity: '.$currentmaptype->opacity.','."\n";
                                            }
                                            $scripttext .= '  name: "'.str_replace('"','', $currentmaptype->title).'",' ."\n";
                                            $scripttext .= '  alt: "'.str_replace('"','', $currentmaptype->description).'"' ."\n";
                                            $scripttext .= '}); ' ."\n";

                                            // Add projection
                                            if ($currentmaptype->fromlatlngtopoint != "" && $currentmaptype->frompointtolatlng != "")
                                            {
                                                    $scripttext .= $currentmaptype->projectionglobal."\n";

                                                    $scripttext .= ' function customMapTypeProjection'.$currentmaptype->id.'() {'."\n";
                                                    $scripttext .= $currentmaptype->projectiondefinition."\n";
                                                    $scripttext .= ' }'."\n";

                                                    $scripttext .= ' customMapTypeProjection'.$currentmaptype->id.'.prototype.fromLatLngToPoint  = ';
                                                    $scripttext .= $currentmaptype->fromlatlngtopoint."\n";
                                                    $scripttext .= ';'."\n";

                                                    $scripttext .= ' customMapTypeProjection'.$currentmaptype->id.'.prototype.fromPointToLatLng = ';
                                                    $scripttext .= $currentmaptype->frompointtolatlng."\n";
                                                    $scripttext .= ';'."\n";

                                                    $scripttext .= ' customMapType'.$currentmaptype->id.'.projection  = new customMapTypeProjection'.$currentmaptype->id.'();' ."\n";
                                            }

                                    }
                            }
                            // End loop by Enabled CustomMapTypes

                    }
                    // End loop by All CustomMapTypes
				}
            }

        }
    
        // main content begin
        if ((int)$map_street_view_content == 0)
        {
        $scripttext .= 'map'.$mapDivSuffix.' = new google.maps.Map(document.getElementById("GMapsID'.$mapDivSuffix.'"), myOptions);' ."\n";
            $scripttext .= 'infowindow'.$mapDivSuffix.' = new google.maps.InfoWindow();' ."\n";

            if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
            {
        
                $scripttext .= ' mapCircle'.$mapDivSuffix.' = new google.maps.Circle({'."\n";
                $scripttext .= ' center: latlng'.$mapDivSuffix."\n";
                if ($fv_override_circle_radius != "" && $fv_override_circle_radius > 0)
                {
                    $scripttext .= ',radius: '.(int)$fv_override_circle_radius."\n";
                }
                else
                {
                    $scripttext .= ',radius: 1000'."\n";                    
                }
                
                if ($fv_override_circle_stroke_color != "")
                {
                    $scripttext .= ',strokeColor: "'.$fv_override_circle_stroke_color.'"'."\n";
                }
                else
                {
                    $scripttext .= ',strokeColor: "red"'."\n";
                }
                
                if ($fv_override_circle_stroke_opacity != "")
                {
                    $scripttext .= ',strokeOpacity: '.$fv_override_circle_stroke_opacity."\n";
                }
                else
                {
                    $scripttext .= ',strokeOpacity: 0.8'."\n";
                }
                
                if ($fv_override_circle_stroke_weight != "" && (int)$fv_override_circle_stroke_weight > 0)
                {
                    $scripttext .= ',strokeWeight: '.(int)$fv_override_circle_stroke_weight."\n";
                }
                else
                {
                    $scripttext .= ',strokeWeight: 4'."\n";
                }
                
                if ($fv_override_circle_fill_color != "")
                {
                    $scripttext .= ',fillColor: "'.$fv_override_circle_fill_color.'"'."\n";
                }
                else
                {
                    $scripttext .= ',fillColor: "green"'."\n";
                }
                
                if ($fv_override_circle_fill_opacity != "")
                {
                    $scripttext .= ',fillOpacity: '.$fv_override_circle_fill_opacity."\n";
                }
                else
                {
                    $scripttext .= ',fillOpacity: 0.3'."\n";
                }

                if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                {
                    $scripttext .= ',draggable: true'."\n";
                }
                else
                {
                    $scripttext .= ',draggable: false'."\n";
                }
                
                if ($fv_override_circle_editable == "" || (int)$fv_override_circle_editable != 0)
                {
                    $scripttext .= ',editable: true'."\n";
                }
                else
                {
                    $scripttext .= ',editable: false'."\n";
                }

                
                $scripttext .= '  });' ."\n";
                
                    
                $scripttext .= '  var v_radius_v = mapCircle'.$mapDivSuffix.'.getRadius();' ."\n";   
                $scripttext .= '  v_radius_v = v_radius_v.toFixed(0);' ."\n";   
                $scripttext .= '  var v_radius_m = "'.Text::_('COM_ZHGOOGLEMAP_MAP_CIRCLEBORDER_RADIUS').'";'."\n";
                $scripttext .= '  v_radius_m =  v_radius_m.replace("%1", v_radius_v);'."\n";
                $scripttext .= '  mapCircle'.$mapDivSuffix.'.set("zhgmInfowinContent", v_radius_m);' ."\n";                    
                        
                $scripttext .= 'mapCircle'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');'."\n";
                
                if ($fv_override_circle_info == "" || (int)$fv_override_circle_info != 0)
                {
                    $scripttext .= '  google.maps.event.addListener(mapCircle'.$mapDivSuffix.', \'radius_changed\', function() {' ."\n";
                    $scripttext .= '  var v_radius_v = mapCircle'.$mapDivSuffix.'.getRadius();' ."\n";   
                    $scripttext .= '  v_radius_v = v_radius_v.toFixed(0);' ."\n";   
                    $scripttext .= '  var v_radius_m = "'.Text::_('COM_ZHGOOGLEMAP_MAP_CIRCLEBORDER_RADIUS').'";'."\n";
                    $scripttext .= '  v_radius_m =  v_radius_m.replace("%1", v_radius_v);'."\n";
                    $scripttext .= '  mapCircle'.$mapDivSuffix.'.set("zhgmInfowinContent", v_radius_m);' ."\n";                    

                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                    // Close the other infobubbles
                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                    $scripttext .= '  }' ."\n";
                    // Hide hover window when feature enabled
                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                    {
                            if ((int)$map->hovermarker == 1)
                            {
                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                            }
                            else if ((int)$map->hovermarker == 2)
                            {
                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                            }
                    }
                    // Open infowin
                    if ((int)$map->markerlistpos != 0)
                    {
                            $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                    }

                    
                    $scripttext .= '});' ."\n";

                    $scripttext .= '  google.maps.event.addListener(mapCircle'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                    // Close the other infobubbles
                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                    $scripttext .= '  }' ."\n";
                    // Hide hover window when feature enabled
                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                    {
                            if ((int)$map->hovermarker == 1)
                            {
                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                            }
                            else if ((int)$map->hovermarker == 2)
                            {
                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                            }
                    }
                    // Open infowin
                    if ((int)$map->markerlistpos != 0)
                    {
                            $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                    }

                    //if ($managePanelInfowin == 1)
                    //{
                    //        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.get("zhgmInfowinContent"));' ."\n";
                    //}    
                    //else
                    //{                                            
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(mapCircle'.$mapDivSuffix.'.get("zhgmInfowinContent"));' ."\n";
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                    //}
                    $scripttext .= '  });' ."\n";                    
                }
                
            }
            
            if ($managePanelInfowin == 1
            || ((int)$map->markerlistpos != 0)
            )
            {
                    $scripttext .='Map_Initialize_All(map'.$mapDivSuffix.');'."\n";
            }

            if (isset($map->disableautopan) && ((int)$map->disableautopan == 1))    
            {
                    $scripttext .= 'infowindow'.$mapDivSuffix.'.setOptions({disableAutoPan: true});'."\n";
            }

            if ($zhgmObjectManager != 0)
            {
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');' ."\n";
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setInfowin(infowindow'.$mapDivSuffix.');' ."\n";
            }

            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
            {

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setHoverMarkerType("'.$map->hovermarker.'");' ."\n";

                    if ((int)$map->hovermarker == 1)
                    {
                            $scripttext .= 'hoverinfowindow'.$mapDivSuffix.' = new google.maps.InfoWindow();' ."\n";
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setHoverInfoWindow(hoverinfowindow'.$mapDivSuffix.');' ."\n";
                    }
                    else if ((int)$map->hovermarker == 2)
                    {
                            $hoverStyle = '';
                            $hoverStyle .= '{' ."\n";
                            $hoverStyle .= ' disableAutoPan: true' ."\n";
                            $hoverStyle .= ',hideCloseButton: true' ."\n";
                            $hoverStyle .= ',shadowStyle: 1' ."\n";
                            $hoverStyle .= ',padding: 2' ."\n";
                            $hoverStyle .= ',borderRadius: 4' ."\n";
                            $hoverStyle .= ',arrowSize: 10' ."\n";
                            $hoverStyle .= ',borderWidth: 1' ."\n";
                            $hoverStyle .= ',arrowPosition: 30' ."\n";
                            $hoverStyle .= ',arrowStyle: 2' ."\n";
                            $hoverStyle .= '}' ."\n";

                            if (isset($map->hoverinfobubble) && (int)$map->hoverinfobubble != 0)
                            {
                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.' = new InfoBubble(' ."\n";
                                    $scripttext .= MapPlacemarksHelper::get_placemark_infobubble_style_by_id((int)$map->hoverinfobubble, $hoverStyle);
                                    $scripttext .= ');' ."\n";
                            }
                            else
                            {
                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.' = new InfoBubble(' ."\n";
                                    $scripttext .= ' '. $hoverStyle .' ' ."\n";
                                    $scripttext .= ');' ."\n";
                            }

                            if ($zhgmObjectManager != 0)
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setHoverInfoBubble(hoverinfobubble'.$mapDivSuffix.');' ."\n";
                            }

                    }
            }


            // Map is created


        $layerWeatherAndCloud = MapPlacemarksHelper::get_WeatherCloudLayers($map->weathertypeid, $mapDivSuffix);

            if ($layerWeatherAndCloud != "")
            {

                    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                    {
                            $this->weather = 1;
                    }
                    else
                    {
                            $weather = 1;
                    }

                    $scripttext .= $layerWeatherAndCloud;
            }

            if (isset($map->mapbounds) && $map->mapbounds != "")
            {
                    $mapBoundsArray = explode(";", str_replace(',',';',$map->mapbounds));
                    if (count($mapBoundsArray) != 4)
                    {
                            $scripttext .= 'alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_ERROR_MAPBOUNDS').'");'."\n";
                    }
                    else
                    {

                            if ($zhgmObjectManager != 0)
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMapBounds('.$mapBoundsArray[0].', '.$mapBoundsArray[1].','.$mapBoundsArray[2].', '.$mapBoundsArray[3].');' ."\n";
                            }        

                    }
            }    

            if (isset($map->streetview) && (int)$map->streetview != 0)
            {
                    $scripttext .= 'var panoramaOptions = {' ."\n";
                    
                    $scripttext .= '  position: latlng'.$mapDivSuffix.'' ."\n";
                    
                    $mapSV = MapPlacemarksHelper::get_StreetViewOptions($map->streetviewstyleid);
                    if ($mapSV != "")
                    {
                            $scripttext .= ', pov: '.$mapSV ."\n";
                    }

                    $scripttext .= '};' ."\n";
                    $scripttext .= 'panorama'.$mapDivSuffix.' = new  google.maps.StreetViewPanorama(document.getElementById("GMapStreetView'.$mapDivSuffix.'"), panoramaOptions);' ."\n";
                    $scripttext .= 'map'.$mapDivSuffix.'.setStreetView(panorama'.$mapDivSuffix.');' ."\n";        


                    if ($zhgmObjectManager != 0)
                    {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMapPanorama(panorama'.$mapDivSuffix.');' ."\n";

                    }

            }    


            
            if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
            {

                    if ($zhgmObjectManager != 0)
                    {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMarkerListPos('.(int)$map->markerlistpos.');' ."\n";
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMarkerListContent('.(int)$map->markerlistcontent.');' ."\n";
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMarkerListAction('.(int)$map->markerlistaction.');' ."\n";
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setMarkerListCSSStyle("'.$markerlistcssstyle.'");' ."\n";

                    }

                    if ((int)$map->markerlistpos == 111
                      ||(int)$map->markerlistpos == 112
                      ||(int)$map->markerlistpos == 121
                      ||(int)$map->markerlistpos == 120 // panel
                      ) 
                    {
                            // Do not create button when table or external
                    }
                    else
                    {
                            if ((int)$map->markerlistbuttontype == 0)
                            {
                                    // Skip creation for non-button
                            }
                            else
                            {

                                    $scripttext .= '  var placemarklistControl = new zhgmPlacemarkListButtonControl('.
                                            '"GMapsMarkerList'.$mapDivSuffix.'",'.
                                            'map'.$mapDivSuffix.','. 
                                            $feature4control.','. 
                                            (int)$map->markerlistbuttontype.','. 
                                            (int)$map->markerlistbuttonpos.','. 
                                            '"placemarklist",'. 
                                            '"'.$fv_override_placemark_button_tooltip.'",'.
                                            '16,'. 
                                            '16,'. 
                                            '"'.$imgpathUtils.'star.png"'.
                                            ');'."\n";                

                            }
                    }

            }

            if ($managePanelFeature == 1)
            {

                            $scripttext .= '  var panelControl = new zhgmPanelButtonControl('.
                                    '"GMapsMainPanel'.$mapDivSuffix.'","GMapsID'.$mapDivSuffix.'","GMapsPanelAccordion'.$mapDivSuffix.'",'.$managePanelContentWidth.','.
                                    'map'.$mapDivSuffix.','. 'zhgmObjMgr'.$mapDivSuffix.','.
                                    $feature4control.','. 
                                    (int)$map->panelstate.','. 
                                    '7,'. 
                                    '"panel",'. 
                                    '"'.$fv_override_panel_button_tooltip.'",'.
                                    '18,'. 
                                    '23,'. 
                                    '"'.$imgpathUtils.'panel_left.png"'.
                                    ');'."\n";            
            }

            // Pushing controls - Begin



            // Begin 1
            if (isset($map->findcontrol) && (int)$map->findcontrol == 1)
            {
                    $scripttext .= '  var markerFind = new google.maps.Marker({' ."\n";
                    $scripttext .= '    map: map'.$mapDivSuffix.'' ."\n";
                    $scripttext .= '  });' ."\n";

                    $controlPosition ="";
                    if (isset($map->findpos)) 
                    {
                            switch ($map->findpos) 
                            {
                                    case 0:
                                    break;
                                    case 1:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_CENTER';
                                    break;
                                    case 2:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_LEFT';
                                    break;
                                    case 3:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_RIGHT';
                                    break;
                                    case 4:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_TOP';
                                    break;
                                    case 5:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_TOP';
                                    break;
                                    case 6:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_CENTER';
                                    break;
                                    case 7:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_CENTER';
                                    break;
                                    case 8:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_BOTTOM';
                                    break;
                                    case 9:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_BOTTOM';
                                    break;
                                    case 10:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_CENTER';
                                    break;
                                    case 11:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_LEFT';
                                    break;
                                    case 12:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_RIGHT';
                                    break;
                                    case 101:
                                            $controlPosition = '';
                                    break;
                                    case 102:
                                            $controlPosition = '';
                                    break;
                                    default:
                                            $controlPosition = '';
                                    break;
                            }
                    }

                    $scripttext .= "\n";
                    $scripttext .= 'var findAddressButton'.$mapDivSuffix.' = document.getElementById(\'findAddressButton'.$mapDivSuffix.'\');' ."\n";
                    $scripttext .= 'var findAddressField'.$mapDivSuffix.' = document.getElementById(\'findAddressField'.$mapDivSuffix.'\');' ."\n";
                    if (isset($map->findroute) && (int)$map->findroute == 2) 
                    {
                            $scripttext .= 'var findAddressButtonFind'.$mapDivSuffix.' = document.getElementById(\'findAddressButtonFind'.$mapDivSuffix.'\');' ."\n";
                    }

                    $scripttext .= 'var findRouteDirectionsDisplay'.$mapDivSuffix.';' ."\n";
                    $scripttext .= 'var findRouteDirectionsService'.$mapDivSuffix.';' ."\n";

                    if ((isset($map->findroute) && (int)$map->findroute != 0) 
                        ||((isset($map->placesenable) && (int)$map->placesenable == 1)
                        && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1)) )
                    {
                            $scripttext .= 'findRouteDirectionsDisplay'.$mapDivSuffix.' = new google.maps.DirectionsRenderer();' ."\n";
                            $scripttext .= 'findRouteDirectionsService'.$mapDivSuffix.' = new google.maps.DirectionsService();' ."\n";
                            $scripttext .= 'findRouteDirectionsDisplay'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');' ."\n";

                            if (isset($map->routeshowpanel) && (int)$map->routeshowpanel == 1) 
                            {
                                    $scripttext .= 'findRouteDirectionsDisplay'.$mapDivSuffix.'.setPanel(document.getElementById("GMapsMainRoutePanel'.$mapDivSuffix.'"));' ."\n";
                            }

                    }

					if(isset($map->region))
					{
						$map_region = $map->region;
					}
                    if ($map_region != "")
                    {
                            if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                            {
                                    $this->map_region = $map_region;
                            }
                    }
                    $map_country = $map->country;

                    if ((isset($map->placesenable) && (int)$map->placesenable == 1)
                     && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1))
                    {

                            $scripttext .= 'var optionsFindAC'.$mapDivSuffix.' = {' ."\n";
                            $scripttext .= '  types: ['.$map->placestypeac.']' ."\n";


                            if ($map_country !="")
                            {
                                    $scripttext .= ', componentRestrictions: {country: \''.$map_country.'\'}' ."\n";
                            }

                            $scripttext .= '  };' ."\n";

                            $scripttext .= '  var findAutocompleteField'.$mapDivSuffix.' = new google.maps.places.Autocomplete(findAddressField'.$mapDivSuffix.', optionsFindAC'.$mapDivSuffix.');' ."\n";

                            /* query bounds, not fine execute,
                                    but restricted by country in autocomplete options

                            if (isset($map->mapbounds) && $map->mapbounds != "")
                            {
                                    $mapSearchBoundsArray = explode(";", str_replace(',',';',$map->mapbounds));
                                    if (count($mapSearchBoundsArray) != 4)
                                    {
                                            $scripttext .= 'alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_ERROR_SEARCH_MAPBOUNDS').'");'."\n";
                                    }
                                    else
                                    {
                                            $scripttext .= 'findAutocompleteField'.$mapDivSuffix.'.setBounds(new google.maps.LatLngBounds(' ."\n";
                                            $scripttext .= '  new google.maps.LatLng('.$mapSearchBoundsArray[0].', '.$mapSearchBoundsArray[1].'),' ."\n";
                                            $scripttext .= '  new google.maps.LatLng('.$mapSearchBoundsArray[2].', '.$mapSearchBoundsArray[3].')));' ."\n";    
                                    }
                            }    
                            */

                            $scripttext .= '  findAutocompleteField'.$mapDivSuffix.'.bindTo(\'bounds\', map'.$mapDivSuffix.');' ."\n";

                            $scripttext .= '  google.maps.event.addListener(findAutocompleteField'.$mapDivSuffix.', \'place_changed\', function() {' ."\n";

                $scripttext .= '  var findPlace = findAutocompleteField'.$mapDivSuffix.'.getPlace();' ."\n";

                            $scripttext .= '  placesACbyButton'.$mapDivSuffix.'('.(int)$map->placesdirection.', findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, findPlace.name, "findAddressTravelMode'.$mapDivSuffix.'", findPlace.geometry.location, routedestination'.$mapDivSuffix.');'."\n";

                $scripttext .= '  });' ."\n";

                    }


                    $scripttext .= "\n" . 'google.maps.event.addDomListener(findAddressButton'.$mapDivSuffix.', \'click\', function() {' ."\n";
                    $scripttext .= '  if (findAddressField'.$mapDivSuffix.'.value == "") {' ."\n";
                    $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_NULL').'");' ."\n";
                    $scripttext .= '  } else {' ."\n";                
                    $scripttext .= '  geocoder'.$mapDivSuffix.'.geocode( { \'address\': findAddressField'.$mapDivSuffix.'.value';
                    /* query bounds, not fine execute,
                            but restricted by region in map loading script

                    if (isset($map->mapbounds) && $map->mapbounds != "")
                    {
                            $mapSearchBoundsArray = explode(";", str_replace(',',';',$map->mapbounds));
                            if (count($mapSearchBoundsArray) != 4)
                            {
                                    //$scripttext .= 'alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_ERROR_SEARCH_MAPBOUNDS').'");'."\n";
                            }
                            else
                            {
                                    $scripttext .= ', \'bounds\': new google.maps.LatLngBounds(' ."\n";
                                    $scripttext .= '  new google.maps.LatLng('.$mapSearchBoundsArray[0].', '.$mapSearchBoundsArray[1].'),' ."\n";
                                    $scripttext .= '  new google.maps.LatLng('.$mapSearchBoundsArray[2].', '.$mapSearchBoundsArray[3].'))' ."\n";    
                            }
                    }    
                    */
                    $scripttext .='}, function(results, status) {'."\n";
                    $scripttext .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
                    $scripttext .= '    var latlngFind = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";

                    if (isset($map->findroute) && (int)$map->findroute == 0) 
                    {
                            $scripttext .= '    placesACbyButton'.$mapDivSuffix.'(0, findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "", "findAddressTravelMode'.$mapDivSuffix.'", latlngFind, routedestination'.$mapDivSuffix.');'."\n";
                    }
                    else if (isset($map->findroute) && (int)$map->findroute == 1) 
                    {
                            $scripttext .= '    placesACbyButton'.$mapDivSuffix.'(1, findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "", "findAddressTravelMode'.$mapDivSuffix.'", latlngFind, routedestination'.$mapDivSuffix.');'."\n";
                    }
                    else if (isset($map->findroute) && (int)$map->findroute == 2) 
                    {
                            $scripttext .= '    placesACbyButton'.$mapDivSuffix.'(1, findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "", "findAddressTravelMode'.$mapDivSuffix.'", latlngFind, routedestination'.$mapDivSuffix.');'."\n";
                    }
                    else
                    {
                            $scripttext .= '    placesACbyButton'.$mapDivSuffix.'(0, findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "", "findAddressTravelMode'.$mapDivSuffix.'", latlngFind, routedestination'.$mapDivSuffix.');'."\n";
                    }

                    $scripttext .= '  }'."\n";
                    $scripttext .= '  else'."\n";
                    $scripttext .= '  {'."\n";
                    $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': "+findAddressField'.$mapDivSuffix.'.value);'."\n";
                    $scripttext .= '  }'."\n";
                    $scripttext .= '});'."\n";
                    $scripttext .= '}});' ."\n";

                    if (isset($map->findroute) && (int)$map->findroute == 2) 
                    {
                            $scripttext .= "\n" . 'google.maps.event.addDomListener(findAddressButtonFind'.$mapDivSuffix.', \'click\', function() {' ."\n";
                            $scripttext .= '  if (findAddressField'.$mapDivSuffix.'.value == "") {' ."\n";
                            $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_NULL').'");' ."\n";
                            $scripttext .= '  } else {' ."\n";
                            $scripttext .= '  geocoder'.$mapDivSuffix.'.geocode( { \'address\': findAddressField'.$mapDivSuffix.'.value}, function(results, status) {'."\n";
                            $scripttext .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
                            $scripttext .= '    var latlngFind = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";

                            $scripttext .= '    placesACbyButton'.$mapDivSuffix.'(0 , findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "", "findAddressTravelMode'.$mapDivSuffix.'", latlngFind, routedestination'.$mapDivSuffix.');'."\n";

                            $scripttext .= '  }'."\n";
                            $scripttext .= '  else'."\n";
                            $scripttext .= '  {'."\n";
                            $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': "+findAddressField'.$mapDivSuffix.'.value);'."\n";
                            $scripttext .= '  }'."\n";
                            $scripttext .= '});'."\n";
                            $scripttext .= '}});' ."\n";
                    }

                    if ($controlPosition != "")
                    {
                            $scripttext .= 'map'.$mapDivSuffix.'.controls['.$controlPosition.'].push(';
                $scripttext .= 'document.getElementById(\'GMapFindAddress'.$mapDivSuffix.'\'));' ."\n";
                    }


            }
            //    End 1

            // Begin 2
            // Geo Location - begin
            if (isset($map->geolocationcontrol) && (int)$map->geolocationcontrol == 1) 
            {

                    $controlPosition ="";
                    if (isset($map->geolocationpos)) 
                    {
                            switch ($map->geolocationpos) 
                            {
                                    case 0:
                                    break;
                                    case 1:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_CENTER';
                                    break;
                                    case 2:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_LEFT';
                                    break;
                                    case 3:
                                            $controlPosition = 'google.maps.ControlPosition.TOP_RIGHT';
                                    break;
                                    case 4:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_TOP';
                                    break;
                                    case 5:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_TOP';
                                    break;
                                    case 6:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_CENTER';
                                    break;
                                    case 7:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_CENTER';
                                    break;
                                    case 8:
                                            $controlPosition = 'google.maps.ControlPosition.LEFT_BOTTOM';
                                    break;
                                    case 9:
                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_BOTTOM';
                                    break;
                                    case 10:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_CENTER';
                                    break;
                                    case 11:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_LEFT';
                                    break;
                                    case 12:
                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_RIGHT';
                                    break;
                                    default:
                                            $controlPosition = '';
                                    break;
                            }
                    }

                    if ($controlPosition != "")
                    {
                            $scripttext .= "\n";
                            $scripttext .= 'var geoLocationButton'.$mapDivSuffix.' = document.getElementById(\'geoLocationButton'.$mapDivSuffix.'\');' ."\n";

                            $scripttext .= "\n" . 'google.maps.event.addDomListener(geoLocationButton'.$mapDivSuffix.', \'click\', function() {' ."\n";

                            if (((isset($map->placesenable) && (int)$map->placesenable == 1)
                                            &&(isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1))
                             || (isset($map->findcontrol) && (int)$map->findcontrol == 1) )
                            {
                                    if ((isset($map->placesenable) && (int)$map->placesenable == 1)
                                     && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1)
                                     && (isset($map->findcontrol) && (int)$map->findcontrol == 0))
                                    {
                                            if (isset($map->placesdirection) && (int)$map->placesdirection == 1)
                                            {
                                                    $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", placesDirectionsDisplay'.$mapDivSuffix.', placesDirectionsService'.$mapDivSuffix.', markerPlacesAC, "searchTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
                                            }
                                            else
                                            {
                                                    $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", placesDirectionsDisplay'.$mapDivSuffix.', placesDirectionsService'.$mapDivSuffix.', markerPlacesAC, "searchTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
                                            }
                                    }
                                    else if (isset($map->findcontrol) && (int)$map->findcontrol == 1)
                                    {
                                            if (isset($map->findroute) && (int)$map->findroute != 0)
                                            {
                                                    $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "findAddressTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
                                            }
                                            else
                                            {
                                                    $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind, "findAddressTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
                                            }
                                    }
                                    else
                                    {
                                            $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Other");' ."\n";                    
                                    }
                            }
                            else
                            {
                                    $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Other");' ."\n";
                            }

                            $scripttext .= '});' ."\n";

                            $scripttext .= 'map'.$mapDivSuffix.'.controls['.$controlPosition.'].push(';
                $scripttext .= 'document.getElementById(\'geoLocation'.$mapDivSuffix.'\'));' ."\n";
                    }

            }
            // Geo Location - end
            // End 2

            // Begin 3
            if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
            {
                    $controlPosition = '';
                    switch ((int)$map->markerlistpos) 
                    {
                            case 0:
                                    // None
                                    $controlPosition = '';
                            break;
                            case 1:
                                    $controlPosition = 'google.maps.ControlPosition.TOP_CENTER';
                            break;
                            case 2:
                                    $controlPosition = 'google.maps.ControlPosition.TOP_LEFT';
                            break;
                            case 3:
                                    $controlPosition = 'google.maps.ControlPosition.TOP_RIGHT';
                            break;
                            case 4:
                                    $controlPosition = 'google.maps.ControlPosition.LEFT_TOP';
                            break;
                            case 5:
                                    $controlPosition = 'google.maps.ControlPosition.RIGHT_TOP';
                            break;
                            case 6:
                                    $controlPosition = 'google.maps.ControlPosition.LEFT_CENTER';
                            break;
                            case 7:
                                    $controlPosition = 'google.maps.ControlPosition.RIGHT_CENTER';
                            break;
                            case 8:
                                    $controlPosition = 'google.maps.ControlPosition.LEFT_BOTTOM';
                            break;
                            case 9:
                                    $controlPosition = 'google.maps.ControlPosition.RIGHT_BOTTOM';
                            break;
                            case 10:
                                    $controlPosition = 'google.maps.ControlPosition.BOTTOM_CENTER';
                            break;
                            case 11:
                                    $controlPosition = 'google.maps.ControlPosition.BOTTOM_LEFT';
                            break;
                            case 12:
                                    $controlPosition = 'google.maps.ControlPosition.BOTTOM_RIGHT';
                            break;
                            case 111:
                                    $controlPosition = '';
                            break;
                            case 112:
                                    $controlPosition = '';
                            break;
                            case 113:
                                    $controlPosition = '';
                            break;
                            case 114:
                                    $controlPosition = '';
                            break;
                            case 120:
                                    $controlPosition = '';
                            break;
                            case 121:
                                    $controlPosition = '';
                            break;
                            default:
                                    $controlPosition = '';
                            break;
                    }

                    if ($controlPosition != "")    
                    {
                            $scripttext .= 'map'.$mapDivSuffix.'.controls['.$controlPosition.'].push(';
                            $scripttext .= 'document.getElementById(\'GMapsMarkerList'.$mapDivSuffix.'\'));' ."\n";
                    }
            }
            // End 3

            // Pushing controls - End

            if ($map->mapstyles != "")
            {
                    $scripttext .= 'var mapStyles = '.$map->mapstyles.';'."\n";

                    $scripttext .= 'map'.$mapDivSuffix.'.setOptions({styles: mapStyles});'."\n";
            }

            $scripttext .= 'var geocoder'.$mapDivSuffix.' = new google.maps.Geocoder();'."\n";

            if (isset($map->openstreet) && (int)$map->openstreet == 1)
            {

                    $scripttext .= ' map'.$mapDivSuffix.'.mapTypes.set(\'osm\', openStreetType);' ."\n";


                    if ((int)$currentMapTypeValue == 5)
                    {
                            $scripttext .= ' map'.$mapDivSuffix.'.setMapTypeId(\'osm\');' ."\n";
                    }
            }


            if (isset($map->opentopomap) && (int)$map->opentopomap == 1)
            {

                    $scripttext .= ' map'.$mapDivSuffix.'.mapTypes.set(\'opentopomap\', openTopoMapType);' ."\n";


                    if ((int)$currentMapTypeValue == 8)
                    {
                            $scripttext .= ' map'.$mapDivSuffix.'.setMapTypeId(\'opentopomap\');' ."\n";
                    }
            }

            if (isset($map->nztopomaps) && (int)$map->nztopomaps != 0)
            {

                    if ((int)$map->nztopomaps == 1
                    || (int)$map->nztopomaps == 11)
                    {
                            $scripttext .= ' map'.$mapDivSuffix.'.overlayMapTypes.insertAt(0, NZTopomapsType);' ."\n";
                            if ($needOverlayControl != 0)
                            {
                                if ((int)$map->nztopomaps == 11)
                                {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addOverlayMapType(NZTopomapsType);'."\n";
                                }
                            }                        
                    }
                    else
                    {
                            $scripttext .= ' map'.$mapDivSuffix.'.mapTypes.set(\'nztopomaps\', NZTopomapsType);' ."\n";


                            if ((int)$currentMapTypeValue == 6)
                            {
                                    $scripttext .= ' map'.$mapDivSuffix.'.setMapTypeId(\'nztopomaps\');' ."\n";
                            }
                    }

                    if ($credits != '')
                    {
                            $credits .= '<br />';
                    }
                    $credits .= 'NZ Topo50 '.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': ';
                    $credits .= '<a href="http://data.linz.govt.nz">Sourced from LINZ. CC BY 4.0</a>';

            }

            if ((int)$map->openstreet == 1)
            {
                    if ($credits != '')
                    {
                            $credits .= '<br />';
                    }
                    $credits .= 'OSM '.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': ';
                    $credits .= '<a href="'.$urlProtocol.'://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> '.Text::_('COM_ZHGOOGLEMAP_MAP_CONTRIBUTORS');
            }
            if ((int)$map->opentopomap == 1)
            {
                    if ($credits != '')
                    {
                            $credits .= '<br />';
                    }
                    $credits .= 'OpenTopoMap '.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': ';
                    $credits .= Text::_('COM_ZHGOOGLEMAP_MAP_MAPDATA').': <a href="'.$urlProtocol.'://openstreetmap.org/copyright">OpenStreetMap</a> '.Text::_('COM_ZHGOOGLEMAP_MAP_CONTRIBUTORS').', <a href="'.$urlProtocol.'://viewfinderpanoramas.org">SRTM</a> | '.Text::_('COM_ZHGOOGLEMAP_MAP_MAPSTYLE').': <a href="'.$urlProtocol.'://opentopomap.org">OpenTopoMap</a> (<a href="'.$urlProtocol.'://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
            }

            if ((int)$map->custommaptype != 0)
            {
				if (isset($maptypes) && !empty($maptypes)) {
                    foreach ($maptypes as $key => $currentmaptype)     
                    {
                            for ($i=0; $i < count($custMapTypeList); $i++)
                            {
                                    if ($currentmaptype->id == (int)$custMapTypeList[$i]
                                    && $currentmaptype->gettileurl != "")
                                    {
                                            if ((int)$currentmaptype->layertype == 1)
                                            {
                                                    $scripttext .= ' map'.$mapDivSuffix.'.overlayMapTypes.insertAt(0, customMapType'.$currentmaptype->id.');' ."\n";
                                                    if ($needOverlayControl != 0)
                                                    {
                                                        if ((int)$currentmaptype->opacitymanage == 1)
                                                        {
                                                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addOverlayMapType(customMapType'.$currentmaptype->id.');'."\n";
                                                        }
                                                    }
                                            }
                                            else
                                            {
                                                    $scripttext .= ' map'.$mapDivSuffix.'.mapTypes.set(\'customMapType'.$currentmaptype->id.'\', customMapType'.$currentmaptype->id.');' ."\n";

                                                    if ((int)$map->maptype == 7)
                                                    {
                                                            if (((int)$custMapTypeFirst != 0) && ((int)$custMapTypeFirst == $currentmaptype->id))
                                                            {
                                                                    $scripttext .= ' map'.$mapDivSuffix.'.setMapTypeId(\'customMapType'.$currentmaptype->id.'\');' ."\n";
                                                            }
                                                    }
                                            }
                                    }
                            }
                            // End loop by Enabled CustomMapTypes

                    }
                    // End loop by All CustomMapTypes
				}
            }



            if (isset($licenseinfo) && (int)$licenseinfo != 0) 
            {

                    if ((int)$licenseinfo == 114 // Map-License (into credits)
                      ) 
                    {
                            // Do not create button when L-M, M-L or external
                            if ($credits != '')
                            {
                                    $credits .= '<br />';
                            }
                            $credits .= ''.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': ';
                            $credits .= '<a href="'.$urlProtocol.'://zhuk.cc/" target="_blank" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').'">zhuk.cc</a>';
                    }
                    else
                    {
                            $scripttext .= 'function LicenseControl(controlDiv, map) {' ."\n";

                            // We set up a variable for the 'this' keyword since we're adding event
                            // listeners later and 'this' will be out of scope.
                            $scripttext .= '  var control = this;' ."\n";

                            // Set CSS styles for the DIV containing the control
                            // Setting padding to 5 px will offset the control
                            // from the edge of the map'.$mapDivSuffix.'.
                            $scripttext .= '  controlDiv.style.padding = \'5px\';' ."\n";

                            // Set CSS for the control border.
                            $scripttext .= '  var controlUI = document.createElement(\'DIV\');' ."\n";
                            $scripttext .= '  controlUI.title = "'.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': zhuk.cc";' ."\n";
                            $scripttext .= '  controlDiv.appendChild(controlUI);' ."\n";

                            // Set CSS for the control interior.
                            $scripttext .= '  var controlText = document.createElement(\'DIV\');' ."\n";

                            $scripttext .= '  controlText.innerHTML = \'<a href="'.$urlProtocol.'://zhuk.cc/" target="_blank" alt="'.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': zhuk.cc"><img style="border: 0px none; padding: 0px; margin: 0px; width: 60px; height: 53px;" src="'.$imgpathUtils.'ZhukLogo.png"></a>\';';

                            $scripttext .= '  controlUI.appendChild(controlText);' ."\n";

                            $scripttext .= '}' ."\n";

                            $scripttext .= '  var licenseControlDiv = document.createElement(\'DIV\');' ."\n";
                            $scripttext .= '  var licenseControl = new LicenseControl(licenseControlDiv, map'.$mapDivSuffix.');' ."\n";

                            $scripttext .= '  licenseControlDiv.index = 1;' ."\n";

                            $controlPosition ="";
                            if (isset($licenseinfo)) 
                            {
                                    switch ($licenseinfo) 
                                    {
                                            case 0:
                                            break;
                                            case 1:
                                                            $controlPosition = 'google.maps.ControlPosition.TOP_CENTER';
                                            break;
                                            case 2:
                                                            $controlPosition = 'google.maps.ControlPosition.TOP_LEFT';
                                            break;
                                            case 3:
                                                            $controlPosition = 'google.maps.ControlPosition.TOP_RIGHT';
                                            break;
                                            case 4:
                                                            $controlPosition = 'google.maps.ControlPosition.LEFT_TOP';
                                            break;
                                            case 5:
                                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_TOP';
                                            break;
                                            case 6:
                                                            $controlPosition = 'google.maps.ControlPosition.LEFT_CENTER';
                                            break;
                                            case 7:
                                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_CENTER';
                                            break;
                                            case 8:
                                                            $controlPosition = 'google.maps.ControlPosition.LEFT_BOTTOM';
                                            break;
                                            case 9:
                                                            $controlPosition = 'google.maps.ControlPosition.RIGHT_BOTTOM';
                                            break;
                                            case 10:
                                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_CENTER';
                                            break;
                                            case 11:
                                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_LEFT';
                                            break;
                                            case 12:
                                                            $controlPosition = 'google.maps.ControlPosition.BOTTOM_RIGHT';
                                            break;
                                            default:
                                                    $controlPosition = '';
                                            break;
                                    }
                            }

                            if ($controlPosition != "")
                            {
                                    $scripttext .= '  map'.$mapDivSuffix.'.controls['.$controlPosition.'].push(licenseControlDiv);' ."\n";
                            }

                    }

            }


            if (((isset($map->elevation) && (int)$map->elevation == 1))
               || $featurePathElevation == 1 || $featurePathElevationKML == 1)
            {    
                    // Create an ElevationService
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enableElevation('."\n";
                    $scripttext .= '  "'.Text::_('COM_ZHGOOGLEMAP_MAP_ELEVATION').'"'."\n";
                    $scripttext .= ' ,"'.Text::_('COM_ZHGOOGLEMAP_MAP_ELEVATION1').'"'."\n";
                    $scripttext .= ' ,"'.Text::_('COM_ZHGOOGLEMAP_MAP_ELEVATION12M').'"'."\n";
                    $scripttext .= ' ,"'.Text::_('COM_ZHGOOGLEMAP_MAP_ELEVATION_NO_DATA').'"'."\n";
                    $scripttext .= ' ,"'.Text::_('COM_ZHGOOGLEMAP_MAP_ELEVATION_FAILED').'"'."\n";
                    $scripttext .= ');' ."\n";
            }
            // Elevation feature
            if ((isset($map->elevation) && (int)$map->elevation == 1))
            {    
                    // Add a listener for the click event and call get elevation on that location
                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlaceElevation();' ."\n";
            }        

            // Create Placemark for Insert Users Placemarks - Begin
            //UserMarker - begin
            if ($allowUserMarker == 1
             && (((int)$map->usermarkersinsert == 1) || (int)$map->usermarkersupdate == 1))
            {        
                    $db = Factory::getDBO();

                    $query = $db->getQuery(true);

                    $query->select('h.title as text, h.id as value ');
                    $query->from('#__zhgooglemaps_markergroups as h');
                    $query->leftJoin('#__categories as c ON h.catid=c.id');
                    $query->where('1=1');
                    // get all groups, because you can add marker and disable group
                    //$query->where('h.published=1');
                    $query->order('h.title');

                    $db->setQuery($query);    

					try {
						$db->execute();
						$newMarkerGroupList = $db->loadObjectList();
					} catch (ExecutionFailureException $e) {
						throw new \Exception("Error (Load Group List Item): " . $e->getMessage(), 500);
					} 


                    // icon type
                    $scripttext .= 'var contentInsertPlacemarkIcon = "" +' ."\n";
                    if (isset($map->usermarkersicon) && (int)$map->usermarkersicon == 1) 
                    {
                            $iconTypeJS = " onchange=\"javascript: ";
                            $iconTypeJS .= " if (document.forms.insertPlacemarkForm.markerimage.options[selectedIndex].value!=\'\') ";
                            $iconTypeJS .= " {document.markericonimage.src=\'".$imgpathIcons."\' + document.forms.insertPlacemarkForm.markerimage.options[selectedIndex].value.replace(/#/g,\'%23\') + \'.png\'}";
                            $iconTypeJS .= " else ";
                            $iconTypeJS .= " {document.markericonimage.src=\'\'}\"";

                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_ICON_TYPE' ).' \'+' ."\n";
                            $scripttext .= ' \'';
                            $scripttext .= '<img name="markericonimage" src="" alt="" />';
                            $scripttext .= '\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= ' \'';
                            $scripttext .= str_replace('.png<', '<', 
                                                                    str_replace('.png"', '"', 
                                                                            str_replace('JOPTION_SELECT_IMAGE', Text::_('COM_ZHGOOGLEMAP_MAP_USER_IMAGESELECT'),
                                                                                    str_replace(array("\r", "\r\n", "\n"),'', HTMLHelper::_('list.images',  'markerimage', $active =  "", $iconTypeJS, $directoryIcons, $extensions =  "png")))));
                            $scripttext .= '\'+' ."\n";

                            $scripttext .= '    \'<br />\';' ."\n";
                    }
                    else
                    {
                            $scripttext .= '    \'<input name="markerimage" type="hidden" value="default#" />\'+' ."\n";    
                            $scripttext .= '    \'\';' ."\n";
                    }

            }

            if ($allowUserMarker == 1 && (int)$map->usermarkersinsert == 1)
            {        
                    $scripttext .= 'var  latlngInsertPlacemark;' ."\n";
                    $scripttext .= 'var  insertPlacemark = new google.maps.Marker({' ."\n";
                    $scripttext .= '      draggable:true, ' ."\n";
                    $scripttext .= '      map: map'.$mapDivSuffix.', ' ."\n";
                    $scripttext .= '      animation: google.maps.Animation.DROP' ."\n";
                    $scripttext .= '  });'."\n";

                    $scripttext .= 'insertPlacemark.title = "'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_NEWMARKER' ).'";' ."\n";
                    $scripttext .= 'insertPlacemark.description = "'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_NEWMARKER_DESC' ).'";' ."\n";

                    $scripttext .= 'var contentInsertPlacemarkPart1 = \'<div id="contentInsertPlacemark">\' +' ."\n";
                    $scripttext .= '\'<'.$placemarkTitleTag.' id="headContentInsertPlacemark" class="insertPlacemarkHead">'.
                            '<img src="'.$imgpathUtils.'published'.(int)$map->usermarkerspublished.'.png" alt="" /> '.
                            Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_NEWMARKER' ).'</'.$placemarkTitleTag.'>\'+' ."\n";
                    $scripttext .= '\'<div id="bodyContentInsertPlacemark"  class="insertPlacemarkBody">\'+'."\n";
                    //$scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_LNG' ).' \'+current.lng() + ' ."\n";
                    //$scripttext .= '    \'<br />'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_LAT' ).' \'+current.lat() + ' ."\n";
                    $scripttext .= '    \'<form id="insertPlacemarkForm" action="'.URI::current().'" method="post">\'+'."\n";

                    // Begin Placemark Properties
                    $scripttext .= '\'<div id="bodyInsertPlacemarkDivA"  class="bodyInsertProperties">\'+'."\n";
                    $scripttext .= '\'<a id="bodyInsertPlacemarkA" href="javascript:showonlyone(\\\'Placemark\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'collapse.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_PROPERTIES' ).'</a>\'+'."\n";
                    $scripttext .= '\'</div>\'+'."\n";
                    $scripttext .= '\'<div id="bodyInsertPlacemark"  class="bodyInsertPlacemarkProperties">\'+'."\n";
                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_NAME' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \'<input name="markername" type="text" maxlength="250" size="50" />\'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_DESCRIPTION' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \'<input name="markerdescription" type="text" maxlength="250" size="50" />\'+' ."\n";
                    $scripttext .= '    \'<br />\';' ."\n";


                    $scripttext .= 'var contentInsertPlacemarkPart2 = "" +' ."\n";
                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BALOON' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \' <select name="markerbaloon" > \'+' ."\n";
                    $scripttext .= '    \' <option value="1" selected="selected">'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_BALOON_DROP').'</option> \'+' ."\n";
                    $scripttext .= '    \' <option value="2" >'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_BALOON_BOUNCE').'</option> \'+' ."\n";
                    $scripttext .= '    \' <option value="3" >'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_BALOON_SIMPLE').'</option> \'+' ."\n";
                    $scripttext .= '    \' </select> \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_USER_MARKERCONTENT' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \' <select name="markermarkercontent" > \'+' ."\n";
                    $scripttext .= '    \' <option value="0" selected="selected">'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE_DESC').'</option> \'+' ."\n";
                    $scripttext .= '    \' <option value="1" >'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE').'</option> \'+' ."\n";
                    $scripttext .= '    \' <option value="2" >'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_MARKERCONTENT_DESCRIPTION').'</option> \'+' ."\n";
                    $scripttext .= '    \' <option value="100" >'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_MARKERCONTENT_NONE').'</option> \'+' ."\n";
                    $scripttext .= '    \' </select> \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_HREFIMAGE_LABEL' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \'<input name="markerhrefimage" type="text" maxlength="500" size="50" value="" />\'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '\'</div>\'+'."\n";
                    // End Placemark Properties

                    // Begin Placemark Group Properties
                    $scripttext .= '\'<div id="bodyInsertPlacemarkGrpDivA"  class="bodyInsertProperties">\'+'."\n";
                    $scripttext .= '\'<a id="bodyInsertPlacemarkGrpA" href="javascript:showonlyone(\\\'PlacemarkGroup\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'</a>\'+'."\n";
                    $scripttext .= '\'</div>\'+'."\n";
                    $scripttext .= '\'<div id="bodyInsertPlacemarkGrp"  class="bodyInsertPlacemarkGrpProperties">\'+'."\n";
                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_GROUP' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \' <select name="markergroup" > \'+' ."\n";
                    $scripttext .= '    \' <option value="" selected="selected">'.Text::_( 'COM_ZHGOOGLEMAP_MAPMARKER_FILTER_PLACEMARK_GROUP').'</option> \'+' ."\n";
                    foreach ($newMarkerGroupList as $key => $newGrp) 
                    {
                            $scripttext .= '    \' <option value="'.$newGrp->value.'">'.$newGrp->text.'</option> \'+' ."\n";
                    }
                    $scripttext .= '    \' </select> \'+' ."\n";

                    $scripttext .= '    \'<br />\'+' ."\n";

                    $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CATEGORY' ).' \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \' <select name="markercatid" > \'+' ."\n";
                    $scripttext .= '    \' <option value="" selected="selected">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_FILTER_CATEGORY').'</option> \'+' ."\n";
                    $scripttext .= '    \''.str_replace(array("\r", "\r\n", "\n"),'', 
                                           HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_zhgooglemap'), 'value', 'text', '')) .
                                                               '\'+' ."\n";
                    $scripttext .= '    \' </select> \'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '    \'<br />\'+' ."\n";
                    $scripttext .= '\'</div>\'+'."\n";
                    // End Placemark Group Properties

                    // Begin Contact Properties
                    if (isset($map->usercontact) && (int)$map->usercontact == 1) 
                    {

                            $scripttext .= '\'<div id="bodyInsertContactDivA"  class="bodyInsertProperties">\'+'."\n";
                            $scripttext .= '\'<a id="bodyInsertContactA" href="javascript:showonlyone(\\\'Contact\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PROPERTIES' ).'</a>\'+'."\n";
                            $scripttext .= '\'</div>\'+'."\n";
                            $scripttext .= '\'<div id="bodyInsertContact"  class="bodyInsertContactProperties">\'+'."\n";
                            $scripttext .= '\'<img src="'.$imgpathUtils.'published'.(int)$map->usercontactpublished.'.png" alt="" /> \'+'."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_NAME' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactname" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_POSITION' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactposition" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PHONE' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactphone" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_MOBILE' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactmobile" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_FAX' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactfax" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_EMAIL' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactemail" type="text" maxlength="250" size="50" />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<input name="contactid" type="hidden" value="" />\'+' ."\n";
                            $scripttext .= '\'</div>\'+'."\n";
                            // Contact Address
                            $scripttext .= '\'<div id="bodyInsertContactAdrDivA"  class="bodyInsertProperties">\'+'."\n";
                            $scripttext .= '\'<a id="bodyInsertContactAdrA" href="javascript:showonlyone(\\\'ContactAddress\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'</a>\'+'."\n";
                            $scripttext .= '\'</div>\'+'."\n";
                            $scripttext .= '\'<div id="bodyInsertContactAdr"  class="bodyInsertContactAdrProperties">\'+'."\n";
                            $scripttext .= '    \''.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS' ).' \'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<textarea name="contactaddress" cols="35" rows="4"></textarea>\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '    \'<br />\'+' ."\n";
                            $scripttext .= '\'</div>\'+'."\n";
                    }
                    // End Contact Properties

                    $scripttext .= '\'\';'."\n";


                    $scripttext .= '    google.maps.event.addListener(insertPlacemark, \'drag\', function(event) {' ."\n";
                    $scripttext .= '        infowindow'.$mapDivSuffix.'.close();' ."\n";
                    $scripttext .= '      latlngInsertPlacemark = event.latLng;' ."\n";

                    $scripttext .= '    });' ."\n";

                    $scripttext .= '    google.maps.event.addListener(insertPlacemark, \'click\', function(event) {' ."\n";
                    $scripttext .= '        latlngInsertPlacemark = event.latLng;' ."\n";

                    $scripttext .= '  contentInsertPlacemarkButtons = \'<div id="contentInsertPlacemarkButtons">\' +' ."\n";
                    $scripttext .= '    \'<hr />\'+' ."\n";                    
                    $scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlngInsertPlacemark.lat() + \'" />\'+' ."\n";
                    $scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlngInsertPlacemark.lng() + \'" />\'+' ."\n";
                    $scripttext .= '    \'<input name="marker_action" type="hidden" value="insert" />\'+' ."\n";    
                    $scripttext .= '    \'<input name="markersubmit" type="submit" value="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BUTTON_ADD' ).'" />\'+' ."\n";
                    $scripttext .= '    \'</form>\'+' ."\n";        
                    $scripttext .= '\'</div>\'+'."\n";
                    $scripttext .= '\'</div>\';'."\n";

                    $scripttext .= '          infowindow'.$mapDivSuffix.'.setContent(contentInsertPlacemarkPart1+';
                    $scripttext .= 'contentInsertPlacemarkIcon+';
                    //$scripttext .= 'contentInsertPlacemarkIcon.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", "default#").'.png"\')+';
                    $scripttext .= 'contentInsertPlacemarkPart2+';
                    $scripttext .= 'contentInsertPlacemarkButtons);' ."\n";
                    $scripttext .= '          infowindow'.$mapDivSuffix.'.setPosition(latlngInsertPlacemark);' ."\n";
                    $scripttext .= '          infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                    $scripttext .= '    });' ."\n";

                    $scripttext .= '    google.maps.event.addListener(map'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                    $scripttext .= '        infowindow'.$mapDivSuffix.'.close();' ."\n";
                    $scripttext .= '      latlngInsertPlacemark = event.latLng;' ."\n";
                    $scripttext .= '        insertPlacemark.setPosition(latlngInsertPlacemark);' ."\n";

                    $scripttext .= '    });' ."\n";

            }
            // New Marker - End


            // Create Placemark for Insert Users Placemarks - End

            if (isset($map->balloon)) 
            {

                    $scripttext .= 'var contentString'.$mapDivSuffix.' = \'<div id="placemarkContent" class="placemarkContent" >\' +' ."\n";
                    $scripttext .= '\'<'.$placemarkTitleTag.' id="headContent" class="placemarkHead">'.htmlspecialchars(str_replace('\\', '/', $map->title), ENT_QUOTES, 'UTF-8').'</'.$placemarkTitleTag.'>\'+' ."\n";
                    $scripttext .= '\'<div id="bodyContent"  class="placemarkBody">\'+'."\n";
                    $scripttext .= '\''.htmlspecialchars(str_replace('\\', '/', $map->description) , ENT_QUOTES, 'UTF-8').'\'+'."\n";
                    $scripttext .= '\'</div>\'+'."\n";

                    // 06.12.2017 Added link to Google map page like for placemark
                    if ((int)$map->gogoogle_map == 10 || (int)$map->gogoogle_map == 11 
                      ||(int)$map->gogoogle_map == 30 || (int)$map->gogoogle_map == 31
                      ||(int)$map->gogoogle_map == 12 || (int)$map->gogoogle_map == 13
                      ||(int)$map->gogoogle_map == 14 || (int)$map->gogoogle_map == 15
                      ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33
                      ||(int)$map->gogoogle_map == 34 || (int)$map->gogoogle_map == 35
                    )
                    {    

                            if ((int)$map->gogoogle_map == 10 
                             || (int)$map->gogoogle_map == 30
                             || (int)$map->gogoogle_map == 12
                             || (int)$map->gogoogle_map == 14
                             || (int)$map->gogoogle_map == 32
                             || (int)$map->gogoogle_map == 34
                            )
                            {
                                    $linkTarget = " target=\"_blank\"";
                            }
                            else
                            {
                                    $linkTarget = "";
                            }




                            $scripttext .= '\'<div id="bodyContentGoGoogle" class="placemarkBodyGoGoogle">\'+'."\n";                
                            $scripttext .= '\'<p><a class="placemarkGOGOOGLE" href="';

                            if ((int)$map->gogoogle_map == 12 || (int)$map->gogoogle_map == 13
                              ||(int)$map->gogoogle_map == 14 || (int)$map->gogoogle_map == 15
                              ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33
                              ||(int)$map->gogoogle_map == 34 || (int)$map->gogoogle_map == 35
                            )
                            {
                                $scripttext .= 'https://maps.google.com/?ll='.
                                                $map->latitude.','.$map->longitude;    
                                $scripttext .= '&amp;z='.$map->zoom; 
                                if ((int)$map->gogoogle_map == 12 || (int)$map->gogoogle_map == 13
                                   ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33)
                                {
                                    $scripttext .= '&amp;q='.htmlspecialchars(str_replace('\\', '/', $map->title) , ENT_QUOTES, 'UTF-8');
                                }
                                else
                                {
                                    $scripttext .= '&amp;q='.$map->latitude.','.$map->longitude;    
                                }

                                if ($main_lang_little != "")
                                {
                                    $scripttext .= '&amp;hl='.$main_lang_little;    
                                }
                            }
                            else
                            {
                                $scripttext .= 'https://maps.google.com/maps?saddr=Current%20Location&amp;daddr='.
                                                $map->latitude.','.$map->longitude;                            
                            }


                            $scripttext .= '" '.$linkTarget.' title="'.$fv_override_gogoogle_text.
                                    '">'.$fv_override_gogoogle_text.'</a></p>\'+'."\n";
                            $scripttext .= '\'</div>\'+'."\n";

                    }                   
                    $scripttext .= '\'</div>\';'."\n";


                    if ((int)$map->balloon != 0) 
                    {
                            $scripttext .= '  var marker'.$mapDivSuffix.' = new google.maps.Marker({' ."\n";
                            $scripttext .= '      position: latlng'.$mapDivSuffix.', ' ."\n";

                            if ((isset($map->markercluster) && (int)$map->markercluster == 0))
                            {
                                            $scripttext .= '      map: map'.$mapDivSuffix.', ' ."\n";
                            }

                            switch ($map->balloon) 
                            {
                                                    case 1:
                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                    break;
                                                    case 2:
                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                    break;
                                                    case 3:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                            }

                            // Replace to new, because all charters are shown
                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $map->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";
                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $map->title)).'"' ."\n";
                            $scripttext .= '});'."\n";

                            $scripttext .= '  google.maps.event.addListener(marker'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'.$mapDivSuffix.');' ."\n";
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'.$mapDivSuffix.');' ."\n";
                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                            $scripttext .= '    });' ."\n";

                            if ($zhgmObjectManager != 0)
                            {
                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd(0, 0, marker'.$mapDivSuffix.', null);'."\n";
                            }


                }

                // Overrides map center
                if ($ajaxLoadObjects != 0) {
                    if ($currentPlacemarkCenter != "do not change") {
                        $curcenterLatLng = MapPlacemarksHelper::get_placemark_coordinates((int)$currentPlacemarkCenter);

                        if ($curcenterLatLng != "") {
                            if ($curcenterLatLng != "geocode") {
                                $scripttext .= 'latlng'.$mapDivSuffix.' = '.$curcenterLatLng.';'."\n";
                                $scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";    
                                $scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.$mapDivSuffix.');'."\n";
                                if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
                                {
                                    //if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                                    //{
                                        $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(latlng'.$mapDivSuffix.');'."\n";
                                    //}
                                }
                            }   
                        }

                    }
                }

                if ((int)$map->openballoon == 1)
                {
                    if ((int)$map->balloon != 0)
                    {
                            $scripttext .= '  google.maps.event.trigger(marker'.$mapDivSuffix.', "click");' ."\n";
                    }
                    else
                    {
                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'.$mapDivSuffix.');' ."\n";
                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'.$mapDivSuffix.');' ."\n";
                        $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                    }
                }

            }

            // Creating Clusters in the beginning for using in geocoding
            if ((isset($map->markercluster) && (int)$map->markercluster == 1))
            {      

                    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                    {
                            $this->markercluster = 1;
                    }
                    else
                    {
                            $markercluster = 1;
                    }


                    $clustererOptions = 'imagePath: icoUtils+\'m\'' ."\n";

                    if ((int)$map->clusterzoom == 0)
                    {
                            $scripttext .= 'markerCluster0 = new MarkerClusterer(map'.$mapDivSuffix.', [], {'.$clustererOptions.'});' ."\n";
                    }
                    else
                    {
                            $scripttext .= 'markerCluster0 = new MarkerClusterer(map'.$mapDivSuffix.', [], { maxZoom: '.$map->clusterzoom.', '.$clustererOptions.'});' ."\n";
                    }

                    if ($zhgmObjectManager != 0)
                    {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.ClusterAdd(0, markerCluster0);' ."\n";
                    }

            if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                    {

                            if (isset($markergroups) && !empty($markergroups)) 
                            {
                                    foreach ($markergroups as $key => $currentmarkergroup) 
                                    {
                                            $clustererOptions = 'imagePath: icoUtils+\'m\'' ."\n";

                                            if ((int)$currentmarkergroup->overridegroupicon == 1)
                                            {
                                                    $imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarkergroup->icontype).'.png';
                                                    $imgimg4size = $imgpath4size.$currentmarkergroup->icontype.'.png';

                                                    list ($imgwidth, $imgheight) = getimagesize($imgimg4size);

                                                    $markergroupstyle = ', styles: [{' ."\n";
                                                    $markergroupstyle .='height: '.$imgheight.',' ."\n";
                                                    $markergroupstyle .='width: '.$imgwidth.',' ."\n";
                                                    $markergroupstyle .='url: "'.$imgimg.'"' ."\n";
                                                    $markergroupstyle .='}]' ."\n";

                                                    $clustererOptions .= $markergroupstyle;
                                            }


                                            if ((int)$map->clusterzoom == 0)
                                            {
                                                    if ((int)$currentmarkergroup->overridegroupicon == 1)
                                                    {
                                                            $scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', [], {'.$clustererOptions.'});' ."\n";
                                                    }
                                                    else
                                                    {
                                                            $scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', [], {'.$clustererOptions.'});' ."\n";
                                                            //$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', []);' ."\n";
                                                    }
                                            }
                                            else
                                            {
                                                    if ((int)$currentmarkergroup->overridegroupicon == 1)
                                                    {
                                                            $scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', [], { maxZoom: '.$map->clusterzoom.','."\n".$clustererOptions.'});' ."\n";
                                                    }
                                                    else
                                                    {
                                                            $scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', [], { maxZoom: '.$map->clusterzoom.','."\n".$clustererOptions.'});' ."\n";
                                                            //$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', [], { maxZoom: '.$map->clusterzoom.'});' ."\n";
                                                    }
                                            }

                                            if ($zhgmObjectManager != 0)
                                            {
                                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.ClusterAdd('.$currentmarkergroup->id.', markerCluster'.$currentmarkergroup->id.');' ."\n";
                                            }
                                    }
                            }

                    }

            }


            if ($featureSpider != 0) 
            {

                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkSpiderManagement();'."\n";

                    $scripttext .= '    google.maps.event.addListener(map'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                    $scripttext .= '        zhgmObjMgr'.$mapDivSuffix.'.RestoreSpidered();' ."\n";
                    //$scripttext .= '        alert("map: click");' ."\n";
                    $scripttext .= '    });' ."\n";

                    $scripttext .= '    google.maps.event.addListener(map'.$mapDivSuffix.', \'zoom_changed\', function(event) {' ."\n";
                    $scripttext .= '        zhgmObjMgr'.$mapDivSuffix.'.RestoreSpidered();' ."\n";
                    //$scripttext .= '        alert("map: zoom_changed");' ."\n";
                    $scripttext .= '    });' ."\n";

                    $scripttext .= '    google.maps.event.addListener(map'.$mapDivSuffix.', \'projection_changed\', function(event) {' ."\n";
                    $scripttext .= '        zhgmObjMgr'.$mapDivSuffix.'.RestoreSpidered();' ."\n";
                    //$scripttext .= '        alert("map: projection_changed");' ."\n";
                    $scripttext .= '    });' ."\n";

            }

            if (isset($map->closepopuponclick) && (int)$map->closepopuponclick != 0)
            {
                    if ($zhgmObjectManager != 0)
                    {
                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enableClosePopupOnClick();'."\n";
                    }
                    
                    $scripttext .= 'google.maps.event.addListener(map'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                    // Close the other infobubbles
                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                    $scripttext .= '  }' ."\n";
                    //$scripttext .= '        alert("map: click");' ."\n";
                    $scripttext .= '});' ."\n";
                    
            }
            if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
            {
                    if ((int)$map->markerlistcontent < 100) 
                    {
                            $tmp_str_message = Text::_('COM_ZHGOOGLEMAP_MAP_MARKERUL_NOTFIND');
                            if ($mapDivSuffix != "")
                            {
                                    $tmp_str4replace = 'GMapsMarkerUL'.$mapDivSuffix;
                                    $tmp_str2replace = 'GMapsMarkerUL';
                                    $tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);
                            }

                            $scripttext .= 'var markerUL = document.getElementById("GMapsMarkerUL'.$mapDivSuffix.'");'."\n";
                            $scripttext .= 'if (!markerUL)'."\n";
                            $scripttext .= '{'."\n";
                            $scripttext .= ' alert("'.$tmp_str_message .'");'."\n";
                            $scripttext .= '}'."\n";
                    }
                    else
                    {
                            $tmp_str_message = Text::_('COM_ZHGOOGLEMAP_MAP_MARKERTABLE_NOTFIND');
                            if ($mapDivSuffix != "")
                            {
                                    $tmp_str4replace = 'GMapsMarkerTABLEBODY'.$mapDivSuffix;
                                    $tmp_str2replace = 'GMapsMarkerTABLEBODY';
                                    $tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);
                            }


                            $scripttext .= 'var markerUL = document.getElementById("GMapsMarkerTABLEBODY'.$mapDivSuffix.'");'."\n";
                            $scripttext .= 'if (!markerUL)'."\n";
                            $scripttext .= '{'."\n";
                            $scripttext .= ' alert("'.$tmp_str_message.'");'."\n";
                            $scripttext .= '}'."\n";
                    }

            }

            // External Group Control
            if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 10) 
            {
                            $tmp_str_message = Text::_('COM_ZHGOOGLEMAP_MAP_GROUPDIV_NOTFIND');

                            if ($mapDivSuffix != "")
                            {

                                    $tmp_str4replace = 'GMapsGroupDIV'.$mapDivSuffix;
                                    $tmp_str2replace = 'GMapsGroupDIV';
                                    $tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);

                            }

                            $scripttext .= 'var groupDivTag = document.getElementById("GMapsGroupDIV'.$mapDivSuffix.'");'."\n";
                            $scripttext .= 'if (!groupDivTag)'."\n";
                            $scripttext .= '{'."\n";
                            $scripttext .= ' alert("'.$tmp_str_message.'");'."\n";
                            $scripttext .= '}'."\n";
                            $scripttext .= 'else'."\n";
                            $scripttext .= '{'."\n";
                            $scripttext .= ' groupDivTag.innerHTML = \''.str_replace('\'', '\\\'', str_replace(array("\r", "\r\n", "\n"),'', $divmarkergroup)).'\';'."\n";
                            $scripttext .= '}'."\n";
            }


            // Markers


            if (isset($markers) && !empty($markers)) 
            {
                    //$scripttext .= '    alert("$map->markercluster='. $map->markercluster.'");'."\n";
                    //$scripttext .= '    alert("$map->markerclustergroup='. $map->markerclustergroup.'");'."\n";
                    //$scripttext .= '    alert("$map->markergroupcontrol='. $map->markergroupcontrol.'");'."\n";

                    // Main loop
                    foreach ($markers as $key => $currentmarker) 
                    {

                            //$scripttext .= '    alert("try marker '. $currentmarker->id.'");'."\n";
                            //$scripttext .= '    alert("$currentmarker->publishedgroup='. $currentmarker->publishedgroup.'");'."\n";

                    // Begin restriction 
                            if (
                                    ((($currentmarker->markergroup != 0)
                                            && ((int)$currentmarker->published == 1)
                                            && ((int)$currentmarker->publishedgroup == 1)) || ($allowUserMarker == 1)
                                    ) || 
                                    ((($currentmarker->markergroup == 0)
                                            && ((int)$currentmarker->published == 1)) || ($allowUserMarker == 1)
                                    ) 
                            )
                            {

                                    //$scripttext .= '    alert("Work on marker '. $currentmarker->id.'");'."\n";
                                    $scripttext .= 'var titlePlacemark'. $currentmarker->id.' = "'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'";'."\n";
                                if (($currentmarker->latitude != "" && $currentmarker->longitude != "")
                                       ||($currentmarker->addresstext != ""))
                                    {
                                            if ($currentmarker->latitude != "" && $currentmarker->longitude != "")
                                            {
                                                    $scripttext .= 'var latlng'. $currentmarker->id.' = new google.maps.LatLng('.$currentmarker->latitude.', ' .$currentmarker->longitude.');' ."\n";

                                                    if (isset($map->auto_center_zoom) && ((int)$map->auto_center_zoom !=0))
                                                    {
                                                        $scripttext .= 'map_bounds'.$mapDivSuffix.'.extend(latlng'. $currentmarker->id.');' ."\n";
                                                    }
                                                    // Begin marker creation with lat,lng
                                                    // contentString - Begin
                                                    $scripttext .= 'var contentString'. $currentmarker->id.' = "";'."\n";
                                                    if (($allowUserMarker == 0)
                                                     || ((int)$map->usermarkersupdate == 0)
                                                     || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                     || ($currentUserID == 0)
                                                     || (isset($currentmarker->createdbyuser) 
                                                        && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                               || ((int)$currentmarker->createdbyuser == 0)))
                                                     )
                                                    {
                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                            {
                                                                    // do not create content string, create by loop only in the end
                                                            }
                                                            else
                                                            {
                                                                    if (((int)$currentmarker->actionbyclick == 1)
                                                                            ||
                                                                            (((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
                                                                            ||  (($managePanelInfowin == 1) && (((int)$currentmarker->actionbyclick == 1) || (int)$currentmarker->actionbyclick == 4))
                                                                            )
                                                                    {
                                                                            if ($managePanelInfowin == 1)                    
                                                                            {
                                                                                    if ((int)$currentmarker->actionbyclick == 1)
                                                                                    {                                        
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_content_string(
                                                                                                            $mapDivSuffix, 
                                                                                                            $currentmarker, $map->usercontact, $map->useruser,
                                                                                                            $userContactAttrs, $service_DoDirection,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                            $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                            $fv_placemark_date_fmt);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }
                                                                                    else if ((int)$currentmarker->actionbyclick == 4)
                                                                                    {
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_tabs_content_string(
                                                                                                            $mapDivSuffix, $currentmarker,
                                                                                                            MapPlacemarksHelper::get_placemark_content_string(
                                                                                                                    $mapDivSuffix, 
                                                                                                                    $currentmarker, $map->usercontact, $map->useruser,
                                                                                                                    $userContactAttrs, $service_DoDirection,
                                                                                                                    $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                                    $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                                    $fv_placemark_date_fmt),
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }
                                                                            }
                                                                            else
                                                                            {
                                                                                    if (((int)$currentmarker->actionbyclick == 1)
                                                                                    ||
                                                                                    (((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
                                                                                    )
                                                                                    {
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_content_string(
                                                                                                            $mapDivSuffix, 
                                                                                                            $currentmarker, $map->usercontact, $map->useruser,
                                                                                                            $userContactAttrs, $service_DoDirection,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                            $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                            $fv_placemark_date_fmt);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }

                                                                            }                                    


                                                                    }
                                                            }
                                                    }
                                                    else
                                                    {
                                                            // contentString - User Placemark can Update - Begin
                                                            $scripttext .= MapPlacemarksHelper::get_placemark_content_update_string(
                                                                                                            $map->usermarkersicon, 
                                                                                                            $map->usercontact, 
                                                                                                            $currentmarker,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons,
                                                                                                            $newMarkerGroupList
                                                                                                            );
                                                            // contentString - User Placemark can Update - End
                                                    }

                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                    {
                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                            {
                                                                    // do not create content string, create by loop only in the end
                                                            }
                                                            else
                                                            {
                                                                    if ((int)$map->hovermarker == 1
                                                                      ||(int)$map->hovermarker == 2)
                                                                    {
                                                                            if ($currentmarker->hoverhtml != "")
                                                                            {
                                                                                    $scripttext .= 'var hoverString'. $currentmarker->id.' = '.
                                                                                            MapPlacemarksHelper::get_placemark_hover_string(
                                                                                                    $currentmarker);                                    
                                                                            }
                                                                    }
                                                            }
                                                    }

                                                    if ((int)$currentmarker->baloon != 0) 
                                                    {                            
                                                            if ((int)$currentmarker->baloon == 21
                                                             || (int)$currentmarker->baloon == 22
                                                             || (int)$currentmarker->baloon == 23
                                                             )
                                                            {
                                                                    $scripttext .= 'var marker'. $currentmarker->id.' = new MarkerWithLabel({' ."\n";

                                                                    if ($currentmarker->labelcontent != "")
                                                                    {
                                                                            if ($currentmarker->labelclass != "")
                                                                            {
                                                                                    $scripttext .= '  labelClass: "'. $currentmarker->labelclass .'", '."\n";
                                                                            }
                                                                            if ((int)$currentmarker->labelanchorx != 0
                                                                            || ((int)$currentmarker->labelanchory != 0))
                                                                            {
                                                                                    $scripttext .= '  labelAnchor: new google.maps.Point('. (int)$currentmarker->labelanchorx .', '.(int)$currentmarker->labelanchory .'), '."\n";
                                                                            }
                                                                            if ($currentmarker->labelcontent != "")
                                                                            {
                                                                                    $scripttext .= '  labelContent: \''; 
                                                                                    $scripttext .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->labelcontent));
                                                                                    $scripttext .= '\', '."\n";
                                                                            }
                                                                            if ((int)$currentmarker->labelinbackground == 0)
                                                                            {
                                                                                    $scripttext .= '  labelInBackground: false, '."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= '  labelInBackground: true, '."\n";
                                                                            }

                                                                    }
                                                            }
                                                            else
                                                            {
                                                                    $scripttext .= 'var marker'. $currentmarker->id.' = new google.maps.Marker({' ."\n";
                                                            }
                                                            $scripttext .= '      position: latlng'. $currentmarker->id.', ' ."\n";

                                                            if ((isset($map->markercluster) && (int)$map->markercluster == 0))
                                                            {
                                                                            $scripttext .= '      map: map'.$mapDivSuffix.', ' ."\n";
                                                            }

                                                            $scripttext .= MapPlacemarksHelper::get_placemark_icon_definition(
                                                                                                    $imgpathIcons,
                                                                                                    $imgpath4size,
                                                                                                    $currentmarker);

                                                            switch ($currentmarker->baloon) 
                                                            {
                                                            case 1:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 2:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 3:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            case 11:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 12:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 13:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            case 21:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 22:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 23:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            default:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            }

                                                            if (($allowUserMarker == 0)
                                                             || ((int)$map->usermarkersupdate == 0)
                                                             || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                             || ($currentUserID == 0)
                                                             || (isset($currentmarker->createdbyuser) 
                                                                    && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                                       || ((int)$currentmarker->createdbyuser == 0))))
                                                            {
                                                                            $scripttext .= '      draggable: false,' ."\n";
                                                            }
                                                            else
                                                            {
                                                                            $scripttext .= '      draggable: true,' ."\n";
                                                            }

                                                            // Replace to new, because all charters are shown
                                                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'"' ."\n";
                                                            if (isset($currentmarker->markercontent) &&
                                                                    (((int)$currentmarker->markercontent == 0) ||
                                                                     ((int)$currentmarker->markercontent == 1) ||
                                                                     ((int)$currentmarker->markercontent == 9))
                                                                    )
                                                            {
                                                                    $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'"' ."\n";
                                                            }
                                                            else
                                                            {
                                                                    $scripttext .= '      title:""' ."\n";
                                                            }


                                                            $scripttext .= '});'."\n";

                                                            if ($externalmarkerlink == 1)
                                                            {
                                                                    $scripttext .= 'PlacemarkByIDAdd('. $currentmarker->id.
                                                                                                                                    ', '.$currentmarker->latitude.
                                                                                                                                    ', '.$currentmarker->longitude.
                                                                                                                                    ', marker'. $currentmarker->id.
                                                                                                                                    ', latlng'. $currentmarker->id.
                                                                                                                                    ', '.$currentmarker->rating_value.
                                                                                                                                    ');'."\n";
                                                            }

                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmRating", '.$currentmarker->rating_value.');' ."\n";                            
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmPlacemarkID", '.$currentmarker->id.');' ."\n";                            
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmContactAttrs", userContactAttrs);' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmUserContact", "'.str_replace(';', ',', $map->usercontact).'");' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmUserUser", "'.str_replace(';', ',', $map->useruser).'");' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmOriginalPosition", latlng'.$currentmarker->id.');' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmInfowinContent", contentString'. $currentmarker->id.');' ."\n";    
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'");' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmIncludeInList", '.$currentmarker->includeinlist.');' ."\n";                            
                                                            if ($fv_override_placemark_list_search == 1)
                                                            {
                                                                $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmPlacemarkDescription", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->description)).'");' ."\n";                                                                                                                    
                                                            }

                                                            if (($featureSpider != 0)
                                                            || ($placemarkSearch != 0))                    
                                                            {
                                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.allObjectsAddPlacemark('. $currentmarker->id.', marker'. $currentmarker->id.');'."\n";
                                                            }

                                                            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                            {
                                                                    if ($currentmarker->hoverhtml != "")
                                                                    {
                                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                            {
                                                                                    // do not create listeners, create by loop only in the end
                                                                                    $scripttext .= '  ajaxmarkersLLhover'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    if ((int)$map->hovermarker == 1)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseover\', function(event) {' ."\n";
                                                                                            $scripttext .= '  this.set("zhgmZIndex", this.getZIndex());' ."\n";
                                                                                            $scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseout\', function(event) {' ."\n";
                                                                                            $scripttext .= '    this.setZIndex(this.get("zhgmZIndex"));' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else if ((int)$map->hovermarker == 2)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseover\', function(event) {' ."\n";
                                                                                            $scripttext .= '  this.set("zhgmZIndex", this.getZIndex());' ."\n";
                                                                                            $scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseout\', function(event) {' ."\n";
                                                                                            $scripttext .= '    this.setZIndex(this.get("zhgmZIndex"));' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                            }
                                                                    }
                                                            }


                                                            //  If user can change placemark - override content string - begin
                                                            //  override content string
                                                            if (($allowUserMarker == 0)
                                                             || ((int)$map->usermarkersupdate == 0)
                                                             || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                             || ($currentUserID == 0)
                                                             || (isset($currentmarker->createdbyuser) 
                                                                    && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                                       || ((int)$currentmarker->createdbyuser == 0)))
                                                            )
                                                            {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            // do not create listeners, create by loop only in the end
                                                                            $scripttext .= '  ajaxmarkersLL'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
                                                                    }
                                                                    else
                                                                    {
                                                                    // Action By Click - Begin        

                                                                    switch ((int)$currentmarker->actionbyclick)
                                                                    {
                                                                            // None
                                                                            case 0:
                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                            break;
                                                                            // Info
                                                                            case 1:
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    //$scripttext .= '  alert("Here I CAN\'T!");' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                                    //$scripttext .= '  alert("Here I can!");' ."\n";
                                                                                            }

                                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                                    {
                                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                                    }

                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    // Close the other infobubbles
                                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                                    $scripttext .= '  }' ."\n";
                                                                                                    // Hide hover window when feature enabled
                                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                                    {
                                                                                                            if ((int)$map->hovermarker == 1)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }
                                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }

                                                                                                    }
                                                                                                    // Open Infowin
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkTitle", titlePlacemark'. $currentmarker->id.');' ."\n";
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";
                                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                                    {
                                                                                                            $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";    
                                                                                                    }
                                                                                                    if ($managePanelInfowin == 1)
                                                                                                    {                                                                                                    
                                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                    }    
                                                                                                    else
                                                                                                    {                                            
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                                                                                                    }
                                                                                                    if (isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))
                                                                                                    {
                                                                                                            $scripttext .= '  PlacemarkRateDivOut'.$mapDivSuffix.'('. $currentmarker->id.', 5);' ."\n";
                                                                                                    }

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }

                                                                                            $scripttext .= '  });' ."\n";
                                                                            break;
                                                                            // Link
                                                                            case 2:
                                                                                    if ($currentmarker->hrefsite != "")
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }

                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  window.open("'.$currentmarker->hrefsite.'");' ."\n";

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }

                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                            $scripttext .= '}' ."\n";
                                                                                                            $scripttext .= 'else {' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= '};' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  });' ."\n";
                                                                                            }
                                                                                    }
                                                                            break;
                                                                            // Link in self
                                                                            case 3:
                                                                                    if ($currentmarker->hrefsite != "")
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  window.location = "'.$currentmarker->hrefsite.'";' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                            $scripttext .= '}' ."\n";
                                                                                                            $scripttext .= 'else {' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= '};' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  });' ."\n";
                                                                                            }
                                                                                    }
                                                                            break;
                                                                            // InfoBubble
                                                                            case 4:
                                                                                    if ($managePanelInfowin == 0)
                                                                                    {

                                                                                            // InfoBubble Create - Begin
                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.' = new InfoBubble('."\n";
                                                                                            $scripttext .= MapPlacemarksHelper::get_placemark_infobubble_style_string($currentmarker, '');
                                                                                            $scripttext .= '  );'."\n";

                                                                                            $scripttext .= '  infobubblemarkers'.$mapDivSuffix.'.push(infoBubble'. $currentmarker->id.');'."\n";


                                                                                            if ((int)$currentmarker->tab_info == 1)
                                                                                            {                    
                                                                                                    $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
                                                                                                    str_replace(array("\r", "\r\n", "\n"), '', Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            if ((int)$currentmarker->tab_info == 9)
                                                                                            {    
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.setContent(contentString'. $currentmarker->id.');'."\n";
                                                                                            }
                                                                                            else
                                                                                            {

                                                                                                    if ($currentmarker->tab1 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab2 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab3 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab4 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab5 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab6 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab7 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab8 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab9 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab10 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab11 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab12 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab13 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab14 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab15 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab16 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab17 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab18 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab19 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19)).'\');'."\n";
                                                                                                    }
                                                                                            }


                                                                                            if ((int)$currentmarker->tab_info == 2)
                                                                                            {                    
                                                                                                    $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
                                                                                                    str_replace(array("\r", "\r\n", "\n"), '', Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            // InfoBubble Create - End
                                                                                    }
                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                    if ($featureSpider != 0)
                                                                                    {
                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                            $scripttext .= '}' ."\n";
                                                                                            $scripttext .= 'else {' ."\n";
                                                                                    }
                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                    {
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                    }
                                                                                    // Close the other infowin and infobubbles
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();'."\n";
                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                    $scripttext .= '  }' ."\n";
                                                                                    // Hide hover window when feature enabled
                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                    {
                                                                                            if ((int)$map->hovermarker == 1)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                    }        
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkTitle", titlePlacemark'. $currentmarker->id.');' ."\n";
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";
                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                    {
                                                                                            $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";    
                                                                                    }
                                                                                    if ($managePanelInfowin == 1)
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContentTabs(this.get("zhgmInfowinContent"));' ."\n";
                                                                                    }    
                                                                                    else
                                                                                    {                                            
                                                                                            // Open infobubble                                        
                                                                                            $scripttext .= '  if (!infoBubble'. $currentmarker->id.'.isOpen())'."\n";
                                                                                            $scripttext .= '  {'."\n";        
                                                                                            $scripttext .= '      infoBubble'. $currentmarker->id.'.open(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
                                                                                            $scripttext .= '  }'."\n";
                                                                                    }                                        

                                                                                    if ($featureSpider != 0)
                                                                                    {
                                                                                            $scripttext .= '};' ."\n";
                                                                                    }
                                                                                    $scripttext .= '  });' ."\n";
                                                                            break;
                                                                            // Open Street View
                                                                            case 5:
                                                                                    if (isset($map->streetview) && (int)$map->streetview != 0) 
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  panorama'.$mapDivSuffix.'.setPosition(latlng'. $currentmarker->id.');' ."\n";

                                                                                            $mapSV = MapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
                                                                                            if ($mapSV != "")
                                                                                            {
                                                                                                    $scripttext .= '  panorama'.$mapDivSuffix.'.setPov('.$mapSV.');'."\n";
                                                                                            }

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            $scripttext .= 'google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";                                                
                                                                                            }

                                                                                            $mapSV = MapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                            $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                            $scripttext .= '  }' ."\n";
                                                                                            // Hide hover window when feature enabled
                                                                                            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                            {
                                                                                                    if ((int)$map->hovermarker == 1)
                                                                                                    {
                                                                                                            $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    }
                                                                                                    else if ((int)$map->hovermarker == 2)
                                                                                                    {
                                                                                                            $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    }
                                                                                            }
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";
                                                                                            if ((int)$map->markerlistpos != 0)
                                                                                            {
                                                                                                    $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            if ($mapSV == "")
                                                                                            {
                                                                                                    $scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', \'\');'."\n";
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                    $scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', '.$mapSV.');'."\n";
                                                                                            }


                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '});' ."\n";
                                                                                    }
                                                                            break;
                                                                            default:
                                                                                    $scripttext .= '' ."\n";
                                                                            break;
                                                                    }

                                                                    // Action By Click - End
                                                                    }
                                                            }
                                                            else
                                                            {
                                                                    // Action By click for update placemark = Open InfoWin
                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";

                                                                            $scripttext .= 'var contentStringButtons'.$currentmarker->id.' = "" +' ."\n";
                                                                            $scripttext .= '    \'<hr />\'+' ."\n";                    
                                                                            $scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlng'. $currentmarker->id.'.lat() + \'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlng'.$currentmarker->id.'.lng() + \'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="marker_action" type="hidden" value="update" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markersubmit" type="submit" value="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BUTTON_UPDATE' ).'" />\'+' ."\n";
                                                                            $scripttext .= '    \'</form>\'+' ."\n";        
                                                                            $scripttext .= '\'</div>\'+'."\n";
                                                                            // Form Delete
                                                                            if ((int)$map->usermarkersdelete == 1)
                                                                            {
                                                                                    $scripttext .= '\'<div id="contentDeletePlacemark">\'+'."\n";
                                                                                    $scripttext .= '    \'<form id="deletePlacemarkForm'.$currentmarker->id.'" action="'.URI::current().'" method="post">\'+'."\n";
                                                                                    $scripttext .= '    \'<input name="marker_action" type="hidden" value="delete" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="markersubmit" type="submit" value="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BUTTON_DELETE' ).'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'</form>\'+' ."\n";        
                                                                                    $scripttext .= '\'</div>\';'."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= '\'\';'."\n";
                                                                            }

                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentStringPart1'.$currentmarker->id.'+';
                                                                            $scripttext .= 'contentInsertPlacemarkIcon.replace(/insertPlacemarkForm/g,"updatePlacemarkForm'. $currentmarker->id.'")';
                                                                            $scripttext .= '.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", $currentmarker->icontype).'.png"\')';
                                                                            $scripttext .= '.replace(\'<option value="'.$currentmarker->icontype.'">'.$currentmarker->icontype.'</option>\', \'<option value="'.$currentmarker->icontype.'" selected="selected">'.$currentmarker->icontype.'</option>\')';
                                                                            $scripttext .= '+';
                                                                            $scripttext .= 'contentStringPart2'.$currentmarker->id.'+';
                                                                            $scripttext .= 'contentStringButtons'.$currentmarker->id;
                                                                            $scripttext .= ');' ."\n";
                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";

                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'drag\', function(event) {' ."\n";

                                                                            $scripttext .= '    infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '    latlng'. $currentmarker->id.' = event.latLng;';

                                                                            $scripttext .= '  });' ."\n";


                                                            }

                                                            // If user can change placemark - override content string - end

                                                            if ($zhgmObjectManager != 0)
                                                            {
                                                                    // fix 07.02.2020 - only if management is enabled
                                                                    // fix for 19.02.2013
                                                                    //  if not managed placemarks (not enabled)
                                                                    if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
                                                                     && (isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker != 0))
                                                                    {
                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
                                                                    }
                                                                    else
                                                                    {                                    
                                                                            // 22.08.2014 placemarks in clusters, therefore not only 0-cluster
                                                                            if ((isset($map->markercluster) && (int)$map->markercluster == 1))
                                                                            {
                                                                                    if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd(0, '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
                                                                                    }
                                                                            }
                                                                            else
                                                                            {
                                                                                    if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            // 28.04.2020 bugfix
                                                                                            // disable clusterer
                                                                                            // enable placemark control
                                                                                            // disable group control                                                                                           
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAdd(0, '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
                                                                                    }
                                                                            }
                                                                            // /////
                                                                    }
                                                            }



                                                            //
                                                            // Generate list elements for each marker.
                                                            $scripttext .= MapPlacemarksHelper::get_placemarklist_string(
                                                                                                    0,
                                                                                                    $mapDivSuffix, 
                                                                                                    $currentmarker, 
                                                                                                    $markerlistcssstyle,
                                                                                                    $map->markerlistpos,
                                                                                                    $map->markerlistcontent,
                                                                                                    $map->markerlistaction,
                                                                                                    $imgpathIcons);
                                                            // Generating Placemark List - End
                                                    }

                                                    // Change Map center and set Center Placemark Action
                                                    if ($currentPlacemarkCenter != "do not change")
                                                    {
                                                            if ((int)$currentPlacemarkCenter == $currentmarker->id)
                                                            {
                                                                    $scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.(int)$currentPlacemarkCenter.');'."\n";
                                                                    $scripttext .= 'latlng'.$mapDivSuffix.' = latlng'.(int)$currentPlacemarkCenter.';'."\n";
                                                                    $scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
                                                                    if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
                                                                    {
                                                                        //if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                                                                        //{
                                                                            $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(latlng'.$mapDivSuffix.');'."\n";
                                                                        //}                                                                      
                                                                    }
                                                            }
                                                    }

                                                    if ($currentPlacemarkActionID != "do not change")
                                                    {
                                                            if ((int)$currentPlacemarkActionID == $currentmarker->id)
                                                            {

                                                                    if ($currentPlacemarkAction != "")
                                                                    {
                                                                            $currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);

                                                                            for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
                                                                            {
                                                                                    switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
                                                                                    {
                                                                                            case "":
                                                                                               // null
                                                                                            break;
                                                                                            case "do not change":
                                                                                                    // do not change
                                                                                            break;
                                                                                            case "click":
                                                                                                    $scripttext .= '  google.maps.event.trigger(marker'. (int)$currentPlacemarkActionID.', "click");' ."\n";
                                                                                            break;
                                                                                            case "bounce":
                                                                                                    $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setAnimation(google.maps.Animation.BOUNCE);'."\n";
                                                                                            break;
                                                                                            default:
                                                                                                    $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setIcon("'.$imgpathIcons.str_replace("#", "%23", trim($currentPlacemarkExecuteArray[$i])).'.png");'."\n";
                                                                                            break;
                                                                                    }
                                                                            }
                                                                    }
                                                            }

                                                    }                    

                                                    if ((int)$currentmarker->openbaloon == 1)
                                                    {
                                                            $lastmarker2open = $currentmarker;
                                                    }

                                                    // End marker creation with lat,lng
                                            }
                                            else
                                            {
                                                    // Begin marker creation with address by geocoding
                                                    $scripttext .= '  geocoder'.$mapDivSuffix.'.geocode( { \'address\': "'.$currentmarker->addresstext.'"}, function(results, status) {'."\n";
                                                    $scripttext .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
                                                    $scripttext .= '    var latlng'. $currentmarker->id.' = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";
                                                    if (isset($map->auto_center_zoom) && ((int)$map->auto_center_zoom !=0))
                                                    {
                                                        $scripttext .= '    map_bounds'.$mapDivSuffix.'.extend(latlng'. $currentmarker->id.');' ."\n";
                                                    }
                                            //$scripttext .= '    alert("Geocode was successful");'."\n";
                                            //$scripttext .= '    alert("latlng="+latlng'. $currentmarker->id.');'."\n";

                                                    // contentString - Begin
                                                    $scripttext .= 'var contentString'. $currentmarker->id.' = "";'."\n";

                                                    if (($allowUserMarker == 0)
                                                     || ((int)$map->usermarkersupdate == 0)
                                                     || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                     || ($currentUserID == 0)
                                                     || (isset($currentmarker->createdbyuser) 
                                                        && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                               || ((int)$currentmarker->createdbyuser == 0)))
                                                     )
                                                    {
                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                            {
                                                                    // do not create content string, create by loop only in the end
                                                            }
                                                            else
                                                            {
                                                                    if (((int)$currentmarker->actionbyclick == 1)
                                                                      ||
                                                                       (((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
                                                                      ||  (($managePanelInfowin == 1) && (((int)$currentmarker->actionbyclick == 1) || (int)$currentmarker->actionbyclick == 4))
                                                                    )
                                                                    {
                                                                            if ($managePanelInfowin == 1)                    
                                                                            {
                                                                                    if ((int)$currentmarker->actionbyclick == 1)
                                                                                    {                                        
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_content_string(
                                                                                                            $mapDivSuffix, 
                                                                                                            $currentmarker, $map->usercontact, $map->useruser,
                                                                                                            $userContactAttrs, $service_DoDirection,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                            $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                            $fv_placemark_date_fmt);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }
                                                                                    else if ((int)$currentmarker->actionbyclick == 4)
                                                                                    {
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_tabs_content_string(
                                                                                                            $mapDivSuffix, $currentmarker,
                                                                                                            MapPlacemarksHelper::get_placemark_content_string(
                                                                                                                    $mapDivSuffix, 
                                                                                                                    $currentmarker, $map->usercontact, $map->useruser,
                                                                                                                    $userContactAttrs, $service_DoDirection,
                                                                                                                    $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                                    $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                                    $fv_placemark_date_fmt),
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }
                                                                            }
                                                                            else
                                                                            {
                                                                                    if (((int)$currentmarker->actionbyclick == 1)
                                                                                    ||
                                                                                    (((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
                                                                                    )
                                                                                    {
                                                                                            $scripttext .= 'contentString'. $currentmarker->id.' = '.
                                                                                                    MapPlacemarksHelper::get_placemark_content_string(
                                                                                                            $mapDivSuffix, 
                                                                                                            $currentmarker, $map->usercontact, $map->useruser,
                                                                                                            $userContactAttrs, $service_DoDirection,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                                                                            $map->gogoogle, $fv_override_gogoogle_text,
                                                                                                            $fv_placemark_date_fmt);                                            
                                                                                            $scripttext .= ';'."\n";
                                                                                    }

                                                                            }                                    


                                                                    }
                                                            }
                                                    }
                                                    else
                                                    {
                                                            // contentString - User Placemark can Update - Begin
                                                            $scripttext .= MapPlacemarksHelper::get_placemark_content_update_string(
                                                                                                            $map->usermarkersicon, 
                                                                                                            $map->usercontact, 
                                                                                                            $currentmarker,
                                                                                                            $imgpathIcons, $imgpathUtils, $directoryIcons,
                                                                                                            $newMarkerGroupList
                                                                                                            );
                                                            // contentString - User Placemark can Update - End
                                                    }

                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                    {
                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                            {
                                                                    // do not create content string, create by loop only in the end
                                                            }
                                                            else
                                                            {
                                                                    if ((int)$map->hovermarker == 1
                                                                      ||(int)$map->hovermarker == 2)
                                                                    {
                                                                            if ($currentmarker->hoverhtml != "")
                                                                            {
                                                                                    $scripttext .= 'var hoverString'. $currentmarker->id.' = '.
                                                                                            MapPlacemarksHelper::get_placemark_hover_string(
                                                                                                    $currentmarker);                                    
                                                                            }

                                                                    }
                                                            }
                                                    }


                                                    if ((int)$currentmarker->baloon != 0) 
                                                    {

                                                            if ((int)$currentmarker->baloon == 21
                                                             || (int)$currentmarker->baloon == 22
                                                             || (int)$currentmarker->baloon == 23
                                                             )
                                                            {
                                                                    $scripttext .= 'var marker'. $currentmarker->id.' = new MarkerWithLabel({' ."\n";

                                                                    if ($currentmarker->labelcontent != "")
                                                                    {
                                                                            if ($currentmarker->labelclass != "")
                                                                            {
                                                                                    $scripttext .= '  labelClass: "'. $currentmarker->labelclass .'", '."\n";
                                                                            }
                                                                            if ((int)$currentmarker->labelanchorx != 0
                                                                            || ((int)$currentmarker->labelanchory != 0))
                                                                            {
                                                                                    $scripttext .= '  labelAnchor: new google.maps.Point('. (int)$currentmarker->labelanchorx .', '.(int)$currentmarker->labelanchory .'), '."\n";
                                                                            }
                                                                            if ($currentmarker->labelcontent != "")
                                                                            {
                                                                                    $scripttext .= '  labelContent: \''; 
                                                                                    $scripttext .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->labelcontent));
                                                                                    $scripttext .= '\', '."\n";
                                                                            }
                                                                            if ((int)$currentmarker->labelinbackground == 0)
                                                                            {
                                                                                    $scripttext .= '  labelInBackground: false, '."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= '  labelInBackground: true, '."\n";
                                                                            }

                                                                    }
                                                            }
                                                            else
                                                            {
                                                                    $scripttext .= 'var marker'. $currentmarker->id.' = new google.maps.Marker({' ."\n";
                                                            }
                                                            $scripttext .= '      position: latlng'. $currentmarker->id.', ' ."\n";

                                                            if ((isset($map->markercluster) && (int)$map->markercluster == 0))
                                                            {
                                                                            $scripttext .= '      map: map'.$mapDivSuffix.', ' ."\n";
                                                            }

                                                            $scripttext .= MapPlacemarksHelper::get_placemark_icon_definition(
                                                                                                    $imgpathIcons,
                                                                                                    $imgpath4size,
                                                                                                    $currentmarker);


                                                            switch ($currentmarker->baloon) 
                                                            {
                                                            case 1:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 2:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 3:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            case 11:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 12:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 13:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            case 21:
                                                                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                                                            break;
                                                            case 22:
                                                                            $scripttext .= '      animation: google.maps.Animation.BOUNCE,' ."\n";
                                                            break;
                                                            case 23:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            default:
                                                                            $scripttext .= '' ."\n";
                                                            break;
                                                            }

                                                            if (($allowUserMarker == 0)
                                                             || ((int)$map->usermarkersupdate == 0)
                                                             || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                             || ($currentUserID == 0)
                                                             || (isset($currentmarker->createdbyuser) 
                                                                    && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                                       || ((int)$currentmarker->createdbyuser == 0))))
                                                            {
                                                                            $scripttext .= '      draggable: false,' ."\n";
                                                            }
                                                            else
                                                            {
                                                                            $scripttext .= '      draggable: true,' ."\n";
                                                            }

                                                            // Replace to new, because all charters are shown
                                                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'"' ."\n";
                                                            if (isset($currentmarker->markercontent) &&
                                                                    (((int)$currentmarker->markercontent == 0) ||
                                                                     ((int)$currentmarker->markercontent == 1) ||
                                                                     ((int)$currentmarker->markercontent == 9))
                                                                    )
                                                            {
                                                                    $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'"' ."\n";
                                                            }
                                                            else
                                                            {
                                                                    $scripttext .= '      title:""' ."\n";
                                                            }
                                                            $scripttext .= '});'."\n";

                                                            if ($externalmarkerlink == 1)
                                                            {
                                                                    $scripttext .= 'PlacemarkByIDAdd('. $currentmarker->id.
                                                                                                    ', results[0].geometry.location.lat()'.
                                                                                                                                    ', results[0].geometry.location.lng()'.
                                                                                                                                    ', marker'. $currentmarker->id.
                                                                                                                                    ', latlng'. $currentmarker->id.
                                                                                                                                    ', '.$currentmarker->rating_value.
                                                                                                                                    ');'."\n";
                                                            }

                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmRating", '.$currentmarker->rating_value.');' ."\n";                            
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmPlacemarkID", '.$currentmarker->id.');' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmContactAttrs", userContactAttrs);' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmUserContact", "'.str_replace(';', ',', $map->usercontact).'");' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmUserUser", "'.str_replace(';', ',', $map->useruser).'");' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmOriginalPosition", latlng'.$currentmarker->id.');' ."\n";
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmInfowinContent", contentString'. $currentmarker->id.');' ."\n";    
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'");' ."\n";    
                                                            $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmIncludeInList", '.$currentmarker->includeinlist.');' ."\n";                            
                                                            if ($fv_override_placemark_list_search == 1)
                                                            {
                                                                $scripttext .= '  marker'. $currentmarker->id.'.set("zhgmPlacemarkDescription", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->description)).'");' ."\n";                                                                                                                    
                                                            }

                                                            if (($featureSpider != 0)
                                                            || ($placemarkSearch != 0))
                                                            {
                                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.allObjectsAddPlacemark('. $currentmarker->id.', marker'. $currentmarker->id.');'."\n";                                
                                                            }

                                                            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                            {
                                                                    if ($currentmarker->hoverhtml != "")
                                                                    {
                                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                            {
                                                                                    $scripttext .= '  ajaxmarkersADRhover'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
                                                                                    if ((int)$map->useajax == 1)
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("mootools", marker'. $currentmarker->id.');' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("jquery", marker'. $currentmarker->id.');' ."\n";
                                                                                    }
                                                                            }
                                                                            else
                                                                            {
                                                                                    if ((int)$map->hovermarker == 1)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseover\', function(event) {' ."\n";
                                                                                            $scripttext .= '  this.set("zhgmZIndex", this.getZIndex());' ."\n";
                                                                                            $scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseout\', function(event) {' ."\n";
                                                                                            $scripttext .= '    this.setZIndex(this.get("zhgmZIndex"));' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else if ((int)$map->hovermarker == 2)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseover\', function(event) {' ."\n";
                                                                                            $scripttext .= '  this.set("zhgmZIndex", this.getZIndex());' ."\n";
                                                                                            $scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'mouseout\', function(event) {' ."\n";
                                                                                            $scripttext .= '    this.setZIndex(this.get("zhgmZIndex"));' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                            }
                                                                    }
                                                            }

                                                            //  If user can change placemark - override content string - begin
                                                            //  override content string
                                                            if (($allowUserMarker == 0)
                                                             || ((int)$map->usermarkersupdate == 0)
                                                             || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
                                                             || ($currentUserID == 0)
                                                             || (isset($currentmarker->createdbyuser) 
                                                                    && (((int)$currentmarker->createdbyuser != $currentUserID )
                                                                       || ((int)$currentmarker->createdbyuser == 0)))
                                                             )
                                                            {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            $scripttext .= '  ajaxmarkersADR'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";

                                                                            if ((int)$map->useajax == 1)
                                                                            {
                                                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("mootools", marker'. $currentmarker->id.');' ."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("jquery", marker'. $currentmarker->id.');' ."\n";
                                                                            }
                                                                    }
                                                                    else
                                                                    {
                                                                    // Action By Click - Begin
                                                                    switch ((int)$currentmarker->actionbyclick)
                                                                    {
                                                                            // None
                                                                            case 0:
                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                            break;
                                                                            // Info
                                                                            case 1:
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                                    {
                                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    // Close the other infobubbles
                                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                                    $scripttext .= '  }' ."\n";
                                                                                                    // Hide hover window when feature enabled
                                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                                    {
                                                                                                            if ((int)$map->hovermarker == 1)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }
                                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }
                                                                                                    }
                                                                                                    // Open InfoWin
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkTitle", titlePlacemark'. $currentmarker->id.');' ."\n";
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";
                                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                                    {
                                                                                                            $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";    
                                                                                                    }
                                                                                                    if ($managePanelInfowin == 1)
                                                                                                    {
                                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                    }    
                                                                                                    else
                                                                                                    {
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";                                                
                                                                                                    }
                                                                                                    if (isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))
                                                                                                    {
                                                                                                            $scripttext .= '  PlacemarkRateDivOut'.$mapDivSuffix.'('. $currentmarker->id.', 5);' ."\n";
                                                                                                    }

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                            break;
                                                                            // Link
                                                                            case 2:
                                                                                    if ($currentmarker->hrefsite != "")
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  window.open("'.$currentmarker->hrefsite.'");' ."\n";

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                            $scripttext .= '}' ."\n";
                                                                                                            $scripttext .= 'else {' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";

                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= '};' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  });' ."\n";
                                                                                            }
                                                                                    }
                                                                            break;
                                                                            // Link in self
                                                                            case 3:
                                                                                    if ($currentmarker->hrefsite != "")
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  window.location = "'.$currentmarker->hrefsite.'";' ."\n";

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                            $scripttext .= '}' ."\n";
                                                                                                            $scripttext .= 'else {' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";

                                                                                                    if ($featureSpider != 0)
                                                                                                    {
                                                                                                            $scripttext .= '};' ."\n";
                                                                                                    }
                                                                                                    $scripttext .= '  });' ."\n";
                                                                                            }
                                                                                    }
                                                                            break;
                                                                            // InfoBubble
                                                                            case 4:
                                                                                    if ($managePanelInfowin == 0)
                                                                                    {

                                                                                            // InfoBubble Create - Begin
                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.' = new InfoBubble('."\n";
                                                                                            $scripttext .= MapPlacemarksHelper::get_placemark_infobubble_style_string($currentmarker, '');
                                                                                            $scripttext .= '  );'."\n";

                                                                                            $scripttext .= '  infobubblemarkers'.$mapDivSuffix.'.push(infoBubble'. $currentmarker->id.');'."\n";


                                                                                            if ((int)$currentmarker->tab_info == 1)
                                                                                            {                    
                                                                                                    $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
                                                                                                    str_replace(array("\r", "\r\n", "\n"), '', Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            if ((int)$currentmarker->tab_info == 9)
                                                                                            {    
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.setContent(contentString'. $currentmarker->id.');'."\n";
                                                                                            }
                                                                                            else
                                                                                            {

                                                                                                    if ($currentmarker->tab1 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab2 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab3 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab4 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab5 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab6 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab7 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab8 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab9 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab10 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab11 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab12 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab13 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab14 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab15 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab16 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab17 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab18 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18)).'\');'."\n";
                                                                                                    }
                                                                                                    if ($currentmarker->tab19 != "")
                                                                                                    {
                                                                                                            $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19)).'\');'."\n";
                                                                                                    }
                                                                                            }


                                                                                            if ((int)$currentmarker->tab_info == 2)
                                                                                            {                    
                                                                                                    $scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
                                                                                                    str_replace(array("\r", "\r\n", "\n"), '', Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            // InfoBubble Create - End
                                                                                    }
                                                                                    $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                    if ($featureSpider != 0)
                                                                                    {
                                                                                            $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                            $scripttext .= '}' ."\n";
                                                                                            $scripttext .= 'else {' ."\n";
                                                                                    }
                                                                                    if ((int)$currentmarker->zoombyclick != 100)
                                                                                    {
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                    }
                                                                                    // Close the other infowin and infobubbles
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();'."\n";
                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                    $scripttext .= '  }' ."\n";
                                                                                    // Hide hover window when feature enabled
                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                    {
                                                                                            if ((int)$map->hovermarker == 1)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                    }        
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkTitle", titlePlacemark'. $currentmarker->id.');' ."\n";
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";
                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                    {
                                                                                            $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";    
                                                                                    }
                                                                                    if ($managePanelInfowin == 1)
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContentTabs(this.get("zhgmInfowinContent"));' ."\n";
                                                                                    }    
                                                                                    else
                                                                                    {                                            
                                                                                            // Open infobubble                                        
                                                                                            $scripttext .= '  if (!infoBubble'. $currentmarker->id.'.isOpen())'."\n";
                                                                                            $scripttext .= '  {'."\n";        
                                                                                            $scripttext .= '      infoBubble'. $currentmarker->id.'.open(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
                                                                                            $scripttext .= '  }'."\n";
                                                                                    }                                        

                                                                                    if ($featureSpider != 0)
                                                                                    {
                                                                                            $scripttext .= '};' ."\n";
                                                                                    }
                                                                                    $scripttext .= '  });' ."\n";                                    
                                                                            break;
                                                                            // Open Street View
                                                                            case 5:
                                                                                    if (isset($map->streetview) && (int)$map->streetview != 0) 
                                                                                    {
                                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  panorama'.$mapDivSuffix.'.setPosition(latlng'. $currentmarker->id.');' ."\n";
                                                                                            $mapSV = MapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
                                                                                            if ($mapSV != "")
                                                                                            {
                                                                                                    $scripttext .= '  panorama'.$mapDivSuffix.'.setPov('.$mapSV.');'."\n";
                                                                                            }

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                            $scripttext .= 'google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";
                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= 'if (zhgmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
                                                                                                    $scripttext .= '}' ."\n";
                                                                                                    $scripttext .= 'else {' ."\n";
                                                                                            }
                                                                                            if ((int)$currentmarker->zoombyclick != 100)
                                                                                            {
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
                                                                                                    $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";                                                
                                                                                            }
                                                                                            $mapSV = MapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                            $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                            $scripttext .= '  }' ."\n";
                                                                                            // Hide hover window when feature enabled
                                                                                            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                            {
                                                                                                    if ((int)$map->hovermarker == 1)
                                                                                                    {
                                                                                                            $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    }
                                                                                                    else if ((int)$map->hovermarker == 2)
                                                                                                    {
                                                                                                            $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    }
                                                                                            }
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.set("zhgmPlacemarkOriginalPosition", this.get("zhgmOriginalPosition"));' ."\n";

                                                                                            if ((int)$map->markerlistpos != 0)
                                                                                            {
                                                                                                    $scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
                                                                                            }

                                                                                            if ($mapSV == "")
                                                                                            {
                                                                                                    $scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', \'\');'."\n";
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                    $scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', '.$mapSV.');'."\n";
                                                                                            }

                                                                                            if ($featureSpider != 0)
                                                                                            {
                                                                                                    $scripttext .= '};' ."\n";
                                                                                            }
                                                                                            $scripttext .= '});' ."\n";
                                                                                    }
                                                                            break;
                                                                            default:
                                                                                    $scripttext .= '' ."\n";
                                                                            break;
                                                                    }
                                                                    // Action By Click - End
                                                                    }
                                                            }
                                                            else
                                                            {
                                                                    // Action By click for update placemark = Open InfoWin
                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'click\', function(event) {' ."\n";

                                                                            $scripttext .= 'var contentStringButtons'.$currentmarker->id.' = "" +' ."\n";
                                                                            $scripttext .= '    \'<hr />\'+' ."\n";                    
                                                                            $scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlng'. $currentmarker->id.'.lat() + \'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlng'.$currentmarker->id.'.lng() + \'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="marker_action" type="hidden" value="update" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
                                                                            $scripttext .= '    \'<input name="markersubmit" type="submit" value="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BUTTON_UPDATE' ).'" />\'+' ."\n";
                                                                            $scripttext .= '    \'</form>\'+' ."\n";        
                                                                            $scripttext .= '\'</div>\'+'."\n";
                                                                            // Form Delete
                                                                            if ((int)$map->usermarkersdelete == 1)
                                                                            {
                                                                                    $scripttext .= '\'<div id="contentDeletePlacemark">\'+'."\n";
                                                                                    $scripttext .= '    \'<form id="deletePlacemarkForm'.$currentmarker->id.'" action="'.URI::current().'" method="post">\'+'."\n";
                                                                                    $scripttext .= '    \'<input name="marker_action" type="hidden" value="delete" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'<input name="markersubmit" type="submit" value="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BUTTON_DELETE' ).'" />\'+' ."\n";
                                                                                    $scripttext .= '    \'</form>\'+' ."\n";        
                                                                                    $scripttext .= '\'</div>\';'."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= '\'\';'."\n";
                                                                            }

                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentStringPart1'.$currentmarker->id.'+';
                                                                            $scripttext .= 'contentInsertPlacemarkIcon.replace(/insertPlacemarkForm/g,"updatePlacemarkForm'. $currentmarker->id.'")';
                                                                            $scripttext .= '.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", $currentmarker->icontype).'.png"\')';
                                                                            $scripttext .= '.replace(\'<option value="'.$currentmarker->icontype.'">'.$currentmarker->icontype.'</option>\', \'<option value="'.$currentmarker->icontype.'" selected="selected">'.$currentmarker->icontype.'</option>\')';
                                                                            $scripttext .= '+';
                                                                            $scripttext .= 'contentStringPart2'.$currentmarker->id.'+';
                                                                            $scripttext .= 'contentStringButtons'.$currentmarker->id;
                                                                            $scripttext .= ');' ."\n";
                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";

                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  google.maps.event.addListener(marker'. $currentmarker->id.', \'drag\', function(event) {' ."\n";

                                                                            $scripttext .= '    infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '    latlng'. $currentmarker->id.' = event.latLng;';

                                                                            $scripttext .= '  });' ."\n";


                                                            }

                                                            // If user can change placemark - override content string - end


                                                            if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
                                                                    && 
                                                                    /* 19.02.2013 
                                                                       for flexible support group management 
                                                                       and have ability to set off placemarks from group managenent */
                                                                (isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker == 1)
                                                                    )
                                                            {
                                                                    if ((isset($map->markercluster) && (int)$map->markercluster == 1))
                                                                    {
                                                                            if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                                                                            {
                                                                                    if ($currentmarker->activeincluster == 1
                                                                                    || $currentmarker->markergroup == 0)
                                                                                    {
                                                                                            $scripttext .= 'markerCluster'.$currentmarker->markergroup.'.addMarker(marker'. $currentmarker->id.');' ."\n";
                                                                                    }
                                                                            }
                                                                            else
                                                                            {
                                                                                    if ($currentmarker->activeincluster == 1
                                                                                    || $currentmarker->markergroup == 0)
                                                                                    {
                                                                                            $scripttext .= 'markerCluster0.addMarker(marker'. $currentmarker->id.');' ."\n";
                                                                                    }
                                                                            }
                                                                    }
                                                                    else
                                                                    {
                                                                            // No need add to cluster
                                                                    }
                                                            }
                                                            else
                                                            {
                                                                    if ((isset($map->markercluster) && (int)$map->markercluster == 1))
                                                                    {
                                                                            if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                                                                            {
                                                                                    $scripttext .= 'markerCluster'.$currentmarker->markergroup.'.addMarker(marker'. $currentmarker->id.');' ."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= 'markerCluster0.addMarker(marker'. $currentmarker->id.');' ."\n";
                                                                            }
                                                                    }
                                                                    else
                                                                    {
                                                                            // No need add to cluster
                                                                    }
                                                            }



                                                            //
                                                            // Generate list elements for each marker.
                                                            $scripttext .= MapPlacemarksHelper::get_placemarklist_string(
                                                                                                    0,
                                                                                                    $mapDivSuffix, 
                                                                                                    $currentmarker, 
                                                                                                    $markerlistcssstyle,
                                                                                                    $map->markerlistpos,
                                                                                                    $map->markerlistcontent,
                                                                                                    $map->markerlistaction,
                                                                                                    $imgpathIcons);
                                                            // Generating Placemark List - End
                                                    }

                                                    // Change Map center and set Center Placemark Action
                                                    if ($currentPlacemarkCenter != "do not change")
                                                    {
                                                            if ((int)$currentPlacemarkCenter == $currentmarker->id)
                                                            {
                                                                    $scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.(int)$currentPlacemarkCenter.');'."\n";
                                                                    $scripttext .= 'latlng'.$mapDivSuffix.' = latlng'.(int)$currentPlacemarkCenter.';'."\n";
                                                                    $scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
                                                                    if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
                                                                    {
                                                                        //if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                                                                        //{
                                                                            $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(latlng'.$mapDivSuffix.');'."\n";
                                                                        //}
                                                                    }

                                                            }
                                                    }

                                                    if ($currentPlacemarkActionID != "do not change")
                                                    {
                                                            if ((int)$currentPlacemarkActionID == $currentmarker->id)
                                                            {

                                                                    if ($currentPlacemarkAction != "")
                                                                    {
                                                                            $currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);

                                                                            for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
                                                                            {
                                                                                    switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
                                                                                    {
                                                                                            case "":
                                                                                               // null
                                                                                            break;
                                                                                            case "do not change":
                                                                                                    // do not change
                                                                                            break;
                                                                                            case "click":
                                                                                                    $scripttext .= '  google.maps.event.trigger(marker'. (int)$currentPlacemarkActionID.', "click");' ."\n";
                                                                                            break;
                                                                                            case "bounce":
                                                                                                    $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setAnimation(google.maps.Animation.BOUNCE);'."\n";
                                                                                            break;
                                                                                            default:
                                                                                                    $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setIcon("'.$imgpathIcons.str_replace("#", "%23", trim($currentPlacemarkExecuteArray[$i])).'.png");'."\n";
                                                                                            break;
                                                                                    }
                                                                            }
                                                                    }
                                                            }

                                                    }                    


                                                    if ((int)$currentmarker->openbaloon == 1)
                                                    {
                                                            $lastmarker2open = $currentmarker;
                                                    }

                                                    if ($ajaxLoadObjects == 0 && (isset($map->auto_center_zoom) && ((int)$map->auto_center_zoom !=0)))
                                                    {
                                                        $scripttext .= 'map'.$mapDivSuffix.'.fitBounds(map_bounds'.$mapDivSuffix.');' ."\n";
                                                        $scripttext .= 'map'.$mapDivSuffix.'.panToBounds(map_bounds'.$mapDivSuffix.');' ."\n";
                                                    }

                                                    // End marker creation with address
                                            $scripttext .= '  }'."\n";
                                                    $scripttext .= '  else'."\n";
                                                    $scripttext .= '  {'."\n";
                                            $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': '.$currentmarker->addresstext.'" + "\n"+"id:'. $currentmarker->id.'");'."\n";
                                            $scripttext .= '  }'."\n";
                                            $scripttext .= '});'."\n";
                                            }



                                    }


                            }
                            // End restriction
                    }
                    // Main loop by markers - End

            }

            // Ajax Marker Listeners
            if (isset($map->useajax) && (int)$map->useajax != 0) 
            {
            //$scripttext .= 'alert("begin: '.$mapDivSuffix.'");' ."\n";
            $scripttext .= 'for (var i=0; i<ajaxmarkersLL'.$mapDivSuffix.'.length; i++)' ."\n";
            $scripttext .= '{' ."\n";
                    //$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].get("zhgmPlacemarkID"));' ."\n";
                    if ((int)$map->useajax == 1)
                    {
                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("mootools", ajaxmarkersLL'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    else
                    {
                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("jquery", ajaxmarkersLL'.$mapDivSuffix.'[i]);' ."\n";
                    }
            $scripttext .= '}' ."\n";
            //scripttext .= 'alert("-end");' ."\n";


                // For Hovering Feature - Begin
                if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                {
                        $scripttext .= 'for (var i=0; i<ajaxmarkersLLhover'.$mapDivSuffix.'.length; i++)' ."\n";
                        $scripttext .= '{' ."\n";
                        //$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].get("zhgmPlacemarkID"));' ."\n";
                        if ((int)$map->useajax == 1)
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("mootools", ajaxmarkersLLhover'.$mapDivSuffix.'[i]);' ."\n";
                        }
                        else
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("jquery", ajaxmarkersLLhover'.$mapDivSuffix.'[i]);' ."\n";
                        }
                        $scripttext .= '}' ."\n";
                }
                // For Hovering Feature - End

            }

            if (isset($map->auto_center_zoom) && ((int)$map->auto_center_zoom !=0))
            {
                if ($ajaxLoadObjects == 0)
                {
                    $scripttext .= 'map'.$mapDivSuffix.'.fitBounds(map_bounds'.$mapDivSuffix.');' ."\n";
                    $scripttext .= 'map'.$mapDivSuffix.'.panToBounds(map_bounds'.$mapDivSuffix.');' ."\n";                
                }
                else
                {
                     $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkCenterZoom(map_bounds'.$mapDivSuffix.');' ."\n";    
                }
            }

            // Execute Action - Open InfoWin and etc
            if (isset($lastmarker2open)
            && (isset($map->useajaxobject) && (int)$map->useajaxobject == 0))
            {
                    if ((int)$lastmarker2open->baloon != 0)
                    {
                            switch ((int)$lastmarker2open->actionbyclick)
                            {
                                    case 0:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    case 1:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    case 2:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    case 3:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    case 4:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    case 5:
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                            $scripttext .= '  google.maps.event.trigger(marker'. $lastmarker2open->id.', "click");' ."\n";
                                    break;
                                    default:
                                            $scripttext .= '' ."\n";
                                    break;
                            }
                    }
                    else
                    {
                                    $scripttext .= 'var contentString'. $lastmarker2open->id.' = '.
                                            MapPlacemarksHelper::get_placemark_content_string(
                                                    $mapDivSuffix,
                                                    $lastmarker2open, $map->usercontact, $map->useruser,
                                                    $userContactAttrs, $service_DoDirection,
                                                    $imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo,
                                                    $map->gogoogle, $fv_override_gogoogle_text,
                                                    $fv_placemark_date_fmt);
                                    $scripttext .= ';'."\n";
                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'. $lastmarker2open->id.');' ."\n";
                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                    }

            }

            if ($placemarkSearch != 0)
            {
                    if ($fv_override_placemark_list_mapping_type != 0)
                    {
                        // remove new lines
                        // change comma to semicolon
                        // fix double quotes, back slash    
                        if ($fv_override_placemark_list_mapping_type == 100)
                        {
                            $fv_override_placemark_list_accent = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_placemark_list_accent))));
                            $fv_override_placemark_list_mapping = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_placemark_list_mapping))));                       
                        }
                        else
                        {
                            $fv_override_placemark_list_accent = "";
                            $fv_override_placemark_list_mapping = "";

                        }
                        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkListSearchMapping('.$fv_override_placemark_list_mapping_type.','.$fv_override_placemark_list_accent_side.', "'.$fv_override_placemark_list_accent.'"'.', "'.$fv_override_placemark_list_mapping.'"'.');'."\n";
                    }
                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkListSearch('.$fv_override_placemark_list_search.');'."\n";
            }

            if ($groupSearch != 0)
            {

                    if ($fv_override_group_list_mapping_type != 0)
                    {
                        // remove new lines
                        // change comma to semicolon
                        // fix double quotes, back slash    
                        if ($fv_override_group_list_mapping_type == 100)
                        {
                            $fv_override_group_list_accent = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_group_list_accent))));
                            $fv_override_group_list_mapping = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_group_list_mapping))));
                        }
                        else
                        {
                            $fv_override_group_list_accent = "";
                            $fv_override_group_list_mapping = "";
                        }
                        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setGroupListSearchMapping('.$fv_override_group_list_mapping_type.','.$fv_override_group_list_accent_side.', "'.$fv_override_group_list_accent.'"'.', "'.$fv_override_group_list_mapping.'"'.');'."\n";
                    }            
                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.enableGroupListSearch('.$fv_override_group_list_search.');'."\n";
            }        


            // 16.08.2013 - ajax loading
            if ($zhgmObjectManager != 0)
            {
                    if ($ajaxLoadObjects != 0)
                    {

                            if ($ajaxLoadObjectType == 2)
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
                                    $scripttext .= 'google.maps.event.addListener(map'.$mapDivSuffix.', \'idle\', function(event) {' ."\n";
                                    if ($ajaxLoadObjects == 1)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
                                    }
                                    else if ($ajaxLoadObjects == 2)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
                                    }
                                    $scripttext .= '});' ."\n";    

                                    $scripttext .= 'google.maps.event.addListenerOnce(map'.$mapDivSuffix.', \'idle\', function(event) {' ."\n";
                                    if ($ajaxLoadObjects == 1)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPathAJAX("mootools");' ."\n";
                                    }
                                    else if ($ajaxLoadObjects == 2)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPathAJAX("jquery");' ."\n";
                                    }
                                    $scripttext .= '});' ."\n";    

                            }
                            else
                            {
                                    $scripttext .= 'google.maps.event.addListenerOnce(map'.$mapDivSuffix.', \'idle\', function(event) {' ."\n";
                                    if ($ajaxLoadObjectType == 1)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType(2);' ."\n";
                                            if ($ajaxLoadObjects == 1)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
                                            }
                                            else if ($ajaxLoadObjects == 2)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
                                            }
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType(0);' ."\n";
                                            if ($ajaxLoadObjects == 1)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
                                            }
                                            else if ($ajaxLoadObjects == 2)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
                                            }
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
                                    }
                                    else
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
                                            if ($ajaxLoadObjects == 1)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
                                            }
                                            else if ($ajaxLoadObjects == 2)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
                                            }
                                    }

                                    if ($ajaxLoadObjects == 1)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPathAJAX("mootools");' ."\n";
                                    }
                                    else if ($ajaxLoadObjects == 2)
                                    {
                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GetPathAJAX("jquery");' ."\n";
                                    }

                                    $scripttext .= '});' ."\n";    

                            }

                    }
            }

            // Routers
            if (isset($routers) && !empty($routers)) 
            {
                    $routepanelcount = 0;
                    $routepaneltotalcount = 0;
                    $scripttext .= 'var directionsService = new google.maps.DirectionsService();' ."\n";

                    $routeHTMLdescription ='';

                    //Begin for each Route
                    foreach ($routers as $key => $currentrouter) 
                    {
                            // Start Route by Address
                            if ($currentrouter->route != "")
                            {
                                    $routername ='';
                                    $routername = 'route'. $currentrouter->id;
                                    $scripttext .= 'var directionsDisplay'. $currentrouter->id.' = new google.maps.DirectionsRenderer();' ."\n";
                                    $scripttext .= 'directionsDisplay'. $currentrouter->id.'.setMap(map'.$mapDivSuffix.');' ."\n";

                                    if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                    {
                                            $scripttext .= 'directionsDisplay'. $currentrouter->id.'.setPanel(document.getElementById("GMapsRoutePanel'.$mapDivSuffix.'"));' ."\n";
                                            $routepanelcount++;
                                            if (isset($currentrouter->showpaneltotal) && (int)$currentrouter->showpaneltotal == 1) 
                                            {
                                                    $routepaneltotalcount++;
                                            }
                                    }

                                    $cs = explode(";", $currentrouter->route);
                                    $cs_total = count($cs)-1;
                                    $cs_idx = 0;
                                    $wp_list = '';
                                    foreach($cs as $curroute)
                                    {    
                                        if ((int)$currentrouter->route_data == 1)
                                        {
                                            $curroute_data = 'new google.maps.LatLng('.$curroute.')';
                                        }
                                        else
                                        {
                                            $curroute_data = $curroute;
                                        }

                                        if ($cs_idx == 0)
                                        {
                                                $scripttext .= 'var startposition='.$curroute_data.';'."\n";
                                        }
                                        else if ($cs_idx == $cs_total)
                                        {
                                                $scripttext .= 'var endposition='.$curroute_data.';'."\n";
                                        }
                                        else
                                        {
                                                if ($wp_list == '')
                                                {
                                                        $wp_list .= '{ location: '.$curroute_data.', stopover:true }';
                                                }
                                                else
                                                {
                                                        $wp_list .= ', '."\n".'{ location: '.$curroute_data.', stopover:true }';
                                                }
                                        }

                                        $cs_idx += 1;
                                    }



                                    $scripttext .= 'var rendererOptions'. $currentrouter->id.' = {' ."\n";
                                    if (isset($currentrouter->draggable))
                                    {
                                            switch ($currentrouter->draggable) 
                                            {
                                            case 0:
                                                    $scripttext .= 'draggable:false' ."\n";
                                            break;
                                            case 1:
                                                    $scripttext .= 'draggable:true' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= 'draggable:false' ."\n";
                                            break;
                                            }
                                    }
                                    if (isset($currentrouter->showtype))
                                    {
                                            switch ($currentrouter->showtype) 
                                            {
                                            case 0:
                                                    $scripttext .= ', preserveViewport:false' ."\n";
                                            break;
                                            case 1:
                                                    $scripttext .= ', preserveViewport:true' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '' ."\n";
                                            break;
                                            }
                                    }

                                    if (isset($currentrouter->suppressmarkers))
                                    {
                                            switch ($currentrouter->suppressmarkers) 
                                            {
                                            case 0:
                                                    $scripttext .= ', suppressMarkers:false' ."\n";
                                            break;
                                            case 1:
                                                    $scripttext .= ', suppressMarkers:true' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '' ."\n";
                                            break;
                                            }
                                    }

                                    // now you can alter route color options
                                    $scripttext .= ', polylineOptions: {' ."\n"; 
                                    $scripttext .= '    strokeColor: "'.$currentrouter->color.'"'."\n";
                                    $scripttext .= '  , strokeOpacity: '.$currentrouter->opacity."\n";
                                    $scripttext .= '  , strokeWeight: '.$currentrouter->weight."\n";
                                    $scripttext .= '}' ."\n";

                                    $scripttext .= '};' ."\n";

                                    $scripttext .= 'directionsDisplay'. $currentrouter->id.'.setOptions(rendererOptions'. $currentrouter->id.');' ."\n";

                                    $scripttext .= '  var directionsRequest'. $currentrouter->id.' = {' ."\n";
                                    $scripttext .= '    origin: startposition, ' ."\n";
                                    $scripttext .= '    destination: endposition,' ."\n";
                                    if ($wp_list != '')
                                    {
                                            $scripttext .= ' waypoints: ['.$wp_list.'],'."\n";
                                    }
                                    if (isset($currentrouter->providealt) && (int)$currentrouter->providealt == 1) 
                                    {
                                            $scripttext .= 'provideRouteAlternatives: true,' ."\n";
                                    } else {
                                            $scripttext .= 'provideRouteAlternatives: false,' ."\n";
                                    }
                                    if (isset($currentrouter->avoidhighways) && (int)$currentrouter->avoidhighways == 1) 
                                    {
                                            $scripttext .= 'avoidHighways: true,' ."\n";
                                    } else {
                                            $scripttext .= 'avoidHighways: false,' ."\n";
                                    }
                                    if (isset($currentrouter->avoidtolls) && (int)$currentrouter->avoidtolls == 1) 
                                    {
                                            $scripttext .= 'avoidTolls: true,' ."\n";
                                    } else {
                                            $scripttext .= 'avoidTolls: false,' ."\n";
                                    }
                                    if (isset($currentrouter->optimizewaypoints) && (int)$currentrouter->optimizewaypoints == 1) 
                                    {
                                            $scripttext .= 'optimizeWaypoints: true,' ."\n";
                                    } else {
                                            $scripttext .= 'optimizeWaypoints: false,' ."\n";
                                    }

                                    if (isset($currentrouter->travelmode)) 
                                    {
                                            switch ($currentrouter->travelmode) 
                                            {
                                            case 0:
                                            break;
                                            case 1:
                                                    $scripttext .= 'travelMode: google.maps.TravelMode.DRIVING,' ."\n";
                                            break;
                                            case 2:
                                                    $scripttext .= 'travelMode: google.maps.TravelMode.WALKING,' ."\n";
                                            break;
                                            case 3:
                                                    $scripttext .= 'travelMode: google.maps.TravelMode.BICYCLING,' ."\n";
                                            break;
                                            case 4:
                                                    $scripttext .= 'travelMode: google.maps.TravelMode.TRANSIT,' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '' ."\n";
                                            break;
                                            }
                                    }

                                    if (isset($currentrouter->unitsystem)) 
                                    {
                                            switch ($currentrouter->unitsystem) 
                                            {
                                            case 0:
                                            break;
                                            case 1:
                                                    $scripttext .= 'unitSystem: google.maps.UnitSystem.METRIC' ."\n";
                                            break;
                                            case 2:
                                                    $scripttext .= 'unitSystem: google.maps.UnitSystem.IMPERIAL' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '' ."\n";
                                            break;
                                            }
                                    }
                                    $scripttext .= '  };' ."\n";


                                    if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                    {
                                            $scripttext .= 'google.maps.event.addListener(directionsDisplay'. $currentrouter->id.', \'directions_changed\', function() {' ."\n";
                                            $scripttext .= '  computeTotalDistance(directionsDisplay'. $currentrouter->id.'.directions);' ."\n";
                                            $scripttext .= '});' ."\n";
                                    }

                                    $scripttext .= '  directionsService.route(directionsRequest'. $currentrouter->id.', function(result, status) {' ."\n";
                                    $scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
                                    $scripttext .= '      directionsDisplay'. $currentrouter->id.'.setDirections(result);' ."\n";
                                    $scripttext .= '    }' ."\n";
                                    $scripttext .= '    else {' ."\n";
                                    $scripttext .= '        alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
                                    $scripttext .= '    }' ."\n";
                                    $scripttext .= '});' ."\n";

                            }
                            // End Route by Address
                            // Start Route by Marker
                            if ($currentrouter->routebymarker != "")
                            {
                                    $routername ='';
                                    $routername = 'routeByMarker'. $currentrouter->id;
                                    $scripttext .= 'var directionsDisplayByMarker'. $currentrouter->id.' = new google.maps.DirectionsRenderer();' ."\n";
                                    $scripttext .= 'directionsDisplayByMarker'. $currentrouter->id.'.setMap(map'.$mapDivSuffix.');' ."\n";

                                    if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                    {
                                            $scripttext .= 'directionsDisplayByMarker'. $currentrouter->id.'.setPanel(document.getElementById("GMapsRoutePanel'.$mapDivSuffix.'"));' ."\n";
                                            $routepanelcount++;
                                            if (isset($currentrouter->showpaneltotal) && (int)$currentrouter->showpaneltotal == 1) 
                                            {
                                                    $routepaneltotalcount++;
                                            }
                                    }

                                    $cs = explode(";", $currentrouter->routebymarker);
                                    $cs_total = count($cs)-1;
                                    $cs_idx = 0;
                                    $wp_list = '';
                                    $skipRouteCreation = 0;
                                    foreach($cs as $curroute)
                                    {    
                                            $currouteLatLng = MapPlacemarksHelper::get_placemark_coordinates($curroute);
                                            //$scripttext .= 'alert("'.$currouteLatLng.'");'."\n";

                                            if ($currouteLatLng != "")
                                            {
                                                    if ($currouteLatLng == "geocode")
                                                    {
                                                            $scripttext .= 'alert(\''.Text::_('COM_ZHGOOGLEMAP_MAPROUTER_FINDMARKER_ERROR_GEOCODE').' '.$curroute.'\');'."\n";
                                                            $skipRouteCreation = 1;
                                                    }
                                                    else
                                                    {
                                                            if ($cs_idx == 0)
                                                            {
                                                                    $scripttext .= 'var startposition='.$currouteLatLng.';'."\n";
                                                            }
                                                            else if ($cs_idx == $cs_total)
                                                            {
                                                                    $scripttext .= 'var endposition='.$currouteLatLng.';'."\n";
                                                            }
                                                            else
                                                            {
                                                                    if ($wp_list == '')
                                                                    {
                                                                            $wp_list .= '{ location: '.$currouteLatLng.', stopover:true }';
                                                                    }
                                                                    else
                                                                    {
                                                                            $wp_list .= ', '."\n".'{ location: '.$currouteLatLng.', stopover:true }';
                                                                    }
                                                            }
                                                    }
                                            }
                                            else
                                            {
                                                    $scripttext .= 'alert(\''.Text::_('COM_ZHGOOGLEMAP_MAPROUTER_FINDMARKER_ERROR_REASON').' '.$curroute.'\');'."\n";
                                                    $skipRouteCreation = 1;
                                            }

                                            $cs_idx += 1;
                                    }


                                    if ($skipRouteCreation == 0)
                                    {
                                            $scripttext .= 'var rendererOptionsByMarker'. $currentrouter->id.' = {' ."\n";
                                            if (isset($currentrouter->draggable))
                                            {
                                                    switch ($currentrouter->draggable) 
                                                    {
                                                    case 0:
                                                            $scripttext .= 'draggable:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'draggable:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= 'draggable:false' ."\n";
                                                    break;
                                                    }
                                            }
                                            if (isset($currentrouter->showtype))
                                            {
                                                    switch ($currentrouter->showtype) 
                                                    {
                                                    case 0:
                                                            $scripttext .= ', preserveViewport:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= ', preserveViewport:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            if (isset($currentrouter->suppressmarkers))
                                            {
                                                    switch ($currentrouter->suppressmarkers) 
                                                    {
                                                    case 0:
                                                            $scripttext .= ', suppressMarkers:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= ', suppressMarkers:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            // now you can alter route color options
                                            $scripttext .= ', polylineOptions: {' ."\n"; 
                                            $scripttext .= '    strokeColor: "'.$currentrouter->color.'"'."\n";
                                            $scripttext .= '  , strokeOpacity: '.$currentrouter->opacity."\n";
                                            $scripttext .= '  , strokeWeight: '.$currentrouter->weight."\n";
                                            $scripttext .= '}' ."\n";    


                                            $scripttext .= '};' ."\n";

                                            $scripttext .= 'directionsDisplayByMarker'. $currentrouter->id.'.setOptions(rendererOptionsByMarker'. $currentrouter->id.');' ."\n";

                                            $scripttext .= '  var directionsRequestByMarker'. $currentrouter->id.' = {' ."\n";
                                            $scripttext .= '    origin: startposition, ' ."\n";
                                            $scripttext .= '    destination: endposition,' ."\n";
                                            if ($wp_list != '')
                                            {
                                                    $scripttext .= ' waypoints: ['.$wp_list.'],'."\n";
                                            }
                                            if (isset($currentrouter->providealt) && (int)$currentrouter->providealt == 1) 
                                            {
                                                    $scripttext .= 'provideRouteAlternatives: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'provideRouteAlternatives: false,' ."\n";
                                            }
                                            if (isset($currentrouter->avoidhighways) && (int)$currentrouter->avoidhighways == 1) 
                                            {
                                                    $scripttext .= 'avoidHighways: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'avoidHighways: false,' ."\n";
                                            }
                                            if (isset($currentrouter->avoidtolls) && (int)$currentrouter->avoidtolls == 1) 
                                            {
                                                    $scripttext .= 'avoidTolls: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'avoidTolls: false,' ."\n";
                                            }
                                            if (isset($currentrouter->optimizewaypoints) && (int)$currentrouter->optimizewaypoints == 1) 
                                            {
                                                    $scripttext .= 'optimizeWaypoints: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'optimizeWaypoints: false,' ."\n";
                                            }

                                            if (isset($currentrouter->travelmode)) 
                                            {
                                                    switch ($currentrouter->travelmode) 
                                                    {
                                                    case 0:
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.DRIVING,' ."\n";
                                                    break;
                                                    case 2:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.WALKING,' ."\n";
                                                    break;
                                                    case 3:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.BICYCLING,' ."\n";
                                                    break;
                                                    case 4:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.TRANSIT,' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            if (isset($currentrouter->unitsystem)) 
                                            {
                                                    switch ($currentrouter->unitsystem) 
                                                    {
                                                    case 0:
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'unitSystem: google.maps.UnitSystem.METRIC' ."\n";
                                                    break;
                                                    case 2:
                                                            $scripttext .= 'unitSystem: google.maps.UnitSystem.IMPERIAL' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }
                                            $scripttext .= '  };' ."\n";

                                            if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                            {
                                                    $scripttext .= 'google.maps.event.addListener(directionsDisplayByMarker'. $currentrouter->id.', \'directions_changed\', function() {' ."\n";
                                                    $scripttext .= '  computeTotalDistance(directionsDisplayByMarker'. $currentrouter->id.'.directions);' ."\n";
                                                    $scripttext .= '});' ."\n";
                                            }

                                            $scripttext .= '  directionsService.route(directionsRequestByMarker'. $currentrouter->id.', function(result, status) {' ."\n";
                                            $scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
                                            $scripttext .= '      directionsDisplayByMarker'. $currentrouter->id.'.setDirections(result);' ."\n";
                                            $scripttext .= '    }' ."\n";
                                            $scripttext .= '    else {' ."\n";
                                            $scripttext .= '        alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
                                            $scripttext .= '    }' ."\n";
                                            $scripttext .= '});' ."\n";

                                    }
                            }
                            // End Route by Marker

                            // Start Route by CSV file
                            if ($currentrouter->csv_file != "")
                            {
                                $csv_row = 1;
                                if ($currentrouter->csv_sep != "")
                                {
                                    $csv_delim = substr($currentrouter->csv_sep, 0, 1);
                                }
                                else
                                {
                                    $csv_delim = ";";
                                }

                                if (($csv_handle = fopen($currentrouter->csv_file, "r")) !== FALSE) 
                                {
                                    while (($csv_data = fgetcsv($csv_handle, 0, $csv_delim)) !== FALSE) 
                                    {
                                        $csv_num = count($csv_data);
                                        if ($csv_num > 0)
                                        {
                                            // ----------- begin
                                            $routername ='';
                                            $routername = 'routeCSV'. $currentrouter->id.'_'.$csv_row;
                                            $scripttext .= 'var directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.' = new google.maps.DirectionsRenderer();' ."\n";
                                            $scripttext .= 'directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.'.setMap(map'.$mapDivSuffix.');' ."\n";

                                            if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                            {
                                                    $scripttext .= 'directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.'.setPanel(document.getElementById("GMapsRoutePanel'.$mapDivSuffix.'"));' ."\n";
                                                    $routepanelcount++;
                                                    if (isset($currentrouter->showpaneltotal) && (int)$currentrouter->showpaneltotal == 1) 
                                                    {
                                                            $routepaneltotalcount++;
                                                    }
                                            }

                                            $cs = $csv_data;
                                            $cs_total = count($cs)-1;
                                            $cs_idx = 0;
                                            $wp_list = '';
                                            foreach($cs as $curroute)
                                            {    
                                                if ((int)$currentrouter->route_data == 1)
                                                {
                                                    $curroute_data = 'new google.maps.LatLng('.$curroute.')';
                                                }
                                                else
                                                {
                                                    $curroute_data = '"'.$curroute.'"';
                                                }
                                                if ($cs_idx == 0)
                                                {
                                                        $scripttext .= 'var startposition='.$curroute_data.';'."\n";
                                                }
                                                else if ($cs_idx == $cs_total)
                                                {
                                                        $scripttext .= 'var endposition='.$curroute_data.';'."\n";
                                                }
                                                else
                                                {
                                                        if ($wp_list == '')
                                                        {
                                                                $wp_list .= '{ location: '.$curroute_data.', stopover:true }';
                                                        }
                                                        else
                                                        {
                                                                $wp_list .= ', '."\n".'{ location: '.$curroute_data.', stopover:true }';
                                                        }
                                                }

                                                $cs_idx += 1;
                                            }



                                            $scripttext .= 'var rendererOptionsCSV'. $currentrouter->id.'_'.$csv_row.' = {' ."\n";
                                            if (isset($currentrouter->draggable))
                                            {
                                                    switch ($currentrouter->draggable) 
                                                    {
                                                    case 0:
                                                            $scripttext .= 'draggable:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'draggable:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= 'draggable:false' ."\n";
                                                    break;
                                                    }
                                            }
                                            if (isset($currentrouter->showtype))
                                            {
                                                    switch ($currentrouter->showtype) 
                                                    {
                                                    case 0:
                                                            $scripttext .= ', preserveViewport:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= ', preserveViewport:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            if (isset($currentrouter->suppressmarkers))
                                            {
                                                    switch ($currentrouter->suppressmarkers) 
                                                    {
                                                    case 0:
                                                            $scripttext .= ', suppressMarkers:false' ."\n";
                                                    break;
                                                    case 1:
                                                            $scripttext .= ', suppressMarkers:true' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            // now you can alter route color options
                                            $scripttext .= ', polylineOptions: {' ."\n"; 
                                            $scripttext .= '    strokeColor: "'.$currentrouter->color.'"'."\n";
                                            $scripttext .= '  , strokeOpacity: '.$currentrouter->opacity."\n";
                                            $scripttext .= '  , strokeWeight: '.$currentrouter->weight."\n";
                                            $scripttext .= '}' ."\n";

                                            $scripttext .= '};' ."\n";

                                            $scripttext .= 'directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.'.setOptions(rendererOptionsCSV'. $currentrouter->id.'_'.$csv_row.');' ."\n";

                                            $scripttext .= '  var directionsRequestCSV'. $currentrouter->id.'_'.$csv_row.' = {' ."\n";
                                            $scripttext .= '    origin: startposition, ' ."\n";
                                            $scripttext .= '    destination: endposition,' ."\n";
                                            if ($wp_list != '')
                                            {
                                                    $scripttext .= ' waypoints: ['.$wp_list.'],'."\n";
                                            }
                                            if (isset($currentrouter->providealt) && (int)$currentrouter->providealt == 1) 
                                            {
                                                    $scripttext .= 'provideRouteAlternatives: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'provideRouteAlternatives: false,' ."\n";
                                            }
                                            if (isset($currentrouter->avoidhighways) && (int)$currentrouter->avoidhighways == 1) 
                                            {
                                                    $scripttext .= 'avoidHighways: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'avoidHighways: false,' ."\n";
                                            }
                                            if (isset($currentrouter->avoidtolls) && (int)$currentrouter->avoidtolls == 1) 
                                            {
                                                    $scripttext .= 'avoidTolls: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'avoidTolls: false,' ."\n";
                                            }
                                            if (isset($currentrouter->optimizewaypoints) && (int)$currentrouter->optimizewaypoints == 1) 
                                            {
                                                    $scripttext .= 'optimizeWaypoints: true,' ."\n";
                                            } else {
                                                    $scripttext .= 'optimizeWaypoints: false,' ."\n";
                                            }

                                            if (isset($currentrouter->travelmode)) 
                                            {
                                                    switch ($currentrouter->travelmode) 
                                                    {
                                                    case 0:
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.DRIVING,' ."\n";
                                                    break;
                                                    case 2:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.WALKING,' ."\n";
                                                    break;
                                                    case 3:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.BICYCLING,' ."\n";
                                                    break;
                                                    case 4:
                                                            $scripttext .= 'travelMode: google.maps.TravelMode.TRANSIT,' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }

                                            if (isset($currentrouter->unitsystem)) 
                                            {
                                                    switch ($currentrouter->unitsystem) 
                                                    {
                                                    case 0:
                                                    break;
                                                    case 1:
                                                            $scripttext .= 'unitSystem: google.maps.UnitSystem.METRIC' ."\n";
                                                    break;
                                                    case 2:
                                                            $scripttext .= 'unitSystem: google.maps.UnitSystem.IMPERIAL' ."\n";
                                                    break;
                                                    default:
                                                            $scripttext .= '' ."\n";
                                                    break;
                                                    }
                                            }
                                            $scripttext .= '  };' ."\n";


                                            if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
                                            {
                                                    $scripttext .= 'google.maps.event.addListener(directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.', \'directions_changed\', function() {' ."\n";
                                                    $scripttext .= '  computeTotalDistance(directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.'.directions);' ."\n";
                                                    $scripttext .= '});' ."\n";
                                            }

                                            $scripttext .= '  directionsService.route(directionsRequestCSV'. $currentrouter->id.'_'.$csv_row.', function(result, status) {' ."\n";
                                            $scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
                                            $scripttext .= '      directionsDisplayCSV'. $currentrouter->id.'_'.$csv_row.'.setDirections(result);' ."\n";
                                            $scripttext .= '    }' ."\n";
                                            $scripttext .= '    else {' ."\n";
                                            $scripttext .= '        alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
                                            $scripttext .= '    }' ."\n";
                                            $scripttext .= '});' ."\n";                                        
                                            // ----------- end
                                            /*
                                            for ($csv_c=0; $csv_c < $csv_num; $csv_c++) 
                                            {
                                                echo $csv_data[$csv_c] . "<br />\n";

                                            }                                             
                                            */                              
                                        }
                                        $csv_row++;
                                    }
                                    fclose($csv_handle);
                                }
                            }
                            // End Route by CSV file

                            if (isset($currentrouter->showdescription) && (int)$currentrouter->showdescription == 1) 
                            {
                                    if ($currentrouter->description != "")
                                    {
                                            $routeHTMLdescription .= '<h2>';
                                            $routeHTMLdescription .= htmlspecialchars($currentrouter->description, ENT_QUOTES, 'UTF-8');
                                            $routeHTMLdescription .= '</h2>';
                                    }
                                    if ($currentrouter->descriptionhtml != "")
                                    {
                                            $routeHTMLdescription .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentrouter->descriptionhtml));
                                    }
                            }

                    }
                    // End for each Route

                    if ($routepanelcount > 1 || $routepanelcount == 0 || $routepaneltotalcount == 0)
                    {
                            $scripttext .= 'var toHideRouteDiv = document.getElementById("GMapsRoutePanel_Total'.$mapDivSuffix.'");' ."\n";
                            $scripttext .= 'toHideRouteDiv.style.display = "none";' ."\n";
                            //$scripttext .= 'alert("Hide because > 1 or = 0");';
                    }

                    if ($routeHTMLdescription != "")
                    {
                            $scripttext .= '  document.getElementById("GMapsRoutePanel_Description'.$mapDivSuffix.'").innerHTML =  "<p>'. $routeHTMLdescription .'</p>";'."\n";
                    }

                    $scripttext .= 'function computeTotalDistance(result) {' ."\n";
                    if ($routepaneltotalcount == 1)
                    {
                            $scripttext .= '  var total = 0;' ."\n";
                            $scripttext .= '  var myroute = result.routes[0];' ."\n";
                            $scripttext .= '  for (i = 0; i < myroute.legs.length; i++) {' ."\n";
                            $scripttext .= '      total += myroute.legs[i].distance.value;' ."\n";
                            $scripttext .= '  }' ."\n";
                            $scripttext .= '  total = total / 1000.;' ."\n";
                            $scripttext .= '  total = total.toFixed(1);' ."\n";

                            $scripttext .= '  document.getElementById("GMapsRoutePanel_Total'.$mapDivSuffix.'").innerHTML = "<p>'.Text::_('COM_ZHGOOGLEMAP_MAPROUTER_DETAIL_SHOWPANEL_HDR_TOTAL').' " + total + " '.Text::_('COM_ZHGOOGLEMAP_MAPROUTER_DETAIL_SHOWPANEL_HDR_KM').'</p>";' ."\n";
                    }
                    $scripttext .= '};' ."\n";

            }


            // Paths
            if (isset($mappaths) && !empty($mappaths)) 
            {
                    foreach ($mappaths as $key => $currentpath) 
                    {

                        $scripttext .= 'var contentPathString'. $currentpath->id.' = "";'."\n";
                        if (isset($map->useajax) && (int)$map->useajax != 0)
                        {
                            // do not create content string, create by loop only in the end
                        }
                        else
                        {
                            if ((int)$currentpath->actionbyclick == 1)
                            {
                                    // contentPathString - Begin
                                    $scripttext .= 'contentPathString'. $currentpath->id.' = '.
                                                            MapPathsHelper::get_path_content_string(
                                                                    $mapDivSuffix,
                                                                    $currentpath, 
                                                                    $imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang, $placemarkTitleTag);
                                    // contentPathString - End
                            }    
                        }

                        if (isset($currentpath->objecttype))
                        {
                                $current_path_path = str_replace(array("\r", "\r\n", "\n"), '', $currentpath->path);

                                if ($current_path_path != "")
                                {



                                        if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                        {
                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                {
                                                        // do not create content string, create by loop only in the end
                                                }
                                                else
                                                {
                                                        if ((int)$map->hovermarker == 1
                                                          ||(int)$map->hovermarker == 2)
                                                        {
                                                                if ($currentpath->hoverhtml != "")
                                                                {
                                                                        $scripttext .= 'var hoverStringPath'. $currentpath->id.' = '.
                                                                                MapPathsHelper::get_path_hover_string(
                                                                                        $currentpath);                                    
                                                                }
                                                        }
                                                }
                                        }                        

                                        switch ($currentpath->objecttype) 
                                        {
                                                case 0: // LINE

                                                        $scripttext .= ' var allCoordinates'. $currentpath->id.' = [ '."\n";
                                                        $scripttext .=' new google.maps.LatLng('.str_replace(";","), new google.maps.LatLng(", $current_path_path).') '."\n";
                                                        $scripttext .= ' ]; '."\n";
                                                        $scripttext .= ' var plPath'. $currentpath->id.' = new google.maps.Polyline({'."\n";
                                                        $scripttext .= ' path: allCoordinates'. $currentpath->id.','."\n";

                                                        if (isset($currentpath->geodesic) && (int)$currentpath->geodesic == 1) 
                                                        {
                                                                $scripttext .= ' geodesic: true '."\n";
                                                        }
                                                        else
                                                        {
                                                                $scripttext .= ' geodesic: false '."\n";
                                                        }
                                                        $scripttext .= ',strokeColor: "'.$currentpath->color.'"'."\n";
                                                        $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
                                                        $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
                                                        $scripttext .= ' });'."\n";

                                                        // 28.01.2015 - Added GroupManagement
                                                        if (((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
                                                              || (isset($map->markermanager) && (int)$map->markermanager == 1))
                                                          &&(isset($map->markergroupctlpath) 
                                                          && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
                                                        {
                                                                if ($zhgmObjectManager != 0)
                                                                {
                                                                        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'. $currentpath->id.');'."\n";
                                                                }
                                                        }
                                                        else
                                                        {
                                                                $scripttext .= 'plPath'. $currentpath->id.'.setMap(map'.$mapDivSuffix.');'."\n";
                                                        }


                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmPathID", '. $currentpath->id.');' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmObjectType", '. $currentpath->objecttype.');' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmInfowinContent", contentPathString'. $currentpath->id.');' ."\n";    
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'");' ."\n";    

                                                        if ($currentpath->hover_color != "")
                                                        {
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmHoverChangeColor", 1);' ."\n";
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmStrokeColor", "'. $currentpath->color.'");' ."\n";
                                                        }
                                                        else
                                                        {
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmHoverChangeColor", 0);' ."\n";
                                                        }

                                                        // Mouse hover - begin
                                                        if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                        {
                                                                if ($currentpath->hoverhtml != "")
                                                                {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                    }
                                                                    else
                                                                    {
                                                                        if ((int)$map->hovermarker == 1)
                                                                        {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        $scripttext .= '       strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                $scripttext .= '  });' ."\n";

                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        $scripttext .= '       strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                        else if ((int)$map->hovermarker == 2)
                                                                        {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        $scripttext .= '       strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                        $scripttext .= '      });' ."\n";                                            
                                                                                }
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                $scripttext .= '  });' ."\n";

                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        $scripttext .= '       strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                        $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                    }

                                                                }
                                                                else
                                                                {
                                                                        if ($currentpath->hover_color != "")
                                                                        {
                                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                            {
                                                                                    $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                $scripttext .= '       strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                $scripttext .= '      });' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                $scripttext .= '       strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                $scripttext .= '      });' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                            }
                                                                        }                                                    
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if ($currentpath->hover_color != "")
                                                                {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                    }
                                                                    else
                                                                    {
                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                        $scripttext .= '       strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                        $scripttext .= '      });' ."\n";
                                                                        $scripttext .= '  });' ."\n";
                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                        $scripttext .= '       strokeColor: "'.$currentpath->color.'"'."\n";
                                                                        $scripttext .= '      });' ."\n";
                                                                        $scripttext .= '  });' ."\n";
                                                                    }
                                                                }                            
                                                        }                            
                                                        // Mouse hover - end


                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                        {
                                                                // do not create listeners, create by loop only in the end
                                                                $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                        }
                                                        else
                                                        {                                                       
                                                            // Action By Click Path - Begin                            
                                                            switch ((int)$currentpath->actionbyclick)
                                                            {
                                                                    // None
                                                                    case 0:
                                                                    break;
                                                                    // Info
                                                                    case 1:
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                    // Close the other infobubbles
                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                    $scripttext .= '  }' ."\n";
                                                                                    // Hide hover window when feature enabled
                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                    {
                                                                                            if ((int)$map->hovermarker == 1)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                    }
                                                                                    // Open infowin
                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                    {
                                                                                            $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                    }

                                                                                    if ($managePanelInfowin == 1)
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                    }    
                                                                                    else
                                                                                    {
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                                                                                    }
                                                                                            $scripttext .= '  });' ."\n";
                                                                    break;
                                                                    // Link
                                                                    case 2:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                    $scripttext .= '  });' ."\n";                                            
                                                                            }
                                                                    break;
                                                                    // Link in self
                                                                    case 3:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                    $scripttext .= '  });' ."\n";
                                                                            }
                                                                    break;
                                                                    default:
                                                                            $scripttext .= '' ."\n";
                                                                    break;
                                                            }
                                                            // Action By Click Path - End
                                                        }
                                                break;
                                                case 1: //POLYGON
                                                        $scripttext .= ' var allCoordinates'. $currentpath->id.' = [ '."\n";
                                                        $scripttext .=' new google.maps.LatLng('.str_replace(";","), new google.maps.LatLng(", $current_path_path).') '."\n";
                                                        $scripttext .= ' ]; '."\n";
                                                        $scripttext .= ' var plPath'. $currentpath->id.' = new google.maps.Polygon({'."\n";
                                                        $scripttext .= ' path: allCoordinates'. $currentpath->id.','."\n";

                                                        if (isset($currentpath->geodesic) && (int)$currentpath->geodesic == 1) 
                                                        {
                                                                $scripttext .= ' geodesic: true, '."\n";
                                                        }
                                                        else
                                                        {
                                                                $scripttext .= ' geodesic: false, '."\n";
                                                        }
                                                        $scripttext .= ' strokeColor: "'.$currentpath->color.'"'."\n";
                                                        $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
                                                        $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
                                                        if ($currentpath->fillcolor != "")
                                                        {
                                                                $scripttext .= ',fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                        }
                                                        if ($currentpath->fillopacity != "")
                                                        {
                                                                $scripttext .= ',fillOpacity: '.$currentpath->fillopacity."\n";
                                                        }
                                                        $scripttext .= ' });'."\n";

                                                        // 28.01.2015 - Added GroupManagement
                                                        if (((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
                                                                || (isset($map->markermanager) && (int)$map->markermanager == 1))
                                                          &&(isset($map->markergroupctlpath) 
                                                          && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
                                                        {
                                                                if ($zhgmObjectManager != 0)
                                                                {
                                                                        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'. $currentpath->id.');'."\n";
                                                                }
                                                        }
                                                        else
                                                        {
                                                                $scripttext .= 'plPath'. $currentpath->id.'.setMap(map'.$mapDivSuffix.');'."\n";
                                                        }


                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmPathID", '. $currentpath->id.');' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmObjectType", '. $currentpath->objecttype.');' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmInfowinContent", contentPathString'. $currentpath->id.');' ."\n";    
                                                        $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'");' ."\n";    

                                                        if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                        {
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmHoverChangeColor", 1);' ."\n";
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmStrokeColor", "'. $currentpath->color.'");' ."\n";
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmFillColor", "'. $currentpath->fillcolor.'");' ."\n";
                                                        }
                                                        else
                                                        {
                                                            $scripttext .= '  plPath'. $currentpath->id.'.set("zhgmHoverChangeColor", 0);' ."\n";
                                                        }                                                

                                                        // Mouse hover - begin
                                                        if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                        {
                                                                if ($currentpath->hoverhtml != "")
                                                                {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                    }
                                                                    else
                                                                    {                                                                
                                                                        if ((int)$map->hovermarker == 1)
                                                                        {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                        }                                            
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                        }    
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                $scripttext .= '  });' ."\n";

                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                        }                                
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }                                    
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                        }
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                        else if ((int)$map->hovermarker == 2)
                                                                        {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                        }                                            
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                        }            
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                $scripttext .= '  });' ."\n";

                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                        }                                
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }                                    
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                        }
                                                                                        $scripttext .= '      });' ."\n";
                                                                                }
                                                                                        $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                    }    

                                                                }
                                                                else
                                                                {
                                                                        if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                        {
                                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                            {
                                                                                    $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                }                                            
                                                                                if ($currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '    ,';
                                                                                        }                    
                                                                                        else
                                                                                        {
                                                                                                $scripttext .= '     ';
                                                                                        }
                                                                                        $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                }                                        
                                                                                $scripttext .= '      });' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                }                                
                                                                                if ($currentpath->hover_fillcolor != "")
                                                                                {
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '    ,';
                                                                                        }                    
                                                                                        else
                                                                                        {
                                                                                                $scripttext .= '     ';
                                                                                        }                                    
                                                                                        $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                }
                                                                                $scripttext .= '      });' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                            }
                                                                        }                                                    
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                {
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                    }
                                                                    else
                                                                    {
                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                        if ($currentpath->hover_color != "")
                                                                        {
                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                        }                                            
                                                                        if ($currentpath->hover_fillcolor != "")
                                                                        {
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    ,';
                                                                                }                    
                                                                                else
                                                                                {
                                                                                        $scripttext .= '     ';
                                                                                }
                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                        }
                                                                        $scripttext .= '      });' ."\n";
                                                                        $scripttext .= '  });' ."\n";
                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                        $scripttext .= '    plPath'. $currentpath->id.'.setOptions({' ."\n";    
                                                                        if ($currentpath->hover_color != "")
                                                                        {
                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                        }                                
                                                                        if ($currentpath->hover_fillcolor != "")
                                                                        {
                                                                                if ($currentpath->hover_color != "")
                                                                                {
                                                                                        $scripttext .= '    ,';
                                                                                }                    
                                                                                else
                                                                                {
                                                                                        $scripttext .= '     ';
                                                                                }                                    
                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                        }
                                                                        $scripttext .= '      });' ."\n";
                                                                        $scripttext .= '  });' ."\n";
                                                                    }
                                                                }                            
                                                        }                            
                                                        // Mouse hover - end


                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                        {
                                                                // do not create listeners, create by loop only in the end
                                                                $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                        }
                                                        else
                                                        {
                                                            // Action By Click Path - Begin                            
                                                            switch ((int)$currentpath->actionbyclick)
                                                            {
                                                                    // None
                                                                    case 0:
                                                                    break;
                                                                    // Info
                                                                    case 1:
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                    // Close the other infobubbles
                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                    $scripttext .= '  }' ."\n";
                                                                                    // Hide hover window when feature enabled
                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                    {
                                                                                            if ((int)$map->hovermarker == 1)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                            {
                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            }
                                                                                    }
                                                                                    // Open infowin
                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                    {
                                                                                            $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                    }

                                                                                    if ($managePanelInfowin == 1)
                                                                                    {
                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                    }    
                                                                                    else
                                                                                    {                                            
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                                                                                    }
                                                                                    $scripttext .= '  });' ."\n";
                                                                    break;
                                                                    // Link
                                                                    case 2:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                    $scripttext .= '  });' ."\n";                                            
                                                                            }
                                                                    break;
                                                                    // Link in self
                                                                    case 3:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                    $scripttext .= '  });' ."\n";
                                                                            }
                                                                    break;
                                                                    default:
                                                                            $scripttext .= '' ."\n";
                                                                    break;
                                                            }
                                                            // Action By Click Path - End

                                                        }
                                                break;
                                                case 2: //CIRCLE
                                                    if ($currentpath->radius != "")
                                                        {
                                                                $arrayPathCoords = explode(';', $current_path_path);
                                                                $arrayPathIndex = 0;
                                                                foreach ($arrayPathCoords as $currentpathcoordinates) 
                                                                {
                                                                        $arrayPathIndex += 1;

                                                                        $scripttext .= ' var plPath'.$arrayPathIndex.'_'. $currentpath->id.' = new google.maps.Circle({'."\n";
                                                                        $scripttext .= ' center: new google.maps.LatLng('.$currentpathcoordinates.')'."\n";
                                                                        $scripttext .= ',radius: '.$currentpath->radius."\n";
                                                                        $scripttext .= ',strokeColor: "'.$currentpath->color.'"'."\n";
                                                                        $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
                                                                        $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
                                                                        if ($currentpath->fillcolor != "")
                                                                        {
                                                                                $scripttext .= ',fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                        }
                                                                        if ($currentpath->fillopacity != "")
                                                                        {
                                                                                $scripttext .= ',fillOpacity: '.$currentpath->fillopacity."\n";
                                                                        }
                                                                        $scripttext .= '  });' ."\n";


                                                                        // 28.01.2015 - Added GroupManagement
                                                                        if (((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
                                                                                || (isset($map->markermanager) && (int)$map->markermanager == 1))
                                                                          &&(isset($map->markergroupctlpath) 
                                                                          && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
                                                                        {
                                                                                if ($zhgmObjectManager != 0)
                                                                                {
                                                                                        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                $scripttext .= 'plPath'.$arrayPathIndex.'_'. $currentpath->id.'.setMap(map'.$mapDivSuffix.');'."\n";
                                                                        }


                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmPathID", '. $currentpath->id.');' ."\n";
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmObjectType", '. $currentpath->objecttype.');' ."\n";
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmInfowinContent", contentPathString'. $currentpath->id.');' ."\n";    
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'");' ."\n";    

                                                                        if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                        {
                                                                            $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmHoverChangeColor", 1);' ."\n";
                                                                            $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmStrokeColor", "'. $currentpath->color.'");' ."\n";
                                                                            $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmFillColor", "'. $currentpath->fillcolor.'");' ."\n";
                                                                        }
                                                                        else
                                                                        {
                                                                            $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.set("zhgmHoverChangeColor", 0);' ."\n";
                                                                        }    

                                                                        // Mouse hover - begin
                                                                        if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                        {
                                                                                if ($currentpath->hoverhtml != "")
                                                                                {
                                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                    {
                                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                    }
                                                                                    else
                                                                                    {                                                                                
                                                                                        if ((int)$map->hovermarker == 1)
                                                                                        {
                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                                        }                                            
                                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                                        {
                                                                                                                if ($currentpath->hover_color != "")
                                                                                                                {
                                                                                                                        $scripttext .= '    ,';
                                                                                                                }                    
                                                                                                                else
                                                                                                                {
                                                                                                                        $scripttext .= '     ';
                                                                                                                }
                                                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                                        }    
                                                                                                        $scripttext .= '      });' ."\n";
                                                                                                }
                                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'.$currentpath->id.');' ."\n";
                                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                                $scripttext .= '  });' ."\n";

                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                                        }                                
                                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                                        {
                                                                                                                if ($currentpath->hover_color != "")
                                                                                                                {
                                                                                                                        $scripttext .= '    ,';
                                                                                                                }                    
                                                                                                                else
                                                                                                                {
                                                                                                                        $scripttext .= '     ';
                                                                                                                }                                    
                                                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                                        }
                                                                                                        $scripttext .= '      });' ."\n";
                                                                                                }
                                                                                                $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                $scripttext .= '  });' ."\n";
                                                                                        }
                                                                                        else if ((int)$map->hovermarker == 2)
                                                                                        {
                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                                        }                                            
                                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                                        {
                                                                                                                if ($currentpath->hover_color != "")
                                                                                                                {
                                                                                                                        $scripttext .= '    ,';
                                                                                                                }                    
                                                                                                                else
                                                                                                                {
                                                                                                                        $scripttext .= '     ';
                                                                                                                }
                                                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                                        }            
                                                                                                        $scripttext .= '      });' ."\n";
                                                                                                }
                                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'.$currentpath->id.');' ."\n";
                                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                                $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                                $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                                $scripttext .= '  });' ."\n";

                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                                        }                                
                                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                                        {
                                                                                                                if ($currentpath->hover_color != "")
                                                                                                                {
                                                                                                                        $scripttext .= '    ,';
                                                                                                                }                    
                                                                                                                else
                                                                                                                {
                                                                                                                        $scripttext .= '     ';
                                                                                                                }                                    
                                                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                                        }
                                                                                                        $scripttext .= '      });' ."\n";
                                                                                                }
                                                                                                        $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                $scripttext .= '  });' ."\n";
                                                                                        }
                                                                                    }

                                                                                }
                                                                                else
                                                                                {
                                                                                        if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                            if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                            {
                                                                                                    $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                                $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                                }                                            
                                                                                                if ($currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '    ,';
                                                                                                        }                    
                                                                                                        else
                                                                                                        {
                                                                                                                $scripttext .= '     ';
                                                                                                        }
                                                                                                        $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                                }                                        
                                                                                                $scripttext .= '      });' ."\n";
                                                                                                $scripttext .= '  });' ."\n";
                                                                                                $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                                $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                                }                                
                                                                                                if ($currentpath->hover_fillcolor != "")
                                                                                                {
                                                                                                        if ($currentpath->hover_color != "")
                                                                                                        {
                                                                                                                $scripttext .= '    ,';
                                                                                                        }                    
                                                                                                        else
                                                                                                        {
                                                                                                                $scripttext .= '     ';
                                                                                                        }                                    
                                                                                                        $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                                }
                                                                                                $scripttext .= '      });' ."\n";
                                                                                                $scripttext .= '  });' ."\n";
                                                                                            }
                                                                                        }                                                    
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                {
                                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                    {
                                                                                            $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseover\', function(event) {' ."\n";
                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->hover_color.'"'."\n";
                                                                                        }                                            
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->hover_fillcolor.'"'."\n";
                                                                                        }
                                                                                        $scripttext .= '      });' ."\n";
                                                                                        $scripttext .= '  });' ."\n";
                                                                                        $scripttext .= '  google.maps.event.addListener(plPath'. $arrayPathIndex.'_'. $currentpath->id.', \'mouseout\', function(event) {' ."\n";
                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setOptions({' ."\n";    
                                                                                        if ($currentpath->hover_color != "")
                                                                                        {
                                                                                                $scripttext .= '     strokeColor: "'.$currentpath->color.'"'."\n";
                                                                                        }                                
                                                                                        if ($currentpath->hover_fillcolor != "")
                                                                                        {
                                                                                                if ($currentpath->hover_color != "")
                                                                                                {
                                                                                                        $scripttext .= '    ,';
                                                                                                }                    
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '     ';
                                                                                                }                                    
                                                                                                $scripttext .= 'fillColor: "'.$currentpath->fillcolor.'"'."\n";
                                                                                        }
                                                                                        $scripttext .= '      });' ."\n";
                                                                                        $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                }                            
                                                                        }                                                                
                                                                        // Mouse hover - end


                                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                        {
                                                                                // do not create listeners, create by loop only in the end
                                                                                $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                        }
                                                                        else
                                                                        { 
                                                                            // Action By Click Path - Begin                            
                                                                            switch ((int)$currentpath->actionbyclick)
                                                                            {
                                                                                    // None
                                                                                    case 0:
                                                                                    break;
                                                                                    // Info
                                                                                    case 1:
                                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'.$arrayPathIndex.'_'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                                    $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                    // Close the other infobubbles
                                                                                                    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                                    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                                    $scripttext .= '  }' ."\n";
                                                                                                    // Hide hover window when feature enabled
                                                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                                                    {
                                                                                                            if ((int)$map->hovermarker == 1)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }
                                                                                                            else if ((int)$map->hovermarker == 2)
                                                                                                            {
                                                                                                                    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                            }
                                                                                                    }
                                                                                                    // Open infowin
                                                                                                    if ((int)$map->markerlistpos != 0)
                                                                                                    {
                                                                                                            $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                                    }

                                                                                                    if ($managePanelInfowin == 1)
                                                                                                    {
                                                                                                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                    }    
                                                                                                    else
                                                                                                    {
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                                            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";    
                                                                                                    }                                            

                                                                                                    $scripttext .= '  });' ."\n";
                                                                                    break;
                                                                    // Link
                                                                    case 2:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'.$arrayPathIndex.'_'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                    $scripttext .= '  });' ."\n";                                            
                                                                            }
                                                                    break;
                                                                    // Link in self
                                                                    case 3:
                                                                            if ($currentpath->hrefsite != "")
                                                                            {
                                                                                    $scripttext .= '  google.maps.event.addListener(plPath'.$arrayPathIndex.'_'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                                    $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                    $scripttext .= '  });' ."\n";
                                                                            }
                                                                    break;
                                                                                    default:
                                                                                            $scripttext .= '' ."\n";
                                                                                    break;
                                                                            }
                                                                            // Action By Click Path - End

                                                                        }

                                                                }
                                                        }
                                                break;
                                        }

                                        if ($featurePathElevation == 1)
                                        {
                                                if ((int)$currentpath->elevation != 0
                                                 && (int)$currentpath->objecttype == 0
                                                 )
                                                {
                                                        if ($currentpath->elevationicontype == "")
                                                        {
                                                                $elevationMouseOverIcon = 'gm#simple-lightblue';
                                                        }
                                                        else
                                                        {
                                                                $elevationMouseOverIcon = $currentpath->elevationicontype;
                                                        }
                                                        $elevationMouseOverIcon = str_replace("#", "%23", $elevationMouseOverIcon).'.png';

                                                        $scripttext .= 'elevationPlotDiagram'.$mapDivSuffix.'(allCoordinates'. $currentpath->id.', '.
                                                                                                                                (int)$currentpath->elevationcount.', '.
                                                                                                                                (int)$currentpath->elevationwidth.', '.
                                                                                                                                (int)$currentpath->elevationheight.', '.
                                                                                                                                '"'.$elevationMouseOverIcon.'", '.
                                                                                                                                (int)$currentpath->elevation.','.
                                                                                                                                (int)$currentpath->elevationbaseline.','.
                                                                                                                                '"'.$currentpath->v_baseline_color.'", '.
                                                                                                                                '"'.$currentpath->v_gridline_color.'", '.
                                                                                                                                (int)(int)$currentpath->v_gridline_count.', '.
                                                                                                                                '"'.$currentpath->v_minor_gridline_color.'", '.
                                                                                                                                (int)$currentpath->v_minor_gridline_count.', '.
                                                                                                                                '"'.$currentpath->background_color_stroke.'", '.
                                                                                                                                (int)$currentpath->background_color_width.', '.
                                                                                                                                '"'.$currentpath->background_color_fill.'", '.
                                                                                                                                '"'.$currentpath->v_max_value.'", '.
                                                                                                                                '"'.$currentpath->v_min_value.'"'.
                                                                                                                                ');' ."\n";
                                                }
                                        }


                                }
                        }

                        if ($currentpath->kmllayer != "") 
                        {
                                    $scripttext .= 'var kmlOptions'. $currentpath->id.' = {' ."\n";
                                    if (isset($currentpath->showtype))
                                    {
                                            switch ($currentpath->showtype) 
                                            {
                                            case 0:
                                                    $scripttext .= 'preserveViewport:false' ."\n";
                                            break;
                                            case 1:
                                                    $scripttext .= 'preserveViewport:true' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= 'preserveViewport:false' ."\n";
                                            break;
                                            }
                                    }
                                    else
                                    {
                                            $scripttext .= 'preserveViewport:false' ."\n";
                                    }
                                    if (isset($currentpath->suppressinfowindows))
                                    {
                                            if ((int)$currentpath->suppressinfowindows == 1)
                                            {
                                                    $scripttext .= ', suppressInfoWindows:true' ."\n";
                                            }
                                            else
                                            {
                                                    $scripttext .= ', suppressInfoWindows:false' ."\n";
                                            }
                                    }
                                    $scripttext .= '};' ."\n";

                                    $scripttext .= 'var kmlLayer'. $currentpath->id.' = new google.maps.KmlLayer(\''.$currentpath->kmllayer.'\', kmlOptions'. $currentpath->id.');' ."\n";

                                    if (((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
                                            || (isset($map->markermanager) && (int)$map->markermanager == 1))
                                      &&(isset($map->markergroupctlpath) 
                                      && (((int)$map->markergroupctlpath == 1) || ((int)$map->markergroupctlpath == 3))))
                                    {
                                            if ($zhgmObjectManager != 0)
                                            {
                                                    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAdd('.$currentpath->markergroup.', kmlLayer'. $currentpath->id.');'."\n";
                                            }
                                    }
                                    else
                                    {
                                            $scripttext .= 'kmlLayer'. $currentpath->id.'.setMap(map'.$mapDivSuffix.');' ."\n";
                                    }




                                    if ($featurePathElevationKML == 1)
                                    {                
                                            if ((int)$currentpath->elevation != 0)
                                            {
                                                            if ($currentpath->elevationicontype == "")
                                                            {
                                                                    $elevationMouseOverIcon = 'gm#simple-lightblue';
                                                            }
                                                            else
                                                            {
                                                                    $elevationMouseOverIcon = $currentpath->elevationicontype;
                                                            }
                                                            $elevationMouseOverIcon = str_replace("#", "%23", $elevationMouseOverIcon).'.png';

                                                            //$scripttext .= ' alert("step1");' ."\n";

                                                            $scripttext .= '    var myParser = new geoXML3.parser({afterParse: useTheData});' ."\n";

                                                            //$scripttext .= ' alert("step2");' ."\n";

                                                            $scripttext .= '    myParser.parse(\''.$currentpath->kmllayer.'\');' ."\n";

                                                            //$scripttext .= ' alert("step3");' ."\n";

                                                            $scripttext .= '    function useTheData(doc) {' ."\n";
                                                            // Geodata handling goes here, using JSON properties of the doc object
                                                            $scripttext .= '        var SAMPLES = '.(int)$currentpath->elevationcountkml.';' ."\n";
                                                            $scripttext .= '        var pathx;' ."\n";

                                                            //$scripttext .= ' alert("doc = "+doc.length);' ."\n";

                                                            $scripttext .= '        var geoXmlDoc = doc[0];' ."\n";
                                                            //$scripttext .= ' alert("count = "+geoXmlDoc.placemarks.length);' ."\n";
                                                            $scripttext .= '          for (var i = 0; i < geoXmlDoc.placemarks.length; i++) {' ."\n";
                                                            $scripttext .= '            var placemark = geoXmlDoc.placemarks[i];' ."\n";
                                                            $scripttext .= '            if (placemark.polyline) {' ."\n";
                                                            $scripttext .= '              if (!pathx) {' ."\n";
                                                            $scripttext .= '                pathx = [];' ."\n";
                                                            $scripttext .= '                var samples = placemark.polyline.getPath().getLength();' ."\n";
                                                            $scripttext .= '                var incr = 1;' ."\n";
                                                            $scripttext .= '                if (SAMPLES != 0) ' ."\n";
                                                            $scripttext .= '                {' ."\n";
                                                            $scripttext .= '                    incr = samples/SAMPLES;' ."\n";
                                                            $scripttext .= '                    if (incr < 1) incr = 1;' ."\n";
                                                            $scripttext .= '                }' ."\n";
                                                            $scripttext .= '                for (var i=0;i<samples; i+=incr)' ."\n";
                                                            $scripttext .= '                {' ."\n";
                                                            $scripttext .= '                  pathx.push(placemark.polyline.getPath().getAt(parseInt(i)));' ."\n";
                                                            $scripttext .= '                }' ."\n";
                                                            $scripttext .= '              }' ."\n";                     
                                                            $scripttext .= '            }' ."\n";
                                                            $scripttext .= '          }' ."\n";
                                                            $scripttext .= '        if (pathx) {' ."\n";
                                                            $scripttext .= '        elevationPlotDiagram'.$mapDivSuffix.'(pathx, '.
                                                                                                                                    (int)$currentpath->elevationcount.', '.
                                                                                                                                    (int)$currentpath->elevationwidth.', '.
                                                                                                                                    (int)$currentpath->elevationheight.', '.
                                                                                                                                    '"'.$elevationMouseOverIcon.'", '.
                                                                                                                                    (int)$currentpath->elevation.','.
                                                                                                                                    (int)$currentpath->elevationbaseline.','.
                                                                                                                                    '"'.$currentpath->v_baseline_color.'", '.
                                                                                                                                    '"'.$currentpath->v_gridline_color.'", '.
                                                                                                                                    (int)$currentpath->v_gridline_count.', '.
                                                                                                                                    '"'.$currentpath->v_minor_gridline_color.'", '.
                                                                                                                                    (int)$currentpath->v_minor_gridline_count.', '.
                                                                                                                                    '"'.$currentpath->background_color_stroke.'", '.
                                                                                                                                    (int)$currentpath->background_color_width.', '.
                                                                                                                                    '"'.$currentpath->background_color_fill.'", '.
                                                                                                                                    '"'.$currentpath->v_max_value.'", '.
                                                                                                                                    '"'.$currentpath->v_min_value.'"'.
                                                                                                                                    ');' ."\n";
                                                            $scripttext .= '        }' ."\n";
                                                            $scripttext .= '    };' ."\n";
                                            }
                                    }

                        }

                        if ($currentpath->imgurl != ""
                            && $currentpath->imgbounds != "") 
                        {


                            $imgGroundBoundsArray = explode(";", str_replace(',',';',$currentpath->imgbounds));
                            if (count($imgGroundBoundsArray) != 4)
                            {
                                $scripttext .= 'alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_ERROR_IMGGROUNDBOUNDS').'");'."\n";
                            }
                            else
                            {
                                $scripttext .= 'var imgGroundBounds'. $currentpath->id.' = new google.maps.LatLngBounds(' ."\n";
                                $scripttext .= '      new google.maps.LatLng('.$imgGroundBoundsArray[0].', '.$imgGroundBoundsArray[1].'),' ."\n";
                                $scripttext .= '      new google.maps.LatLng('.$imgGroundBoundsArray[2].', '.$imgGroundBoundsArray[3].'));' ."\n";


                                $scripttext .= 'var imgGroundOptions'. $currentpath->id.' = {' ."\n";
                                if (isset($currentpath->imgopacity))
                                {
                                    if ($currentpath->imgopacity != "")
                                    {
                                        $scripttext .= '  opacity:'.$currentpath->imgopacity ."\n";
                                    }
                                    else
                                    {
                                        $scripttext .= '  opacity: 1'."\n";
                                    }    
                                }
                                else
                                {
                                    $scripttext .= '  opacity: 1'."\n";
                                }

                                if (isset($currentpath->imgclickable))
                                {
                                        if ((int)$currentpath->imgclickable == 1)
                                        {
                                                $scripttext .= ', clickable:true' ."\n";
                                        }
                                        else
                                        {
                                                $scripttext .= ', clickable:false' ."\n";
                                        }
                                }
                                $scripttext .= '};' ."\n";

                                $scripttext .= 'var imgGroundLayer'. $currentpath->id.' = new google.maps.GroundOverlay(\''.$currentpath->imgurl.'\', imgGroundBounds'. $currentpath->id.', imgGroundOptions'. $currentpath->id.');' ."\n";

                                $scripttext .= '  imgGroundLayer'. $currentpath->id.'.set("zhgmPathID", '. $currentpath->id.');' ."\n";
                                $scripttext .= '  imgGroundLayer'. $currentpath->id.'.set("zhgmInfowinContent", contentPathString'. $currentpath->id.');' ."\n";    
                                $scripttext .= '  imgGroundLayer'. $currentpath->id.'.set("zhgmTitle", "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'");' ."\n";    


                                $scripttext .= 'imgGroundLayer'. $currentpath->id.'.setMap(map'.$mapDivSuffix.');' ."\n";

                                if ($needOverlayControl != 0)
                                {
                                    if ((int)$currentpath->imgopacitymanage == 1)
                                    {
                                        $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.addGroundOverlay(imgGroundLayer'.$currentpath->id.');'."\n";
                                    }
                                }


                                if (isset($currentpath->imgclickable))
                                {
                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                    {
                                            // do not create listeners, create by loop only in the end
                                            $scripttext .= '  ajaxpathsOVL'.$mapDivSuffix.'.push(imgGroundLayer'. $currentpath->id.');'."\n";
                                    }
                                    else
                                    {
                                        if ((int)$currentpath->imgclickable == 1)
                                        {                    
                                                // Action By Click Path - Begin                            
                                                switch ((int)$currentpath->actionbyclick)
                                                {
                                                        // None
                                                        case 0:
                                                        break;
                                                        // Info
                                                        case 1:
                                                                        $scripttext .= '  google.maps.event.addListener(imgGroundLayer'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                        $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                        // Close the other infobubbles
                                                                        $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                        $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                        $scripttext .= '  }' ."\n";
                                                                        // Hide hover window when feature enabled
                                                                        if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))    
                                                                        {
                                                                                if ((int)$map->hovermarker == 1)
                                                                                {
                                                                                        $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                }
                                                                                else if ((int)$map->hovermarker == 2)
                                                                                {
                                                                                        $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                }
                                                                        }
                                                                        // Open infowin
                                                                        if ((int)$map->markerlistpos != 0)
                                                                        {
                                                                                $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                        }

                                                                        if ($managePanelInfowin == 1)
                                                                        {
                                                                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                        }    
                                                                        else
                                                                        {                                            
                                                                                $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.get("zhgmInfowinContent"));' ."\n";
                                                                                $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.latLng);' ."\n";
                                                                                $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
                                                                        }
                                                                        $scripttext .= '  });' ."\n";
                                                        break;
                                                        // Link
                                                        case 2:
                                                                if ($currentpath->hrefsite != "")
                                                                {
                                                                        $scripttext .= '  google.maps.event.addListener(imgGroundLayer'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                        $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                        $scripttext .= '  });' ."\n";                                            
                                                                }
                                                        break;
                                                        // Link in self
                                                        case 3:
                                                                if ($currentpath->hrefsite != "")
                                                                {
                                                                        $scripttext .= '  google.maps.event.addListener(imgGroundLayer'. $currentpath->id.', \'click\', function(event) {' ."\n";
                                                                        $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                        $scripttext .= '  });' ."\n";
                                                                }
                                                        break;
                                                        default:
                                                                $scripttext .= '' ."\n";
                                                        break;
                                                }
                                                // Action By Click Path - End    
                                        }

                                    }
                                }
                            }



                        }



                    }



            }

            if (isset($map->useajax) && (int)$map->useajax != 0) 
            {

                $scripttext .= 'for (var i=0; i<ajaxpaths'.$mapDivSuffix.'.length; i++)' ."\n";
                $scripttext .= '{' ."\n";
                        if ((int)$map->useajax == 1)
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddListeners("mootools", ajaxpaths'.$mapDivSuffix.'[i]);' ."\n";
                        }
                        else
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddListeners("jquery", ajaxpaths'.$mapDivSuffix.'[i]);' ."\n";
                        }
                $scripttext .= '}' ."\n";

                // For Hovering Feature - Begin
                $scripttext .= 'for (var i=0; i<ajaxpathshover'.$mapDivSuffix.'.length; i++)' ."\n";
                $scripttext .= '{' ."\n";
                //$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].get("zhgmPlacemarkID"));' ."\n";
                    if ((int)$map->useajax == 1)
                    {
                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddHoverListeners("mootools", ajaxpathshover'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    else
                    {
                            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddHoverListeners("jquery", ajaxpathshover'.$mapDivSuffix.'[i]);' ."\n";
                    }
                $scripttext .= '}' ."\n";

                // For Hovering Feature - End   

                $scripttext .= 'for (var i=0; i<ajaxpathsOVL'.$mapDivSuffix.'.length; i++)' ."\n";
                $scripttext .= '{' ."\n";
                        if ((int)$map->useajax == 1)
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddListeners("mootools", ajaxpathsOVL'.$mapDivSuffix.'[i]);' ."\n";
                        }
                        else
                        {
                                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PathAddListeners("jquery", ajaxpathsOVL'.$mapDivSuffix.'[i]);' ."\n";
                        }
                $scripttext .= '}' ."\n";            
            }


            if ($needOverlayControl != 0)
            {

                $scripttext .= '  var overlayOpacityControl = new zhgmOverlayOpacityControl('.
                        '"'.$mapDivSuffix.'",'. 
                        'map'.$mapDivSuffix.','. 
                        'zhgmObjMgr'.$mapDivSuffix.','.
                        $feature4control.','. 
                        (int)$map->overlayopacitycontrol.','. 
                        (int)$map->overlayopacitycontrolpos.','. 
                        '"opacityoverlay",'. 
                        '"'.Text::_('COM_ZHGOOGLEMAP_MAP_OPACITY_OVERLAY_CONTROL').'"'.
                        ');'."\n";       


            }

            // Map center - begin
            if ((int)$map->mapcentercontrol != 0) 
            {

                            $scripttext .= '  var mapcenterControl = new zhgmMapCenterButtonControl('.
                                    'latlng'.$mapDivSuffix.','.
                                    '"'.$ctrl_zoom.'",'.
                                    'map'.$mapDivSuffix.','. 
                                    $feature4control.','. 
                                    (int)$map->mapcentercontrol.','. 
                                    (int)$map->mapcentercontrolpos.','. 
                                    '"mapcenter",'. 
                                    '"'.Text::_('COM_ZHGOOGLEMAP_MAP_HOMECONTROL_LABEL').'",'.
                                    '19,'. 
                                    '16,'. 
                                    '"'.$imgpathUtils.'home.png"'.
                                    ');'."\n";                

            }
            // Map center - end    


            // Traffic Layer - Begin
            if ((int)$map->trafficcontrol == 0) 
            {
                    // Do not create button when layer is not enabled
            }
            else
            {
                    $scripttext .= '  var trafficLayer = new google.maps.TrafficLayer();' ."\n";
                    if ((int)$map->trafficcontrol == 1)
                    {
                            $scripttext .= 'trafficLayer.setMap(map'.$mapDivSuffix.');' ."\n";
                    }
                    else
                    {

                            $scripttext .= '  var trafficLayerControl = new zhgmLayerButtonControl('.
                                'map'.$mapDivSuffix.','. 
                                    'trafficLayer,'.
                                    $feature4control.','. 
                                    (int)$map->trafficcontrol.','. 
                                    (int)$map->trafficcontrolpos.','. 
                                    '"traffic",'. 
                                    '"'.Text::_('COM_ZHGOOGLEMAP_MAP_TRAFFICLAYER').'",'.
                                    '14,'. 
                                    '16,'. 
                                    '"'.$imgpathUtils.'traffic_light.png"'.
                                    ');'."\n";
                    }
            }
            // Traffic Layer - End    

            //  Transit - begin
            if ((int)$map->transitcontrol == 0) 
            {
                    // Do not create button when layer is not enabled
            }
            else
            {
                    $scripttext .= '  var transitLayer = new google.maps.TransitLayer();' ."\n";
                    if ((int)$map->transitcontrol == 1)
                    {
                            $scripttext .= 'transitLayer.setMap(map'.$mapDivSuffix.');' ."\n";
                    }
                    else
                    {

                            $scripttext .= '  var transitLayerControl = new zhgmLayerButtonControl('.
                                'map'.$mapDivSuffix.','. 
                                    'transitLayer,'.
                                    $feature4control.','. 
                                    (int)$map->transitcontrol.','. 
                                    (int)$map->transitcontrolpos.','. 
                                    '"transit",'. 
                                    '"'.Text::_('COM_ZHGOOGLEMAP_MAP_TRANSITLAYER').'",'.
                                    '17,'. 
                                    '16,'. 
                                    '"'.$imgpathUtils.'bus.png"'.
                                    ');'."\n";

                    }
            }
            //Transit - end

            // Bike - begin
            if ((int)$map->bikecontrol == 0) 
            {
                    // Do not create button when layer is not enabled
            }
            else
            {
                    $scripttext .= '  var bikeLayer = new google.maps.BicyclingLayer();' ."\n";
                    if ((int)$map->bikecontrol == 1)
                    {
                            $scripttext .= 'bikeLayer.setMap(map'.$mapDivSuffix.');' ."\n";
                    }
                    else
                    {

                            $scripttext .= '  var bikeLayerControl = new zhgmLayerButtonControl('.
                                'map'.$mapDivSuffix.','. 
                                    'bikeLayer,'.
                                    $feature4control.','. 
                                    (int)$map->bikecontrol.','. 
                                    (int)$map->bikecontrolpos.','. 
                                    '"bike",'. 
                                    '"'.Text::_('COM_ZHGOOGLEMAP_MAP_BIKELAYER').'",'.
                                    '19,'. 
                                    '16,'. 
                                    '"'.$imgpathUtils.'bike.png"'.
                                    ');'."\n";                        
                    }
            }
            // Bike - end



            if (isset($map->kmllayer) && $map->kmllayer != "") 
            {
                    $scripttext .= 'var kmlLayer'.$mapDivSuffix.' = new google.maps.KmlLayer(\''.$map->kmllayer.'\');' ."\n";
                    $scripttext .= 'kmlLayer'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');' ."\n";
            }




            // Places Library - Begin
            if (isset($map->placesenable) && (int)$map->placesenable == 1) 
            {
                    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
                    {
                            $this->places = 1;
                    }
                    else
                    {
                            $places = 1;
                    }


                    if ((int)$map->placesradius != 0)
                    {
                            $scripttext .= 'var requestPlaces'.$mapDivSuffix.' = {' ."\n";
                            $scripttext .= '  location: latlng'.$mapDivSuffix.',' ."\n";
                            $scripttext .= '  radius: '.(int)$map->placesradius.',' ."\n";
                            $scripttext .= '  types: ['.$map->placestype.']' ."\n";
                            $scripttext .= '  };' ."\n";

                            $scripttext .= '  var servicePlaces'.$mapDivSuffix.' = new google.maps.places.PlacesService(map'.$mapDivSuffix.');' ."\n";
                            $scripttext .= '  servicePlaces'.$mapDivSuffix.'.search(requestPlaces'.$mapDivSuffix.', callbackPlaces'.$mapDivSuffix.');' ."\n";
                    }

                    $scripttext .= 'var placesDirectionsDisplay'.$mapDivSuffix.';' ."\n";
                    $scripttext .= 'var placesDirectionsService'.$mapDivSuffix.';' ."\n";

                    if ((isset($map->placesenable) && (int)$map->placesenable == 1)
                     && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1) 
                     && (isset($map->findcontrol) && (int)$map->findcontrol == 0)
                     )
                    {
                            if (isset($map->placesdirection) && (int)$map->placesdirection == 1) 
                            {
                                    $scripttext .= 'placesDirectionsDisplay'.$mapDivSuffix.' = new google.maps.DirectionsRenderer();' ."\n";
                                    $scripttext .= 'placesDirectionsService'.$mapDivSuffix.' = new google.maps.DirectionsService();' ."\n";
                                    $scripttext .= 'placesDirectionsDisplay'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');' ."\n";

                                    if (isset($map->routeshowpanel) && (int)$map->routeshowpanel == 1) 
                                    {
                                            $scripttext .= 'placesDirectionsDisplay'.$mapDivSuffix.'.setPanel(document.getElementById("GMapsMainRoutePanel'.$mapDivSuffix.'"));' ."\n";
                                    }

                            }


                            $scripttext .= 'var optionsPlacesAC'.$mapDivSuffix.' = {' ."\n";
                            $scripttext .= '  types: ['.$map->placestypeac.']' ."\n";
                            $scripttext .= '  };' ."\n";

                        $scripttext .= '  var inputPlacesAC'.$mapDivSuffix.' = document.getElementById(\'searchTextField'.$mapDivSuffix.'\');' ."\n";
                            $scripttext .= '  var autocompletePlaces'.$mapDivSuffix.' = new google.maps.places.Autocomplete(inputPlacesAC'.$mapDivSuffix.', optionsPlacesAC'.$mapDivSuffix.');' ."\n";
                            $scripttext .= '  var markerPlacesAC'.$mapDivSuffix.' = new google.maps.Marker({' ."\n";
                            $scripttext .= '    map: map'.$mapDivSuffix.'' ."\n";
                            $scripttext .= '  });' ."\n";
                            // Strange bug with clicking on bouncing marker, or others
                            //   scrolls to markerAC and show infowin from bouncing
                //$scripttext .= '  google.maps.event.addListener(markerPlacesAC'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
                //$scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
                //$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(markerPlacesAC'.$mapDivSuffix.'.getTitle());' ."\n";
                //$scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', markerPlacesAC'.$mapDivSuffix.');' ."\n";
                //$scripttext .= '    });' ."\n";

                            $scripttext .= '  autocompletePlaces'.$mapDivSuffix.'.bindTo(\'bounds\', map'.$mapDivSuffix.');' ."\n";

                            $scripttext .= '  google.maps.event.addListener(autocompletePlaces'.$mapDivSuffix.', \'place_changed\', function() {' ."\n";

                $scripttext .= '  var place = autocompletePlaces'.$mapDivSuffix.'.getPlace();' ."\n";

                            $scripttext .= '  placesACbyButton'.$mapDivSuffix.'('.(int)$map->placesdirection.', placesDirectionsDisplay'.$mapDivSuffix.', placesDirectionsService'.$mapDivSuffix.', markerPlacesAC'.$mapDivSuffix.', place.name, "searchTravelMode'.$mapDivSuffix.'", place.geometry.location, routedestination'.$mapDivSuffix.');'."\n";

                $scripttext .= '  });' ."\n";
                    }


            }
            // Places Library - End



            if ((isset($map->autoposition) && (int)$map->autoposition == 1))
            {
                            $scripttext .= 'findMyPosition'.$mapDivSuffix.'("Map");' ."\n";
            }


            // 06.12.2017 Added link to Google map page like for placemark
            if ((int)$map->balloon != 0) 
            {
                if ((int)$map->gogoogle_map == 20 || (int)$map->gogoogle_map == 21 
                  ||(int)$map->gogoogle_map == 22 || (int)$map->gogoogle_map == 23
                  ||(int)$map->gogoogle_map == 24 || (int)$map->gogoogle_map == 25
                  ||(int)$map->gogoogle_map == 30 || (int)$map->gogoogle_map == 31
                  ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33
                  ||(int)$map->gogoogle_map == 34 || (int)$map->gogoogle_map == 35
                )
                {    

                        if ((int)$map->gogoogle_map == 20 
                         || (int)$map->gogoogle_map == 22
                         || (int)$map->gogoogle_map == 24
                         || (int)$map->gogoogle_map == 30
                         || (int)$map->gogoogle_map == 32
                         || (int)$map->gogoogle_map == 34
                        )
                        {
                                $linkTarget = " target=\"_blank\"";
                        }
                        else
                        {
                                $linkTarget = "";
                        }

                        if ($credits != '')
                        {
                                $credits .= '<br />';
                        }
                        $credits .= '<div id="bodyContentGoGoogle" class="placemarkBodyGoGoogle">';                
                        $credits .= '<p><a class="placemarkGOGOOGLE" href="';

                            if ((int)$map->gogoogle_map == 22 || (int)$map->gogoogle_map == 23
                              ||(int)$map->gogoogle_map == 24 || (int)$map->gogoogle_map == 25
                              ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33
                              ||(int)$map->gogoogle_map == 34 || (int)$map->gogoogle_map == 35
                            )
                            {
                                $credits .= 'https://maps.google.com/?ll='.
                                                $map->latitude.','.$map->longitude;    
                                $credits .= '&amp;z='.$map->zoom; 
                                if ((int)$map->gogoogle_map == 22 || (int)$map->gogoogle_map == 23
                                  ||(int)$map->gogoogle_map == 32 || (int)$map->gogoogle_map == 33)
                                {
                                    $credits .= '&amp;q='.htmlspecialchars(str_replace('\\', '/', $map->title) , ENT_QUOTES, 'UTF-8');
                                }
                                else
                                {
                                    $credits .= '&amp;q='.$map->latitude.','.$map->longitude;    
                                }
                                if ($main_lang_little != "")
                                {
                                    $credits .= '&amp;hl='.$main_lang_little;    
                                }                            
                            }
                            else
                            {
                                $credits .= 'https://maps.google.com/maps?saddr=Current%20Location&amp;daddr='.
                                                $map->latitude.','.$map->longitude;                            
                            }

                        $credits .= '" '.$linkTarget.' title="'.$fv_override_gogoogle_text.
                                '">'.$fv_override_gogoogle_text.'</a></p>';
                        $credits .= '</div>';

                }     
            }

            if ($credits != '')
            {
                    $scripttext .= '  document.getElementById("GMapsCredit'.$mapDivSuffix.'").innerHTML = \''.$credits.'\';'."\n";
            }


            //$scripttext .= 'alert("'.$doAddToListCount.'");'."\n";


        $scripttext .= ' google.maps.event.addListenerOnce(map'.$mapDivSuffix.', \'idle\', function(event) {' ."\n";
            if (isset($doShowDivGeo) && (int)$doShowDivGeo == 1)
            {
                    $scripttext .= ' var toShowDivGeo = document.getElementById("geoLocation'.$mapDivSuffix.'");' ."\n";
                    $scripttext .= ' toShowDivGeo.style.display = "block";' ."\n";
            }

            if (isset($doShowDivFind) && (int)$doShowDivFind == 1)
            {
                    $scripttext .= ' var toShowDivFind = document.getElementById("GMapFindAddress'.$mapDivSuffix.'");' ."\n";
                    $scripttext .= ' toShowDivFind.style.display = "block";' ."\n";
            }

            // Do open list if preset to yes
            if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
            {
                    if ((int)$map->markerlistpos == 111
                      ||(int)$map->markerlistpos == 112
                      ||(int)$map->markerlistpos == 121
                      ) 
                    {
                            // We don't have to do in any case when table or external
                            // because it displayed        
                    }
                    else
                    {
                            if ((int)$map->markerlistbuttontype == 0
                            ||(int)$map->markerlistpos == 120 // panel
                            )                
                            {
                                    // Open because for non-button
                                    $scripttext .= '    var toShowDiv = document.getElementById("GMapsMarkerList'.$mapDivSuffix.'");' ."\n";
                                    $scripttext .= '    toShowDiv.style.display = "block";' ."\n";
                            }
                            else
                            {
                                    switch ($map->markerlistbuttontype) 
                                    {
                                            case 0:
                                                    $scripttext .= '    var toShowDiv = document.getElementById("GMapsMarkerList'.$mapDivSuffix.'");' ."\n";
                                                    $scripttext .= '    toShowDiv.style.display = "block";' ."\n";
                                            break;
                                            case 1:
                                                    $scripttext .= '';
                                            break;
                                            case 2:
                                                    $scripttext .= '';
                                            break;
                                            case 11:
                                                    $scripttext .= '    var toShowDiv = document.getElementById("GMapsMarkerList'.$mapDivSuffix.'");' ."\n";
                                                    $scripttext .= '    toShowDiv.style.display = "block";' ."\n";
                                            break;
                                            case 12:
                                                    $scripttext .= '    var toShowDiv = document.getElementById("GMapsMarkerList'.$mapDivSuffix.'");' ."\n";
                                                    $scripttext .= '    toShowDiv.style.display = "block";' ."\n";
                                            break;
                                            default:
                                                    $scripttext .= '';
                                            break;
                                    }
                            }

                    }    
            }
            // Open Placemark List Presets


        $scripttext .= '});' ."\n";


            if ((int)$map->maptype == 9)
            {
                $scripttext .= 'mapPanorama'.$mapDivSuffix.' = map'.$mapDivSuffix.'.getStreetView();'."\n";
                $scripttext .= 'mapPanorama.setPosition(latlng'.$mapDivSuffix.');'."\n";
                $mapSV = MapPlacemarksHelper::get_StreetViewOptions($map->streetviewstyleid);
                if ($mapSV != "")
                {
                        $scripttext .= 'mapPanorama'.$mapDivSuffix.'.setPov('.$mapSV.');'."\n";
                }
                $scripttext .= 'mapPanorama.setVisible(true);'."\n";           
            }

        } 
        else
        {
            $scripttext .= 'var panoramaOptions = {' ."\n";
            $scripttext .= '  position: latlng'.$mapDivSuffix.'' ."\n";
            $mapSV = MapPlacemarksHelper::get_StreetViewOptions($map->streetviewstyleid);
            if ($mapSV != "")
            {
                    $scripttext .= ', pov: '.$mapSV ."\n";
            }

            $scripttext .= '};' ."\n";
            $scripttext .= 'panorama'.$mapDivSuffix.' = new google.maps.StreetViewPanorama(document.getElementById("GMapsID'.$mapDivSuffix.'"), panoramaOptions);' ."\n";
        }
        // main content end
    
    $scripttext .= 'var toShowLoading = document.getElementById("GMapsLoading'.$mapDivSuffix.'");'."\n";
    $scripttext .= '  toShowLoading.style.display = \'none\';'."\n";
    
    
        if ((int)$map_street_view_content == 0)
        {
            if ($zhgmObjectManager != 0)
            {
                    if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0))
                    {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setGroupCountObject('.(int)$map->markergroupshowicon.');' ."\n";

                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enableObjectGroupManagement();' ."\n";

                            if ((isset($map->markergrouptype) && (int)$map->markergrouptype == 1))
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.setObjectGroupManagementType("OnlyOneActive");' ."\n";
                            }


                            if ((isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker != 0))
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkGroupManagement();' ."\n";
                            }
                            if (isset($map->markergroupctlpath) 
                            && (((int)$map->markergroupctlpath == 1) || ((int)$map->markergroupctlpath == 3)))
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePathGroupManagement();' ."\n";
                            }

                            if (isset($map->markergroupctlpath) 
                            && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3)))
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePathXGroupManagement();' ."\n";
                            }

                    }


                    if ((isset($map->markercluster) && (int)$map->markercluster == 1))
                    {
                            $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkClusterization();' ."\n";
                            if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
                            {
                                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.enablePlacemarkClusterizationByGroup();' ."\n";
                            }
                    }

                    $scripttext .= 'zhgmObjMgr'.$mapDivSuffix.'.InitializeByGroupState();'."\n";

            }
    
        }
        
// end initialize
$scripttext .= '};' ."\n";

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
	
	$scripttext .= 'function ShowGPDRMessage'.$mapDivSuffix.'() {'."\n";
	$scripttext .= ' document.getElementById("GMapsID'.$mapDivSuffix.'").innerHTML = \''.str_replace(array("\r", "\r\n", "\n"), '', str_replace('\'', '\\\'', $gpdr_text)).'\';' ."\n";
	
	$scripttext .= '};' ."\n"; 
}
//
//

if ((isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))  
  || ($ajaxLoadObjects != 0)
  || ($ajaxLoadContent != 0)
  )
{
    $scripttext .= 'function PlacemarkRateOver'.$mapDivSuffix.'(p_id, p_idx, p_max) {' ."\n";
    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateMouseOver(p_id, p_idx, p_max);' ."\n";
    $scripttext .= '};' ."\n";

    $scripttext .= 'function PlacemarkRateOut'.$mapDivSuffix.'(p_id, p_idx, p_max) {' ."\n";
    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateMouseOut(p_id, p_idx, p_max);' ."\n";
    $scripttext .= '};' ."\n";

    $scripttext .= 'function PlacemarkRateDivOut'.$mapDivSuffix.'(p_id, p_max) {' ."\n";
    $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateDivMouseOut(p_id, p_max);' ."\n";    
    $scripttext .= '};' ."\n";
    
    $scripttext .= 'function PlacemarkRateUpdate'.$mapDivSuffix.'(p_id, p_val, p_max) {' ."\n";
    if ($ajaxLoadObjects != 0)
    {
        if ($ajaxLoadObjects == 1)
        {
            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
        }
        else if ($ajaxLoadObjects == 2)
        {
            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
        }
    }
    else
    {
        if ((int)$map->useajax != 0)
        {
            if ((int)$map->useajax == 1)
            { 
                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
            }
            else if ((int)$map->useajax == 2)
            {
                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
            }
        }
        else 
        {
            if ((int)$map->placemark_rating == 1)
            { 
                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
            }
            else if ((int)$map->placemark_rating == 2)
            {
                $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";    
            }
        }
        
    }
    $scripttext .= '};' ."\n";
    
}

    $scripttext .= 'function PlacemarkByIDShow(p_id, p_action, p_zoom) {' ."\n";
    if ($externalmarkerlink == 1)
    {
        $scripttext .= '  if (p_zoom != undefined && p_zoom != "")' ."\n";
        $scripttext .= '  {' ."\n";
        $scripttext .= '      map'.$mapDivSuffix.'.setZoom(p_zoom);' ."\n";
        $scripttext .= '  }' ."\n";

        $scripttext .= '  if( allPlacemarkArray[p_id] === undefined ) ' ."\n";
        $scripttext .= '  {' ."\n";
        $scripttext .= '      alert("Unable to find placemark with ID = " + p_id);' ."\n";
        $scripttext .= '  }' ."\n";
        $scripttext .= '  else' ."\n";
        $scripttext .= '  {' ."\n";
        $scripttext .= '    cur_action = p_action.toLowerCase().split(",");' ."\n";
        $scripttext .= '    for (i = 0; i < cur_action.length; i++) {' ."\n";
        $scripttext .= '      if (cur_action[i] == "click")' ."\n";
        $scripttext .= '      {' ."\n";
        $scripttext .= '        google.maps.event.trigger(allPlacemarkArray[p_id].markerobject, "click");' ."\n";
        $scripttext .= '      }' ."\n";
        $scripttext .= '      else if (cur_action[i] == "center")' ."\n";
        $scripttext .= '      {' ."\n";
        $scripttext .= '          map'.$mapDivSuffix.'.setCenter(allPlacemarkArray[p_id].latlngobject);' ."\n";
        $scripttext .= '      }' ."\n";
        $scripttext .= '    }' ."\n";
        $scripttext .= '  }' ."\n";
    }
    else
    {
        $scripttext .= '      alert("This feature is supported only when you enable it in map menu item or module property!");' ."\n";
    }
    $scripttext .= '}' ."\n";
    

    if ($externalmarkerlink == 1)
    {
        $scripttext .= 'function PlacemarkByID(p_id, p_lat, p_lng, p_obj, p_ll, p_rate) {' ."\n";
        $scripttext .= 'this.id = p_id;' ."\n";
        $scripttext .= 'this.lat = p_lat;' ."\n";
        $scripttext .= 'this.lng = p_lng;' ."\n";
        $scripttext .= 'this.markerobject = p_obj;' ."\n";
        $scripttext .= 'this.latlngobject = p_ll;' ."\n";
        $scripttext .= 'this.rate = p_rate;' ."\n";
        $scripttext .= '}' ."\n";
        
        $scripttext .= 'function PlacemarkByIDAdd(p_id, p_lat, p_lng, p_obj, p_ll, p_rate) {' ."\n";
        $scripttext .= '    allPlacemarkArray[p_id] = new PlacemarkByID(p_id, p_lat, p_lng, p_obj, p_ll, p_rate);' ."\n";
        $scripttext .= '}' ."\n";
    }
    
    
    // Infowin content generated by helper. Need more changes, static methods...
    //if ($zhgmObjectManager == 0) 
    //{
        $scripttext .= 'function showPlacemarkPanorama'.$mapDivSuffix.'(p_width, p_height, p_pov) {' ."\n";
        if ($managePanelInfowin == 1)
        {
            $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.panelPlacemarkShowPanorama(p_width, p_height, p_pov);' ."\n";
        }
        else
        {
            $scripttext .= '  PlacemarkShowPanorama(map'.$mapDivSuffix.', infowindow'.$mapDivSuffix.', p_width, p_height, p_pov, "'.$mapDivSuffix.'", infowindow'.$mapDivSuffix.'.zhgmPlacemarkOriginalPosition);' ."\n";
        }
        $scripttext .= '};' ."\n";
    //}


    //13.04.2016 disabled if not exists find control
    //if (isset($map->findcontrol) && (int)$map->findcontrol == 1) 
        // 21.03.2017 use the other way to show (like in placemark)
        if ($service_DoDirection == 1)
    {    
        $scripttext .= 'function setRouteDestination'.$mapDivSuffix.'(p_direction) {' ."\n";
        $scripttext .= '  routedirection'.$mapDivSuffix.' = p_direction;' ."\n";
        $scripttext .= '  var cur_panel = document.getElementById("GMapFindPanelIcon'.$mapDivSuffix.'");' ."\n";
        $scripttext .= '  var cur_target = document.getElementById("GMapFindTargetIcon'.$mapDivSuffix.'");' ."\n";
        $scripttext .= '  var cur_target_text = document.getElementById("GMapFindTargetText'.$mapDivSuffix.'");' ."\n";
        $scripttext .= '  cur_target_text.innerHTML = \'\'+infowindow'.$mapDivSuffix.'.get("zhgmPlacemarkTitle")+\'\';'."\n";
        $scripttext .= '  if (routedirection'.$mapDivSuffix.' == 1)' ."\n";
        $scripttext .= '  {' ."\n";
        $scripttext .= '      cur_panel.innerHTML = \'<a href="#" title="'.Text::_('COM_ZHGOOGLEMAP_ROUTER_ACTION_CHANGE_DIRECTION').'" onclick="';
        $scripttext .= 'setRouteDestinationChange'.$mapDivSuffix.'();';
        $scripttext .= 'return false;"><img style="border: 0px none; padding: 0px; margin: 0px;" src="'.$imgpathUtils.'a.png"></a>\';'."\n";
        $scripttext .= '      cur_target.innerHTML = \'<a href="#" title="'.Text::_('COM_ZHGOOGLEMAP_ROUTER_ACTION_CHANGE_DIRECTION').'" onclick="';
        $scripttext .= 'setRouteDestinationChange'.$mapDivSuffix.'();';
        $scripttext .= 'return false;"><img style="border: 0px none; padding: 0px; margin: 0px;" src="'.$imgpathUtils.'b.png"></a>\';'."\n";
        $scripttext .= '  }' ."\n";
        $scripttext .= '  else' ."\n";
        $scripttext .= '  {' ."\n";
        $scripttext .= '      cur_panel.innerHTML = \'<a href="#" title="'.Text::_('COM_ZHGOOGLEMAP_ROUTER_ACTION_CHANGE_DIRECTION').'" onclick="';
        $scripttext .= 'setRouteDestinationChange'.$mapDivSuffix.'();';
        $scripttext .= 'return false;"><img style="border: 0px none; padding: 0px; margin: 0px;" src="'.$imgpathUtils.'b.png"></a>\';'."\n";
        $scripttext .= '      cur_target.innerHTML = \'<a href="#" title="'.Text::_('COM_ZHGOOGLEMAP_ROUTER_ACTION_CHANGE_DIRECTION').'" onclick="';
        $scripttext .= 'setRouteDestinationChange'.$mapDivSuffix.'();';
        $scripttext .= 'return false;"><img style="border: 0px none; padding: 0px; margin: 0px;" src="'.$imgpathUtils.'a.png"></a>\';'."\n";
        $scripttext .= '  }' ."\n";
        $scripttext .= '  routedestination'.$mapDivSuffix.' = infowindow'.$mapDivSuffix.'.get("zhgmPlacemarkOriginalPosition");' ."\n";
        $scripttext .= '};' ."\n";

        $scripttext .= 'function setRouteDestinationChange'.$mapDivSuffix.'() {' ."\n";
        $scripttext .= '    if (routedirection'.$mapDivSuffix.' == 0)' ."\n";
        $scripttext .= '    {' ."\n";
        $scripttext .= '        setRouteDestination'.$mapDivSuffix.'(1);' ."\n";
        $scripttext .= '    }' ."\n";
        $scripttext .= '    else' ."\n";
        $scripttext .= '    {' ."\n";
        $scripttext .= '        setRouteDestination'.$mapDivSuffix.'(0);' ."\n";
        $scripttext .= '    }' ."\n";
        $scripttext .= '};' ."\n";
    }

if ($featurePathElevation == 1
  || $featurePathElevationKML == 1)
{
    $scripttext .= 'function elevationPlotDiagram'.$mapDivSuffix.'(' ."\n";
    $scripttext .= ' el_coords, ' ."\n";
    $scripttext .= ' el_count, ' ."\n";
    $scripttext .= ' el_width, ' ."\n";
    $scripttext .= ' el_height, ' ."\n";
    $scripttext .= ' el_icon, ' ."\n";
    $scripttext .= ' el_type, ' ."\n";
    $scripttext .= ' el_baseline,' ."\n";
    $scripttext .= ' el_baseline_color,' ."\n";
    $scripttext .= ' el_gridline_color,' ."\n";
    $scripttext .= ' el_gridline_count,' ."\n";
    $scripttext .= ' el_minor_gridline_color,' ."\n";
    $scripttext .= ' el_minor_gridline_count,'."\n";
    $scripttext .= ' el_bg_color,' ."\n";
    $scripttext .= ' el_bg_width,' ."\n";
    $scripttext .= ' el_bg_fill,' ."\n";
    $scripttext .= ' el_max_value,' ."\n";
    $scripttext .= ' el_min_value' ."\n";
    $scripttext .= ' ) ';
    $scripttext .= ' {' ."\n";
    $scripttext .= ' zhgmObjMgr'.$mapDivSuffix.'.ElevationShowDiagram('."\n";
    $scripttext .= ' el_coords, ' ."\n";
    $scripttext .= ' el_count, ' ."\n";
    $scripttext .= ' el_width, ' ."\n";
    $scripttext .= ' el_height, ' ."\n";
    $scripttext .= ' el_icon, ' ."\n";
    $scripttext .= ' el_type, ' ."\n";
    $scripttext .= ' el_baseline,' ."\n";
    $scripttext .= ' el_baseline_color,' ."\n";
    $scripttext .= ' el_gridline_color,' ."\n";
    $scripttext .= ' el_gridline_count,' ."\n";
    $scripttext .= ' el_minor_gridline_color,' ."\n";
    $scripttext .= ' el_minor_gridline_count,'."\n";
    $scripttext .= ' el_bg_color,' ."\n";
    $scripttext .= ' el_bg_width,' ."\n";
    $scripttext .= ' el_bg_fill,' ."\n";
    $scripttext .= ' el_max_value,' ."\n";
    $scripttext .= ' el_min_value' ."\n";
    $scripttext .= ' );';    
    $scripttext .= '}' ."\n";

    
    // Remove the green rollover marker when the mouse leaves the chart
    $scripttext .= 'function clearMarkerElevation'.$mapDivSuffix.'() {' ."\n";
    //$scripttext .= '  if (markerElevation != null) {' ."\n";
    //$scripttext .= '    markerElevation.setMap(null);' ."\n";
    //$scripttext .= '    markerElevation = null;' ."\n";
    //$scripttext .= '  }' ."\n";
    $scripttext .= '}' ."\n";
}

    


    if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
    {
        $scripttext .= 'function callToggleGroup'.$mapDivSuffix.'(groupid){   ' ."\n";
        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GroupStateToggle(groupid);' ."\n";
        $scripttext .= '}'."\n";
        
        $scripttext .= 'function callShowAllGroup'.$mapDivSuffix.'(){   ' ."\n";
        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GroupStateShowAll();' ."\n";
        $scripttext .= '}'."\n";

        $scripttext .= 'function callHideAllGroup'.$mapDivSuffix.'(){   ' ."\n";
        $scripttext .= '  zhgmObjMgr'.$mapDivSuffix.'.GroupStateHideAll();' ."\n";
        $scripttext .= '}'."\n";
    }



    // For Places Support functions
    if ((isset($map->placesenable) && (int)$map->placesenable == 1))
    {
      $scripttext .= 'function callbackPlaces'.$mapDivSuffix.'(results, status) {' ."\n";
      $scripttext .= '  if (status == google.maps.places.PlacesServiceStatus.OK) {' ."\n";
      $scripttext .= '    for (var i = 0; i < results.length; i++) {' ."\n";
      $scripttext .= '      createPlacesMarker'.$mapDivSuffix.'(results[i]);' ."\n";
      $scripttext .= '    }' ."\n";
      $scripttext .= '  }' ."\n";
      $scripttext .= '};' ."\n";

      $scripttext .= 'function createPlacesMarker'.$mapDivSuffix.'(place) {' ."\n";
      // Clusterization not implementing (like map baloon, into clusterer0)
      //   because limit = only 20 in result
      $scripttext .= 'var marker'.$mapDivSuffix.' = new google.maps.Marker({' ."\n";
      $scripttext .= '    map: map'.$mapDivSuffix.', ' ."\n";
      $scripttext .= '    position: place.geometry.location' ."\n";
      $scripttext .= '  });' ."\n";

      $scripttext .= '  var image = new google.maps.MarkerImage(' ."\n";
      $scripttext .= '  place.icon, null,' ."\n";
      $scripttext .= '  new google.maps.Point(0, 0), null,' ."\n";
      $scripttext .= '  new google.maps.Size(25, 25));' ."\n";
      $scripttext .= '  marker'.$mapDivSuffix.'.setIcon(image);' ."\n";

      $scripttext .= ' google.maps.event.addListener(marker'.$mapDivSuffix.', \'click\', function(event) {' ."\n";
      $scripttext .= '    var markerTitle = place.name;' ."\n";
      $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
      $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(markerTitle);' ."\n";
      $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', marker'.$mapDivSuffix.');' ."\n";
      $scripttext .= ' });' ."\n";
      $scripttext .= '};' ."\n";
    }

    
    // Geo Position - Begin
    if ((isset($map->autoposition) && (int)$map->autoposition == 1)
     || (isset($map->geolocationcontrol) && (int)$map->geolocationcontrol == 1))
    {
            $scripttext .= 'function findMyPosition'.$mapDivSuffix.'(AutoPosition, DirectionsDisplay, DirectionsService, Marker, SearchTravelMode, LocationDestination) {' ."\n";
            // Try W3C Geolocation method (Preferred)
            //$scripttext .= 'alert("Try to find");'."\n";
            $scripttext .= '    if (navigator.geolocation) {' ."\n";
            //$scripttext .= 'alert("Try W3C Geolocation method");'."\n";
            $scripttext .= '        browserSupportFlag = true;' ."\n";
            $scripttext .= '        navigator.geolocation.getCurrentPosition(function(position) {' ."\n";
            $scripttext .= '        initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);' ."\n";
            $scripttext .= '        map'.$mapDivSuffix.'.setCenter(initialLocation);' ."\n";
            //$scripttext .= '          alertContentString = "Location found using W3C standard";' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.setContent(alertContentString);' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.setPosition(initialLocation);' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
            if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
            {
                if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                {
                    $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(initialLocation);'."\n";
                }
            }

            $scripttext .= '     if (AutoPosition == "Button")' ."\n";
            $scripttext .= '     {' ."\n";
            $scripttext .= '         placesACbyButton'.$mapDivSuffix.'(0, DirectionsDisplay, DirectionsService, Marker, "", SearchTravelMode, initialLocation, LocationDestination);' ."\n";
            $scripttext .= '     }' ."\n";

            $scripttext .= '        }, function() {' ."\n";
            $scripttext .= '          handleNoGeolocation(browserSupportFlag);' ."\n";
            $scripttext .= '        });' ."\n";
            $scripttext .= '    } else if (google.gears) {' ."\n";
            // Try Google Gears Geolocation
            //$scripttext .= 'alert("Try Google Gears Geolocation");'."\n";
            $scripttext .= '        browserSupportFlag = true;' ."\n";
            $scripttext .= '        var geo = google.gears.factory.create(\'beta.geolocation\');' ."\n";
            $scripttext .= '        geo.getCurrentPosition(function(position) {' ."\n";
            $scripttext .= '        initialLocation = new google.maps.LatLng(position.latitude,position.longitude);' ."\n";
            $scripttext .= '        map'.$mapDivSuffix.'.setCenter(initialLocation);' ."\n";
            //$scripttext .= '          alertContentString = "Location found using Google Gears";' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.setContent(alertContentString);' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.setPosition(initialLocation);' ."\n";
            //$scripttext .= '          infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";

            if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
            {
                if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                {
                    $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(initialLocation);'."\n";
                }
            }

            $scripttext .= '     if (AutoPosition == "Button")' ."\n";
            $scripttext .= '     {' ."\n";
            $scripttext .= '         placesACbyButton'.$mapDivSuffix.'(0, DirectionsDisplay, DirectionsService, Marker, "", SearchTravelMode, initialLocation, LocationDestination);' ."\n";
            $scripttext .= '     }' ."\n";

            $scripttext .= '        }, function() {' ."\n";
            $scripttext .= '          handleNoGeolocation(browserSupportFlag);' ."\n";
            $scripttext .= '        });' ."\n";
            $scripttext .= '    } else {' ."\n";
            // Browser doesn\'t support Geolocation
            //$scripttext .= 'alert("Browser doesn\'t support Geolocation");'."\n";
            $scripttext .= '        browserSupportFlag = false;' ."\n";
            $scripttext .= '        handleNoGeolocation(browserSupportFlag);' ."\n";
            $scripttext .= '    }' ."\n";
            $scripttext .= '};' ."\n";

            $scripttext .= 'function handleNoGeolocation(errorFlag) {' ."\n";
            $scripttext .= '  if (errorFlag == true) {' ."\n";
            $scripttext .= '    alertContentString = "'.Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATION_FAILED').'";' ."\n";
            $scripttext .= '  } else {' ."\n";
            $scripttext .= '    alertContentString = "'.Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATION_BROWSER_NOT_SUPPORT').'";' ."\n";
            $scripttext .= '  }' ."\n";
            $scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(map'.$mapDivSuffix.'.getCenter());' ."\n";
            $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(alertContentString);' ."\n";
            $scripttext .= '  infowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.');' ."\n";
            $scripttext .= '};' ."\n";

            
    }
    // Geo Position - End
    
    // Begin placesACbyButton (for geo position button)
        if (((isset($map->placesenable) && (int)$map->placesenable == 1) 
             && (isset($map->placesautocomplete) && (int)$map->placesautocomplete == 1))
         || (isset($map->findcontrol) && (int)$map->findcontrol == 1) )
        {
            
            $scripttext .= ' function placesACbyButton'.$mapDivSuffix.'(routeDoIt, placesDDisplay, placesDService, markerPlacesAC, markerPlacesACText, searchTravelModeAC, placeLocation, placeDestination) {' ."\n";
            
            $scripttext .= '  infowindow'.$mapDivSuffix.'.close();' ."\n";
            
            $scripttext .= '  if (routeDoIt == 1) ' ."\n";
            $scripttext .= '  {' ."\n";
                $scripttext .= '  var searchTravelMode = document.getElementById(searchTravelModeAC);' ."\n";
                $scripttext .= '  var searchTravelModeText = searchTravelMode.options[searchTravelMode.selectedIndex].value;' ."\n";
                //$scripttext .= '  alert("Mode="+searchTravelModeText);' ."\n";
                $scripttext .= '  var searchTravelModeValue;'."\n";
                $scripttext .= '  if (searchTravelModeText == "google.maps.TravelMode.DRIVING") ';
                $scripttext .= '  {' ."\n";
                $scripttext .= '    searchTravelModeValue = google.maps.TravelMode.DRIVING;';
                $scripttext .= '  }' ."\n";
                $scripttext .= '  else if (searchTravelModeText == "google.maps.TravelMode.WALKING")' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    searchTravelModeValue = google.maps.TravelMode.WALKING;';
                $scripttext .= '  }' ."\n";
                $scripttext .= '  else if (searchTravelModeText == "google.maps.TravelMode.BICYCLING")' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    searchTravelModeValue = google.maps.TravelMode.BICYCLING;';
                $scripttext .= '  }' ."\n";
                $scripttext .= '  else if (searchTravelModeText == "google.maps.TravelMode.TRANSIT")' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    searchTravelModeValue = google.maps.TravelMode.TRANSIT;';
                $scripttext .= '  }' ."\n";
                $scripttext .= '  else' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    searchTravelModeValue = ""';
                $scripttext .= '  }' ."\n";

                $scripttext .= 'var placesDirectionsRendererOptions = {' ."\n";
                if (isset($map->routedraggable))
                {
                    switch ($map->routedraggable) 
                    {
                    case 0:
                        $scripttext .= 'draggable:false' ."\n";
                    break;
                    case 1:
                        $scripttext .= 'draggable:true' ."\n";
                    break;
                    default:
                        $scripttext .= 'draggable:false' ."\n";
                    break;
                    }
                }
                $scripttext .= '};' ."\n";

                $scripttext .= '  placesDDisplay.setOptions(placesDirectionsRendererOptions);' ."\n";
                $scripttext .= '  if (routedirection'.$mapDivSuffix.' == 1)' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    var placesDirectionsRequest = {' ."\n";
                $scripttext .= '      origin: placeLocation, ' ."\n";
                $scripttext .= '      destination: placeDestination,' ."\n";
                // to do - move to parameters -- begin
                if (isset($map->routeavoidhighways) && (int)$map->routeavoidhighways == 1) 
                {
                    $scripttext .= '      avoidHighways: true,' ."\n";
                } else {
                    $scripttext .= '      avoidHighways: false,' ."\n";
                }
                if (isset($map->routeavoidtolls) && (int)$map->routeavoidtolls == 1) 
                {
                    $scripttext .= '      avoidTolls: true,' ."\n";
                } else {
                    $scripttext .= '      avoidTolls: false,' ."\n";
                }
                if (isset($map->routeunitsystem)) 
                {
                    switch ($map->routeunitsystem) 
                    {
                    case 0:
                    break;
                    case 1:
                        $scripttext .= '      unitSystem: google.maps.UnitSystem.METRIC,' ."\n";
                    break;
                    case 2:
                        $scripttext .= '      unitSystem: google.maps.UnitSystem.IMPERIAL,' ."\n";
                    break;
                    default:
                        $scripttext .= '';
                    break;
                    }
                }
                // to do - move to parameters -- end
                $scripttext .= '      travelMode: searchTravelModeValue ' ."\n";
                $scripttext .= '    };' ."\n";
                $scripttext .= '  }' ."\n";
                $scripttext .= '  else' ."\n";
                $scripttext .= '  {' ."\n";
                $scripttext .= '    var placesDirectionsRequest = {' ."\n";
                $scripttext .= '      origin: placeDestination, ' ."\n";
                $scripttext .= '      destination: placeLocation,' ."\n";
                // to do - move to parameters -- begin
                if (isset($map->routeavoidhighways) && (int)$map->routeavoidhighways == 1) 
                {
                    $scripttext .= '      avoidHighways: true,' ."\n";
                } else {
                    $scripttext .= '      avoidHighways: false,' ."\n";
                }
                if (isset($map->routeavoidtolls) && (int)$map->routeavoidtolls == 1) 
                {
                    $scripttext .= '      avoidTolls: true,' ."\n";
                } else {
                    $scripttext .= '      avoidTolls: false,' ."\n";
                }
                if (isset($map->routeunitsystem)) 
                {
                    switch ($map->routeunitsystem) 
                    {
                    case 0:
                    break;
                    case 1:
                        $scripttext .= '      unitSystem: google.maps.UnitSystem.METRIC,' ."\n";
                    break;
                    case 2:
                        $scripttext .= '      unitSystem: google.maps.UnitSystem.IMPERIAL,' ."\n";
                    break;
                    default:
                        $scripttext .= '';
                    break;
                    }
                }
                // to do - move to parameters -- end
                $scripttext .= '      travelMode: searchTravelModeValue ' ."\n";
                $scripttext .= '    };' ."\n";
                $scripttext .= '  }' ."\n";

                $scripttext .= '  placesDService.route(placesDirectionsRequest, function(result, status) {' ."\n";
                $scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
                $scripttext .= '      placesDDisplay.setDirections(result);' ."\n";
                $scripttext .= '    }' ."\n";
                $scripttext .= '    else {' ."\n";
                $scripttext .= '        alert("'.Text::_('COM_ZHGOOGLEMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
                $scripttext .= '    }' ."\n";
                $scripttext .= '});' ."\n";
            $scripttext .= '  }' ."\n";

            $scripttext .= '  map'.$mapDivSuffix.'.setCenter(placeLocation);' ."\n";
            if (isset($map->circle_border) && ((int)$map->circle_border == 1))    
            {
                if ($fv_override_circle_draggable == "" || (int)$fv_override_circle_draggable != 0)
                {
                    $scripttext .= 'mapCircle'.$mapDivSuffix.'.setCenter(placeLocation);'."\n";
                }
            }


            if (isset($map->zoombyfind) && (int)$map->zoombyfind != 100) 
            {
                $scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$map->zoombyfind.');' ."\n";
            }
            
            $scripttext .= '  markerPlacesAC.setPosition(placeLocation);' ."\n";
            $scripttext .= '  markerPlacesAC.setTitle(markerPlacesACText);' ."\n";

            $scripttext .= '};' ."\n";
        }
        // End function placesACbyButton


        // Toggle for Insert Markers - Begin
    if (isset($map->usermarkers) 
        && ((int)$map->usermarkersinsert == 1 || (int)$map->usermarkersupdate == 1)
        && ((int)$map->usermarkers == 1
            ||(int)$map->usermarkers == 2)) 
    {
        if ($allowUserMarker == 1)
        {
                $scripttext .= 'function showonlyone(thename, theid) {'."\n";
                $scripttext .= '  var xPlacemarkA = document.getElementById("bodyInsertPlacemarkA"+theid);'."\n";
                $scripttext .= '  var xPlacemarkGrpA = document.getElementById("bodyInsertPlacemarkGrpA"+theid);'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '  var xContactA = document.getElementById("bodyInsertContactA"+theid);'."\n";
                $scripttext .= '  var xContactAdrA = document.getElementById("bodyInsertContactAdrA"+theid);'."\n";
            }
                $scripttext .= '  if (thename == \'Contact\')'."\n";
                $scripttext .= '  {'."\n";
                $scripttext .= '    var toHide2 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
                $scripttext .= '    var toHide3 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    var toHide1 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
                $scripttext .= '    var toShow = document.getElementById("bodyInsertContact"+theid);'."\n";
            }
                $scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
            }
                $scripttext .= '  }'."\n";
                $scripttext .= '  else if (thename == \'Placemark\')'."\n";
                $scripttext .= '  {'."\n";
                $scripttext .= '    var toHide1 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
                $scripttext .= '    var toShow = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    var toHide2 = document.getElementById("bodyInsertContact"+theid);'."\n";
                $scripttext .= '    var toHide3 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
            }
                $scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
            }
                $scripttext .= '  }'."\n";
                $scripttext .= '  else if (thename == \'PlacemarkGroup\')'."\n";
                $scripttext .= '  {'."\n";
                $scripttext .= '    var toShow = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
                $scripttext .= '    var toHide1 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    var toHide2 = document.getElementById("bodyInsertContact"+theid);'."\n";
                $scripttext .= '    var toHide3 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
            }
                $scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
            }
                $scripttext .= '  }'."\n";
                $scripttext .= '  else if (thename == \'ContactAddress\')'."\n";
                $scripttext .= '  {'."\n";
                $scripttext .= '    var toHide2 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
                $scripttext .= '    var toHide3 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    var toHide1 = document.getElementById("bodyInsertContact"+theid);'."\n";
                $scripttext .= '    var toShow = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
            }
                $scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
                $scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
            }
                $scripttext .= '  }'."\n";
                $scripttext .= '  toHide1.style.display = \'none\';'."\n";
                $scripttext .= '  toShow.style.display = \'block\';'."\n";
            if (isset($map->usercontact) && (int)$map->usercontact == 1)
            {
                $scripttext .= '  toHide2.style.display = \'none\';'."\n";
                $scripttext .= '  toHide3.style.display = \'none\';'."\n";
            }
                $scripttext .= '}'."\n";
        }   
    }
    // Toggle for Insert Markers - End

if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0)
&& (isset($useObjectStructure) && (int)$useObjectStructure == 1))
{
    // Do not add loader
	// 27.12.23 - for plugin and GPDR
	if ($do_map_load == 0) {
		$scripttext .= 'ShowGPDRMessage'.$mapDivSuffix.'();' ."\n";
	}
}
else
{
	
	if ($do_map_load == 1) {
		if ($loadtype == "1")
		{
			$scripttext .= ' window.addEvent(\'domready\', initialize'.$mapInitTag.');' ."\n";
		}
		else if ($loadtype == "2")
		{
			$scripttext .= 'var tmpJQ'.$mapDivSuffix.' = jQuery.noConflict();'."\n";
			$scripttext .= ' tmpJQ'.$mapDivSuffix.'(document).ready(function() {initialize'.$mapInitTag.'();});' ."\n";
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
			$scripttext .= ' function addLoadEvent(func) {' ."\n";
			$scripttext .= '  var oldonload = window.onload;' ."\n";
			$scripttext .= '  if (typeof window.onload != \'function\') {' ."\n";
			$scripttext .= '    window.onload = func;' ."\n";
			$scripttext .= '  } else {' ."\n";
			$scripttext .= '    window.onload = function() {' ."\n";
			$scripttext .= '      if (oldonload) {' ."\n";
			$scripttext .= '        oldonload();' ."\n";
			$scripttext .= '      }' ."\n";
			$scripttext .= '      func();' ."\n";
			$scripttext .= '    }' ."\n";
			$scripttext .= '  }' ."\n";
			$scripttext .= '}    ' ."\n";    

			$scripttext .= 'addLoadEvent(initialize'.$mapInitTag.');' ."\n";
		}
	
	}
	else 
	{
	    $scripttext .= 'ShowGPDRMessage'.$mapDivSuffix.'();' ."\n";
	}
            

}

    // Google Maps JS API initialization - begin
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
     
    if (isset($map->placesenable) && (int)$map->placesenable == 1)
    {
        $scriptParametersExist = 1;
        if ($mainScriptLibrary == "")
        {
            $mainScriptLibrary .= '&libraries=places';
        }
        else
        {
            $mainScriptLibrary .= ',places';
        }
    }



    $mainScriptAdd .= $mainScriptLibrary;
	
	// fix for Elevation
	if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
	{
		$loadVisualisationKML = $this->loadVisualisationKML;
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
		
		if ($script_loadVisualisation == 1)
		{
			
			$wa->registerAndUseScript('zhgooglemaps.jsapi', $urlProtocol.'://www.google.com/jsapi');
			
			if ($loadVisualisationKML == 1)
			{
				//echo "\n".' <script type="text/javascript" src="'.$urlProtocol.'://geoxml3.googlecode.com/svn/branches/polys/geoxml3.js"></script>' ."\n";
				//echo "\n".' <script type="text/javascript" src="'.$urlProtocol.'://geoxml3.googlecode.com/svn/trunk/ProjectedOverlay.js"></script>' ."\n";
				$wa->registerAndUseScript('zhgooglemaps.geoxml3_1', $current_custom_js_path.'geoxml3/polys/geoxml3.js');
				$wa->registerAndUseScript('zhgooglemaps.geoxml3_2', $current_custom_js_path.'geoxml3/trunk/ProjectedOverlay.js');
			}
		}
		
	}   
	else
	{
		$mainScriptAdd .= '&callback='.'initialize'.$mapInitTag;
		$scripttext .= 'function HideGPDRMessage'.$mapDivSuffix.'(p_exp) {'."\n";
		
		$scripttext .= 'var tmpJQ'.$mapDivSuffix.' = jQuery.noConflict();'."\n";
		$scripttext .= ' tmpJQ'.$mapDivSuffix.'.getScript("'.$mainScriptBegin . $mainScriptAdd.'");' ."\n";
		$scripttext .= ' var CookiesMap = Cookies.noConflict();' ."\n";
		$scripttext .= ' if (p_exp == 0) {';
	    $scripttext .= ' CookiesMap.set("'.$cookieNameH.'", 1, { sameSite: \'strict\' });' ."\n";
		$scripttext .= '} else {';
		$scripttext .= ' CookiesMap.set("'.$cookieNameH.'", 1, { sameSite: \'strict\', expires: p_exp });' ."\n";
		$scripttext .= '}';

		if ($script_loadVisualisation == 1)
		{
			
			$scripttext .= ' tmpJQ'.$mapDivSuffix.'.getScript("'.$urlProtocol.'://www.google.com/jsapi", function(){'."\n";
			$scripttext .= '   google.load("visualization", "1", {packages: ["corechart"]});'."\n";

			if ($loadVisualisationKML == 1)
			{
				$scripttext .= ' tmpJQ'.$mapDivSuffix.'.getScript("'.$current_custom_js_path.'geoxml3/trunk/ProjectedOverlay.js");'."\n";
			}

            $scripttext .= '});'."\n";

			if ($loadVisualisationKML == 1)
			{
				$scripttext .= ' tmpJQ'.$mapDivSuffix.'.getScript("'.$current_custom_js_path.'geoxml3/polys/geoxml3.js");'."\n";
			}
                    
		}
		
		$scripttext .= '};' ."\n"; 
		
		$scripttext .= 'function HideGPDRMessageCookies'.$mapDivSuffix.'(p_exp) {'."\n";		
		$scripttext .= ' document.getElementById("zhgm-display-gpdr-'.$mapDivSuffix.'").setAttribute(\'onclick\', \'HideGPDRMessage'.$mapDivSuffix.'(\'+p_exp+\');\');'."\n";
		$scripttext .= ' document.getElementById("zhgm-display-gpdr-cookie-'.$mapDivSuffix.'").style.display = \'none\'';
		$scripttext .= '};' ."\n"; 
	}
	
	// Google Maps JS API initialization - end
    

    
$scripttextEnd .= '</script>' ."\n";
// Script end


if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
    {
        $this->scripttext = $scripttext;
        $this->scripthead = $scripthead;
        $this->scriptinitialize .= ' initialize'.$mapInitTag.'();' ."\n";
    }
    else if (isset($useObjectStructure) && (int)$useObjectStructure == 2)
    {
        // for module case
        $scripttextFull = $scripttextBegin . $scripttext. $scripttextEnd;
        echo $scripttextFull;
    }
    else
    {
    }
}
else
{
    $scripttextFull = $scripttextBegin . $scripttext. $scripttextEnd;
    echo $scripttextFull;
}

}
// end of main part
