<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Plugin\System\JchOptimize\Extension;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Model\CacheMaintainer;
use JchOptimize\Core\Optimize;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\DI\Container;
use Joomla\Input\Input;
use Psr\Log\LoggerInterface;

use function define;
use function defined;
use function error_reporting;
use function in_array;
use function ob_end_flush;

use const E_ALL;
use const E_NOTICE;

class JchOptimize extends CMSPlugin
{
    private Container $container;

    public bool $enabled = false;

    public $params;

    private CMSApplication|CMSApplicationInterface|null $app;

    private Input $input;

    public function onAfterInitialise(): void
    {
        $this->app = $this->getApplication();

        if ($this->app === null) {
            return;
        }

        $this->input = $this->app->getInput();
        $user = $this->app->getIdentity();

        if (!$this->app->isClient('site')) {
            return;
        }

        // Disable if the component is not installed or disabled
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            return;
        }

        /** @var JchOptimizeComponent $component */
        $component = $this->app->bootComponent('com_jchoptimize');
        $this->container = $component->getContainer();

        if ($this->app->get('offline', '0') && $user->guest) {
            return;
        }

        //Disable if jchnooptimize set
        if ($this->app->getInput()->get('jchnooptimize', '', 'int') == 1) {
            return;
        }

        //Get and set component's parameters
        $this->params = $this->container->get('params');

        if (!defined('JCH_DEBUG')) {
            define('JCH_DEBUG', ($this->params->get('debug', 0) && JDEBUG));
        }

        if ($this->params->get('disable_logged_in_users', '1') && !$user->guest) {
            return;
        }

        $this->enabled = true;
    }

    public function onAfterRoute(): void
    {
        //If not enabled, return
        if (!$this->enabled) {
            return;
        }

        //Disable if in iframe
        if ($this->input->server->getString('HTTP_SEC_FETCH_DEST') == 'iframe') {
            $this->enabled = false;

            return;
        }

        //Disable if menu or page excluded
        $menuexcluded = $this->params->get('menuexcluded', []);
        $menuexcludedurl = $this->params->get('menuexcludedurl', []);

        if (
            in_array($this->input->get('Itemid', '', 'int'), $menuexcluded) ||
            Helper::findExcludes($menuexcludedurl, SystemUri::toString())
        ) {
            $this->enabled = false;

            return;
        }

        //Disable if page being edited
        if ($this->input->get('layout') == 'edit') {
            $this->enabled = false;
        }
    }

    public function onAfterRender(): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!$this->app instanceof CMSApplication) {
            return;
        }

        if ($this->params->get('debug', 0)) {
            error_reporting(E_ALL & ~E_NOTICE);
        }

        $html = $this->app->getBody();

        //Html invalid
        if (!Helper::validateHtml($html)) {
            return;
        }

        if (
            $this->input->get('jchbackend') == '1'
            || $this->input->get('jchnooptimize' == '1')
        ) {
            return;
        }

        if ($this->input->get('jchbackend') == '2') {
            echo $html;
            while (@ob_end_flush()) {
                ;
            }
            exit;
        }

        try {
            /** @var Optimize $optimize */
            $optimize = $this->container->get(Optimize::class);
            $optimize->setHtml($html);

            $sOptimizedHtml = $optimize->process();
        } catch (Exception $e) {
            $logger = $this->container->get(LoggerInterface::class);
            $logger->error($e->getMessage());

            $sOptimizedHtml = $html;
        }

        $this->app->setBody($sOptimizedHtml);
    }

    public function onJchCacheExpired(): bool
    {
        if (!$this->enabled) {
            return false;
        }
        /** @var CacheMaintainer $cacheModel
         */
        $cacheModel = $this->container->get(CacheMaintainer::class);

        return $cacheModel->cleanCache();
    }
}
