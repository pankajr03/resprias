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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Controller;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\FilesystemHelper;
use Joomla\CMS\Filesystem\File;


class MapbufmrksController extends AdminController
{

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
	}
	
    /**
     * Proxy for getModel.
     * @since    1.6
     */
    public function getModel($name = 'Mapbufmrk', $prefix = 'Administrator', $config = array('ignore_request' => true)) 
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

        
    function getFileSizeText($bytes, $precision = 2, $space = false)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB'); 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 

        return round($bytes, $precision) . ($space ? ' ' : '') . $units[$pow];
    }
    
    function getFileSizeBytes($size) 
    {
        $size_fix = str_replace(" ", "", $size);
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $numbers = substr($size_fix, 0, -2);
        $suffix = strtoupper(substr($size_fix,-2));

        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $size_fix);
        }

        $flipped = array_flip($units);
        
        $exponent = $flipped[$suffix];
        if($exponent === null) {
            return null;
        }

        return $numbers * (1024 ** $exponent);
    }

    
    
    public function marker_load_all()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            // Get the model
            $model = $this->getModel();
            
            $app = Factory::getApplication(); 
			$input = $app->input; 
            // get the data from the HTTP POST request
            $data  = $input->get('jform', array(), 'array');

            // Get the current URI to set in redirects. As we're handling a POST, 
            // this URI comes from the <form action="..."> attribute in the layout file above
            $currentUri = (string)Uri::getInstance();
          
            $mapid = $data["mapid"];
          
            $flg_validate_text = "";
            $flg_validate_error = false;
            if (!isset($mapid) || $mapid == "" || (int)$mapid == 0)
            {
                $flg_validate_error = true;
                $flg_validate_text .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_IMPORT_ERROR_INVALID')." ".Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_MAPID_LABEL')."<br />";
            }
                
            if ($flg_validate_error)
            {
                $app->enqueueMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_IMPORT_ERROR_REQUIRED'), 'error');
                $app->enqueueMessage("<br />".$flg_validate_text, 'error');
                return false;
            }
            else
            {      
            
                $return = $model->marker_load($mapid, null);
                
                echo $return;
            }

            return true;
            
            // Redirect to the list screen.
            //$this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));

    }    
        
    public function marker_delete_all()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
                
            // Get the model
            $model = $this->getModel();

            $return = $model->marker_delete_all();
            
            if (!$return)
            {
                if (count($errors = $model->getErrors())) {
					throw new GenericDataException(implode("\n", $errors), 500);
				}
            }
            else
            {
                    $this->setMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_MARKERS_DELETED'));
            }
            
            // Redirect to the list screen.
            $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));

    }        

    public function marker_delete_processed()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            // Get the model
            $model = $this->getModel();

            $return = $model->marker_delete_processed();
            
            if (!$return)
            {
				if (count($errors = $model->getErrors())) {
					throw new GenericDataException(implode("\n", $errors), 500);
				}
            }
            else
            {
                    $this->setMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_PROCESSED_MARKERS_DELETED'));
            }


            // Redirect to the list screen.
            $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));

    }    

    public function marker_log()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            $model = $this->getModel();

            $return = $model->checkLog("csv_file_marker");
            
            if ($return == 0)
            {
                $this->setMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_NO_DATA_FOUND'));
                
                // Redirect to the list screen.
                $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));
            }
            else
            {
                // Redirect to the list screen.
                $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrklogs', false));
            }

            

    }

    public function marker_delete_log()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            // Get the model
            $model = $this->getModel();

            $return = $model->deleteLog("csv_file_marker");
            
            if (!$return)
            {
				if (count($errors = $model->getErrors())) {
					throw new GenericDataException(implode("\n", $errors), 500);
				}
            }
            else
            {
                    $this->setMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_DELETED'));
            }

            $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));
            
    }      
               

    public function file_load()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            $maxSize = FilesystemHelper::fileUploadMaxSize();
            $maxSizeBytes = $this->getFileSizeBytes($maxSize.'B');
            
            $model = $this->getModel();
            
            $app = Factory::getApplication(); 
			$input = $app->input; 
            // get the data from the HTTP POST request
            $data  = $input->get('jform', array(), 'array');
            $files = $input->files->get('jform', array(), 'array');

            // Get the current URI to set in redirects. As we're handling a POST, 
            // this URI comes from the <form action="..."> attribute in the layout file above
            $currentUri = (string)Uri::getInstance();
          
            $icon = $data["icontype"];
            $markergroup = $data["markergroup"];
            $catid = $data["catid"];
            $published = $data["published"];
            $delimiter = $data["delimiter"];
            
            $flg_files = false;
            foreach ($files as $file)
            {
                if ($file['name'] != "")
                {
                    $flg_files = true;
                }
            }
            
            $flg_validate_text = "";
            $flg_validate_error = false;
            
            if (!isset($files) || empty($files) || count($files) == 0 || !$flg_files)
            {
                $flg_validate_error = true;
                $flg_validate_text .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_ERROR_INVALID')." ".Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_FILE_LABEL')."<br />";
            }
            if (!isset($delimiter) || $delimiter == "")
            {
                $flg_validate_error = true;
                $flg_validate_text .= Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_ERROR_INVALID')." ".Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DELIM_LABEL')."<br />";
            }
                
            if ($flg_validate_error)
            {
                $app->enqueueMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_ERROR_REQUIRED'), 'error');
                $app->enqueueMessage("<br />".$flg_validate_text, 'error');
                return false;
            }
            else
            {
                // Clean up filename to get rid of strange characters like spaces etc
                foreach ($files as $file)
                {
                    $filename = File::makeSafe($file['name']);

                    // Set up the source and destination of the file
                    $src = $file['tmp_name'];
                    $dest = JPATH_COMPONENT . '/' . "uploads" . '/' . $filename;
                    
                    $size = (int)$file['size'];
                    //echo "upload:".$src ."<br />";
                    //echo "to:".$dest ."<br />";
                    // First check if the file has the right extension, we need jpg only
                    if ($size < $maxSizeBytes) 
                    {
                        // TODO: Add security checks
 
                        if (File::upload($src, $dest))
                        {
                            $return = $model->file_load($icon, $markergroup, $catid, $published, $delimiter, $filename);
                            
                            File::delete($dest);

                            echo $return;  
                        } 
                        else
                        {
                            $app->enqueueMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_ERROR_NOTUPLOADED'), 'error');
                            return false;
                        }
                    }
                    else
                    {
                        $app->enqueueMessage(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_ERROR_FILE_SIZE').": ".$this->getFileSizeText($size)." (".$maxSize.")", 'error');
                        return false;
                    }

                }
                
                return true;
            }
             
            

            
            //$this->setMessage($return);

            // Redirect to the list screen.
            //$this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks', false));
            
    }      
    
    public function back()
    {
            // Check for request forgeries.
            Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

            // Redirect to the list screen.
            $this->setRedirect(Route::_('index.php?option=com_zhgooglemap&view=utils', false));

    }



}
