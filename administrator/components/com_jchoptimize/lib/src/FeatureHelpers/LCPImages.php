<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Laminas\EventManager\Event;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use JchOptimize\Core\CacheObject;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Elements\Img;
use JchOptimize\Core\Html\Elements\Video;
use JchOptimize\Core\Html\HtmlElementInterface;
use JchOptimize\Core\Html\HtmlManager;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Preloads\Http2Preload;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Uri\Utils;

use function array_merge;
use function in_array;

class LCPImages extends AbstractFeatureHelper
{
    private bool $autoLcpLoaded = false;

    private bool $configuredLcpLoaded = false;

    private bool $configuredDesktopLcpLoaded = false;

    private bool $configuredMobileLcpLoaded = false;

    private bool $enabled;

    public function __construct(
        Container $container,
        Registry $params,
        private Http2Preload $http2Preload,
        private ResponsiveImages $responsiveImages
    ) {
        parent::__construct($container, $params);

        $this->enabled = (bool) $this->params->get('pro_lcp_images_enable', '0');
    }

    public function process(HtmlElementInterface $element): bool
    {
        if ($element->hasChildren()) {
            foreach ($element->getChildren() as $child) {
                if ($child instanceof Img) {
                    if ($this->process($child)) {
                        return true;
                    }
                }
            }
        }

        if ($element instanceof Img || $element instanceof Video) {
            return $this->processImgVideo($element);
        }

        return false;
    }

    protected function processImgVideo(Img|Video $element): bool
    {
        if ($element instanceof Img) {
            return $this->processImage($element);
        } else {
            return $this->processVideo($element);
        }
    }

    public function preloadConfiguredCssLcpImages(array $attributes): void
    {
        if (!$this->configuredLcpLoaded) {
            $this->preloadLcpImages($attributes);
            if (empty($attributes['media'])) {
                $this->configuredLcpLoaded = true;
            } elseif ($attributes['media'] == '(min-width: 769px)') {
                $this->configuredDesktopLcpLoaded = true;
            } else {
                $this->configuredMobileLcpLoaded = true;
            }
        }
    }

    public function preloadAutoCssLcpImages(array $attributes): void
    {
        if (!$this->autoLcpLoaded) {
            $this->preloadLcpImages($attributes);
            $this->autoLcpLoaded = true;
        }
    }

    protected function preloadLcpImages(array $attributes): void
    {
        if (isset($attributes['src'])) {
            $src = $attributes['src'];
            unset($attributes['src']);

            if (!empty($attributes['imagesrcset']) && empty($attributes['imagesizes'])) {
                $attributes['imagesizes'] = ResponsiveImages::$sizes;
            }

            $as = $attributes['as'] ?? 'image';
            $attributes['fetchpriority'] = 'high';
            $this->http2Preload->preload($src, $as, $attributes);
        }
    }

    protected function preloadMobileLcpImages(array $attributes): void
    {
        $attributes = array_merge($attributes, ['media' => ResponsiveImages::$mobileAndTabletOnly]);

        $this->preloadLcpImages($attributes);
    }

    protected function preloadDesktopLcpImages(array $attributes): void
    {
        $attributes = array_merge($attributes, ['media' => ResponsiveImages::$desktopOnly]);

        $this->preloadLcpImages($attributes);
    }

    protected function extractUrisFromElement(HtmlElementInterface $element): array
    {
        $uris = [];

        if ($element->hasAttribute('srcset')) {
            $srcset = $element->attributeValue('srcset');
            $uris = Helper::extractUrlsFromSrcset($srcset);
        }

        if (($src = $element->attributeValue('src')) instanceof UriInterface) {
            $uris = array_merge($uris, [$src]);
        }

        return $uris;
    }

