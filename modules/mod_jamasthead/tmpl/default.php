<?php
/**
 * ------------------------------------------------------------------------
 * JA Masthead Module 
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');
$mh_background = new stdClass();
$mh_background->url = '';
$mh_background->type = '';
$mh_bg_check = '';

if(isset($masthead['params']['background']) && !empty($masthead['params']['background'])){
    $mh_background->url = $masthead['params']['background'];
    $frags = explode('#', $mh_background->url);
    $mh_background->url = array_shift($frags);
    $mh_bg_check = 'has-bg';

    if(preg_match('/^.*\.(mp4|ogg|webm)$/', $mh_background->url)){
        $mh_background->type = 'video';
    }else{
        $mh_background->type = 'image';
    }
}
?>
<div class="ja-masthead <?php echo $mh_bg_check = 'has-bg' . ' ' . $params->get('moduleclass_sfx','')?>" <?php if ($mh_background && $mh_background->type == 'image') : ?>style="background-image: url('<?php echo $mh_background->url; ?>')"<?php endif; ?>>
	<?php
        // Video backround
        if($mh_background && $mh_background->type == 'video') :
        preg_match_all('/^.*\.(mp4|ogg|webm)$/', $mh_background->url, $mathes);
    ?>
        <div class="ja-masthead-bg">
            <video id="ja_masthead_bg_video" loop="true" autoplay="true" playsinline muted>
                <source src="<?php echo $mh_background->url ?>" />
            </video>
        </div>
    <?php endif; ?>
    <div class="ja-masthead-detail">
		<h3 class="ja-masthead-title"><?php echo $masthead['title']; ?></h3>
        <?php if ($masthead['description'] != '') : ?>
		  <div class="ja-masthead-description"><?php echo $masthead['description']; ?></div>
        <?php endif; ?>
	</div>
</div>