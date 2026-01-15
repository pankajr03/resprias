<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Laminas\EventManager\Event;
use JchOptimize\Core\FileInfo;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\Elements\Iframe;
use JchOptimize\Core\Html\Elements\LiteYoutube;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\Html\HtmlManager;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;

use function parse_str;
use function str_replace;

class YouTubeFacade extends AbstractFeatureHelper
{
    private bool $facadeLoaded = false;

    public function __construct(
        Container $container,
        Registry $params,
        private CacheManager $cacheManager,
        private PathsInterface $paths
    ) {
        parent::__construct($container, $params);
    }

    public function loadYouTubeFacadeAssets(Event $event): void
    {
        if (
            $this->params->get('lazyload_enable', '0')
            && $this->params->get('use_youtube_facade', '0')
            && $this->facadeLoaded
        ) {
            /** @var HtmlManager $htmlManager */
            $htmlManager = $event->getTarget();
            $cssCacheObj = $this->cacheManager->getCombinedFiles(
                [new FileInfo(HtmlElementBuilder::link()->href(
                    $this->paths->mediaUrl() . '/lite-youtube-embed/lite-yt-embed.css?' . JCH_VERSION
                ))],
                $cssCacheId,
                'css'
            );

            $htmlManager->appendChildToHead(
                HtmlElementBuilder::style()
                    ->class('jchoptimize-lite-youtube-embed')
                    ->addChild(str_replace(
                        ['@charset "UTF-8";', 'max-width:720px'],
                        ['', 'max-width:100%'],
                        $cssCacheObj->getContents()
                    ))
                    ->render()
            );

            $jsCacheObj = $this->cacheManager->getCombinedFiles(
                [new FileInfo(HtmlElementBuilder::script()->src(
                    $this->paths->mediaUrl() . '/lite-youtube-embed/lite-yt-embed.js?' . JCH_VERSION
                ))],
                $jsCacheId,
                'js'
            );

            $htmlManager->appendChildToHTML(
                $htmlManager->getNewJsLink(
                    $htmlManager->buildUrl($jsCacheId, 'js', $jsCacheObj),
                    false,
                    true
                ),
                'body'
            );
        }
    }

    public function convert(Iframe $iframe): Iframe|LiteYoutube
    {
        if (!preg_match('#(?:www\.)?youtu\.?be(?:-nocookie)?(?:\.com)?#i', $iframe->getSrc()->getHost())) {
            return $iframe;
        }

        $videoId = $this->getVideoId($iframe);
        if ($videoId === '') {
            return $iframe;
        }

        $facade = HtmlElementBuilder::liteYoutube();
        $facade->videoid($videoId)
            ->style("background-image: url('https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg');");

        $a = HtmlElementBuilder::a();
        $a->href($iframe->getSrc())->class('lyt-playbtn')->title('Play');

        if (($title = $iframe->getTitle()) !== false) {
            $span = HtmlElementBuilder::span();
            $span->class('lyt-visually-hidden')
                ->addChild($title);
            $a->addChild($span);
        }

        if (($class = $iframe->getClass()) !== false) {
            $facade->class(implode(' ', $class));
        }

        $facade->addChild($a);

        $this->facadeLoaded = true;

        return $facade;
    }

    private function getVideoId(Iframe $iframe): string
    {
        parse_str($iframe->getSrc()->getQuery(), $query);

        if (isset($query['v'])) {
            return $query['v'];
        }

        $path = $iframe->getSrc()->getPath();
        $parts = explode('/', trim($path, '/\\'));

        if ($parts[0] == 'user') {
            return array_pop($parts);
        }

        if (preg_match('#^(?:embed|vi?|watch)$#i', $parts[0])) {
            array_shift($parts);
        }

        if (isset($parts[0])) {
            return $parts[0];
        }

        return '';
    }
}
