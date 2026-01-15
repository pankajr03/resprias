<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

Session::checkToken( 'get' ) or die( 'Invalid Token' );

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		HTMLHelper::_('select.option',  '0', Text::_( 'COM_SECURITYCHECK_NO' ) ),
		HTMLHelper::_('select.option',  '1', Text::_( 'COM_SECURITYCHECK_YES' ) )
	);
	return HTMLHelper::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}


// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
HTMLHelper::stylesheet($media_url);

$site_url = Uri::base();
?>

<?php
	if ($this->server == 'apache'){
?>
<div class="alert alert-warning">
	<?php echo Text::_('COM_SECURITYCHECK_USER_AGENT_INTRO'); ?>
</div>
<div class="alert alert-danger">
	<?php echo Text::_('COM_SECURITYCHECK_USER_AGENT_WARN'); ?>	
</div>
<div class="alert alert-info">
<?php if($this->ExistsHtaccess) { 
		echo Text::_('COM_SECURITYCHECK_USER_AGENT_HTACCESS');
	  } else { 
	  	echo Text::_('COM_SECURITYCHECK_USER_AGENT_NO_HTACCESS');
} ?>
</div>
<?php
	} else if ($this->server == 'nginx'){
?>
<div class="alert alert-danger">
	<?php echo Text::_('COM_SECURITYCHECK_NGINX_SERVER'); ?>	
</div>
<?php
	}
?>

<script type="text/javascript" language="javascript">

var Password = {
 
  _pattern : /[a-zA-Z0-9]/, 
  
  _getRandomByte : function()
  {
    // http://caniuse.com/#feat=getrandomvalues
    if(window.crypto && window.crypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.crypto.getRandomValues(result);
      return result[0];
    }
    else if(window.msCrypto && window.msCrypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.msCrypto.getRandomValues(result);
      return result[0];
    }
    else
    {
      return Math.floor(Math.random() * 256);
    }
  },
  
  generate : function(length)
  {
    return Array.apply(null, {'length': length})
      .map(function()
      {
        var result;
        while(true) 
        {
          result = String.fromCharCode(this._getRandomByte());
          if(this._pattern.test(result))
          {
            return result;
          }
        }        
      }, this)
      .join('');  
  }    
    
};
</script>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="com_securitycheck" />
	<input type="hidden" name="view" value="protection" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="task" id="task" value="save" />
	<input type="hidden" name="controller" value="protection" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	
		
	<fieldset>
		<legend><?php echo Text::_('COM_SECURITYCHECK_BACKEND_PROTECTION_TEXT') ?></legend>
		
		<div class="alert alert-danger">
			<?php echo Text::_('COM_SECURITYCHECK_BACKEND_PROTECTION_EXPLAIN'); ?>	
		</div>
		
		<div class="control-group">
				<div class="controls controls-row">
				<?php
				// Obtenemos la longitud de la clave que tenemos que generar
				$params = ComponentHelper::getParams('com_securitycheck');
				$size = $params->get('secret_key_length',20);															
				?>
					<div class="input-group mb-3">
					  <span class="input-group-text" id="basic-addon1"><?php echo Text::_('COM_SECURITYCHECK_HIDE_BACKEND_URL_TEXT'); ?></span>
					  <input type="text" class="form-control" placeholder="Secret key" aria-label="Secret key" aria-describedby="basic-addon1" name="hide_backend_url" id="hide_backend_url" value="<?php echo $this->protection_config['hide_backend_url']?>" placeholder="<?php echo $this->protection_config['hide_backend_url'] ?>">
					  <button class="btn btn-outline-secondary" type="button" onclick='document.getElementById("hide_backend_url").value = Password.generate(<?php echo $size; ?>)'><?php echo Text::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?></button>
					</div>			
				<?php							
				if ( $this->config_applied['hide_backend_url'] ) {?>
					<span class="help-inline">
						<div class="applied">
							<?php echo Text::_('COM_SECURITYCHECK_APPLIED') ?>
						</div>
					</span>
				<?php } ?>
			</div>
			<div>
				<figure>
				  <blockquote class="blockquote">				
				  </blockquote>
				  <figcaption class="blockquote-footer">
					<?php echo Text::_('COM_SECURITYCHECK_HIDE_BACKEND_URL_EXPLAIN') ?>
				  </figcaption>
				</figure>
			</div>			
		</div>
	</fieldset>
</form>
