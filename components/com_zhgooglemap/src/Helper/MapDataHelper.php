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

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

abstract class MapDataHelper
{

    public static function getMap($id) 
    {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $query->select('h.*, c.title as category')
                    ->from('#__zhgooglemaps_maps as h')
                    ->leftJoin('#__categories as c ON h.catid=c.id')
                    ->where('h.id=' . (int)$id);

            $db->setQuery($query);        
                
            $item = $db->loadObject();

            return $item;
    }

    public static function getMarkers($id, 
                                      $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                      $usermarkers, $usermarkersfilter, $usercontact, $markerorder) 
    {        
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

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


            // Create some addition filters - Begin

            if ($usermarkers == 0)
            {
                    // You can not enter markers

                    // You can see all published, and you can't enter markers

                    switch ((int)$usermarkersfilter)
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
            }
            else
            {
                    // You can enter markers

                    switch ((int)$usermarkersfilter)
                    {
                            case 0:
                                    $currentUser = Factory::getUser();
                                    if ((int)$currentUser->id == 0)
                                    {
                                            $addWhereClause .= ' and h.published=1';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and (h.published=1 or h.createdbyuser='.(int)$currentUser->id .')';
                                    }
                            break;
                            case 1:
                                    $currentUser = Factory::getUser();
                                    if ((int)$currentUser->id == 0)
                                    {
                                            $addWhereClause .= ' and h.published=1';
                                            $addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
                                    }
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
            }
            // Create some addition filters - End


            if ((int)$usercontact == 1)
            {
                    $query->select('h.*, '.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                            ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                            ' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage, '.
                            ' cn.suburb as contact_suburb, cn.state as contact_state, cn.country as contact_country, cn.postcode as contact_postcode, '.
                            ' bub.disableanimation, bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
                            ->from('#__zhgooglemaps_markers as h')
                            ->leftJoin('#__categories as c ON h.catid=c.id')
                            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                            ->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
                            ->leftJoin('#__contact_details as cn ON h.contactid=cn.id')
                            ->where('1=1' . $addWhereClause);
            }
            else
            {
                    $query->select('h.*, '.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                            ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                            ' bub.disableanimation, bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
                            ->from('#__zhgooglemaps_markers as h')
                            ->leftJoin('#__categories as c ON h.catid=c.id')
                            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                            ->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
                            ->where('1=1' . $addWhereClause);

            }

            
            if ((int)$markerorder == 0)
            {
                    $query->order('h.title');
            }
            else if ((int)$markerorder == 1)
            {
                    $query->order('c.title, h.ordering');
            }
            else if ((int)$markerorder == 2)
            {
                    $query->order('c.title desc, h.ordering');
            }
            else if ((int)$markerorder == 10)
            {
                    $query->order('h.userorder, h.title');
            }
            else if ((int)$markerorder == 20)
            {
                    $query->order('g.title, h.title');
            }
            else if ((int)$markerorder == 21)
            {
                    $query->order('g.title desc, h.title');
            }
            else if ((int)$markerorder == 22)
            {
                    $query->order('g.userorder, g.title, h.title');
            }
            else if ((int)$markerorder == 23)
            {
                    $query->order('g.userorder desc, g.title, h.title');
            }
            else if ((int)$markerorder == 30)
            {
                    $query->order('h.createddate, h.title');
            }
            else if ((int)$markerorder == 31)
            {
                    $query->order('h.createddate desc, h.title');
            }
            else 
            {
                    $query->order('h.title');
            }                    

            $nowDate = $db->Quote(Factory::getDate()->toSQL());
            $query->where('(h.publish_up IS NULL OR h.publish_up <= ' . $nowDate . ')');
            $query->where('(h.publish_down IS NULL OR h.publish_down >= ' . $nowDate . ')');

            $db->setQuery($query);        
            
            // Markers
            $markers = $db->loadObjectList();


            return $markers;

    }
    
    public static function getRouters($id, $routelistid, $exroutelistid, $grouplistid, $categorylistid) 
    {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = '';

            if ($routelistid == ""
             && $grouplistid == ""
             && $categorylistid == ""
            )
            {

                    $addWhereClause .= ' and h.mapid='.(int)$id;

                    if ($exroutelistid != "")
                    {
                            $tmp_expl_ids = explode(',', str_replace(';',',', $exroutelistid));                                       
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
                    if ($routelistid != "")
                    {
                            $tmp_pl_ids = explode(',', str_replace(';',',', $routelistid));                                       
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
                    if ($exroutelistid != "")
                    {
                            $tmp_expl_ids = explode(',', str_replace(';',',', $exroutelistid));                                       
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
                            /* it is not in table yet
                            if (strpos($tmp_grp_ids, ','))
                            {
                                    $addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
                            }
                            else
                            {
                                    $addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
                            }
                            */
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
            }



            $query->select('h.*, c.title as category ')
                    ->from('#__zhgooglemaps_routers as h')
                    ->leftJoin('#__categories as c ON h.catid=c.id')
                    ->where('h.published=1'.$addWhereClause);

            $db->setQuery($query);        

            // Routers
            $routers = $db->loadObjectList();

            return $routers;
    }

    public static function getMarkerGroups($id, 
                                           $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                           $markergrouporder) 
    {

                $db = Factory::getDBO();

                $query = $db->getQuery(true);
            
                $addWhereClause = "";

                if ($placemarklistid == ""
                 && $grouplistid == ""
                 && $categorylistid == ""
                 && $taglistid == ""
                )
                {
                        $addWhereClause .= ' and m.mapid='.(int)$id;
                        if ($explacemarklistid != "")
                        {
                                $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                if (strpos($tmp_expl_ids, ','))
                                {
                                        $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                        $addWhereClause .= ' and m.id IN ('.$tmp_pl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id = '.(int)$tmp_pl_ids;
                                }
                        }
                        if ($explacemarklistid != "")
                        {
                                $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                if (strpos($tmp_expl_ids, ','))
                                {
                                        $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
                                }
                        }
                        if ($grouplistid != "")
                        {
                                $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                                $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                $tmp_grp_ids = implode(',', $tmp_grp_ids);

                                if (strpos($tmp_grp_ids, ','))
                                {
                                        $addWhereClause .= ' and m.markergroup IN ('.$tmp_grp_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.markergroup = '.(int)$tmp_grp_ids;
                                }
                        }
                        if ($categorylistid != "")
                        {
                                $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                                $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                $tmp_cat_ids = implode(',', $tmp_cat_ids);

                                if (strpos($tmp_cat_ids, ','))
                                {
                                        $addWhereClause .= ' and m.catid IN ('.$tmp_cat_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.catid = '.(int)$tmp_cat_ids;
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
                                                             ' WHERE tagmap.content_item_id = m.id '.
                                                             ' AND tagmap.type_alias = \'com_zhgooglemap.mapmarker\''.$addWherePlacemarkTag.')';    

                                $addWhereClause .= $addWherePlacemarkTagExist;        
                        }
                }


                // Remove 'h.published=1 and m.published=1
                // because group may be disabled, but manual edit users placemark enable

                $nowDate = $db->Quote(Factory::getDate()->toSQL());
                $addWhereClause .= ' and (m.publish_up IS NULL OR m.publish_up <= ' . $nowDate . ')';
                $addWhereClause .= ' and (m.publish_down IS NULL OR m.publish_down >= ' . $nowDate . ')';

                $query->select('h.*, c.title as category ')
                        ->from('#__zhgooglemaps_markergroups as h')
                        ->leftJoin('#__categories as c ON h.catid=c.id')
                        ->where(' EXISTS (SELECT 1 FROM #__zhgooglemaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause.')')
                        ;

                if ((int)$markergrouporder == 0)
                {
                        $query->order('h.title');
                }
                else if ((int)$markergrouporder == 1)
                {
                        $query->order('c.title, h.ordering');
                }
                else if ((int)$markergrouporder == 10)
                {
                        $query->order('h.userorder, h.title');
                }
                else 
                {
                        $query->order('h.title');
                }

                $db->setQuery($query);        

                // MarkerGroups
                $markergroups = $db->loadObjectList();


        return $markergroups;
    }

    public static function getMarkerGroupsManage($id, 
                                                 $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                 $markergrouporder, 
                                                 $markergroupctlmarker, 
                                                 $markergroupctlpath,
                                                 $pathlistid, $expathlistid, $grouplistpathid, $categorylistpathid, $pathtaglistid) 
    {


            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = "";
            $addWhereClausePath = "";

            if ((int)$markergroupctlmarker == 1)
            {

                    if ($placemarklistid == ""
                     && $grouplistid == ""
                     && $categorylistid == ""
                     && $taglistid == ""
                    )
                    {
                            $addWhereClause .= ' and m.mapid='.(int)$id;

                            if ($explacemarklistid != "")
                            {
                                    $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                    $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                    $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                    if (strpos($tmp_expl_ids, ','))
                                    {
                                            $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                            $addWhereClause .= ' and m.id IN ('.$tmp_pl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id = '.(int)$tmp_pl_ids;
                                    }
                            }
                            if ($explacemarklistid != "")
                            {
                                    $tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                    $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                    $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                    if (strpos($tmp_expl_ids, ','))
                                    {
                                            $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
                                    }
                            }
                            if ($grouplistid != "")
                            {
                                    $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                                    $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                    $tmp_grp_ids = implode(',', $tmp_grp_ids);

                                    if (strpos($tmp_grp_ids, ','))
                                    {
                                            $addWhereClause .= ' and m.markergroup IN ('.$tmp_grp_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.markergroup = '.(int)$tmp_grp_ids;
                                    }
                            }
                            if ($categorylistid != "")
                            {
                                    $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                                    $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                    $tmp_cat_ids = implode(',', $tmp_cat_ids);

                                    if (strpos($tmp_cat_ids, ','))
                                    {
                                            $addWhereClause .= ' and m.catid IN ('.$tmp_cat_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.catid = '.(int)$tmp_cat_ids;
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
                                                                 ' WHERE tagmap.content_item_id = m.id '.
                                                                 ' AND tagmap.type_alias = \'com_zhgooglemap.mapmarker\''.$addWherePlacemarkTag.')';    

                                    $addWhereClause .= $addWherePlacemarkTagExist;        
                            }
                    }
            }


            $nowDate = $db->Quote(Factory::getDate()->toSQL());

            // Remove 'h.published=1 and m.published=1
            // because group may be disabled, but manual edit users placemark enable

            if ((int)$markergroupctlmarker == 1)
            {
                    if ((int)$markergroupctlpath != 0)
                    {
                            $addWhereClause .= ' and (m.publish_up IS NULL OR m.publish_up <= ' . $nowDate . ')';
                            $addWhereClause .= ' and (m.publish_down IS NULL OR m.publish_down >= ' . $nowDate . ')';

                            $addWhereClausePath .= ' and (p.publish_up IS NULL OR p.publish_up <= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.publish_down IS NULL OR p.publish_down >= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.published = 1)';

                            // new parameters - start
                            //$addWhereClausePathPath .= ' and (p.mapid = '.(int)$id.')';

                            if ($pathlistid == ""
                             && $grouplistpathid == ""
                             && $categorylistpathid == ""
                             && $pathtaglistid == ""
                            )
                            {

                                    $addWhereClausePath .= ' and p.mapid='.(int)$id;

                                    if ($expathlistid != "")
                                    {
                                            $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                            $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                            if (strpos($tmp_expl_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
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
                                                    $addWhereClausePath .= ' and p.id IN ('.$tmp_pl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id = '.(int)$tmp_pl_ids;
                                            }
                                    }
                                    if ($expathlistid != "")
                                    {
                                            $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                            $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                            if (strpos($tmp_expl_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
                                            }
                                    }
                                    if ($grouplistpathid != "")
                                    {
                                            $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistpathid));                                       
                                            $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                            $tmp_grp_ids = implode(',', $tmp_grp_ids);

                                            if (strpos($tmp_grp_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup IN ('.$tmp_grp_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup = '.(int)$tmp_grp_ids;
                                            }
                                    }
                                    if ($categorylistpathid != "")
                                    {
                                            $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistpathid));                                       
                                            $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                            $tmp_cat_ids = implode(',', $tmp_cat_ids);

                                            if (strpos($tmp_cat_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.catid IN ('.$tmp_cat_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.catid = '.(int)$tmp_cat_ids;
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
                                                                    ' WHERE tagmap.content_item_id = p.id '.
                                                                    ' AND tagmap.type_alias = \'com_zhgooglemap.mappath\''.$addWherePathTag.')';    

                                            $addWhereClausePath .= $addWherePathTagExist;        
                                    }
                            }

                            // new parameters - end

                            if ((int)$markergroupctlpath == 1)
                            {
                                    $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 2)
                            {
                                    $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 3)
                            {
                                    $addWhereClausePath .= ' and ((p.path IS NOT NULL and p.path != \'\') or (p.kmllayer IS NOT NULL and p.kmllayer != \'\'))';
                            }
                            else 
                            {
                                    $addWhereClausePath .= ' and (1=2)';
                            }


                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhgooglemaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('( EXISTS (SELECT 1 FROM #__zhgooglemaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause. ')'.
                                    ' or EXISTS (SELECT 1 FROM #__zhgooglemaps_paths as p WHERE p.markergroup=h.id ' . $addWhereClausePath.'))')
                                    ;
                    }
                    else
                    {
                            $addWhereClause .= ' and (m.publish_up IS NULL OR m.publish_up <= ' . $nowDate . ')';
                            $addWhereClause .= ' and (m.publish_down IS NULL OR m.publish_down >= ' . $nowDate . ')';

                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhgooglemaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('EXISTS (SELECT 1 FROM #__zhgooglemaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause.')')
                                    ;
                    }
            }
            else
            {
                    if ((int)$markergroupctlpath != 0)
                    {
                            $addWhereClausePath .= ' and (p.publish_up IS NULL OR p.publish_up <= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.publish_down IS NULL OR p.publish_down >= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.published = 1)';

                            // new parameters - start
                            //$addWhereClausePathPath .= ' and (p.mapid = '.(int)$id.')';

                            if ($pathlistid == ""
                             && $grouplistpathid == ""
                             && $categorylistpathid == ""
                             && $pathtaglistid == ""
                            )
                            {

                                    $addWhereClausePath .= ' and p.mapid='.(int)$id;

                                    if ($expathlistid != "")
                                    {
                                            $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                            $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                            if (strpos($tmp_expl_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
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
                                                    $addWhereClausePath .= ' and p.id IN ('.$tmp_pl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id = '.(int)$tmp_pl_ids;
                                            }
                                    }
                                    if ($expathlistid != "")
                                    {
                                            $tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                            $tmp_expl_ids = implode(',', $tmp_expl_ids);

                                            if (strpos($tmp_expl_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
                                            }
                                    }
                                    if ($grouplistpathid != "")
                                    {
                                            $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistpathid));                                       
                                            $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                            $tmp_grp_ids = implode(',', $tmp_grp_ids);

                                            if (strpos($tmp_grp_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup IN ('.$tmp_grp_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup = '.(int)$tmp_grp_ids;
                                            }
                                    }
                                    if ($categorylistpathid != "")
                                    {
                                            $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistpathid));                                       
                                            $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                            $tmp_cat_ids = implode(',', $tmp_cat_ids);

                                            if (strpos($tmp_cat_ids, ','))
                                            {
                                                    $addWhereClausePath .= ' and p.catid IN ('.$tmp_cat_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.catid = '.(int)$tmp_cat_ids;
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
                                                                    ' WHERE tagmap.content_item_id = p.id '.
                                                                    ' AND tagmap.type_alias = \'com_zhgooglemap.mappath\''.$addWherePathTag.')';    

                                            $addWhereClausePath .= $addWherePathTagExist;        
                                    }
                            }

                            // new parameters - end

                            if ((int)$markergroupctlpath == 1)
                            {
                                    $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 2)
                            {
                                    $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 3)
                            {
                                    $addWhereClausePath .= ' and ((p.path IS NOT NULL and p.path != \'\') or (p.kmllayer IS NOT NULL and p.kmllayer != \'\'))';
                            }
                            else 
                            {
                                    $addWhereClausePath .= ' and (1=2)';
                            }

                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhgooglemaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('EXISTS (SELECT 1 FROM #__zhgooglemaps_paths as p WHERE p.markergroup=h.id ' . $addWhereClausePath.')')
                                    ;
                    }
                    else
                    {
                            // return nothing
                            $query->select(' h.*, c.title as category ')
                                    ->from('#__zhgooglemaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('1=2')
                                    ;
                    }
            }

            if ((int)$markergrouporder == 0)
            {
                    $query->order('h.title');
            }
            else if ((int)$markergrouporder == 1)
            {
                    $query->order('c.title, h.ordering');
            }
            else if ((int)$markergrouporder == 10)
            {
                    $query->order('h.userorder, h.title');
            }
            else 
            {
                    $query->order('h.title');
            }

            $db->setQuery($query);        

            // MarkerGroups
            $markergroups_manage = $db->loadObjectList();


            return $markergroups_manage;
    }


    
    public static function getPaths($id, 
                                    $pathlistid, $expathlistid, $grouplistid, $categorylistid, $pathtaglistid) 
    {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);
            
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
            
            $query->select('h.*, c.title as category ')
                ->from('#__zhgooglemaps_paths as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1'.$addWhereClause);
                       
            $db->setQuery($query);        
            
            // Paths
            $mappaths = $db->loadObjectList();


            return $mappaths;
    }

    public static function getMapTypes() 
    {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);
            $query->select('h.*, c.title as category ')
                ->from('#__zhgooglemaps_maptypes as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1');
            $db->setQuery($query);        
            
            // Map Types
            $maptypes = $db->loadObjectList(); 


            return $maptypes;
    }


    public static function getMapAPIKey() 
    {
        // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $mapapikey4map = $comparams->get( 'map_map_key');

            return $mapapikey4map;
    }

    public static function getMapGPDR() 
    {
        // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $enable_map_gpdr = $comparams->get( 'enable_map_gpdr');

            return $enable_map_gpdr;
    }

    public static function getMapGPDR_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonlabel = $comparams->get( 'buttonlabel');

            return $map_gpdr_buttonlabel;
    }
    public static function getMapGPDR_Header() 
    {
        // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_header = $comparams->get( 'headerhtml');

            return $map_gpdr_header;
    }
    public static function getMapGPDR_Footer() 
    {
        // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_footer = $comparams->get( 'footerhtml');

            return $map_gpdr_footer;
    }	

    public static function getMapGPDR_Cookie() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonc = $params->get( 'cookies_button');

            return $map_gpdr_buttonc;
    }
    public static function getMapGPDR_Cookie_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonclabel = $params->get( 'buttonlabelc');

            return $map_gpdr_buttonclabel;
    }
    public static function getMapGPDR_Cookie_Days() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttoncexp = $params->get( 'cookies_days');

            return $map_gpdr_buttoncexp;
    }
	
    public static function getNZMapAPIKey() 
    {
        // Get global params
        $app = Factory::getApplication();
        $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapapikey4map_nz = $comparams->get( 'map_map_key_nz', '' );
    }
    public static function getCompatibleMode() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $mapcompatiblemode = $comparams->get( 'map_compatiblemode');

            return $mapcompatiblemode;
    }
    
	public static function getLoadJQuery() 
    {
        // Get global params
        $app = Factory::getApplication();
        $comparams = ComponentHelper::getParams('com_zhgooglemap');
		
		$loadjquery = $comparams->get( 'load_jquery', '' );

        return $loadjquery;
    }
	
    public static function getHttpsProtocol() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
            $httpsprotocol = $comparams->get( 'httpsprotocol');

            return $httpsprotocol;
    }
    
    public static function getLoadType() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );
            $loadtype = $comparams->get( 'loadtype');

            return $loadtype;
    }
    
    public static function getMapAPIVersion() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );

            $mapapiversion = $comparams->get( 'map_api_version');

            return $mapapiversion;
    }

