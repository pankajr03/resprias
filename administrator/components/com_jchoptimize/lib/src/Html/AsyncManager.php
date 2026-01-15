<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Html;

use _JchOptimizeVendor\V91\Laminas\EventManager\Event;
use JchOptimize\Core\FileInfo;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;

use function defined;

defined('_JCH_EXEC') or die('Restricted access');

class AsyncManager
{
    private array $assets = [];

    public function __construct(
        private Registry $params,
        private CacheManager $cacheManager,
        private PathsInterface $paths
    ) {
    }

    public function loadCssAsync(): void
    {
        if ($this->params->get('pro_reduce_unused_css', '0')) {
            $this->loadOnUIFunction();
            $this->assets[] = new FileInfo(
                HtmlElementBuilder::script()->src($this->paths->mediaUrl() . '/dynamic-js/reduce_unused_css.js')
            );
        }
    }

    private function loadOnUIFunction(): void
    {
        $this->assets[0] = new FileInfo(
            HtmlElementBuilder::script()->src($this->paths->mediaUrl() . '/dynamic-js/user-interact-callback.js?')
        );
    }

    public function loadAsyncManagerAssets(Event $event): void
    {
        $this->loadCssAsync();
        $this->loadJsDynamic();

        if (!empty($this->assets)) {
            $cacheObj = $this->cacheManager->getCombinedFiles($this->assets, $id, 'js');
            /** @var HtmlManager $htmlManager */
            $htmlManager = $event->getTarget();
            $url = $htmlManager->buildUrl($id, 'js', $cacheObj);
            $script = $htmlManager->getNewJsLink((string)$url, false, true);

            $htmlManager->appendChildToHTML((string)$script, 'body');
        }
    }

    public function loadJsDynamic(): void
    {
        if (
            $this->params->get('pro_reduce_unused_js_enable', '0')
            && $this->params->get('bottom_js', '0')
        ) {
            $this->loadOnUIFunction();
            $this->assets[] = new FileInfo(
                HtmlElementBuilder::script()->src($this->paths->mediaUrl() . '/dynamic-js/reduce_unused_js.js')
            );
        }
    }
}
