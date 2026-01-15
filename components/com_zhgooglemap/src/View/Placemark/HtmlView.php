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
namespace ZhukDL\Component\ZhGoogleMap\Site\View\Placemark;


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {


    protected $item;
    protected $state;
	
	protected $mapapikey4map;
	protected $loadjquery;
    
    /**
     * display method of ZhGoogle Placemark view
     * @return void
     */
    public function display($tpl = null) 
    {


        $this->item = $this->get('Item');
		$this->state = $this->get('State');
  
        $this->mapapikey4map = $this->get('MapAPIKey');
		$this->mapapiversion = $this->get('MapAPIVersion');
		$this->loadtype = $this->get('LoadType');
		$this->httpsprotocol = $this->get('HttpsProtocol');
		$this->mapapitype = $this->get('MapAPIType');		  
		$this->loadjquery = $this->get('LoadJQuery');		

		$this->enable_map_gpdr = $this->get('MapGPDR');
		$this->map_gpdr_buttonlabel = $this->get('MapGPDR_Button');
		$this->map_gpdr_header = $this->get('MapGPDR_Header');
		$this->map_gpdr_footer = $this->get('MapGPDR_Footer');
        
		$this->map_gpdr_buttonc = $this->get('MapGPDR_Cookie');
		$this->map_gpdr_buttonclabel = $this->get('MapGPDR_Cookie_Button');
		$this->map_gpdr_buttoncexp = $this->get('MapGPDR_Cookie_Days');
        

        // Display the template
        parent::display($tpl);


    }

}
