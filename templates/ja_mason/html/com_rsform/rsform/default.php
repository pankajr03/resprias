<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_heading', 0)) { ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php } ?>
<div class="custom-contact">
              <div class="section-title">
              <div class="sub-heading"></div>
             <h2 class="contact_title">Contact Form</h2>
             <p class="contact_subtitle">Send an Email. All fields with an asterisk (*) are required.</p>
         </div>
   <div class="row">
      <div class="col-md-7 custom_contact_form ">
         <?php echo RSFormProHelper::displayForm($this->formId); ?>
      </div>
      <div class="col-md-5 direct-contact-outer">
         <div class="direct-contact-container">
            <!--<ul class="contact-list">-->
            <!--   <li class="list-item">-->
            <!--      <i class="fas fa-map-marker-alt fa-2x"></i>-->
            <!--      <span class="contact-text place">-->
            <!--         <p style="margin:0px;">7606 N. Union Boulevard Suite 100-A</p>-->
            <!--         <p style="margin:0px;">Colorado Springs, CO 80920 MOUNTAIN STANDARD TIME</p>-->
            <!--      </span>-->
            <!--   </li>-->
            <!--   <li class="list-item"><i class="fa fa-phone fa-2x"></i><span class="contact-text phone"><a href="tel:1-212-555-5555" title="Give me a call">(719) 387-8389</a></span></li>-->
            <!--   <li class="list-item"><i class="fa fa-envelope fa-2x"></i><span class="contact-text gmail"><a href="mailto:#" title="Send me an email">roberto@respiras.com</a></span></li>-->
            <!--</ul>-->
            <!--<hr>-->
            <!--<ul class="social-media-list">-->
            <!--   <li><a href="#" target="_blank" class="contact-icon">-->
            <!--      <i class="fa fa-facebook" aria-hidden="true"></i></a>-->
            <!--   </li>-->
            <!--   <li><a href="#" target="_blank" class="contact-icon">-->
            <!--      <i class="fa fa-youtube-play" aria-hidden="true"></i></a>-->
            <!--   </li>-->
            <!--   <li><a href="#" target="_blank" class="contact-icon">-->
            <!--      <i class="fa fa-twitter" aria-hidden="true"></i></a>-->
            <!--   </li>-->
            <!--   <li><a href="#" target="_blank" class="contact-icon">-->
            <!--      <i class="fa fa-instagram" aria-hidden="true"></i></a>-->
            <!--   </li>-->
            <!--</ul>-->
            <!--<hr>-->
            <div class="exp-card">
                <h3><span>Since 2009,</span> we provide quality of care to our valued clients.</h3>
                <div class="exp-text">
                   <span>15</span>
                   <h2>Years of experience</h2>
                </div>  
                <p>We are creating change, transformation, and breakthroughs</p>
            </div>
         </div>
      </div>
   </div>
</div>
