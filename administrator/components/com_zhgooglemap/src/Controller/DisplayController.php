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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;


class DisplayController extends BaseController {
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'dashboard';
    
    public function display($cachable = false, $urlparams = array()) {
        
        $view   = $this->input->get('view', 'dashboard');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->get('id', '', "INT");
        
        return parent::display();
    }
    
}
