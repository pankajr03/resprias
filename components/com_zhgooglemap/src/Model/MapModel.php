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
namespace ZhukDL\Component\ZhGoogleMap\Site\Model;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Input\Input;

use Joomla\Registry\Registry;

class MapModel extends ItemModel 
{
    /**
     * @var object item
     */
    protected $item;
    var $markers;
    var $markergroups;
    var $mgrgrouplist;
    var $routers;
    var $mappaths;
    var $maptypes;
    var $mapapikey4map;
    var $mapapiversion;
    var $mapapitype;
    var $placemarktitletag;

    var $mapcompatiblemode;
    var $httpsprotocol;
    var $loadtype;
    var $licenseinfo;
	var $loadjquery;
    
    var $centerplacemarkid;
    var $centerplacemarkaction;
    var $mapzoom;
    var $mapwidth;
    var $mapheight;
    var $externalmarkerlink;
    var $usermarkersfilter;
        
    // Populate to pass parameters into main script
    var $mapid;
    var $placemarklistid;
    var $explacemarklistid;
    var $grouplistid;
    var $categorylistid;
    var $taglistid;

    var $routelistid;
    var $exroutelistid;
    var $routecategorylistid;

    var $pathlistid;
    var $expathlistid;
    var $pathgrouplistid;
    var $pathcategorylistid;
    var $pathtaglistid;
	
	var $enable_map_gpdr;
	var $map_gpdr_buttonlabel;
    var $map_gpdr_header;
    var $map_gpdr_footer;

	var $map_gpdr_buttonc;
	var $map_gpdr_buttonclabel;
	var $map_gpdr_buttoncexp;
        
    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     * @since    1.6
     */
    protected function populateState() 
    {
        $app = Factory::getApplication();
        // Get the map id
        $id = $app->input->get('id', '', "INT");
        $this->setState('map.id', $id);

        $placemarklistid = $app->input->get('placemarklistid', '', "STRING");
        $this->setState('map.placemarklistid', $placemarklistid);

        $explacemarklistid = $app->input->get('explacemarklistid', '', "STRING");
        $this->setState('map.explacemarklistid', $explacemarklistid);
        
        $grouplistid = $app->input->get('grouplistid', '', "STRING");
        $this->setState('map.grouplistid', $grouplistid);

        $categorylistid = $app->input->get('categorylistid', '', "STRING");
        $this->setState('map.categorylistid', $categorylistid);

        $centerplacemarkid = $app->input->get('centerplacemarkid', '', "INT");
        $this->setState('map.centerplacemarkid', $centerplacemarkid);

        $centerplacemarkaction = $app->input->get('centerplacemarkaction', '', "STRING");
        $this->setState('map.centerplacemarkaction', $centerplacemarkaction);

        $mapzoom = $app->input->get('mapzoom', '', "INT");
        $this->setState('map.mapzoom', $mapzoom);
        
        $mapwidth = $app->input->get('mapwidth', '', "INT");
        $this->setState('map.mapwidth', $mapwidth);
        $mapheight = $app->input->get('mapheight', '', "INT");
        $this->setState('map.mapheight', $mapheight);
        
        $externalmarkerlink = $app->input->get('externalmarkerlink', '', "INT");
        $this->setState('map.externalmarkerlink', $externalmarkerlink);
        
        $usermarkersfilter = $app->input->get('usermarkersfilter', '', "INT");
        $this->setState('map.usermarkersfilter', $usermarkersfilter);

        //
        $routelistid = $app->input->get('routelistid', '', "STRING");
        $this->setState('map.routelistid', $routelistid);

        $exroutelistid = $app->input->get('exroutelistid', '', "STRING");
        $this->setState('map.exroutelistid', $exroutelistid);
        
        $routecategorylistid = $app->input->get('routecategorylistid', '', "STRING");
        $this->setState('map.routecategorylistid', $routecategorylistid);

        $pathlistid = $app->input->get('pathlistid', '', "STRING");
        $this->setState('map.pathlistid', $pathlistid);

        $expathlistid = $app->input->get('expathlistid', '', "STRING");
        $this->setState('map.expathlistid', $expathlistid);
        
        $pathgrouplistid = $app->input->get('pathgrouplistid', '', "STRING");
        $this->setState('map.pathgrouplistid', $pathgrouplistid);

        $pathcategorylistid = $app->input->get('pathcategorylistid', '', "STRING");
        $this->setState('map.pathcategorylistid', $pathcategorylistid);
                
        $taglistid = $app->input->get('taglistid', '', "STRING");
        $this->setState('map.taglistid', $taglistid);
        $pathtaglistid = $app->input->get('pathtaglistid', '', "STRING");
        $this->setState('map.pathtaglistid', $pathtaglistid);

        //
        
        // Load the parameters.
        $params = ComponentHelper::getParams('com_zhgooglemap');

        $this->setState('params', $params);
        parent::populateState();
    }

