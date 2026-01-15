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

use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use CodeAlfa\Minify\Js;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Elements\Script;
use JchOptimize\Core\Html\JsLayout\JsPlacementItem;
use JchOptimize\Core\Preloads\Http2Preload;

use function defined;

use const JCH_DEBUG;

defined('_JCH_EXEC') or die('Restricted access');

class DynamicJs extends AbstractFeatureHelper
{
    private function preloadCriticalScript(Script $script): void
    {
        $http2Preload = $this->getContainer()->get(Http2Preload::class);
        if ($http2Preload instanceof Http2Preload) {
            if ($script->getType() != 'module' && ($uri = $script->getSrc()) instanceof UriInterface) {
                $http2Preload->preload($uri, 'script');
            }
        }
    }

    public function prepareJsDynamicUrl(JsPlacementItem $placementItem, array $combinedByGroup): ?Script
    {
        if ($placementItem->isProcessed) {
            $script = $combinedByGroup[$placementItem->groupIndex];
        } else {
            $script = $placementItem->item->node;
        }

        if (!$script instanceof Script) {
            return null;
        }

        if ($placementItem->item->node && $this->isCritical($placementItem->item->node)) {
            $script->class('jchoptimize-critical-js');
            if ($this->params->get('loadAsynchronous', '0') && $script->getType() !== 'module') {
                $script->defer();
            }

            if ($this->params->get('preload_criticalJs', '0')) {
                $this->preloadCriticalScript($script);
            }
        } else {
            if ($script->hasAttribute('nomodule')) {
                $script->remove('nomodule');
                $script->type('jchoptimize-text/nomodule');
            } elseif ($script->getType() === 'module') {
                $script->type('jchoptimize-text/module');
            } else {
                $script->type('jchoptimize-text/javascript');
            }

            $script->remove('async');
            $script->remove('defer');
        }

        return $script;
    }

    private function isCriticalJs(Script $script): bool
    {
        $criticalJsUrls = Helper::getArray($this->params->get('pro_criticalJs', []));
        $configHelperUrls = Helper::getArray($this->params->get('criticalJs_configure_helper', []));
        $criticalScripts = Helper::getArray($this->params->get('pro_criticalScripts', []));
        $configHelperScripts = Helper::getArray($this->params->get('criticalScripts_configure_helper'));

        return (
                ($uri = $script->getSrc()) instanceof UriInterface
                && (
                    Helper::findExcludes($criticalJsUrls, (string)$uri)
                    || (
                        JCH_DEBUG && Helper::findExcludes($configHelperUrls, (string)$uri)
                    )
                )
            )
            || (
                $script->hasChildren() && ($content = $script->getChildren()[0]) !== null
                && (
                    Helper::findExcludes($criticalScripts, Js::optimize($content))
                    || (
                        JCH_DEBUG && Helper::findExcludes($configHelperScripts, Js::optimize($content))
                    )
                )
            );
    }

    private function isCriticalModule(Script $module): bool
    {
        $criticalModules = Helper::getArray($this->params->get('pro_criticalModules', []));
        $configHelperModules = Helper::getArray($this->params->get('criticalModules_configure_helper', []));
        $criticalModulesScripts = Helper::getArray($this->params->get('pro_criticalModulesScripts', []));
        $configHelperScripts = Helper::getArray($this->params->get('criticalModulesScripts_configure_helper', []));

        return (
                ($uri = $module->getSrc()) instanceof UriInterface
                && (
                    Helper::findExcludes($criticalModules, (string)$uri)
                    || (
                        JCH_DEBUG && Helper::findExcludes($configHelperModules, (string)$uri)
                    )
                )
            )
            || (
                $module->hasChildren() && ($content = $module->getChildren()[0]) != ''
                && (
                    Helper::findExcludes($criticalModulesScripts, Js::optimize($content))
                    || (
                        JCH_DEBUG && Helper::findExcludes($configHelperScripts, Js::optimize($content))
                    )
                )
            );
    }

    public function isCritical(Script $script): bool
    {
        if ($script->getType() === 'module') {
            return $this->isCriticalModule($script);
        } elseif (!$script->hasAttribute('nomodule')) {
            return $this->isCriticalJs($script);
        }

        return false;
    }
}
