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
namespace ZhukDL\Component\ZhGoogleMap\Site\Controller;

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapPlacemarksHelper;
use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapPathsHelper;


class DisplayController extends BaseController {


    public function display($cachable = false, $urlparams = array()) {        
        $document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        $view->setModel($this->getModel($viewName), true);
        
        $view->document = $document;
        $view->display();
    }



    public function getPlacemarkDetails() {
		
		$app = Factory::getApplication();

        $id = $app->input->get('id', '', "STRING") ;
        $usercontactattributes = $app->input->get('contactattrs', '', "STRING");
        $usercontact = $app->input->get('usercontact', '', "STRING");
        $useruser = $app->input->get('useruser', '', "STRING");
        $service_DoDirection = $app->input->get('servicedirection', '', "STRING");
        $imgpathIcons = $app->input->get('iconicon', '', "STRING");
        $imgpathUtils = $app->input->get('iconutil', '', "STRING");
        $directoryIcons = $app->input->get('icondir', '', "STRING");
        $currentArticleId = $app->input->get('articleid', '', "STRING");
        $placemarkrating = $app->input->get('placemarkrating', '', "STRING");
        $placemarkTitleTag = $app->input->get('placemarktitletag', '', "STRING");
        $showcreateinfo = $app->input->get('showcreateinfo', '', "STRING");
        $panelinfowin = $app->input->get('panelinfowin', '', "STRING");
        $gogoogle = $app->input->get('gogoogle', '', "STRING");
        $gogoogle_text = $app->input->get('gogoogle_text', '', "STRING");
        $placemark_date_fmt = $app->input->get('placemarkdateformat', '', "STRING");
        
        $lang = $app->input->get('language', '', "STRING");

        
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

  
        // Create some addition filters - Begin
        $addWhereClause = '';
        $addWhereClause .= ' and h.id = '. (int)$id;
        
        if ((int)$usercontact == 1)
        {
            $query->select('h.*, '.
                ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                ' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage,'.
                ' cn.suburb as contact_suburb, cn.state as contact_state, cn.country as contact_country, cn.postcode as contact_postcode, '.
                ' bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
                ->from('#__zhgooglemaps_markers as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                ->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
                ->leftJoin('#__contact_details as cn ON h.contactid=cn.id')
                ->where('1=1' . $addWhereClause)
                ;
        }
        else
        {
            $query->select('h.*, '.
                ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                ' bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
                ->from('#__zhgooglemaps_markers as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                ->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
                ->where('1=1'. $addWhereClause)
                ;

        }
        
        $db->setQuery($query);        
        
        $marker = $db->loadObject();
        
        
        if (isset($marker))
        {
            $responseVar = array( 'id'=>(int)$id
                                , 'dataexists'=>1
                                , 'actionbyclick'=>$marker->actionbyclick
                                , 'zoombyclick'=>$marker->zoombyclick
            //, 'usercontactattributes'=>$usercontactattributes
            //, 'usercontact'=>$usercontact
            //, 'useruser'=>$useruser
            //, 'service_DoDirection'=> $service_DoDirection
            //,'i'=>$imgpathIcons
            //,'u'=>$imgpathUtils
            //,'d'=>$directoryIcons
                                );
            if ($marker->actionbyclick == 1)
            {
                $responseVar['titleplacemark'] = htmlspecialchars(str_replace('\\', '/', $marker->title), ENT_QUOTES, 'UTF-8');
                $responseVar['contentstring'] = MapPlacemarksHelper::get_placemark_content_string(
                                            $currentArticleId,
                                            $marker, $usercontact, $useruser,
                                            $usercontactattributes, $service_DoDirection,
                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo,
                                            $gogoogle, $gogoogle_text,
                                            $placemark_date_fmt) . ';';
            }

            if ($marker->actionbyclick == 2 
             || $marker->actionbyclick == 3)
            {
                $responseVar['hrefsite'] = $marker->hrefsite;
            }
            if ($marker->actionbyclick ==4)
            {
                if ((int)$panelinfowin == 1)
                {
                    $responseVar['tab_info_title'] = Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' );
                    $responseVar['contentstring'] = MapPlacemarksHelper::get_placemark_tabs_content_string(
                                                        $currentArticleId, $marker,
                                                        MapPlacemarksHelper::get_placemark_content_string(
                                                            $currentArticleId,
                                                            $marker, $usercontact, $useruser,
                                                            $usercontactattributes, $service_DoDirection,
                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo,
                                                            $gogoogle, $gogoogle_text,
                                                            $placemark_date_fmt),
                                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $lang). ';';    
                    
                }
                else
                {
                    $responseVar['tab_info'] = $marker->tab_info;
                    
                    if ((int)$marker->tab_info != 0)
                    {
                        $responseVar['tab_info_title'] = Text::_( 'COM_ZHGOOGLEMAP_INFOBUBBLE_TAB_INFO_TITLE' );
                        $responseVar['contentstring'] = MapPlacemarksHelper::get_placemark_content_string(
                                                    $currentArticleId,
                                                    $marker, $usercontact, $useruser,
                                                    $usercontactattributes, $service_DoDirection,
                                                    $imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo,
                                                    $gogoogle, $gogoogle_text,
                                                    $placemark_date_fmt). ';';
                    }
                    $responseVar['infobubblestyle'] = MapPlacemarksHelper::get_placemark_infobubble_style_string($marker, '');
                    $responseVar['tab1'] = $marker->tab1;
                    $responseVar['tab2'] = $marker->tab2;
                    $responseVar['tab3'] = $marker->tab3;
                    $responseVar['tab4'] = $marker->tab4;
                    $responseVar['tab5'] = $marker->tab5;
                    $responseVar['tab6'] = $marker->tab6;
                    $responseVar['tab7'] = $marker->tab7;
                    $responseVar['tab8'] = $marker->tab8;
                    $responseVar['tab9'] = $marker->tab9;
                    $responseVar['tab10'] = $marker->tab10;
                    $responseVar['tab11'] = $marker->tab11;
                    $responseVar['tab12'] = $marker->tab12;
                    $responseVar['tab13'] = $marker->tab13;
                    $responseVar['tab14'] = $marker->tab14;
                    $responseVar['tab15'] = $marker->tab15;
                    $responseVar['tab16'] = $marker->tab16;
                    $responseVar['tab17'] = $marker->tab17;
                    $responseVar['tab18'] = $marker->tab18;
                    $responseVar['tab19'] = $marker->tab19;
                    $responseVar['tab1title'] = $marker->tab1title;
                    $responseVar['tab2title'] = $marker->tab2title;
                    $responseVar['tab3title'] = $marker->tab3title;
                    $responseVar['tab4title'] = $marker->tab4title;
                    $responseVar['tab5title'] = $marker->tab5title;
                    $responseVar['tab6title'] = $marker->tab6title;
                    $responseVar['tab7title'] = $marker->tab7title;
                    $responseVar['tab8title'] = $marker->tab8title;
                    $responseVar['tab9title'] = $marker->tab9title;
                    $responseVar['tab10title'] = $marker->tab10title;
                    $responseVar['tab11title'] = $marker->tab11title;
                    $responseVar['tab12title'] = $marker->tab12title;
                    $responseVar['tab13title'] = $marker->tab13title;
                    $responseVar['tab14title'] = $marker->tab14title;
                    $responseVar['tab15title'] = $marker->tab15title;
                    $responseVar['tab16title'] = $marker->tab16title;
                    $responseVar['tab17title'] = $marker->tab17title;
                    $responseVar['tab18title'] = $marker->tab18title;
                    $responseVar['tab19title'] = $marker->tab19title;                    
                }

                
                
            }

            if ($marker->actionbyclick == 5)
            {
                $responseVar['streetviewinfowinw'] = $marker->streetviewinfowinw;
                $responseVar['streetviewinfowinh'] = $marker->streetviewinfowinh;
                $responseVar['streetviewinfowinmapsv'] = MapPlacemarksHelper::get_StreetViewOptions($marker->streetviewstyleid);
            }


            
        }
        else
        {
            $responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
        }
        echo (json_encode($responseVar));
        

    }
    
    
    public function setPlacemarkRating() {
		
		$app = Factory::getApplication();

        $id = $app->input->get('id', '', "STRING") ;
        $rating = $app->input->get('rating', '', "STRING") ;
        $lang = $app->input->get('language', '', "STRING") ;

        $currentLanguage = Factory::getLanguage();
        $currentLangTag = $currentLanguage->getTag();
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
            $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap' , $currentLangTag, true);    
        }
    

        $currentUser = Factory::getUser();

        $userIP = $_SERVER['REMOTE_ADDR'];
        $userHOST = ((isset($_SERVER['REMOTE_HOST']) && !empty($_SERVER['REMOTE_HOST'])) ? $_SERVER['REMOTE_HOST'] : gethostbyaddr($_SERVER['REMOTE_ADDR'])); 
        
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        if ($currentUser->id == 0)
        {
            $db->setQuery( 'SELECT 1 as done FROM `#__zhgooglemaps_marker_rates` '.
            ' WHERE 1=1 '.
            ' and `ip`='.$db->Quote($userIP).
            ' and `hostname`='.$db->Quote($userHOST).
            ' and `markerid`='.(int)$id
            );
        }
        else
        {
            $db->setQuery( 'SELECT 1 as done FROM `#__zhgooglemaps_marker_rates` '.
            ' WHERE 1=1 '.
            ' and `createdbyuser`='.$currentUser->id.
            ' and `markerid`='.(int)$id
            );
        }

        
        $selectExist = $db->loadObject();
        
        if (!isset($selectExist)) 
        {
            // insert into rating table
            $newRow = new \stdClass;
            $newRow->markerid = (int)$id;
            $newRow->rating_value = $rating;
            $newRow->rating_date = Factory::getDate()->toSQL();
            $newRow->ip = $userIP;
            $newRow->hostname = $userHOST;
            $newRow->createdbyuser = $currentUser->id;

            $dml_result_insert = $db->insertObject( '#__zhgooglemaps_marker_rates', $newRow, 'id' );
            
            // get average rating
            if ($dml_result_insert)
            {
                $query = $db->getQuery(true);

                $db->setQuery( 'SELECT AVG(rating_value) as rating, COUNT(*) as cnt FROM `#__zhgooglemaps_marker_rates` '.
                'WHERE `markerid`='.(int)$id);
                
                $selectAVG = $db->loadObject();
                
                if (isset($selectAVG)) 
                {
                    $rating_avg = $selectAVG->rating;
                    $rating_cnt = $selectAVG->cnt;
                    
                    // update rating field
                    $updateRow = new \stdClass;
                    $updateRow->id = (int)$id;
                    $updateRow->rating_value = $rating_avg;
                    $updateRow->rating_count = $rating_cnt;
                    
                    $dml_result_update = $db->updateObject( '#__zhgooglemaps_markers', $updateRow, 'id' );
                    
                    
                    if ($dml_result_update)
                    {
                        $responseVar = array( 'id'=>(int)$id
                                            , 'dataexists'=>1
                                            , 'userrating'=>$rating
                                            , 'averagerating'=>$rating_avg
                                            , 'averagecount'=>$rating_cnt
                                            , 'IP'=>$userIP
                                            , 'HOST'=>$userHOST
                                            , 'errortext'=>Text::_('COM_ZHGOOGLEMAP_MAP_RATING_THANKS') 
                                            );
                        
                    }
                    else
                    {
                        $responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            ,'errortext'=>Text::_('COM_ZHGOOGLEMAP_MAP_RATING_UNABLE_UPDATE_AVERAGE')
                                            );
                    }
                }
                else
                {
                    $responseVar = array('id'=>$id
                                        ,'dataexists'=>0
                                        ,'errortext'=>Text::_('COM_ZHGOOGLEMAP_MAP_RATING_UNABLE_GET_AVERAGE')
                                        );
                }
            }
            else
            {
                $responseVar = array('id'=>$id
                                    ,'dataexists'=>0
                                    ,'errortext'=>Text::_('COM_ZHGOOGLEMAP_MAP_RATING_UNABLE_INSERT_RATE')
                                    );
            }
        }
        else
        {
            $responseVar = array('id'=>$id
                                ,'dataexists'=>0
                                ,'errortext'=>Text::_('COM_ZHGOOGLEMAP_MAP_RATING_ALREADY_VOTED')
                                );
        }
        
        echo (json_encode($responseVar));
        

    }
    
    
    public function getPlacemarkHoverText() {
		
		$app = Factory::getApplication();

        $id = $app->input->get('id', '', "STRING") ;
        
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        // Create some addition filters - Begin
        $addWhereClause = '';
        $addWhereClause .= ' and h.id = '. (int)$id;
        
        $query->select('h.*, '.
            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
            ' bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
            ->from('#__zhgooglemaps_markers as h')
            ->leftJoin('#__categories as c ON h.catid=c.id')
            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
            ->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
            ->where('1=1'. $addWhereClause)
            ;

        
        $db->setQuery($query);        
        
        $marker = $db->loadObject();
        
        
        if (isset($marker))
        {
            if ($marker->hoverhtml != "")
            {
                $responseVar = array( 'id'=>(int)$id
                                , 'dataexists'=>1
                                );
                $responseVar['hoverstring'] = MapPlacemarksHelper::get_placemark_hover_string(
                                                $marker);
            }
            else
            {
                $responseVar = array('id'=>$id
                                    ,'dataexists'=>0
                                    );
            }
        }
        else
        {
            $responseVar = array('id'=>$id
                                ,'dataexists'=>0
                                );
        }
        echo (json_encode($responseVar));

    }

	public function getPathHoverText() {
		
		$app = Factory::getApplication();

        $id = $app->input->get('id', '', "STRING") ;
        
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        // Create some addition filters - Begin
        $addWhereClause = '';
        $addWhereClause .= ' and h.id = '. (int)$id;
        
        $query->select('h.*, '.
            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster')
            ->from('#__zhgooglemaps_paths as h')
            ->leftJoin('#__categories as c ON h.catid=c.id')
            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
            ->where('1=1'. $addWhereClause)
            ;

        
        $db->setQuery($query);        
        
        $path = $db->loadObject();
        
        
        if (isset($path))
        {
            if ($path->hoverhtml != "")
            {
                $responseVar = array( 'id'=>(int)$id
                                                    , 'dataexists'=>1
                                                    );
                $responseVar['hoverstring'] = MapPathsHelper::get_path_hover_string(
                                                $path);
				$responseVar['objecttype'] = $path->objecttype;                               
				$responseVar['color'] = $path->color;
				$responseVar['fillcolor'] = $path->fillcolor;
				$responseVar['hover_color'] = $path->hover_color;
				$responseVar['hover_fillcolor'] = $path->hover_fillcolor;
                                
            }
            else if ($path->hover_color != "" || $path->hover_fillcolor != "")
            {
                $responseVar = array( 'id'=>(int)$id
                                    , 'dataexists'=>1
                                    );
                $responseVar['hoverstring'] = "";
				$responseVar['objecttype'] = $path->objecttype;                               
				$responseVar['color'] = $path->color;
				$responseVar['fillcolor'] = $path->fillcolor;
				$responseVar['hover_color'] = $path->hover_color;
				$responseVar['hover_fillcolor'] = $path->hover_fillcolor;                           
            }
            else
            {
                $responseVar = array('id'=>$id
                                    ,'dataexists'=>0
                                    );
            }
        }
        else
        {
            $responseVar = array('id'=>$id
                                ,'dataexists'=>0
                                );
        }
        echo (json_encode($responseVar));

    }

    
    // 16.08.2013 ajax loading

    public function getAJAXPlacemarkList() {

        $db = Factory::getDBO();
        $query = $db->getQuery(true);
		
		$app = Factory::getApplication();

        $x1 = $app->input->get('x1', '', "STRING");
        $x2 = $app->input->get('x2', '', "STRING");
        $y1 = $app->input->get('y1', '', "STRING");
        $y2 = $app->input->get('y2', '', "STRING");

        $placemarkloadtype = $app->input->get('placemarkloadtype', '', "STRING");
        
        $mapid = $app->input->get('mapid', '', "STRING");
        $placemarklistid = str_replace(';',',', $app->input->get('placemarklistid', '', "STRING"));
        $explacemarklistid = str_replace(';',',', $app->input->get('explacemarklistid', '', "STRING"));
        $grouplistid = str_replace(';',',', $app->input->get('grouplistid', '', "STRING"));
        $categorylistid = str_replace(';',',', $app->input->get('categorylistid', '', "STRING"));
        $taglistid = str_replace(';',',', $app->input->get('taglistid', '', "STRING"));
        $mf = $app->input->get('usermarkersfilter', '', "STRING");
        
        $id = $mapid;


        
        // Create some addition filters - Begin
        $addWhereClause = '';

        
        if ($placemarklistid == ""
         && $grouplistid == ""
         && $categorylistid == ""
         && $taglistid == ""
        )
        {
            $addWhereClause .= ' and h.mapid='.(int)$id;

            if ($explacemarklistid != "")
            {
                $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                $tmp_expl_ids = implode(',', $tmp_expl_ids);
                
                if (strpos($tmp_expl_ids, ','))
                {
                    $addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
                }
            }
            
        }
        else
        {
            if ($placemarklistid != "")
            {
                $tmp_pl_ids = explode(',', str_replace(';',',', $placemarklistid));                                       
                $tmp_pl_ids = ArrayHelper::toInteger($tmp_pl_ids);
                $tmp_pl_ids = implode(',', $tmp_pl_ids);
                
                if (strpos($tmp_pl_ids, ','))
                {
                    $addWhereClause .= ' and h.id IN ('.$tmp_pl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id = '.(int)$tmp_pl_ids;
                }
            }
            if ($explacemarklistid != "")
            {
                $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                $tmp_expl_ids = implode(',', $tmp_expl_ids);
                
                if (strpos($tmp_expl_ids, ','))
                {
                    $addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
                }
            }
            if ($grouplistid != "")
            {
                $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                $tmp_grp_ids = implode(',', $tmp_grp_ids);
                
                if (strpos($tmp_grp_ids, ','))
                {
                    $addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
                }
            }
            if ($categorylistid != "")
            {
                $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                $tmp_cat_ids = implode(',', $tmp_cat_ids);
                
                if (strpos($tmp_cat_ids, ','))
                {
                    $addWhereClause .= ' and h.catid IN ('.$tmp_cat_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.catid = '.(int)$tmp_cat_ids;
                }
            }
            // tag search is not as usual
            if ($taglistid != "")
            {
                    $tmp_tag_ids = explode(',', str_replace(';',',', $taglistid));                                       
                    $tmp_tag_ids = ArrayHelper::toInteger($tmp_tag_ids);
                    $tmp_tag_ids = implode(',', $tmp_tag_ids);                         
                    if (strpos($tmp_tag_ids, ','))
                    {
                        $addWherePlacemarkTag = ' and tagmap.tag_id IN ('.$tmp_tag_ids.')';                         
                    }
                    else
                    {
                        $addWherePlacemarkTag = ' and tagmap.tag_id = '. (int) $tmp_tag_ids;                                              
                    }

                    $addWherePlacemarkTagExist = ' AND EXISTS (SELECT 1 FROM #__contentitem_tag_map AS tagmap '.
                                                 ' WHERE tagmap.content_item_id = h.id '.
                                                 ' AND tagmap.type_alias = \'com_zhgooglemap.mapmarker\''.$addWherePlacemarkTag.')';    

                    $addWhereClause .= $addWherePlacemarkTagExist;        
            }
        }
        
        // You can not enter markers

        switch ((int)$mf)
        {
            case 0:
                $addWhereClause .= ' and h.published=1';
            break;
            case 1:
                $currentUser = Factory::getUser();
                $addWhereClause .= ' and h.published=1';
                $addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
            break;
            case 2:
                $currentUser = Factory::getUser();
                $currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
                $addWhereClause .= ' and h.published=1';
                $addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
            break;
            default:
                $addWhereClause .= ' and h.published=1';
            break;                    
        }
        
        // Create some addition filters - End


        if ($placemarkloadtype == "2")
        {            
            $addWhereClause .= ' and h.longitude >= '.(int)$x1;
            $addWhereClause .= ' and h.longitude <= '.(int)$x2;
            $addWhereClause .= ' and h.latitude >= '.(int)$y1;
            $addWhereClause .= ' and h.latitude <= '.(int)$y2;
        }

        $query->select('h.id'
            //',g.published as publishedgroup '
            )
            ->from('#__zhgooglemaps_markers as h')
            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
            ->where('1=1' . $addWhereClause)
        ;

        $nowDate = $db->Quote(Factory::getDate()->toSQL());
        
        $query->where('(h.publish_up IS NULL OR h.publish_up <= ' . $nowDate . ')');
        $query->where('(h.publish_down IS NULL OR h.publish_down >= ' . $nowDate . ')');
        
        $db->setQuery($query);        
        
        // Markers
        if (!$markers = $db->loadObjectList()) 
        {
            $responseVar = array('cnt'=>0
                                ,'dataexists'=>0
                                );
        }
        else
        {
            $responseVar = array( 'cnt'=>count($markers)
                                , 'dataexists'=>1
                                , 'markers'=> $markers 
                                );
        }
        
    
        echo (json_encode($responseVar));
        

    }
    
    public function getAJAXPlacemarks() {

        $app = Factory::getApplication();
		
        $ajaxarray = $app->input->get('ajaxarray', [], "ARRAY");
        $markerlistpos = $app->input->get('mapmarkerlistpos', '', "STRING");
        $markerlistcontent = $app->input->get('mapmarkerlistcontent', '', "STRING");
        $markerlistaction = $app->input->get('mapmarkerlistaction', '', "STRING");
        $markerlistcssstyle = $app->input->get('mapmarkerlistcssstyle', '', "STRING");
        $mapDivSuffix = $app->input->get('maparticleid', '', "STRING");
        $imgpathIcons = $app->input->get('iconicon', '', "STRING");
        
        if (count($ajaxarray) > 0)
        {
            $ajaxarray = ArrayHelper::toInteger($ajaxarray);
            $placemarklist = implode(",", $ajaxarray);
                
    
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

      
            // Create some addition filters - Begin
            $addWhereClause = '';

            $addWhereClause .= ' and h.id IN ('.$placemarklist.')';

            $query->select('h.*, '.
                ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                ' g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster ')
                ->from('#__zhgooglemaps_markers as h')
                ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                ->where('1=1' . $addWhereClause)
                ->order('h.title');
            
            $db->setQuery($query);        
            
            // Markers
            if (!$markers = $db->loadObjectList()) 
            {
                $responseVar = array('cnt'=>0
                                    ,'dataexists'=>0
                                    );
            }
            else
            {
                
                $ret_markers = array();
                
                foreach ($markers as $key => &$marker)     
                {
                    $ret_marker = array();

                    $ret_marker["id"] = $marker->id;
                    $ret_marker["title"] = $marker->title;
                    $ret_marker["description"] = $marker->description;
                    $ret_marker["latitude"] = $marker->latitude;
                    $ret_marker["longitude"] = $marker->longitude;
                    $ret_marker["markergroup"] = $marker->markergroup;
                    $ret_marker["actionbyclick"] = $marker->actionbyclick;
                    $ret_marker["baloon"] = $marker->baloon;

                    $ret_marker["markercontent"] = $marker->markercontent;
                    $ret_marker["openbaloon"] = $marker->openbaloon;

                    $ret_marker["labelcontent"] = $marker->labelcontent;
                    $ret_marker["labelclass"] = $marker->labelclass;
                    $ret_marker["labelanchorx"] = $marker->labelanchorx;
                    $ret_marker["labelanchory"] = $marker->labelanchory;
                    $ret_marker["labelinbackground"] = $marker->labelinbackground;

                    $ret_marker["overridemarkericon"] = $marker->overridemarkericon;
                    $ret_marker["publishedgroup"] = $marker->publishedgroup;
                    $ret_marker["groupiconofsetx"] = $marker->groupiconofsetx;
                    $ret_marker["groupiconofsety"] = $marker->groupiconofsety;
                    $ret_marker["groupicontype"] = $marker->groupicontype;

                    $ret_marker["iconofsetx"] = $marker->iconofsetx;
                    $ret_marker["iconofsety"] = $marker->iconofsety;
                    $ret_marker["icontype"] = $marker->icontype;

                    $ret_marker["includeinlist"] = $marker->includeinlist;

                    $ret_marker["rating_value"] = $marker->rating_value;

                    if ($marker->hoverhtml != "")
                    {
                        $ret_marker["do_hover"] = "1";
                    }
                    else
                    {
                        $ret_marker["do_hover"] = "";
                    }

                    if ((int)$markerlistpos != 0)
                    {
                        
                        $ret_marker["placemarklistcontent"] = MapPlacemarksHelper::get_placemarklist_string(
                                                1,
                                                $mapDivSuffix, 
                                                $marker, 
                                                $markerlistcssstyle,
                                                $markerlistpos,
                                                $markerlistcontent,
                                                $markerlistaction,
                                                $imgpathIcons);
                    }         
                    
                    array_push($ret_markers, $ret_marker);
                }
                

                
                $responseVar = array( 'cnt'=>count($markers)
                                    , 'dataexists'=>1
                                    , 'markers'=> $ret_markers 
                                    // it doesn't need for production
                                    // , 'ajaxarray'=>$placemarklist
                                    );
            }
            
        }
        else
        {
            $responseVar = array('cnt'=>0
                                ,'dataexists'=>0
                                );
        }

        echo (json_encode($responseVar));
        

    }
  
    public function getPathDetails() {
		
		$app = Factory::getApplication();

        $id = $app->input->get('id', '', "STRING") ;
        $service_DoDirection = $app->input->get('servicedirection', '', "STRING");
        $imgpathIcons = $app->input->get('iconicon', '', "STRING");
        $imgpathUtils = $app->input->get('iconutil', '', "STRING");
        $directoryIcons = $app->input->get('icondir', '', "STRING");
        $currentArticleId = $app->input->get('articleid', '', "STRING");
        $placemarkTitleTag = $app->input->get('placemarktitletag', '', "STRING");
        $panelinfowin = $app->input->get('panelinfowin', '', "STRING");
                
        $lang = $app->input->get('language', '', "STRING");

        
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

  
        // Create some addition filters - Begin
        $addWhereClause = '';
        $addWhereClause .= ' and h.id = '. (int)$id;
        

            $query->select('h.*, '.
                ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety')
                ->from('#__zhgooglemaps_paths as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                ->where('1=1'. $addWhereClause)
                ;
        
        $db->setQuery($query);        
        
        $path = $db->loadObject();
        
        
        if (isset($path))
        {
            $responseVar = array( 'id'=>(int)$id
                                , 'dataexists'=>1
                                , 'actionbyclick'=>$path->actionbyclick
            //,'i'=>$imgpathIcons
            //,'u'=>$imgpathUtils
            //,'d'=>$directoryIcons
                                );
            if ($path->actionbyclick == 1)
            {
                $responseVar['titlepath'] = htmlspecialchars(str_replace('\\', '/', $path->title), ENT_QUOTES, 'UTF-8');
                $responseVar['contentstring'] = MapPathsHelper::get_path_content_string(
                                            $currentArticleId,
                                            $path, 
                                            $imgpathIcons, $imgpathUtils, $directoryIcons, $lang, $placemarkTitleTag) . ';';
            }

            if ($path->actionbyclick == 2 
            || $path->actionbyclick == 3)
            {
                $responseVar['hrefsite'] = $path->hrefsite;
            }

            
        }
        else
        {
            $responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
        }
        echo (json_encode($responseVar));
        

    }

    public function getAJAXPathList() {

        $db = Factory::getDBO();
        $query = $db->getQuery(true);
		
		$app = Factory::getApplication();

        $mapid = $app->input->get('mapid', '', "STRING");
        $pathlistid = str_replace(';',',', $app->input->get('pathlistid', '', "STRING"));
        $expathlistid = str_replace(';',',', $app->input->get('expathlistid', '', "STRING"));
        $grouplistid = str_replace(';',',', $app->input->get('grouplistid', '', "STRING"));
        $categorylistid = str_replace(';',',', $app->input->get('categorylistid', '', "STRING"));
        $pathtaglistid = str_replace(';',',', $app->input->get('pathtaglistid', '', "STRING"));
        
        $id = $mapid;


        
        // Create some addition filters - Begin
        $addWhereClause = '';

        
        if ($pathlistid == ""
         && $grouplistid == ""
         && $categorylistid == ""
         && $pathtaglistid == ""
        )
        {
            $addWhereClause .= ' and h.mapid='.(int)$id;

            if ($expathlistid != "")
            {
                $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                $tmp_expl_ids = implode(',', $tmp_expl_ids);
                
                if (strpos($tmp_expl_ids, ','))
                {
                    $addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
                }
            }
            
        }
        else
        {
            if ($pathlistid != "")
            {
                $tmp_pl_ids = explode(',', str_replace(';',',', $pathlistid));                                       
                $tmp_pl_ids = ArrayHelper::toInteger($tmp_pl_ids);
                $tmp_pl_ids = implode(',', $tmp_pl_ids);
                
                if (strpos($tmp_pl_ids, ','))
                {
                    $addWhereClause .= ' and h.id IN ('.$tmp_pl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id = '.(int)$tmp_pl_ids;
                }
            }
            if ($expathlistid != "")
            {
                $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                $tmp_expl_ids = implode(',', $tmp_expl_ids);
                
                if (strpos($tmp_expl_ids, ','))
                {
                    $addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
                }
            }
            if ($grouplistid != "")
            {
                $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                $tmp_grp_ids = implode(',', $tmp_grp_ids);
                
                if (strpos($tmp_grp_ids, ','))
                {
                    $addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
                }
            }
            if ($categorylistid != "")
            {
                $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                $tmp_cat_ids = implode(',', $tmp_cat_ids);
                
                if (strpos($tmp_cat_ids, ','))
                {
                    $addWhereClause .= ' and h.catid IN ('.$tmp_cat_ids.')';
                }
                else
                {
                    $addWhereClause .= ' and h.catid = '.(int)$tmp_cat_ids;
                }
            }
            // tag search is not as usual
            if ($pathtaglistid != "")
            {
                    $tmp_tag_ids = explode(',', str_replace(';',',', $pathtaglistid));                                       
                    $tmp_tag_ids = ArrayHelper::toInteger($tmp_tag_ids);
                    $tmp_tag_ids = implode(',', $tmp_tag_ids);                         
                    if (strpos($tmp_tag_ids, ','))
                    {
                        $addWherePathTag = "\n" . ' and tagmap.tag_id IN ('.$tmp_tag_ids.')';                         
                    }
                    else
                    {
                        $addWherePathTag = "\n" . ' and tagmap.tag_id = '. (int) $tmp_tag_ids;                                              
                    }

                    $addWherePathTagExist = ' AND EXISTS (SELECT 1 FROM #__contentitem_tag_map AS tagmap '.
                                            ' WHERE tagmap.content_item_id = h.id '.
                                            ' AND tagmap.type_alias = \'com_zhgooglemap.mappath\''.$addWherePathTag.')';    

                    $addWhereClause .= $addWherePathTagExist;        
            }
        }
        

        $addWhereClause .= ' and h.published=1';
            
        // Create some addition filters - End


        $query->select('h.id'
            //',g.published as publishedgroup '
            )
            ->from('#__zhgooglemaps_paths as h')
            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
            ->where('1=1' . $addWhereClause)
        ;

        //$nowDate = $db->Quote(Factory::getDate()->toSQL());
        
        //$query->where('(h.publish_up IS NULL OR h.publish_up <= ' . $nowDate . ')');
        //$query->where('(h.publish_down IS NULL OR h.publish_down >= ' . $nowDate . ')');
        
		$db->setQuery($query);        
        
        // Paths
        if (!$paths = $db->loadObjectList()) 
        {
            $responseVar = array('cnt'=>0
                                ,'dataexists'=>0
                                );
        }
        else
        {
            $responseVar = array( 'cnt'=>count($paths)
                                                , 'dataexists'=>1
                                                , 'paths'=> $paths 
                                                );
        }
        
    
        echo (json_encode($responseVar));
        

    }
    
    public function getAJAXPaths() {

        $app = Factory::getApplication();
		
        $ajaxarray = $app->input->get('ajaxarray', [], "ARRAY");
        $mapDivSuffix = $app->input->get('maparticleid', '', "STRING");
        
        if (count($ajaxarray) > 0)
        {
            $ajaxarray = ArrayHelper::toInteger($ajaxarray);
            $pathlist = implode(",", $ajaxarray);
                
    
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

      
            // Create some addition filters - Begin
            $addWhereClause = '';

            $addWhereClause .= ' and h.id IN ('.$pathlist.')';

            $query->select('h.*, '.
                ' g.published as publishedgroup ')
                ->from('#__zhgooglemaps_paths as h')
                ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                ->where('1=1' . $addWhereClause)
                ->order('h.title');
            
                        $db->setQuery($query);        
            
            // Paths
            if (!$paths = $db->loadObjectList()) 
            {
                $responseVar = array('cnt'=>0
                                    ,'dataexists'=>0
                                    );
            }
            else
            {
                /*
                if ((int)$markerlistpos != 0)
                {
                    foreach ($markers as $key => &$marker)     
                    {
                        $marker->placemarklistcontent = MapPlacemarksHelper::get_placemarklist_string(
                                                1,
                                                $mapDivSuffix, 
                                                $marker, 
                                                $markerlistcssstyle,
                                                $markerlistpos,
                                                $markerlistcontent,
                                                $markerlistaction,
                                                $imgpathIcons);
                    }                    
                }
                
                */

                //
                foreach ($paths as $key => $currentpath) 
                {
                    $current_path_path = '';
                    $current_path_path = str_replace(array("\r", "\r\n", "\n"), '', $currentpath->path);
                    $currentpath->path = $current_path_path;
                    
                    if ($currentpath->imgbounds != "")
                    {
                        $current_path_imgbounds = '';
                        $current_path_imgbounds = str_replace(',',';',$currentpath->imgbounds);
                        $currentpath->imgbounds = $current_path_imgbounds;
                    }
                }
                                
                $responseVar = array( 'cnt'=>count($paths)
                                    , 'dataexists'=>1
                                    , 'paths'=> $paths 
                                    // it doesn't need for production
                                    // , 'ajaxarray'=>$placemarklist
                                    );
            }
            
        }
        else
        {
            $responseVar = array('cnt'=>0
                                ,'dataexists'=>0
                                );
        }

        echo (json_encode($responseVar));
        

    }        
   

    
}
