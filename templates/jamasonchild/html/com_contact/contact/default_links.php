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
use Joomla\CMS\Language\Text;

if ($this->params->get('presentation_style')=='sliders'):?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#slide-contact" href="#display-links">
			<?php echo JText::_('COM_CONTACT_LINKS');?>
			</a>
		</h4>
	</div>
	<div id="display-links" class="panel-collapse collapse">
		<div class="panel-body">
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs') : ?>
<div id="display-links" class="tab-pane">
<?php endif; ?>

			<div class="contact-links">
				<ul class="nav nav-stacked">
					<?php
					foreach (range('a', 'e') as $char) :// letters 'a' to 'e'
						$link = $this->contact->params->get('link'.$char);
						$label = $this->contact->params->get('link'.$char.'_name');

						if (!$link) :
							continue;
						endif;

						// Add 'http://' if not present
						$link = (0 === strpos($link, 'http')) ? $link : 'http://'.$link;

						// If no label is present, take the link
						$label = ($label) ? $label : $link;
						?>
						<li class="<?php echo JApplication::stringURLSafe(strtolower($label)) ?>">
							<a href="<?php echo $link; ?>">
								<?php if($label): ?>
									<span class="fa fa-<?php echo JApplication::stringURLSafe(strtolower($label)) ?>"></span>
								<?php else: ?>
									<?php echo $link ?>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

<?php if ($this->params->get('presentation_style')=='sliders'):?>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs') : ?>
</div>
<?php endif; ?>
