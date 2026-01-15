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
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

Session::checkToken('get') or die('Invalid Token');

// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
HTMLHelper::stylesheet($media_url);

$badge_style = "bg-";	

?>

<div class="securitycheck-bootstrap">	
	<?php 
	$div = "<div class=\"badge " . $badge_style . "success\">";
	if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") ) { 
		echo $div;
		?>		
			<h4><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES' ); ?></h4>
			<p><strong><?php echo Text::_( 'COM_SECURITYCHECKPRO_DATABASE_VERSION' ); ?></strong><?php echo($this->database_version); ?></p>
			<p><strong><?php echo Text::_( 'COM_SECURITYCHECKPRO_LAST_CHECK' ); ?></strong><?php echo($this->last_check); ?></p>
		</div>
	<?php } else if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && (is_null($this->database_message)) ) { 
		echo $div;
		?>		
			<h4><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES' ); ?></h4>
			<p><strong><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_LAUNCHED' ); ?></strong></p>						
		</div>
	<?php } else if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ( !($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") && !(is_null($this->database_message) )) ) { 
		$div = "<div class=\"badge " . $badge_style . "danger\">";
		echo $div;
		?>		
			<h4><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_PROBLEM' ); ?></h4>
			<p><strong><?php echo Text::_( 'COM_SECURITYCHECKPRO_DATABASE_MESSAGE' ); ?></strong><?php echo Text::_( $this->database_message ); ?></p>
			<a href="<?php echo 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->plugin_id?>" class="btn"><?php echo Text::_('COM_SECURITYCHECKPRO_CHECK_CONFIG'); ?></a>			
		</div>	
	<?php } else if ( !($this->update_database_plugin_exists) ) { 
		$div = "<div class=\"badge " . $badge_style . "info\">";
		echo $div;
		?>		
			<h4><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_INSTALLED' ); ?></h4>
			<p><strong><?php echo Text::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_RECEIVE' ); ?></strong></p>			
		</div>
	
	<?php } ?>				
</div>


<form action="<?php echo Route::_('index.php?option=com_securitycheck&view=vulnerabilities&'. Session::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm" style="margin-top: 10px;">

<div id="editcell">
	<div class="card-header text-center">
		<?php echo Text::_('COM_SECURITYCHECK_COLOR_CODE'); ?>
    </div>
	<table class="table table-borderless">
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
	
	<div>
		<?php $span = "<span class=\"badge " . $badge_style . "info\" style=\"padding: 10px 10px 10px 10px; float:right;\">" . Text::_( 'COM_SECURITYCHECK_UPDATE_DATE' ) . 'Nov 11 2025' . "</span>"; 
		echo $span; ?>
	</div>
</div>

<table class="table table-bordered table-hover">
<thead>
	<tr>
		<th width="5" class="vulnerabilities">
			<?php echo Text::_( 'COM_SECURITYCHECK_HEADING_ID' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo Text::_( 'COM_SECURITYCHECK_HEADING_PRODUCT' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo Text::_( 'COM_SECURITYCHECK_HEADING_TYPE' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo Text::_( 'COM_SECURITYCHECK_HEADING_INSTALLED_VERSION' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo Text::_( 'COM_SECURITYCHECK_HEADING_VULNERABLE' ); ?>
		</th>
	</tr>
</thead>
<?php
$k = 0;
foreach ($this->items as &$row)
{
?>
<tr class="<?php echo "row$k"; ?>">
	<td align="center">
		<?php echo $row->id; ?>
	</td>
	<td align="center">
		<?php echo $row->Product; ?>
	</td>
	<?php 
		$type = $row->Type;
		if ( $type == 'core' ) {
		 echo "<td><span class=\"badge\" style=\"background-color: #FFADF5; \">";
		} else if ( $type == 'component' ) {
		 echo "<td><span class=\"badge " . $badge_style . "info\">";
		} else if ( $type == 'module' ) {
		 echo "<td><span class=\"badge\">";
		} else {
		 echo "<td><span class=\"badge " . $badge_style . "dark\">";
		}
	?>
	<?php echo Text::_('COM_SECURITYCHECK_TYPE_' . $row->Type); ?>
	<td align="center">
		<?php echo $row->Installedversion; ?>
	</td>
<?php 
$vulnerable = $row->Vulnerable;
if ( $vulnerable == 'Si' )
{
 echo "<td><span class=\"badge " . $badge_style . "danger\">";
} else if ( $vulnerable == 'Indefinido' )
{
 echo "<td><span class=\"badge " . $badge_style . "warning\">";
} else
{
 echo "<td><span class=\"badge " . $badge_style . "success\">";
}
?>
<?php echo Text::_('COM_SECURITYCHECK_VULNERABLE_' . $row->Vulnerable); ?>
</span>
</td>
</tr>
<?php
$k = 1 - $k;
}
?>
</table>

<?php
if ( !empty($this->items) ) {		
?>
<div class="margen">
	<div>
		<?php echo $this->pagination->getListFooter(); ?></td>
	</div>
</div>
<?php
}
?>

<input type="hidden" name="option" value="com_securitycheck" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="vulnerabilities" />
</form>
