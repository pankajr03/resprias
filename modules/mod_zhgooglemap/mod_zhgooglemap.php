<?php
/*------------------------------------------------------------------------
# mod_zhgooglemap - Zh GoogleMap Module
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapDataHelper;

require ModuleHelper::getLayoutPath('mod_zhgooglemap', $params->get('layout', 'default'));