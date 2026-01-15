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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Model;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;


/**
 * ZhGOOGLEBufmrk Model
 */
class MapbufmrkModel extends AdminModel
{   
	public $typeAlias = 'com_zhgooglemap.mapbufmrk';

    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return    mixed    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_zhgooglemap.mapbufmrk', 'mapbufmrk', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) 
        {
            return false;
        }
        return $form;
    }

    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData() 
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_zhgooglemap.edit.mapbufmrk.data', array());
        if (empty($data)) 
        {
            $data = $this->getItem();
        }
		
		$this->preprocessData('com_zhgooglemap.mapbufmrk', $data);
		
        return $data;
    }


	protected function prepareTable($table)
	{
		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
	}


        /////////
        
        function insertLog($extension, $kind, $title, $id_target, $id_src, $id_find, $description, $remark) {
            
            $rec2ins = new \stdClass();
            
            $rec2ins->extension = $extension;
            $rec2ins->title = $title;           
            $rec2ins->kind = $kind;
            $rec2ins->description = $description;
            $rec2ins->remarks = $remark;
            $rec2ins->id = null;

            $rec2ins->id_target = $id_target;
            $rec2ins->id_source = $id_src;
            $rec2ins->id_find = $id_find;

            
            $result = Factory::getDbo()->insertObject('#__zhgooglemaps_log', $rec2ins, 'id');

        }

        
        function getCategoryID($extension, $catid, $pfx) {
            
            $ret_val = 0;
            
            
            if ($catid != 0)
            {
                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $query->select('h.id')
                    ->from('#__categories as t')
                    ->leftJoin('#__categories as h ON h.path=t.path')
                    ->where('h.extension=\'com_zhgooglemap\' and t.extension='.$db->quote('com_zh'.$extension.'map').' and t.id='.(int)$catid)
                ;          

                $db->setQuery($query);        

                $cat = $db->loadObject();

                if (isset($cat) && !empty($cat)) 
                {
                    $ret_val = $cat->id;
                }
                else 
                {
                   $ret_val = 0;
                }
            }

            return $ret_val;

        }

        function getGroupID($extension, $groupid, $pfx) {
            
            $ret_val = 0;
            
            
            if ($groupid != 0)
            {
                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $query->select('h.id')
                    ->from($db->quoteName('#__zh'.$extension.'maps_markergroups', 't'))
                    ->leftJoin('#__zhgooglemaps_markergroups as h ON h.title=t.title')
                    ->where('t.id='.(int)$groupid)
                ;          
 
                $db->setQuery($query);        

                $grp = $db->loadObject();

                if (isset($grp) && !empty($grp)) 
                {
                    $ret_val = $grp->id;
                }
                else 
                {
                   $ret_val = 0;
                }
            }

            return $ret_val;

        }

        function validateCategoryID($catid) {
            
            $ret_val = 0;
            
            
            if ($catid != 0)
            {
                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $query->select('t.id')
                    ->from('#__categories as t')
                    ->where('t.extension=\'com_zhgooglemap\' and t.id='.(int)$catid)
                ;          

                $db->setQuery($query);        

                $cat = $db->loadObject();

                if (isset($cat) && !empty($cat)) 
                {
                    $ret_val = $cat->id;
                }
                else 
                {
                   $ret_val = 0;
                }
            }

            return $ret_val;

        }

        function validateGroupID($groupid) {
            
            $ret_val = 0;
            
            
            if ($groupid != 0)
            {
                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $query->select('t.id')
                    ->from('#__zhgooglemaps_markergroups as t')
                    ->where('t.id='.(int)$groupid)
                ;          
 
                $db->setQuery($query);        

                $grp = $db->loadObject();

                if (isset($grp) && !empty($grp)) 
                {
                    $ret_val = $grp->id;
                }
                else 
                {
                   $ret_val = 0;
                }
            }

            return $ret_val;

        }
        
        function validateUserID($groupid) {
            
            $ret_val = 0;
            
            
            if ($groupid != 0)
            {
                $db = Factory::getDBO();

                $query = $db->getQuery(true);

                $query->select('t.id')
                    ->from('#__users as t')
                    ->where('t.id='.(int)$groupid)
                ;          
 
                $db->setQuery($query);        

                $grp = $db->loadObject();

                if (isset($grp) && !empty($grp)) 
                {
                    $ret_val = $grp->id;
                }
                else 
                {
                   $ret_val = 0;
                }
            }

            return $ret_val;

        }
 
        
        function loadPlacemark($extension, $marker_src, $map_id_to) {
            
            
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            $query->select('h.* ')
                ->from('#__zhgooglemaps_markers as h')
                ->where('h.title='.$db->quote($marker_src->title).' and h.mapid='.(int)$map_id_to)
            ;          
            
            $db->setQuery($query);        

            $grp = $db->loadObject();
            
            if (isset($grp) && !empty($grp)) 
            {
                // skipping
                $this->skipped += 1;
                
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__zhgooglemaps_marker_buffer'))->set('status=8')->where('id='.(int)$marker_src->id);
                $db->setQuery($query);
                $db->execute();
                 
            }
            else 
            {
                $new_id = $this->insertPlacemark($extension, $marker_src, $map_id_to);
            }

        }
        
        function insertPlacemark($extension, $marker_src, $map_id_to) {
            
            $currentUser = Factory::getUser();
            
            $db = Factory::getDBO();
            
            //$currentUser->id;
            $marker2ins = new \stdClass();
            
            $marker2ins->mapid = (int)$map_id_to;
            ////
            $marker2ins->actionbyclick = 1;
            // change default to simple
            $marker2ins->baloon = 3;
            ////

            $marker2ins->title = $marker_src->title;           
            $marker2ins->latitude = $marker_src->latitude;
            $marker2ins->longitude = $marker_src->longitude;

            $marker2ins->icontype = $marker_src->icontype;
            $marker2ins->iconofsetx = $marker_src->iconofsetx;
            $marker2ins->iconofsety = $marker_src->iconofsety;

            $marker2ins->published = $marker_src->published;
            $marker2ins->addresstext = $marker_src->addresstext;           
            $marker2ins->description = $marker_src->description;
            $marker2ins->descriptionhtml = $marker_src->descriptionhtml;
            $marker2ins->markercontent = $marker_src->markercontent;
            
            $marker2ins->hrefimage = $marker_src->hrefimage;
                       
            $marker2ins->params = $marker_src->params;          

            $marker2ins->preparecontent = $marker_src->preparecontent;
            $marker2ins->createddate = $marker_src->createddate;
            $marker2ins->showuser = $marker_src->showuser;            
            $marker2ins->showgps = $marker_src->showgps;
                        
            $marker2ins->createdbyuser = $this->validateUserID($marker_src->createdbyuser);

            $marker2ins->markergroup = $this->validateGroupID($marker_src->markergroup);
            
            $marker2ins->catid = $this->validateCategoryID($marker_src->catid);
            
            $marker2ins->id = null;
            
            $db->insertObject('#__zhgooglemaps_markers', $marker2ins, 'id');
            
            $new_id = $marker2ins->id;

            
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__zhgooglemaps_marker_buffer'))->set('status=1')->where('id='.(int)$marker_src->id);
            $db->setQuery($query);
            $db->execute();
            
            $this->inserted += 1;
            if ($marker_src->catid != 0 && $marker2ins->catid == 0)
            {
                $this->insertLog($extension, Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_PLACEMARK'), Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CATEGORY_NOT_FOUND'), $new_id, $marker_src->id, $marker_src->catid, "", "");
                $this->warnings += 1;
            }
            if ($marker_src->markergroup != 0 && $marker2ins->markergroup == 0)
            {
                $this->insertLog($extension, Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_PLACEMARK'), Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_GROUP_NOT_FOUND'), $new_id, $marker_src->id, $marker_src->markergroup, "", "");
                $this->warnings += 1;
            }
            
            if ($marker_src->createdbyuser != 0 && $marker2ins->createdbyuser == 0)
            {
                $this->insertLog($extension, Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_PLACEMARK'), Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_USER_NOT_FOUND'), $new_id, $marker_src->id, $marker_src->createdbyuser, "", "");
                $this->warnings += 1;
            }

            return $new_id;
        }
        ////////
        
        
        public function deleteLog($extension) {

            $db = Factory::getDbo();

            $query = $db->getQuery(true);

            $query->delete($db->quoteName('#__zhgooglemaps_log'));
            $query->where('extension='.$db->quote($extension));

            $db->setQuery($query);
          
            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }
                        
            return true;

        }

        public function checkLog($extension) {
            $ret_val = 0;

            $db = Factory::getDbo();

            $query = $db->getQuery(true);

            $query->select('h.id ')
                ->from('#__zhgooglemaps_log as h')
                ->where('h.extension='.$db->quote($extension))
                ->order('h.id');
            ;

            $db->setQuery($query);        

            $loglines = $db->loadObject();

            if (isset($loglines) && !empty($loglines)) 
            {
                $ret_val = 1;
            }

            return $ret_val;
        } 
        
		public function marker_load($mapid, $pks)
		{
            $extension = 'csv_file_marker';

            // perform whatever you want on each item checked in the list
            //echo "Here Load:" ."<br />";
            //echo "Count:" . count($pks)."<br />";
    
            $this->inserted = 0;
            $this->errors = 0;
            $this->warnings = 0;
            $this->skipped = 0;
            
            $db = Factory::getDbo();

            $query = $db->getQuery(true);

            $query->select('h.* ')
                ->from('#__zhgooglemaps_marker_buffer as h');
            ;
            $query->where('h.status = 0');
            
            if (is_numeric($pks)) {
                    $query->where('h.id = '.(int) $pks);
            }
            else if (is_array($pks)) {
                    
					$pks = ArrayHelper::toInteger($pks);
                    
                    $pks = implode(',', $pks);
                    $query->where('h.id IN ('.$pks.')');
            }

            $db->setQuery($query);        

            $markers = $db->loadObjectList();
            
            $ret_val = "";

            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_PROCESSING'). "<br />";
            
            $proc_lines = 0;
            
            $this->deleteLog($extension);
                        
            if (isset($markers) && !empty($markers)) 
            {
                foreach ($markers as $current_marker) 
                {
                    $proc_lines += 1;
                    
                    //$ret_val .= "id: " . $current_marker->id. "<br />";
                    //$ret_val .= "title: " . $current_marker->title. "<br />";
                    $this->loadPlacemark($extension, $current_marker, (int)$mapid);
                }
            }    
            
            if ($this->inserted != 0) 
            {
                
                $ret_val .= '<br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_INSERTED').' '.$this->inserted.'.</span>';
                if ($this->warnings != 0)
                {
                    $ret_val .= '<br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_WARNINGS').' '.$this->warnings.'.</span><br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CHECK_LOG').'</span>';
                }
                if ($this->skipped != 0)
                {
                    $ret_val .= '<br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_SKIPPED').' '.$this->skipped.'.</span>';
                }

            }
            else
            {
                if ($this->skipped != 0)
                {
                    $ret_val .= '<br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_SKIPPED').' '.$this->skipped.'.</span>';
                }
            }
            if ($this->inserted == 0 && $this->skipped == 0)
            {
                $ret_val .= '<br /><span>'.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_NO_NEW_DATA_FOUND').'</span>';
            } 
                
            $ret_val .= "<br /><br />";
            
            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_FINISH'). "<br />";
            $ret_val .= "<br />";
            $ret_val .= "<br />";
            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_NEED_REFRESH')."<br />";            
            return $ret_val;

		}        
        
        public function marker_delete_all()
        {
                //echo "Here Delete All" ."<br />";

                $db = Factory::getDbo();

                $query = $db->getQuery(true);

                $query->delete($db->quoteName('#__zhgooglemaps_marker_buffer'));

                $db->setQuery($query);

                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());

                    return false;
                }
                
                return true;
        }        

        public function marker_delete_processed()
        {
                //echo "Here Delete Processed" ."<br />";
                $db = Factory::getDbo();

                $query = $db->getQuery(true);

                $query->delete($db->quoteName('#__zhgooglemaps_marker_buffer'))
                        ->where('status=1');

                $db->setQuery($query);

                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());

                    return false;
                }
                
                return true;
 
        }   
                
        public function file_load($icon, $markergroup, $catid, $published, $delimiter, $filename) 
        {
            $ret_val = "";
            $currentUser = Factory::getUser();
            $db = Factory::getDBO();

            if (isset($delimiter) && $delimiter != "")
            {
                $delim = $delimiter;
            }
            else
            {
                $delim = ";";
            }
            
            $vld = array(  
                "title",
                "latitude",
                "longitude",
                "published",
                "addresstext",
                "icontype",
                "iconofsetx",
                "iconofsety",
                "description",
                "descriptionhtml",
                "hrefimage",
                "catid",
                "markergroup",
                "createdbyuser",
                "showuser",
                "showgps",
                "preparecontent",
                "markercontent"
                );
            $cols = array();
            
            //$data = array("title;latitude;longitude;description;icontype", "Hello;10;20;some desc;default#", "Here;20;10");
            $dest = JPATH_COMPONENT . '/' . "uploads" . '/' . $filename;
            $cur_line_num = 0;
            
            $cnt_ins = 0;
            $cnt_err = 0;
            $col_def_exist = false;
            
            if (($csv_handle = fopen($dest, "r")) !== FALSE) 
            {
                while (($line = fgetcsv($csv_handle, 0, $delim)) !== FALSE) 
                {
                    $cur_line_num += 1;
                    
                    $cnt = count($line);                   
                                         
                    if ($cnt > 0)
                    {                    
                        //$ret_val .="line=" .$line ."<br />";

                        if ($cur_line_num == 1)
                        {
                            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_CHECK_COL_NAME')."<br />";

                            $cnt_line = count($line);

                            $ret_val .= "... ".Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_COL_CNT')." ".$cnt_line."<br />";

                            if ($cnt_line > 0)
                            {
                                for ($j = 0; $j < $cnt_line; $j++) 
                                {
                                    //$ret_val .="check column:".$title[$j]."<br />";

                                    $col = strtolower(trim($line[$j]));
                                    if (in_array($col, $vld))
                                    {
                                        //$ret_val .= "... ".$title[$j]." is valid<br />";
                                        $cols[$j] = $col;
                                        $col_def_exist = true;
                                    }
                                    else 
                                    {
                                        $ret_val .= "... <b>".$line[$j]."</b> ".Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_COL_NOT_VALID')."<br />";
                                    }
                                }
                            }

                            //foreach ($cols as $key => $value)
                            //{
                            //   $ret_val .= "{$key} => {$value} "."<br />"; 
                            //}
                        }
                        else 
                        {
                            //$ret_val .= "check column value record<br />";
                            if ($col_def_exist)
                            {
                                if ($cur_line_num == 2)
                                {
                                    $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_PROC_DATA')."<br />";
                                }
                                
                                $cnt_line = count($line);

                                //$ret_val .= "count of columns: ".$cnt_val."<br />";

                                $marker2ins = new \stdClass();
                                $marker2insA = array();

                                if ($cnt_line > 0)
                                {
                                    for ($j = 0; $j < $cnt_line; $j++) 
                                    {
                                        if (array_key_exists($j, $cols))
                                        {
                                            //$ret_val .= "... ".$j." is need to add<br />";
                                            $marker2insA[$cols[$j]] = $line[$j];
                                        }
                                        else 
                                        {
                                            //$ret_val .= "... ".$j." is not need to add<br />";
                                        }
                                    }
                                }
                                $marker2ins = (object)$marker2insA;

                                //apply defaults
                                if (!isset($marker2ins->icontype) || $marker2ins->icontype == "")
                                {
                                    if (isset($icon) && $icon != "")
                                    {
                                        $marker2ins->icontype = $icon;
                                    }
                                    else
                                    {                                   
                                        $marker2ins->icontype = "gm#dot-red";
                                    }
                                }
                                if (!isset($marker2ins->markergroup) || $marker2ins->markergroup == "")
                                {
                                    if (isset($markergroup) && (int)$markergroup != 0)
                                    {
                                        $marker2ins->markergroup = (int)$markergroup;
                                    }
                                }
                                if (!isset($marker2ins->catid) || $marker2ins->catid == "")
                                {
                                    if (isset($catid) && (int)$catid != 0)
                                    {
                                        $marker2ins->catid = (int)$catid;
                                    }
                                }
                                if (!isset($marker2ins->published) || $marker2ins->published == "")
                                {
                                    if (isset($published) && (int)$published != 0)
                                    {
                                        $marker2ins->published = (int)$published;
                                    }
                                }                                                       


                                if (isset($marker2ins->title) && $marker2ins->title != ""
                                 && isset($marker2ins->latitude) && $marker2ins->latitude != ""
                                 && isset($marker2ins->longitude) && $marker2ins->longitude != ""
                                )
                                {
                                    $marker2ins->id = null;
                                    
                                    $db->insertObject('#__zhgooglemaps_marker_buffer', $marker2ins, 'id');

                                    $new_id = $marker2ins->id;

                                    //$ret_val .= "New placemark added";
                                    $cnt_ins += 1;
                                }
                                else
                                {
                                    $ret_val .= "#".($cur_line_num-1)." - ".Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_VAL_REQ')."<br />";
                                    $cnt_err += 1;
                                }

                            }
                            else 
                            {
                                $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_NO_COL_DEF')."<br />";
                                break;
                            }

                        }


                    }



                }
            }
                            

            $ret_val .= "<br />";
            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_TOTAL_INS')." ".$cnt_ins."<br />";
            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_TOTAL_ERR')." ".$cnt_err."<br />";    
            $ret_val .= "<br />";
            $ret_val .= "<br />";
            $ret_val .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_NEED_REFRESH_BUFFER')."<br />";
            
            return $ret_val;
            
        }
         
    	
     
}
