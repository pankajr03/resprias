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

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\Filesystem\Folder;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use JchOptimize\Core\Admin\AdminHelper as AdminHelper;
use JchOptimize\Core\Browser;
use JchOptimize\Core\Cdn\Cdn;
use JchOptimize\Core\Css\Components\CssUrl;
use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\Exception\InvalidArgumentException;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Elements\Img;
use JchOptimize\Core\Html\Elements\Input;
use JchOptimize\Core\Html\Elements\Source;
use JchOptimize\Core\Html\HtmlElementInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Traits\TestableFileExistsTrait;
use JchOptimize\Core\Uri\UriConverter;

use function array_map;
use function defined;
use function file_exists;
use function pathinfo;
use function preg_replace_callback;
use function rawurldecode;
use function str_replace;

defined('_JCH_EXEC') or die('Restricted access');

class Webp extends AbstractFeatureHelper
{
    use TestableFileExistsTrait;

    public function __construct(
        Container $container,
        Registry $params,
        private Cdn $cdn,
        private PathsInterface $pathsUtils,
        private AdminHelper $adminHelper
    ) {
        parent::__construct($container, $params);
    }

    public function convert(HtmlElementInterface $element): void
    {
        if ($element instanceof Img || $element instanceof Input) {
            if ($element->getSrc() instanceof UriInterface) {
                $this->processSrcAttribute($element);
            }

            if ($element instanceof Img && $element->hasAttribute('srcset')) {
                $this->processSrcSetAttribute($element);
            }
        } elseif (
            $element instanceof Source
            && $element->hasAttribute('srcset')
            && !$element->hasAttribute('type')
        ) {
            $this->processSrcSetAttribute($element);
        } elseif ($element->getStyle() !== false) {
            $this->processStyleAttribute($element);
        }
    }

    private function processSrcAttribute(Img|Input $element): void
    {
        $srcWebpValue = $this->getWebpImages($element->getSrc());
        $element->src($srcWebpValue);
    }

    private function processStyleAttribute(HtmlElementInterface $element): void
    {
        $style = $element->getStyle();

        if ($style !== false) {
            $style = preg_replace_callback("#" . Parser::cssUrlToken() . '#i', function ($matches) {
                try {
                    $cssUrl = CssUrl::load($matches[0]);
                } catch (InvalidArgumentException) {
                    return $matches[0];
                }

                $webp = $this->getWebpImages($cssUrl->getUri());

                return $cssUrl->setUri($webp)->render();
            }, $style);

            $element->style($style);
        }
    }


    private function processSrcSetAttribute(Img|Source $element): void
    {
        $srcSet = $element->getSrcset();
        $urls = Helper::extractUrlsFromSrcset($srcSet);
        $webpUrls = array_map(function (UriInterface $v) {
            return (string)($this->getWebpImages($v));
        }, $urls);

        if ($urls != $webpUrls) {
            $webpSrcSet = str_replace($urls, $webpUrls, $srcSet);
            $element->srcset($webpSrcSet);
        }
    }

    public function getWebpImages(UriInterface $imageUri): UriInterface
    {
        if ($imageUri->getScheme() == 'data') {
            return $imageUri;
        }

        $imagePath = UriConverter::uriToFilePath($imageUri, $this->cdn, $this->pathsUtils);

        $aPotentialPaths = [
            $this->getWebpPath($imagePath),
            $this->getWebpPathLegacy($imagePath),
        ];

        foreach ($aPotentialPaths as $potentialWebpPath) {
            if ($this->fileExists($potentialWebpPath)) {
                $webpImageUri = UriConverter::filePathToUri($potentialWebpPath, $this->cdn, $this->pathsUtils);

                return $webpImageUri->withQuery($imageUri->getQuery())
                    ->withFragment($imageUri->getFragment());
            }
        }

        return $imageUri;
    }

    /**
     * @param string $originalImagePath
     * @return string
     */
    public function getWebpPathLegacy(string $originalImagePath): string
    {
        if (!file_exists($this->pathsUtils->nextGenImagesPath())) {
            Folder::create($this->pathsUtils->nextGenImagesPath());
        }

        $fileParts = pathinfo($this->adminHelper->contractFileNameLegacy($originalImagePath));

        return $this->pathsUtils->nextGenImagesPath() . '/' . $fileParts['filename'] . '.webp';
    }

    /**
     * @param string $originalImagePath
     * @return string
     */
    public function getWebpPath(string $originalImagePath): string
    {
        if (!file_exists($this->pathsUtils->nextGenImagesPath())) {
            Folder::create($this->pathsUtils->nextGenImagesPath());
        }

        $fileParts = pathinfo($this->adminHelper->contractFileName($originalImagePath));

        return $this->pathsUtils->nextGenImagesPath() . '/' . rawurldecode($fileParts['filename']) . '.webp';
    }
}
