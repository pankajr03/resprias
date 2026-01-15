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

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Cdn\Cdn;
use JchOptimize\Core\Css\Components\CssRule;
use JchOptimize\Core\Css\Components\CssUrl;
use JchOptimize\Core\Css\Components\NestingAtRule;
use JchOptimize\Core\Css\CssComponents;
use JchOptimize\Core\Css\ModifyCssUrlsProcessor;
use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Elements\Img;
use JchOptimize\Core\Html\HtmlElementInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Traits\TestableFileExistsTrait;
use JchOptimize\Core\Uri\UriConverter;
use JchOptimize\Core\Uri\Utils;

use function array_map;
use function implode;
use function krsort;
use function pathinfo;
use function str_contains;

class ResponsiveImages extends AbstractFeatureHelper implements ModifyCssUrlsProcessor
{
    use TestableFileExistsTrait;

    public array $responsiveImages = [];

    public static array $breakpoints = [
        '576',
        '768',
    ];

    public static string $mobileOnly = '(max-width: 576px)';

    public static string $tabletOnly = '(max-width: 768px) and (min-width: 578px)';

    public static string $desktopOnly = '(min-width: 769px)';

    public static string $mobileAndTabletOnly = '(max-width: 768px)';

    public static string $tabletAndDesktopOnly = '(min-width: 577px)';

    public static string $sizes = "(max-width: 576px) and (min-resolution: 3dppx) 25vw, (max-width: 576px) and (min-resolution: 2dppx) 40vw, 100vw";

    public function __construct(
        Container $container,
        Registry $params,
        private Cdn $cdn,
        private PathsInterface $pathsUtils
    ) {
        parent::__construct($container, $params);
    }

    public function convert(HtmlElementInterface $element): void
    {
        if (!$element instanceof Img) {
            return;
        }

        $width = Helper::getElementWidth($element);

        if (
            $element->getSrc() instanceof UriInterface
            && $width > 1
            && (!$element->hasAttribute('srcset')
                || $this->params->get('replace_srcset_responsive', '0'))
        ) {
            $this->makeResponsiveImages($element);
        }
    }

    private function makeResponsiveImages(Img $element): void
    {
        $uri = $element->getSrc();

        if ($uri instanceof UriInterface) {
            $srcsetString = $this->createSrcsetString(
                $this->getResponsiveImages($uri),
                $uri,
                (string)Helper::getElementWidth($element)
            );
            if ($srcsetString) {
                $element->srcset($srcsetString);
                $element->sizes(self::$sizes);
                $element->class('jchoptimize-responsive-images');
            }
        }
    }

    public function processCssUrls(CssUrl $cssUrl): ?CssUrl
    {
        return $cssUrl;
    }

    public function createSrcsetString(array $rsImages, UriInterface $uri, string $width = ''): string
    {
        $srcset = array_map(
            fn(string $breakpoint, string $image): string => $image . ' ' . $breakpoint . 'w',
            array_keys($rsImages),
            array_values($rsImages),
        );

        if (!empty($srcset)) {
            //If responsive images found we add the original as fallback
            $src = (string)$uri;
            $width = $width ?: $this->getImageSizeFromUri($uri);
            $srcset[] = $src . ' ' . $width . 'w';
        }

        return implode(', ', $srcset);
    }

    private function getImageSizeFromUri(UriInterface $uri): string
    {
        $imagePath = UriConverter::uriToFilePath($uri, $this->pathsUtils, $this->cdn);
        $size = @getimagesize($imagePath);

        return (string)($size[0] ?? '1');
    }

    public function getResponsiveImages(UriInterface $image): array
    {
        return $this->responsiveImages[(string)$image] ??= $this->getResponsiveImagesArray(
            $this->getResponseImageName($image)
        );
    }

    private function getResponsiveImagesArray(string $rsImageName): array
    {
        $rsImages = [];

        foreach (self::$breakpoints as $breakpoint) {
            $rsImagePath = '/' . $breakpoint . '/' . $rsImageName;

            $potentialPaths = [];

            if ($this->params->get('load_avif_webp_images', '0')) {
                if ($this->params->get('load_avif_images', '1')) {
                    $potentialPaths[] = self::convertRsImagePathToAvif($rsImagePath);
                }
                if ($this->params->get('pro_load_webp_images', '1')) {
                    $potentialPaths[] = self::convertRsImagePathToWebp($rsImagePath);
                }
            }

            $potentialPaths[] = $rsImagePath;

            foreach ($potentialPaths as $potentialPath) {
                $filePath = $this->pathsUtils->responsiveImagePath() . $potentialPath;

                if ($this->fileExists($filePath)) {
                    $rsImages[$breakpoint] = (string)$this->pathToUrlResponsive($filePath);

                    break;
                }
            }
        }

        return $rsImages;
    }

    public static function convertRsImagePathToWebp(string $rsImagePath): string
    {
        return self::getRsImagePathWithoutExtension($rsImagePath) . '.webp';
    }

