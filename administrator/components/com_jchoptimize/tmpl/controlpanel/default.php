<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted Access');

$aToggleIcons = $this->icons->compileToggleFeaturesIcons($this->icons->getToggleSettings());
$aAdvancedToggleIcons = $this->icons->compileToggleFeaturesIcons($this->icons->getAdvancedToggleSettings());

?>
<div class="grid mt-3" style="grid-template-rows: auto;">
    <div class="g-col-12 g-col-lg-8" style="grid-row-end: span 2;">
        <div id="combine-files-block" class="admin-panel-block">
            <h4><?= Text::_('COM_JCHOPTIMIZE_COMBINE_FILES_AUTO_SETTINGS') ?></h4>
            <p class="alert alert-info"><?= Text::_('COM_JCHOPTIMIZE_COMBINE_FILES_DESC') ?></p>
            <div class="icons-container">
                <?= $this->icons->printIconsHTML(
                    $this->icons->compileToggleFeaturesIcons(
                        $this->icons->getCombineFilesEnableSetting()
                    )
                ); ?>
                <div class="icons-container">
                    <?= $this->icons->printIconsHTML(
                        $this->icons->compileAutoSettingsIcons(
                            $this->icons->getAutoSettingsArray()
                        )
                    ); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="g-col-12 g-col-lg-4" style="grid-row-end: span 3;">
        <div id="utility-settings-block" class="admin-panel-block">
            <h4><?= Text::_('COM_JCHOPTIMIZE_UTILITY_SETTINGS'); ?></h4>
            <p class="alert alert-info"><?= Text::_('COM_JCHOPTIMIZE_UTILITY_DESC') ?></p>
            <div>
                <div class="icons-container">
                    <?= $this->icons->printIconsHTML($this->icons->compileUtilityIcons($this->icons->getUtilityArray([
                        'browsercaching',
                        'orderplugins',
                        'keycache',
                        'recache',
                        'bulksettings'
                    ]))); ?>
                    <div class="icons-container">
                        <?= $this->icons->printIconsHTML(
                            $this->icons->compileUtilityIcons(
                                $this->icons->getUtilityArray(['cleancache'])
                            )
                        ); ?>
                        <div>
                            <br>
                            <div>
                                <em><span><?= Text::_('COM_JCHOPTIMIZE_FILES'); ?></span>&nbsp;
                                    <span class="numFiles-container">
                                        <img src="<?= Uri::root(true)
                                        . '/media/com_jchoptimize/core/images/loader.gif'; ?>"/>
                                    </span>
                                </em>
                            </div>
                            <div>
                                <em>
                                    <span><?= Text::_('COM_JCHOPTIMIZE_SIZE') ?></span>&nbsp;
                                    <span class="fileSize-container">
                                        <img src="<?= Uri::root(true)
                                        . '/media/com_jchoptimize/core/images/loader.gif' ?>"/>
                                    </span>
                                </em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
    </div>
    <div class="g-col-12 g-col-lg-8" style="grid-row-end: span 3;">
        <div id="toggle-settings-block" class="admin-panel-block">
            <h4><?= Text::_('COM_JCHOPTIMIZE_STANDARD_SETTINGS'); ?></h4>
            <p class="alert alert-info"><?= Text::_('COM_JCHOPTIMIZE_STANDARD_SETTINGS_DESC'); ?></p>
            <div>
                <div class="icons-container">
                    <?= $this->icons->printIconsHTML($aToggleIcons); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="g-col-12 g-col-lg-4" style="grid-row-end: span 2;">
        <div id="advanced-settings-block" class="admin-panel-block">
            <h4><?= Text::_('COM_JCHOPTIMIZE_ADVANCED_SETTINGS'); ?></h4>
            <p class="alert alert-info"><?= Text::_('COM_JCHOPTIMIZE_ADVANCED_SETTINGS_DESC'); ?></p>
            <div>
                <div class="icons-container">
                    <?= $this->icons->printIconsHTML($aAdvancedToggleIcons); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="g-col-12">
        <div id="copyright-block" class="admin-panel-block">
            <p><strong>JCH Optimize Pro <?= JCH_VERSION; ?></strong>
                Copyright 2022 &copy; <a href="https://www.jch-optimize.net/">JCH Optimize</a>
            </p>
            <?php
            if (!JCH_PRO) : ?>
                <p class="alert alert-success">
                    <a href="https://www.jch-optimize.net/subscribes/subscribe-joomla/jmstarter/new/jmstarter.html?layout=default&coupon=JCHGOPRO20">
                        Upgrade to the PRO version today
                    </a>
                    with 20% off using JCHGOPRO20
                </p>
                <?php
            endif; ?>
        </div>
    </div>
</div>
<div id="bulk-settings-modal-container" class="modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Text::_('COM_JCHOPTIMIZE_BULK_SETTINGS_OPERATIONS'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
            </div>
            <div class="modal-body p-4">
                <?= $this->loadTemplate('bulk_settings'); ?>
            </div>
        </div>
    </div>
</div>