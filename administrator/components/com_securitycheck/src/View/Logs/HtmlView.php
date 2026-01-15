<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Main Admin View
 */
class HtmlView extends BaseHtmlView {
    
    /**
     * Display the main view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null) {
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_CPANEL_VIEW_FIREWALL_LOGS_TEXT'), 'securitycheck' );
		ToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL',false);
		ToolBarHelper::custom('mark_read','checkbox','checkbox','COM_SECURITYCHECK_LOG_READ_CHANGE');
		ToolBarHelper::custom('mark_unread','checkbox-unchecked','checkbox-unchecked','COM_SECURITYCHECK_LOG_NO_READ_CHANGE');
		ToolBarHelper::custom('delete','delete','delete','COM_SECURITYCHECK_DELETE');
		ToolBarHelper::custom('delete_all','delete','delete','COM_SECURITYCHECKPRO_DELETE_ALL',false);

		// Obtenemos los datos del modelo
		$model               = $this->getModel();		
		$this->pagination    = $model->getPagination();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		
		$search = $this->state->get('filter.search');
		$description = $this->state->get('filter.description');
		$type= $this->state->get('filter.type');
		$leido = $this->state->get('filter.leido');
				
		if ( ($search == '') && ($description == '') && ($type == '') && ($leido == '') ) { //No hay establecido ningún filtro de búsqueda
			$log_details = $this->get('Data');		
		} else {			
			$log_details = $this->get('FilterData');		
		}
		
		// Ponemos los datos y la paginación en el template
		$this->log_details = $log_details;		       
		
        parent::display($tpl);
    }


}