    /**
     * Get the map
     * @return object The map to be displayed to the user
     */
    public function getItem($pk = null) 
    {
        if (!isset($this->item)) 
        {
            $id = $this->getState('map.id');

            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $query->select('h.*, c.title as category')
                ->from('#__zhgooglemaps_maps as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.id=' . (int)$id)
                ->order('h.title');

            $db->setQuery($query);        
                
            if (!$this->item = $db->loadObject()) 
            {
                $this->setError($db->getError());
            }
            else
            {
               $params = $this->state->get('params');
            }

        }

        return $this->item;
    }

    public function getMarkers() 
    {
        if ((int)$this->item->useajaxobject == 0)
        {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);
            
            if (!isset($this->markers)) 
            {       
                $id = $this->getState('map.id');

                // Create some addition filters - Begin
                $addWhereClause = '';

                // Check if placemark list defined
                $placemarklistid = $this->getState('map.placemarklistid');
                $explacemarklistid = $this->getState('map.explacemarklistid');
                $grouplistid = $this->getState('map.grouplistid');
                $categorylistid = $this->getState('map.categorylistid');
                $taglistid = $this->getState('map.taglistid');

                if ($this->getState('map.usermarkersfilter') == "")
                {
                    $usermarkersfilter = (int)$this->item->usermarkersfilter;
                }
                else
                {
                    $usermarkersfilter = (int)$this->getState('map.usermarkersfilter');
                }
                
                if ($placemarklistid == ""
                 && $grouplistid == ""
                 && $categorylistid == ""
                 && $taglistid == "")
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
                
                if ((int)$this->item->usermarkers == 0)
                {
                    // You can not enter markers

                    // You can see all published, and you can't enter markers
                    
                    switch ($usermarkersfilter)
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
                    
                    switch ($usermarkersfilter)
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
                            if ((int)$currentUser->id == 0)
                            {
                                $addWhereClause .= ' and h.published=1';
                                $currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
                                $addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
                            }
                            else
                            {
                                $currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
                                $addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
                            }
                        break;
                        default:
                            $addWhereClause .= ' and h.published=1';
                        break;                    
                    }
                }
                // Create some addition filters - End


                    
                if ((int)$this->item->usermarkers == 0
                 && (int)$this->item->useajax != 0)
                {
                        $query->select('h.id, h.markergroup, h.published, h.title, h.description, h.latitude, h.longitude, h.addresstext, h.icontype, h.baloon, '.
                            ' h.descriptionhtml, h.hrefimagethumbnail, h.includeinlist, '.
                            ' h.userprotection, h.createdbyuser, h.markercontent, h.openbaloon, h.actionbyclick,  h.hoverhtml, h.rating_value, '.
                            ' h.labelcontent, h.labelclass, h.labelanchorx, h.labelanchory, h.labelinbackground, '.
                            ' h.ordering,h.userorder, h.iconofsetx, h.iconofsety, g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster ')
                            ->from('#__zhgooglemaps_markers as h')
                            ->leftJoin('#__categories as c ON h.catid=c.id')
                            ->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
                            ->where('1=1' . $addWhereClause);
                }
                else
                {
                    if ((int)$this->item->usercontact == 1)
                    {
                        $query->select('h.*, '.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
                            ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                            ' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage,'.
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
                            ->where('1=1'. $addWhereClause);
                    }
                    
                }
                                

                if ((int)$this->item->markerorder == 0)
                {
                    $query->order('h.title');
                }
                else if ((int)$this->item->markerorder == 1)
                {
                    $query->order('c.title, h.ordering');
                }
                else if ((int)$this->item->markerorder == 2)
                {
                    $query->order('c.title desc, h.ordering');
                }
                else if ((int)$this->item->markerorder == 10)
                {
                    $query->order('h.userorder, h.title');
                }
                else if ((int)$this->item->markerorder == 20)
                {
                    $query->order('g.title, h.title');
                }
                else if ((int)$this->item->markerorder == 21)
                {
                    $query->order('g.title desc, h.title');
                }
                else if ((int)$this->item->markerorder == 22)
                {
                    $query->order('g.userorder, g.title, h.title');
                }
                else if ((int)$this->item->markerorder == 23)
                {
                    $query->order('g.userorder desc, g.title, h.title');
                }
                else if ((int)$this->item->markerorder == 30)
                {
                    $query->order('h.createddate, h.title');
                }
                else if ((int)$this->item->markerorder == 31)
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
                
                try
                {
                    $rows = $db->loadObjectList();
                }
                catch (RuntimeException $e)
                {
                    $this->setError("ERROR! " . $e->getMessage());

                    return false;
                }
                $this->markers = $rows;

            }
        }