    protected function processImage(Img $element): bool
    {
        $uris = $this->extractUrisFromElement($element);

        $lcpImages = Helper::getArray($this->params->get('pro_lcp_images'));
        $lcpMobileImages = Helper::getArray($this->params->get('lcp_images_mobile'));
        $lcpDesktopImages = Helper::getArray($this->params->get('lcp_images_desktop'));

        $found = false;

        foreach ($uris as $uri) {
            if (!$this->configuredLcpLoaded && Helper::findMatches($lcpImages, $uri)) {
                $this->assignHighFetchPriority($element);
                $this->configuredLcpLoaded = true;
                $found = true;
                break;
            }

            if (
                !$this->configuredLcpLoaded
                && !$this->configuredDesktopLcpLoaded
                && Helper::findMatches($lcpDesktopImages, $uri)
            ) {
                $this->assignHighFetchPriority($element, 'desktop');
                $this->configuredDesktopLcpLoaded = true;
                $found = true;

                break;
            }

            if (
                !$this->configuredLcpLoaded
                && !$this->configuredMobileLcpLoaded
                && Helper::findMatches($lcpMobileImages, $uri)
            ) {
                $this->assignHighFetchPriority($element, 'mobile');
                $this->configuredMobileLcpLoaded = true;
                $found = true;

                break;
            }
        }

        if (!$found) {
            $identifiers = [];
            if (($id = $element->getId()) !== false) {
                $identifiers[] = $id;
            }

            if (($classes = $element->getClass()) !== false) {
                $identifiers = array_merge($identifiers, $classes);
            }

            $lcpIdentifiers = Helper::getArray($this->params->get('pro_lcp_identifiers'));
            foreach ($identifiers as $identifier) {
                if (!$this->configuredLcpLoaded && in_array($identifier, $lcpIdentifiers)) {
                    $this->assignHighFetchPriority($element);
                    $this->configuredLcpLoaded = true;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $found = $this->autoLoadLcpImage($element);
        }

        return $found;
    }

    protected function autoLoadLcpImage(Img|Video $element): bool
    {
        if (!$this->autoLcpLoaded && ($element instanceof Video || Helper::getElementWidth($element) > 400)) {
            if ($element instanceof Img && ($src = $element->getSrc()) instanceof UriInterface) {
                $attributes = [];
                $attributes['src'] = $src;
                $attributes['class'] = 'jchoptimize-auto-lcp';

                if (($srcset = $element->getSrcset()) !== false) {
                    $attributes['imagesrcset'] = $srcset;

                    if ($element->hasAttribute('sizes')) {
                        $attributes['imagesizes'] = $element->attributeValue('sizes');
                    }
                }

                $this->preloadLcpImages($attributes);
                $this->autoLcpLoaded = true;
                return true;
            }

            if ($element instanceof Video && ($poster = $element->getPoster()) instanceof UriInterface) {
                $this->preloadLcpImages(['src' => $poster, 'class' => 'jchoptimize-auto-lcp']);
                $this->autoLcpLoaded = true;
                return true;
            }

            if ($element instanceof Video && ($src = $element->getSrc()) instanceof UriInterface) {
                $this->preloadLcpImages(['src' => $src, 'as' => 'video', 'class' => 'jchoptimize-auto-lcp']);
                $this->autoLcpLoaded = true;

                return true;
            }
        }

        return false;
    }

    protected function processVideo(Video $element): bool
    {
        $poster = $element->getPoster();
        $found = false;

        if ($poster instanceof UriInterface) {
            if ($this->preloadLcpImagePerViewPort($poster)) {
                $found = true;
            }
        } elseif (($src = $element->getSrc()) instanceof UriInterface) {
            if ($this->preloadLcpImagePerViewPort($src, ['as' => 'video'])) {
                $found = true;
            }
        }

        if (!$found) {
            $this->autoLoadLcpImage($element);
        }

        return $found;
    }

    protected function assignHighFetchPriority(Img $element, string $viewport = 'global'): void
    {
        $attributes = ['src' => $element->getSrc()];

        if (($srcset = $element->getSrcset()) !== false && trim($srcset)  !== '') {
            $attributes['imagesrcset'] = $srcset;
        }

        if ($viewport == 'mobile') {
            $this->preloadMobileLcpImages($attributes);
        } elseif ($viewport == 'desktop') {
            $this->preloadDesktopLcpImages($attributes);
        } elseif ($element->hasAttribute('srcset') || $element->getParent() == 'picture') {
            $element->fetchpriority('high');
        } else {
            $this->preloadLcpImages($attributes);
        }

        if ($element->hasAttribute('loading')) {
            $element->loading('eager');
        }
    }

    public function preloadLcpImagePerViewPort(UriInterface $imageUri, array $attributes = []): bool
    {
        $lcpImages = Helper::getArray($this->params->get('pro_lcp_images', []));

        if (!empty($attributes)) {
            $attributes = array_merge(['src' => $imageUri], $attributes);
        } else {
            $attributes = ['src' => $imageUri];
        }

        if (!$this->configuredLcpLoaded && Helper::findMatches($lcpImages, (string)$imageUri)) {
            $this->preloadLcpImages($attributes);
            $this->configuredLcpLoaded = true;

            return true;
        }

        $lcpMobileImages = Helper::getArray($this->params->get('lcp_images_mobile'));

        if (
            !$this->configuredLcpLoaded
            && !$this->configuredMobileLcpLoaded
            && Helper::findMatches($lcpMobileImages, (string)$imageUri)
        ) {
            $this->preloadMobileLcpImages($attributes);
            $this->configuredMobileLcpLoaded = true;

            return true;
        }

        $lcpDesktopImages = Helper::getArray($this->params->get('lcp_images_desktop'));

        if (
            !$this->configuredLcpLoaded
            && !$this->configuredDesktopLcpLoaded
            && Helper::findMatches($lcpDesktopImages, (string)$imageUri)
        ) {
            $this->preloadDesktopLcpImages($attributes);
            $this->configuredDesktopLcpLoaded = true;

            return true;
        }

        return false;
    }

    public function removeAutoLcp(Event $event): void
    {
        if (
            $this->enabled
            && ($this->configuredLcpLoaded
                || $this->configuredMobileLcpLoaded
                || $this->configuredDesktopLcpLoaded)
        ) {
            /** @var HtmlManager $htmlManager */
            $htmlManager = $event->getTarget();
            $htmlManager->removeAutoLcp();
        }
    }

    public function prepareBackgroundLcpImages(UriInterface $imageUri, CacheObject $cacheObject): void
    {
        if (
            !str_contains(
                $imageUri->getPath(),
                /** @see PathsInterface::responsiveImagePath() */
                $this->getContainer()->get(PathsInterface::class)->responsiveImagePath(true)
            )
        ) {
            $lcpImages = Helper::getArray($this->params->get('pro_lcp_images', []));
            if (Helper::findMatches($lcpImages, (string)$imageUri)) {
                $lcpImage = [];
                $lcpImage['src'] = $imageUri;

                $lcpImages = [$lcpImage];
                if ($this->params->get('pro_load_responsive_images', '0')) {
                    $lcpImages = $this->responsiveImages->mergeResponsiveImageCacheItems($lcpImages);
                }

                foreach ($lcpImages as $lcpImage) {
                    $cacheObject->addLcpImages($lcpImage);
                }

                return;
            }

            if ($this->params->get('pro_load_responsive_images', '0')) {
                $lcpDesktopImages = Helper::getArray($this->params->get('lcp_images_desktop'));
                if (Helper::findMatches($lcpDesktopImages, (string)$imageUri)) {
                    $lcpDesktopImage = [];
                    $lcpDesktopImage['src'] = $imageUri;
                    $lcpDesktopImage['media'] = ResponsiveImages::$desktopOnly;
                    $cacheObject->addLcpImages($lcpDesktopImage);

                    return;
                }

                $lcpMobileImages = Helper::getArray($this->params->get('lcp_images_mobile'));
                $potentialLcpMobileImages = [$imageUri];
                if (isset($this->responsiveImages->responsiveImages[(string)$imageUri])) {
                    $potentialLcpMobileImages = array_merge(
                        $potentialLcpMobileImages,
                        $this->responsiveImages->responsiveImages[(string)$imageUri]
                    );
                }

                foreach ($potentialLcpMobileImages as $potentialLcpMobileImage) {
                    if (Helper::findMatches($lcpMobileImages, $potentialLcpMobileImage)) {
                        $lcpMobileImage = [];
                        $lcpMobileImage['src'] = Utils::uriFor($potentialLcpMobileImage);
                        $lcpMobileImage['media'] = ResponsiveImages::$mobileOnly;
                        $cacheObject->addLcpImages($lcpMobileImage);
                        break;
                    }
                }
            }
        }
    }
}
