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
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use JchOptimize\Core\Exception\PregErrorException;
use JchOptimize\Core\FileInfo;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\Callbacks\ReduceDom as ReduceDomCallback;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\Html\HtmlManager;
use JchOptimize\Core\Html\HtmlProcessor;
use JchOptimize\Core\Html\Parser;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\ProfilerInterface;
use JchOptimize\Core\Registry;

use function array_map;
use function defined;
use function implode;
use function preg_quote;
use function strlen;
use function substr;

use const JCH_DEBUG;

defined('_JCH_EXEC') or die('Restricted access');

class ReduceDom extends AbstractFeatureHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private bool $enabled;

    private array $defaultSections = ['header', 'nav', 'main', 'article', 'section', 'aside', 'footer'];

    private array $targetedSections = ['div', 'ul'];

    public function __construct(
        Container $container,
        Registry $params,
        private HtmlProcessor $processor,
        private ReduceDomCallback $callback,
        private CacheManager $cacheManager,
        private ProfilerInterface $profiler,
        private PathsInterface $pathsUtils
    ) {
        parent::__construct($container, $params);

        $this->enabled = (bool)$this->params->get('pro_reduce_dom', '0');
    }

    /**
     * @throws PregErrorException
     */
    public function process(): void
    {
        if ($this->enabled) {
            !JCH_DEBUG ?: $this->profiler->start('ReduceDom');

            if (!empty($this->params->get('reduce_dom_above_fold_identifiers', []))) {
                $bodyHtml = $this->processor->getBodyHtml();
                $bodyParser = new Parser();
                $this->setupBodyCriteria($bodyParser);

                try {
                    $processedBodyHtml = $bodyParser->processMatchesWithCallback(
                        $bodyHtml,
                        $this->callback
                    );
                } catch (PregErrorException $e) {
                    $this->logger?->error('ReduceDOM Above Fold failed: ' . $e->getMessage());
                    $processedBodyHtml = $bodyHtml;
                }

                $this->processor->setBodyHtml($processedBodyHtml);
            }

            if (
                !empty($this->configuredHtmlSectionsLessUl())
                || !empty($this->params->get('reduce_dom_identifiers', []))
            ) {
                    $bodyHtml = $this->processor->getBodyHtml();
                    $aboveFoldHtml = $this->processor->getAboveFoldHtml($bodyHtml);
                    $belowFoldHtml = substr($bodyHtml, strlen($aboveFoldHtml));

                try {
                    $belowFoldParser = new Parser();
                    $this->setupBelowFoldCriteria($belowFoldParser);

                    $processedBelowFoldHtml = $belowFoldParser->processMatchesWithCallback(
                        $belowFoldHtml,
                        $this->callback
                    );
                } catch (PregErrorException $e) {
                    $this->logger?->error('ReduceDOM Below Fold failed: ' . $e->getMessage());
                    $processedBelowFoldHtml = $belowFoldHtml;
                }

                $this->processor->setBodyHtml($aboveFoldHtml . $processedBelowFoldHtml);
            }

            !JCH_DEBUG ?: $this->profiler->stop('ReduceDom', true);
        }
    }

    private function setupBelowFoldCriteria(Parser $parser): void
    {
        $parser->addExcludes(['script','noscript','style']);
        $excludeIdentifiers = $this->getIdentifiers('reduce_dom_exclude_identifiers');
        $activeHtmlSections = $this->configuredHtmlSectionsLessUl();
        $diffHtmlSections = array_merge(
            array_diff($this->defaultSections, $activeHtmlSections),
            $this->targetedSections
        );

        if (!empty($this->params->get('reduce_dom_identifiers', []))) {
            $includeIdentifiers = $this->getIdentifiers();
            foreach ($diffHtmlSections as $section) {
                $divElement = new ElementObject();
                $divElement->setNamesArray([$section]);
                $divElement->isNested = true;
                $divElement->addPosAttrCriteriaRegex("(?:id|class)~={$includeIdentifiers}");
                if (!empty($this->params->get('reduce_dom_exclude_identifiers', []))) {
                    $divElement->addNegAttrCriteriaRegex("(?:id|class)~={$excludeIdentifiers}");
                }
                $parser->addElementObject($divElement);
            }
        }

        foreach ($activeHtmlSections as $section) {
            $html5ElementObj = new ElementObject();
            $html5ElementObj->setNamesArray([$section]);
            $html5ElementObj->isNested = true;
            if (!empty($this->params->get('reduce_dom_exclude_identifiers', []))) {
                $html5ElementObj->addNegAttrCriteriaRegex("(?:id|class)~={$excludeIdentifiers}");
            }
            $parser->addElementObject($html5ElementObj);
            unset($html5ElementObj);
        }
    }

    private function getIdentifiers(string $params = 'reduce_dom_identifiers'): string
    {
        return '(?:' . implode(
            '|',
            array_map(
                fn($a) => preg_quote($a),
                Helper::getArray($this->params->get($params, []))
            )
        ) . ')';
    }

    private function setupBodyCriteria(Parser $parser): void
    {
        $parser->addExcludes(['script', 'noscript', 'style']);
        $includeIdentifiers = $this->getIdentifiers('reduce_dom_above_fold_identifiers');
        $excludeIdentifiers = $this->getIdentifiers('reduce_dom_exclude_identifiers');
        $htmlSections = array_merge(
            $this->configuredHtmlSectionsLessUl(),
            $this->targetedSections
        );

        foreach ($htmlSections as $section) {
            $element = new ElementObject();
            $element->setNamesArray([$section]);
            $element->isNested = true;
            $element->addPosAttrCriteriaRegex("(?:id|class)~={$includeIdentifiers}");
            if (!empty($this->params->get('reduce_dom_exclude_identifiers'))) {
                $element->addNegAttrCriteriaRegex("(?:id|class)~={$excludeIdentifiers}");
            }
            $parser->addElementObject($element);
        }
    }

    public function loadReduceDomResources(Event $event): void
    {
        if ($this->enabled) {
            $htmlManager = $event->getTarget();

            if ($htmlManager instanceof HtmlManager) {
                $reduceDomScript = $htmlManager->getNewJsLink(
                    $this->pathsUtils->mediaUrl() . '/dynamic-js/reduce_dom.js'
                )->async();
                $cacheObj = $this->cacheManager->getCombinedFiles([new FileInfo($reduceDomScript)], $cacheId, 'js');

                if ($cacheId !== null) {
                    $htmlManager->appendChildToHTML(
                        (string)$reduceDomScript->src($htmlManager->buildUrl($cacheId, 'js', $cacheObj)),
                        'body'
                    );
                    $style = HtmlElementBuilder::style();
                    $style->addChild('.jchoptimize-reduce-dom{min-height:200px;}');
                    try {
                        $htmlManager->appendChildToHead((string)$style);
                    } catch (PregErrorException $e) {
                        $this->logger?->debug($e->getMessage());
                    }
                }
            }
        }
    }

    private function configuredHtmlSectionsLessUl(): array
    {
        return array_diff(
            $this->params->get('pro_html_sections', $this->defaultSections),
            ['ul']
        );
    }
}
