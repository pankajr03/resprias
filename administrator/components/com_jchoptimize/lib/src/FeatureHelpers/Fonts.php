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

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Laminas\EventManager\Event;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use JchOptimize\Core\CacheObject;
use JchOptimize\Core\Exception\PregErrorException;
use JchOptimize\Core\FileInfo;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\Html\HtmlManager;
use JchOptimize\Core\Preloads\Preconnector;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Uri\Uri;

use function defined;

defined('_JCH_EXEC') or die('Restricted access');

class Fonts extends AbstractFeatureHelper
{
    /**
     * @var array Array of files containing @font-face content to be preloaded
     */
    public array $fonts = [];
    /**
     * @var bool If the Optimize Fonts feature is enabled
     */
    private bool $enable;

    public function __construct(
        Container $container,
        Registry $params,
        private HtmlManager $htmlManager,
        private Preconnector $preconnector
    ) {
        parent::__construct($container, $params);

        $this->enable = (bool)$params->get('pro_optimizeFonts_enable', '0');
    }

    public function generateCombinedFilesForFonts(CacheObject $cssCache): void
    {
        //If google font files were collected we can just add them straight to the fonts array property
        if (!empty($gFonts = $cssCache->getGFonts())) {
            $this->pushFilesToFontsArray($gFonts);
        }

        //If any @font-face content was captured then we combine them into a file and add the file
        // to the fonts array property
        if (!empty($fontFace = $cssCache->getFontFace())) {
            //Prepare info in a format the combiner expects
            $fontInfosArray = $this->prepareFontsInfo($fontFace);
            /** @var CacheManager $oCacheManager */
            $oCacheManager = $this->getContainer()->get(CacheManager::class);
            $cacheObj = $oCacheManager->getCombinedFiles($fontInfosArray, $fontsId, 'css');
            $this->pushFileToFontsArray(
                $this->htmlManager->buildUrl($fontsId, 'css', $cacheObj),
                ''
            );
        }

        //Any external domains to preconnect is added to the preconnect array property
        if ($this->preconnector->isEnabled() && !empty($prefetches = $cssCache->getPrefetches())) {
            $this->preconnector->pushDomainsToPrefetches($prefetches);
        }
    }

    /**
     * Iterates over an associated array of font file information and adds then to the font array property
     *
     * @param array $fontsInfo
     *
     * @return void
     */
    public function pushFilesToFontsArray(array $fontsInfo): void
    {
        if ($this->enable) {
            foreach ($fontsInfo as $fonts) {
                $url = $fonts['url'];
                $media = $fonts['media'];

                $this->pushFileToFontsArray($url, $media);
            }
        }
    }

    public function pushFileToFontsArray(UriInterface $uri, string $media): void
    {
        if ($this->enable) {
            if (str_contains($uri->getHost(), 'fonts.googleapis.com')) {
                $uri = Uri::withQueryValue($uri, 'display', 'swap');

                $this->preconnector->setGoogleFontsOptimized();
            }

            if ($media == 'none' || $media == '') {
                $media = 'all';
            }

            $this->fonts[] = [
                'url' => $uri,
                'media' => $media
            ];
        }
    }

    private function prepareFontsInfo($fontFaceArray): array
    {
        $fontInfosArray = [];

        foreach ($fontFaceArray as $fontFace) {
            $style = HtmlElementBuilder::style()
                ->media($fontFace['media'])
                ->addChild($fontFace['content']);
            $fileInfo = new FileInfo($style);
            $fileInfo->setAlreadyProcessed(true);
            $fontInfosArray[] = $fileInfo;
        }

        return $fontInfosArray;
    }

    public function appendOptimizedFontsToHead(Event $event): void
    {
        foreach ($this->fonts as $font) {
            $link = HtmlElementBuilder::link()->href($font['url']);

            if (!empty($font['media'])) {
                $link->media($font['media']);
            }

            try {
                /** @var HtmlManager $htmlManager */
                $htmlManager = $event->getTarget();
                $htmlManager->preloadStyleSheet($link);
                $htmlManager->prependChildToHead((string)$link);
            } catch (PregErrorException $e) {
            }
        }
    }
}
