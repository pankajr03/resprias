<?php 
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die('Restricted access'); 

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
HTMLHelper::stylesheet($media_url);
?>

<form action="<?php echo Route::_('index.php?option=com_securitycheck&view=logs');?>" method="post" name="adminForm" id="adminForm">

<div id="filter-bar" class="btn-toolbar">
	<?php
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?> 
</div>

<div class="clearfix"> </div>

<div>
	<span class="badge" style="background-color: #C68C51; padding: 10px 10px 10px 10px; float:right;"><?php echo Text::_('COM_SECURITYCHECK_LIST_LOGS');?></span>
</div>
	
	<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th class="logs" align="center">
				<?php echo Text::_( "Ip" ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_LOG_TIME' ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_LOG_DESCRIPTION' ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_LOG_URI' ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_TYPE_COMPONENT' ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_LOG_TYPE' ); ?>
			</th>
			<th class="logs" align="center">
				<?php echo Text::_( 'COM_SECURITYCHECK_LOG_READ' ); ?>
			</th>
			<th class="logs" align="center">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
		</tr>
	</thead>
<?php
$k = 0;
foreach ($this->log_details as &$row) {
?>
<tr>
	<td align="center">
			<?php 
				$ip_sanitized = htmlspecialchars($row->ip);
			echo $ip_sanitized; ?>	
	</td>
	<td align="center">
			<?php echo $row->time; ?>	
	</td>		
	<td align="center">
			<?php $title = Text::_( 'COM_SECURITYCHECK_ORIGINAL_STRING' ); ?>
			<?php $original_string_sanitized = htmlspecialchars($row->original_string); ?>			
			<?php if (strlen($original_string_sanitized)<=60){
						$tip = $original_string_sanitized;
					} else {
						$tip = substr($original_string_sanitized,0,50) .' ' .Text::_( 'COM_SECURITYCHECK_TRUNCATED' );
					}
			?>
			<?php $tag_description_sanitized = htmlspecialchars($row->tag_description); ?>
			<?php echo Text::_( 'COM_SECURITYCHECK_' .$tag_description_sanitized ); ?>
			<?php $description_sanitized = htmlspecialchars($row->original_string); ?>
			<?php echo Text::_( ':' . substr($description_sanitized,0,50) ); ?>
			<?php echo HTMLHelper::tooltip($tip,$title,'tooltip.png','','',false); ?>
	</td>	
	<td align="center">
			<?php 
				$uri_sanitized = htmlspecialchars($row->uri);
			echo substr(($uri_sanitized),0,60); ?>	
	</td>
	<td align="center">
			<?php $component_sanitized = htmlspecialchars($row->component);
			echo substr(($component_sanitized),0,20);	?>				
	</td>
	<td align="center">
		<?php 
			$type_sanitized = htmlspecialchars($row->type);
			$type = $type_sanitized;			
			if ( $type == 'XSS' ){
				echo ('<img src="../media/com_securitycheck/images/xss.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'XSS_BASE64' ){
				echo ('<img src="../media/com_securitycheck/images/xss_base64.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'SQL_INJECTION' ){
				echo ('<img src="../media/com_securitycheck/images/sql_injection.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'SQL_INJECTION_BASE64' ){
				echo ('<img src="../media/com_securitycheck/images/sql_injection_base64.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'LFI' ){
				echo ('<img src="../media/com_securitycheck/images/local_file_inclusion.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'LFI_BASE64' ){
				echo ('<img src="../media/com_securitycheck/images/local_file_inclusion_base64.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'IP_PERMITTED' ){
				echo ('<img src="../media/com_securitycheck/images/permitted.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'IP_BLOCKED' ){
				echo ('<img src="../media/com_securitycheck/images/blocked.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'SECOND_LEVEL' ){
				echo ('<img src="../media/com_securitycheck/images/second_level.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'USER_AGENT_MODIFICATION' ){
				echo ('<img src="../media/com_securitycheck/images/http.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'REFERER_MODIFICATION' ){
				echo ('<img src="../media/com_securitycheck/images/http.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'SESSION_PROTECTION' ){
				echo ('<img src="../media/com_securitycheck/images/session_protection.png" title="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_TITLE_' .$row->type ) .'">');
			}else if ( $type == 'SPAM_PROTECTION' ){
				echo ('<img src="../media/com_securitycheck/images/spam_protection.png" title="' . Text::_( 'COM_SECURITYCHECKPRO_TITLE_' .$row->type ) .'" alt="' . Text::_( 'COM_SECURITYCHECKPRO_TITLE_' .$row->type ) .'">');
			}
		?>
	</td>
	<td align="center">
		<?php 
			$marked = $row->marked;			
			if ( $marked == 1 ){
				echo ('<img src="../media/com_securitycheck/images/read.png" title="' . Text::_( 'COM_SECURITYCHECK_LOG_NO_READ_CHANGE' ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_LOG_NO_READ_CHANGE' ) .'">');
			} else {
				echo ('<img src="../media/com_securitycheck/images/no_read.png" title="' . Text::_( 'COM_SECURITYCHECK_LOG_READ_CHANGE' ) .'" alt="' . Text::_( 'COM_SECURITYCHECK_LOG_READ_CHANGE' ) .'">');
			}
		?>
	</td>
	<td align="center">
			<?php echo HTMLHelper::_('grid.id', $k, $row->id); ?>
	</td>
</tr>
<?php
$k = $k+1;
}
?>

</table>

<div>
	<?php echo $this->pagination->getListFooter(); ?></td>
</div>

<div class="clearfix"> </div>

<input type="hidden" name="option" value="com_securitycheck" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="logs" />
</form>