    public static function convertRsImagePathToAvif(string $rsImagePath): string
    {
        return self::getRsImagePathWithoutExtension($rsImagePath) . '.avif';
    }

    private static function getRsImagePathWithoutExtension(string $rsImagePath): string
    {
        $fileParts = pathinfo($rsImagePath);

        return $fileParts['dirname'] . '/' . $fileParts['filename'];
    }

    private function pathToUrlResponsive(string $path): UriInterface
    {
        return UriConverter::filePathToUri($path, $this->pathsUtils, $this->cdn);
    }

    public function getResponseImageName(UriInterface $image): string
    {
        $imagePath = $this->getContainer()
            ->get(AdminHelper::class)
            ->contractFileName(UriConverter::uriToFilePath($image, $this->pathsUtils, $this->cdn));

        return rawurldecode($imagePath);
    }

    public function makeCssRuleResponsive(CssRule $cssRule): void
    {
        $cssRule->modifyCssUrls($this);
    }

    public function postProcessModifiedCssComponent(CssComponents $cssComponent): void
    {
        if (
            $cssComponent instanceof CssRule
            && $this->params->get('pro_load_responsive_images', '0')
            && $cssComponent->getSelectorList() != ''
            && !str_contains($cssComponent->getSelectorList(), '.jchoptimize-responsive-images__loaded')
            && !empty($this->responsiveImages)
            && str_contains($cssComponent->getDeclarationList(), 'url(')
        ) {
            $this->internalMakeCssRuleResponsive($cssComponent);
        }
    }

    private function internalMakeCssRuleResponsive(CssRule $cssRule): void
    {
        $cssRuleRx = Parser::cssRuleToken();
        $atRuleRx = Parser::cssNestingAtRulesToken();
        $commentRx = Parser::cssBlockToken();
        $rsCss = '';
        $cssRuleClone = clone $cssRule;
        $unNestedDeclaration = preg_replace(
            "#{$commentRx}|{$cssRuleRx}|{$atRuleRx}#ix",
            '',
            $cssRuleClone->getDeclarationList(),
        );

        $isResponsive = false;
        foreach ($this->responsiveImages as $url => $responsiveImages) {
            if (str_contains($unNestedDeclaration, $url)) {
                $urlRx = preg_quote($url, '#');
                $isImportant = (bool)preg_match("#background[^;]*?{$urlRx}[^;]*?!important#i", $unNestedDeclaration);
                krsort($responsiveImages);
                foreach ($responsiveImages as $breakpoint => $rsImage) {
                    $tmpCssRule = clone $cssRuleClone;

                    $cssUrl = new CssUrl(Utils::uriFor($rsImage));

                    $important = $isImportant ? ' !important' : '';
                    $tmpCssRule->setDeclarationList("background-image: {$cssUrl->render()}{$important}");
                    $tmpCssRule->setSelectorList('&');

                    $rsCss .= (new NestingAtRule())
                        ->setIdentifier('media')
                        ->setRule("(max-width: {$breakpoint}px)")
                        ->setCssRuleList($tmpCssRule->render())
                        ->render();
                }
                $isResponsive = true;
            }
        }

        if ($isResponsive) {
            $cssRule->appendDeclarationList($rsCss);
            $cssRule->appendSelectorList('.jchoptimize-responsive-images__loaded');
        }
    }

    public function mergeResponsiveImageCacheItems(array $imageCacheItems): array
    {
        $result = [];
        foreach ($imageCacheItems as $imageItem) {
            if (isset($imageItem['src']) && isset($this->responsiveImages[(string)$imageItem['src']])) {
                $imageItem['media'] = ResponsiveImages::$tabletAndDesktopOnly;
                $lcp768Image = [];
                $lcp576Image = [];

                $responsiveImages = $this->responsiveImages[(string)$imageItem['src']];
                krsort($responsiveImages);
                foreach ($responsiveImages as $breakpoint => $url) {
                    switch ($breakpoint) {
                        case '768':
                            $imageItem['media'] = ResponsiveImages::$desktopOnly;
                            $lcp768Image['src'] = Utils::uriFor($url);
                            $lcp768Image['media'] = ResponsiveImages::$mobileAndTabletOnly;
                            $lcp768Image['as'] = 'image';
                            break;
                        case '576':
                            $lcp768Image['media'] = ResponsiveImages::$tabletOnly;
                            $lcp576Image['src'] = Utils::uriFor($url);
                            $lcp576Image['media'] = ResponsiveImages::$mobileOnly;
                            $lcp576Image['as'] = 'image';
                            break;
                        default:
                            break;
                    }
                }

                if (isset($lcp576Image['src'])) {
                    $result[] = $lcp576Image;
                }
                if (isset($lcp768Image['src'])) {
                    $result[] = $lcp768Image;
                }
                $result[] = $imageItem;
            }
        }

        return $result;
    }
}
