<?php 
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

Session::checkToken('get') or die('Invalid Token');

// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
HTMLHelper::stylesheet($media_url);

$badge_style = "bg-";	
?>

<?php if ( $this->database_error == "DATABASE_ERROR" ) { ?>
<div class="alert alert-error">
	<?php echo Text::_('COM_SECURITYCHECK_FILEMANAGER_DATABASE_ERROR'); ?>
</div>
<?php } ?>

<?php if ( $this->files_with_incorrect_permissions >3000 ) { ?>
<div class="alert alert-error">
	<?php echo Text::_('COM_SECURITYCHECK_FILEMANAGER_ALERT'); ?>
</div>
<?php } ?>

<?php if ( $this->show_all == 1 ) { ?>
<div class="alert alert-info">
	<?php echo Text::_('COM_SECURITYCHECK_FILEMANAGER_INFO'); ?>
</div>
<?php } ?>

<form action="<?php echo Route::_('index.php?option=com_securitycheck&view=filesstatus&'. Session::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div id="filter-bar" class="btn-toolbar">
	<?php
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>  
	
</div>


<div class="clearfix"> </div>

<div id="editcell">
<div class="accordion-group">
<table class="table table-striped">
<div style="font-weight:bold; font-size:10pt; text-align:center;"><?php echo Text::_( 'COM_SECURITYCHECK_COLOR_CODE' ); ?></div>
<thead>
	<tr>
		<?php $span = "<span class=\"badge " . $badge_style . "success\"> </span>"; ?>
		<td><?php echo $span; ?>
		</td>
		<td>
			<?php echo Text::_( 'COM_SECURITYCHECK_GREEN_COLOR' ); ?>
		</td>
		<?php $span = "<span class=\"badge " . $badge_style . "warning\"> </span>"; ?>
		<td><?php echo $span; ?>
		</td>
		<td>
			<?php echo Text::_( 'COM_SECURITYCHECK_YELLOW_COLOR' ); ?>
		</td>
		<?php $span = "<span class=\"badge " . $badge_style . "danger\"> </span>"; ?>
		<td><?php echo $span; ?>
		</td>
		<td>
			<?php echo Text::_( 'COM_SECURITYCHECK_RED_COLOR' ); ?>
		</td>
	</tr>
</thead>
</table>
</div>
</div>

<div>
	<span class="badge" style="background-color: #CEA0EA; padding: 10px 10px 10px 10px; float:right;"><?php echo Text::_('COM_SECURITYCHECK_FILEMANAGER_ANALYZED_FILES');?></span>
</div>

<table class="table table-bordered table-hover">
<thead>
	<tr>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_NAME' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_EXTENSION' ); ?>				
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_KIND' ); ?>				
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_RUTA' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_TAMANNO' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_PERMISSIONS' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo Text::_( 'COM_SECURITYCHECK_FILEMANAGER_LAST_MODIFIED' ); ?>
		</th>		
	</tr>
</thead>
<?php
if ( !empty($this->items) ) {	
	foreach ($this->items as &$row) {		
?>
	<td align="center">
		<?php echo $row['name']; ?>
	</td>
	<td align="center">
		<?php echo $row['extension']; ?>
	</td>
	<td align="center">
		<?php echo $row['kind']; ?>
	</td>
	<td align="center">
		<?php echo $row['path']; ?>
	</td>
	<td align="center">
		<?php echo $row['size']; ?>
	</td>
	<?php 
		$safe = $row['safe'];
		if ( $safe == '0' ) {
			echo "<td><span class=\"badge " . $badge_style . "danger\">";
		} else if ( $safe == '1' ) {
			echo "<td><span class=\"badge " . $badge_style . "success\">";
		} else if ( $safe == '2' ) {
			echo "<td><span class=\"badge " . $badge_style . "warning\">";
		} ?>
		<?php echo $row['permissions']; ?>
	</td>
	<td align="center">
		<?php echo $row['last_modified']; ?>
	</td>
</tr>
<?php
	}
}
?>
</table>
</div>

<?php
if ( !empty($this->items) ) {	
?>
<div class="margen">
	<?php echo $this->pagination->getListFooter(); ?>
</div>
<?php
}
?>

<input type="hidden" name="option" value="com_securitycheck" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="filesstatus" />
<input type="hidden" name="boxchecked" value="0" />
</form>