        return $this->markers;
    }
    
    public function getRouters() 
    {
        if (!isset($this->routers)) 
        {       
            $id = $this->getState('map.id');

            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = '';
            $routelistid = $this->getState('map.routelistid');
            $exroutelistid = $this->getState('map.exroutelistid');
            $grouplistid = $this->getState('map.routegrouplistid');
            $categorylistid = $this->getState('map.routecategorylistid');
            
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
                ->where('h.published=1' . $addWhereClause);

            $db->setQuery($query);        

            try
            {
                $rows = $db->loadObjectList();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }
            $this->routers = $rows;


        }

        return $this->routers;
    }

    public function getMarkerGroups() 
    {

        if (!isset($this->markergroups)) 
        {       
            $id = $this->getState('map.id');

            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = "";
                        
            $placemarklistid = $this->getState('map.placemarklistid');
            $explacemarklistid = $this->getState('map.explacemarklistid');
            $grouplistid = $this->getState('map.grouplistid');
            $categorylistid = $this->getState('map.categorylistid');
            $taglistid = $this->getState('map.taglistid');
            

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


            if ((int)$this->item->markergrouporder == 0)
            {
                $query->order('h.title');
            }
            else if ((int)$this->item->markergrouporder == 1)
            {
                $query->order('c.title, h.ordering');
            }
            else if ((int)$this->item->markergrouporder == 10)
            {
                $query->order('h.userorder, h.title');
            }
            else 
            {
                $query->order('h.title');
            }
            
            
            $db->setQuery($query);        

            try
            {
                $rows = $db->loadObjectList();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }
            $this->markergroups = $rows;


        }

        return $this->markergroups;
    }


    public function getMgrGroupsList() 
    {

        /* 19.02.2013 
           for flexible support group management 
           and have ability to set off placemarks from group managenent 
           markergroups changed to mgrgrouplist
           */
    
        if (!isset($this->mgrgrouplist)) 
        {       
            $id = $this->getState('map.id');

            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = "";
            $addWhereClausePath = "";
                        
            $placemarklistid = $this->getState('map.placemarklistid');
            $explacemarklistid = $this->getState('map.explacemarklistid');
            $grouplistid = $this->getState('map.grouplistid');
            $categorylistid = $this->getState('map.categorylistid');
            
            // 26.06.2015 - new parameters
            $pathlistid = $this->getState('map.pathlistid');
            $expathlistid = $this->getState('map.expathlistid');
            $grouplistpathid = $this->getState('map.pathgrouplistid');
            $categorylistpathid = $this->getState('map.pathcategorylistid');
            
            $taglistid = $this->getState('map.taglistid');
            $pathtaglistid = $this->getState('map.pathtaglistid');
            
            
            if ((int)$this->item->markergroupctlmarker == 1)
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

            if ((int)$this->item->markergroupctlmarker == 1)
            {
                if ((int)$this->item->markergroupctlpath != 0)
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
                    
                    if ((int)$this->item->markergroupctlpath == 1)
                    {
                        $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                    }
                    else if ((int)$this->item->markergroupctlpath == 2)
                    {
                        $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                    }
                    else if ((int)$this->item->markergroupctlpath == 3)
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
                if ((int)$this->item->markergroupctlpath != 0)
                {
                    $addWhereClausePath .= ' and (p.publish_up IS NULL OR p.publish_up <= ' . $nowDate . ')';
                    $addWhereClausePath .= ' and (p.publish_down IS NULL OR p.publish_down >= ' . $nowDate . ')';
                    $addWhereClausePath .= ' and (p.published = 1)';

                    // new parameters - start
                    //$addWhereClausePathPath .= ' and (p.mapid = '.(int)$id.')';
            
                    if ($pathlistid == ""
                        && $grouplistpathid == ""
                        && $categorylistpathid == ""
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
                    }
                    
                    // new parameters - end
                    
                    if ((int)$this->item->markergroupctlpath == 1)
                    {
                        $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                    }
                    else if ((int)$this->item->markergroupctlpath == 2)
                    {
                        $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                    }
                    else if ((int)$this->item->markergroupctlpath == 3)
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
            
            if ((int)$this->item->markergrouporder == 0)
            {
                $query->order('h.title');
            }
            else if ((int)$this->item->markergrouporder == 1)
            {
                $query->order('c.title, h.ordering');
            }
            else if ((int)$this->item->markergrouporder == 10)
            {
                $query->order('h.userorder, h.title');
            }
            else 
            {
                $query->order('h.title');
            }
            
            $db->setQuery($query);        

            try
            {
                $rows = $db->loadObjectList();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }
            $this->mgrgrouplist = $rows;


        }

        return $this->mgrgrouplist;
    }
    
    public function getPaths() 
    {
        if ((int)$this->item->useajaxobject == 0)
        {
            if (!isset($this->mappaths)) 
            {       
                $id = $this->getState('map.id');

                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $addWhereClause = '';
                $pathlistid = $this->getState('map.pathlistid');
                $expathlistid = $this->getState('map.expathlistid');
                $grouplistid = $this->getState('map.pathgrouplistid');
                $categorylistid = $this->getState('map.pathcategorylistid');
                $pathtaglistid = $this->getState('map.pathtaglistid');

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

                try
                {
                    $rows = $db->loadObjectList();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());

                    return false;
                }
                $this->mappaths = $rows;


            }
                
        }

        return $this->mappaths;
    }

    public function getMapTypes() 
    {
        if (!isset($this->maptypes)) 
        {       
            $db = Factory::getDBO();

            $query = $db->getQuery(true);
            $query->select('h.*, c.title as category ')
                ->from('#__zhgooglemaps_maptypes as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1');
            $db->setQuery($query);        
            
            try
            {
                $rows = $db->loadObjectList();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }
            $this->maptypes = $rows;


        }

        return $this->maptypes;
    }


    public function getMapAPIVersion() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapiversion = $params->get( 'map_api_version', '' );
    }
    
    public function getMapAPIType() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapitype = $params->get( 'api_type', '' );
    }
    
    public function getPlacemarkTitleTag() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $placemarktitletag = $params->get( 'placemarktitletag', '' );
    }
    
    public function getMapGPDR() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $enable_map_gpdr = $params->get( 'enable_map_gpdr');

            return $enable_map_gpdr;
    }
	
    public function getMapGPDR_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonlabel = $params->get( 'buttonlabel');

            return $map_gpdr_buttonlabel;
    }
    public function getMapGPDR_Header() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_header = $params->get( 'headerhtml');

            return $map_gpdr_header;
    }
    public function getMapGPDR_Footer() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_footer = $params->get( 'footerhtml');

            return $map_gpdr_footer;
    }	

    public function getMapGPDR_Cookie() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonc = $params->get( 'cookies_button');

            return $map_gpdr_buttonc;
    }
    public function getMapGPDR_Cookie_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonclabel = $params->get( 'buttonlabelc');

            return $map_gpdr_buttonclabel;
    }
    public function getMapGPDR_Cookie_Days() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttoncexp = $params->get( 'cookies_days');

            return $map_gpdr_buttoncexp;
    }

	
    public function getMapAPIKey() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapikey4map = $params->get( 'map_map_key', '' );
    }

    public function getNZMapAPIKey() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapikey4map_nz = $params->get( 'map_map_key_nz', '' );
    }

    public function getCompatibleMode() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapcompatiblemode = $params->get( 'map_compatiblemode', '' );
    }

	public function getLoadJQuery() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');

        return $loadjquery = $params->get( 'load_jquery', '' );
    }
	
    public function getHttpsProtocol() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $httpsprotocol = $params->get( 'httpsprotocol', '' );
    }
    
    public function getLicenseInfo() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $licenseinfo = $params->get( 'licenseinfo', '' );
    }
    
    public function getLoadType() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $loadtype = $params->get( 'loadtype', '' );
    }


    public function getCenterPlacemarkId() 
    {
        $centerplacemarkid = $this->getState('map.centerplacemarkid');
        return $centerplacemarkid;
    }
    
    public function getCenterPlacemarkAction() 
    {
        $centerplacemarkaction = $this->getState('map.centerplacemarkaction');
        return $centerplacemarkaction;
    }

    public function getMapZoom() 
    {
        $mapzoom = $this->getState('map.mapzoom');
        return $mapzoom;
    }

    public function getExternalMarkerLink() 
    {
        $externalmarkerlink = $this->getState('map.externalmarkerlink');
        return $externalmarkerlink;
    }

    public function getUserMarkersFilter() 
    {
        $usermarkersfilter = $this->getState('map.usermarkersfilter');
        return $usermarkersfilter;
    }
    
    public function getMapWidth() 
    {
        $mapwidth = $this->getState('map.mapwidth');
        return $mapwidth;
    }
    public function getMapHeight() 
    {
        $mapheight = $this->getState('map.mapheight');
        return $mapheight;
    }

    public function getMapID() 
    {
        $mapid = $this->getState('map.id');
        return $mapid;
    }
    
    public function getPlacemarkListID() 
    {
        $placemarklistid = str_replace(',',';', $this->getState('map.placemarklistid'));
        return $placemarklistid;
    }

    public function getExPlacemarkListID() 
    {
        $explacemarklistid = str_replace(',',';', $this->getState('map.explacemarklistid'));
        return $explacemarklistid;
    }

    public function getGroupListID() 
    {
        $grouplistid = str_replace(',',';', $this->getState('map.grouplistid'));
        return $grouplistid;
    }

    public function getCategoryListID() 
    {
        $categorylistid = str_replace(',',';', $this->getState('map.categorylistid'));
        return $categorylistid;
    }
    
    //
    public function getRouteListID() 
    {
        $routelistid = str_replace(',',';', $this->getState('map.routelistid'));
        return $routelistid;
    }

    public function getExRouteListID() 
    {
        $exroutelistid = str_replace(',',';', $this->getState('map.exroutelistid'));
        return $exroutelistid;
    }

    public function getRouteCategoryListID() 
    {
        $routecategorylistid = str_replace(',',';', $this->getState('map.routecategorylistid'));
        return $routecategorylistid;
    }
    public function getPathListID() 
    {
        $pathlistid = str_replace(',',';', $this->getState('map.pathlistid'));
        return $pathlistid;
    }

    public function getExPathListID() 
    {
        $expathlistid = str_replace(',',';', $this->getState('map.expathlistid'));
        return $expathlistid;
    }

    public function getPathGroupListID() 
    {
        $pathgrouplistid = str_replace(',',';', $this->getState('map.pathgrouplistid'));
        return $pathgrouplistid;
    }

    public function getPathCategoryListID() 
    {
        $pathcategorylistid = str_replace(',',';', $this->getState('map.pathcategorylistid'));
        return $pathcategorylistid;
    }
        
    public function getTagListID() 
    {
        $taglistid = str_replace(',',';', $this->getState('map.taglistid'));
        return $taglistid;
    }
           
    public function getPathTagListID() 
    {
        $pathtaglistid = str_replace(',',';', $this->getState('map.pathtaglistid'));
        return $pathtaglistid;
    }
    
}