    public static function getMapLicenseInfo() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );

            $licenseinfo = $comparams->get( 'licenseinfo');

            return $licenseinfo;
    }

    public static function getMapAPIType() 
    {
            // Get global params
            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );

            $mapapitype = $comparams->get( 'api_type');

            return $mapapitype;
    }
    
    
    public static function getPlacemarkTitleTag() 
    {
            // Get global params

            $app = Factory::getApplication();
            $comparams = ComponentHelper::getParams( 'com_zhgooglemap' );

            $placemarktitletag = $comparams->get( 'placemarktitletag');

            return $placemarktitletag;
    }

    public static function getMarkerCoordinatesLatLngObject($markerId)
    {
            if ((int)$markerId != 0)
            {
                    $dbMrk = Factory::getDBO();

                    $queryMrk = $dbMrk->getQuery(true);
                    $queryMrk->select('h.*')
                            ->from('#__zhgooglemaps_markers as h')
                            ->where('h.id = '.(int) $markerId);
                    $dbMrk->setQuery($queryMrk);        
                    $myMarker = $dbMrk->loadObject();

                    if (isset($myMarker))
                    {
                            if ($myMarker->latitude != "" && $myMarker->longitude != "")
                            {
                                    return 'new google.maps.LatLng('.$myMarker->latitude.', ' .$myMarker->longitude.')';
                            }
                            else
                            {
                                    return 'geocode';
                            }
                    }
                    else
                    {
                            return '';
                    }    
            }
    }    
}
