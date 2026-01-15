<?php
/**
 * ------------------------------------------------------------------------
 * JA Mason Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

if(!class_exists('ContentHelperRoute')){
	if(version_compare(JVERSION, '4', 'ge')){
		abstract class ContentHelperRoute extends \Joomla\Component\content\Site\Helper\RouteHelper{};
	}else{
		JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
	}
}
jimport('joomla.html.html.bootstrap');

$cparams = ComponentHelper::getParams('com_media');
$tparams = $this->item->params;
if(version_compare(JVERSION, '4', 'ge')) $this->contact = $this->item;

if(version_compare(JVERSION, '4', 'ge')){
	$presentation_style = 'plain';
} else {
	$presentation_style = $tparams->get('presentation_style');
} 
?>
<div class="contact<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Person">
	<!-- Page heading -->
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($tparams->get('page_heading')); ?>
		</h1>
	<?php endif; ?>
	<!-- End page heading -->
	
	<!-- Show Contact name -->
	<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
		<?php if($presentation_style != 'plain') :?>
		<div class="page-header">
			<h2>
				<?php if ($this->item->published == 0) : ?>
					<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
				<span class="contact-name" itemprop="name"><?php echo $this->contact->name; ?></span>
			</h2>
		</div>
		<?php endif;  ?>
	<?php endif;  ?>
	<!-- End Show Contact name -->
	
	<?php if ($this->params->get('show_contact_category') == 'show_no_link') : ?>
		<h3>
			<span class="contact-category"><?php echo $this->contact->category_title; ?></span>
		</h3>
	<?php endif; ?>
	<?php if ($this->params->get('show_contact_category') == 'show_with_link') : ?>
		<?php $contactLink = ContactHelperRoute::getCategoryRoute($this->contact->catid); ?>
		<h3>
			<span class="contact-category"><a href="<?php echo $contactLink; ?>">
				<?php echo $this->escape($this->contact->category_title); ?></a>
			</span>
		</h3>
	<?php endif; ?>

	<?php echo $this->item->event->afterDisplayTitle; ?>
	

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php if ($tparams->get('show_user_custom_fields') && $this->contactUser) : ?>
		<?php echo $this->loadTemplate('user_custom_fields'); ?>
	<?php endif; ?>
	
	<!-- JA Override Contact From for case "plain" -->
	<?php if($presentation_style == 'plain') :?>
	<div class="row <?php echo $presentation_style ?>-style">
		<?php if($this->contact->misc && $tparams->get('show_misc')) :?>
		<div class="col-xs-12">
			<!-- Contact other information -->
			<div class="other-information">
				<div class="contact-body">
					<?php echo $this->contact->misc; ?>
				</div>
				<!-- End other information -->
			</div>
		</div>
		<?php endif ;?>

		<!-- Show form contact -->
		<div class="col-xs-12 <?php echo($this->params->get('show_info')) ? 'col-sm-7 col-lg-8' : '' ;?> contact-left">
			<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
			<div class="contact-box">
				<div class="contact-form">
					<div class="section-title">
						<div class="sub-heading">
							<?php echo text::_('TPL_GET_IN_TOUCH') ;?>
						</div>
						<?php echo '<h2>'. Text::_('COM_CONTACT_EMAIL_FORM').'</h2>';  ?>
					</div>
					<div class="contact-body">
						<?php echo $this->loadTemplate('form');  ?>
					</div>
				</div>
			</div>
			<?php endif ;?>
		</div>
		<!-- End Show form contact -->

		<?php if ($this->params->get('show_info', 1)) :?>
		<!-- Show Other information -->
		<div class="col-xs-12 col-sm-5 col-lg-4 contact-information">
			<div class="inner">				
					<!-- Contact images -->
					<?php if ($this->contact->image && $this->params->get('show_image')) : ?>
						<div class="contact-image">
							<?php echo HTMLHelper::_('image', $this->contact->image, Text::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle')); ?>
						</div>
					<?php endif; ?>
					<!-- End Contact images -->
				
					<!-- Show Contact name -->
					<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
						<div class="section-title">
							<div class="sub-heading"><?php echo Text::_('TPL_CONTACT') ;?></div>
							<h2>
								<?php if ($this->item->published == 0) : ?>
									<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
								<?php endif; ?>
								<?php echo $this->contact->name; ?>
							</h2>
						</div>
					<?php endif;  ?>
					<!-- End Show Contact name -->

					<!-- Contact -->
					<?php if ($this->params->get('show_info', 1) || $tparams->get('show_links')) : ?>
					<div class="contact-info">
						<?php if ($this->params->get('show_info', 1)) :?>
							<?php if ($this->contact->con_position && $this->params->get('show_position')) : ?>
								<dl class="contact-position dl-horizontal">
									<dd>
										<?php echo $this->contact->con_position; ?>
									</dd>
								</dl>
							<?php endif; ?>
							
							<?php echo $this->loadTemplate('address'); ?>
					
							<?php if ($this->params->get('allow_vcard')) :	?>
								<?php echo Text::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
									<a href="<?php echo Route::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
									<?php echo Text::_('COM_CONTACT_VCARD');?></a>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ($tparams->get('show_links')) : ?>
							<?php echo $this->loadTemplate('links'); ?>
						<?php endif; ?>
					</div>
					<!-- End contact-->
					<?php endif; ?>

					<?php if ($this->params->get('show_profile') && $this->contact->user_id && PluginHelper::isEnabled('user', 'profile')) : ?>
						<?php echo '<h4>'. Text::_('COM_CONTACT_PROFILE').'</h4>'; ?>
						<?php echo $this->loadTemplate('profile'); ?>
					<?php endif; ?>
			</div>
		</div>
		<!-- End Show -->
		<?php endif; ?>
		
	</div>
	<?php endif;?>
	<!-- End Override -->

	<?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
		<form action="#" method="get" name="selectForm" id="selectForm">
			<?php echo Text::_('COM_CONTACT_SELECT_CONTACT'); ?>
			<?php echo HTMLHelper::_('select.genericlist', $this->contacts, 'id', 'class="input" onchange="document.location.href = this.value"', 'link', 'name', $this->contact->link);?>
		</form>
	<?php endif; ?>

	<?php if ($this->params->get('show_tags', 1) && !empty($this->item->tags)) : ?>
		<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>
	
	<?php if ($presentation_style == 'sliders') : ?>
		<div class="panel-group" id="slide-contact">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#basic-details">
						<?php echo Text::_('COM_CONTACT_DETAILS');?>
						</a>
					</h4>
				</div>
				<div id="basic-details" class="panel-collapse collapse in">
					<div class="panel-body">
	<?php endif; ?>
	<?php if ($presentation_style == 'tabs'):?>
		<ul class="nav nav-tabs" id="myTab">
				<li class="active"><a data-toggle="tab" href="#basic-details"><?php echo Text::_('COM_CONTACT_DETAILS'); ?></a></li>
				<?php if ($this->params->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?><li><a data-toggle="tab" href="#display-form"><?php echo Text::_('COM_CONTACT_EMAIL_FORM'); ?></a></li><?php endif; ?>
				<?php if ($this->params->get('show_links')) : ?><li><a data-toggle="tab" href="#display-links"><?php echo Text::_('COM_CONTACT_LINKS'); ?></a></li><?php endif; ?>
				<?php if ($this->params->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?><li><a data-toggle="tab" href="#display-articles"><?php echo Text::_('JGLOBAL_ARTICLES'); ?></a></li><?php endif; ?>
				<?php if ($this->params->get('show_profile') && $this->contact->user_id && PluginHelper::isEnabled('user', 'profile')) : ?><li><a data-toggle="tab" href="#display-profile"><?php echo Text::_('COM_CONTACT_PROFILE'); ?></a></li><?php endif; ?>
				<?php if ($this->contact->misc && $this->params->get('show_misc')) : ?><li><a data-toggle="tab" href="#display-misc"><?php echo Text::_('COM_CONTACT_OTHER_INFORMATION'); ?></a></li><?php endif; ?>
		</ul>
		<div class="tab-content" id="myTabContent">
			<div id="basic-details" class="tab-pane active">
	<?php endif; ?>
	
	<?php if ($this->contact->image && $this->params->get('show_image') && ($presentation_style != 'plain')) : ?>
		<div class="thumbnail pull-right">
			<?php echo HTMLHelper::_('image', $this->contact->image, Text::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle', 'itemprop' => 'image')); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->contact->con_position && $this->params->get('show_position') && ($presentation_style != 'plain')) : ?>
		<dl class="contact-position dl-horizontal">
			<dd itemprop="jobTitle">
				<?php echo $this->contact->con_position; ?>
			</dd>
		</dl>
	<?php endif; ?>
	
	<?php if($presentation_style != 'plain') :?>
	
	<?php echo $this->loadTemplate('address'); ?>
	
	<?php endif;?>
	
	<?php if ($this->params->get('allow_vcard') && ($presentation_style != 'plain')) :	?>
		<?php echo Text::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
			<a href="<?php echo Route::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
			<?php echo Text::_('COM_CONTACT_VCARD');?></a>
	<?php endif; ?>

	<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
	<?php endif; ?>
	
	<?php if ($presentation_style == 'tabs') : ?>
			</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>

		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-form">
						<?php echo Text::_('COM_CONTACT_EMAIL_FORM');?>
						</a>
					</h4>
				</div>
				<div id="display-form" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-form" class="tab-pane">
		<?php endif; ?>
		
		<?php if($presentation_style != 'plain') :?>
		<?php  echo $this->loadTemplate('form');  ?>
		<?php endif;?>
		
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ($this->params->get('show_links') && ($presentation_style != 'plain')) : ?>
		<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>
		
	<?php if ($this->params->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-articles">
						<?php echo Text::_('JGLOBAL_ARTICLES');?>
						</a>
					</h4>
				</div>
				<div id="display-articles" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-articles" class="tab-pane">
		<?php endif; ?>
		<?php if  ($presentation_style=='plain'):?>
			<?php echo '<h3>'. Text::_('JGLOBAL_ARTICLES').'</h3>'; ?>
		<?php endif; ?>
			<?php echo $this->loadTemplate('articles'); ?>
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->params->get('show_profile') && $this->contact->user_id && PluginHelper::isEnabled('user', 'profile') && ($presentation_style!='plain')) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-profile">
						<?php echo Text::_('COM_CONTACT_PROFILE');?>
						</a>
					</h4>
				</div>
				<div id="display-profile" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-profile" class="tab-pane">
		<?php endif; ?>
		<?php echo $this->loadTemplate('profile'); ?>
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->contact->misc && $this->params->get('show_misc')) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-misc">
						<?php echo Text::_('COM_CONTACT_OTHER_INFORMATION');?>
						</a>
					</h4>
				</div>
				<div id="display-misc" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-misc" class="tab-pane">
		<?php endif; ?>
		
		<?php if($presentation_style != 'plain') :?>
				<div class="contact-miscinfo">
					<dl class="dl-horizontal">
						<dt>
							<span class="<?php echo $this->params->get('marker_class'); ?>">
								<?php echo $this->params->get('marker_misc'); ?>
							</span>
						</dt>
						<dd>
							<span class="contact-misc">
								<?php echo $this->contact->misc; ?>
							</span>
						</dd>
					</dl>
				</div>
		<?php endif;?>		
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($presentation_style=='sliders'):?>
			<script type="text/javascript">
				(function($){
					$('#slide-contact').collapse({ parent: false, toggle: true, active: 'basic-details'});
				})(jQuery);
			</script>
		</div>
	<?php endif; ?>
	<?php if ($presentation_style == 'tabs') : ?>
		</div>
	<?php endif; ?>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
