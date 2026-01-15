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

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\UriResolver;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use _JchOptimizeVendor\V91\Symfony\Component\Process\PhpExecutableFinder;
use _JchOptimizeVendor\V91\Symfony\Component\Process\Process;
use CodeAlfa\Component\JchOptimize\Administrator\CrawlObservers\ReCacheAfterRedirect;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use Exception;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Spatie\Crawler;
use JchOptimize\Core\Spatie\CrawlQueues\CacheCrawlQueue;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\MVC\Model\BaseModel;
use LogicException;

use function defined;
use function is_executable;
use function sprintf;

use const JCH_DEBUG;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access'); //Joomla guard
// phpcs:enable PSR1.Files.SideEffects

class ReCacheModel extends BaseModel
{
    private ?Registry $params = null;

    private ?PathsInterface $pathsUtils = null;

    private ?CacheCrawlQueue $crawlQueue = null;

    private ?LoggerInterface $logger = null;

    /**
     * @throws Exception
     */
    public function reCache(): void
    {
        $params = $this->getState('params');
        $baseUrl = (string)UriResolver::resolve(
            new Uri(SystemUri::homePageAbsolute($this->getPathsUtils())),
            new Uri((string)$params->get('recache_base_url'))
        );
        $crawlLimit = (int)$params->get('recache_crawl_limit', 500);
        $concurrency = (int)$params->get('recache_concurrency', 20);
        $maxDepth = (int)$params->get('recache_max_depth', 5);

        $logger = JDEBUG && JCH_DEBUG ? $this->getLogger() : null;

        Crawler::create($baseUrl)
            ->setCrawlQueue($this->getCrawlQueue())
            ->setCrawlObserver(new ReCacheAfterRedirect($logger))
            ->setTotalCrawlLimit($crawlLimit)
            ->setConcurrency($concurrency)
            ->setMaximumDepth($maxDepth)
            ->startCrawling($baseUrl);
    }

    /**
     * @throws Exception
     */
    public function triggerCliRecache(): void
    {
        if (!PluginHelper::isEnabled('console', 'jchoptimize')) {
            throw new Exception('Recache plugin not enabled');
        }

        $params = $this->getState('params');
        $php = (new PhpExecutableFinder())->find(false);

        if ($php === false || !is_executable($php)) {
            foreach (
                [
                    '/usr/local/bin/php',
                    '/usr/bin/php',
                    '/usr/local/lsws/lsphp82/bin/lsphp',
                    '/usr/local/lsws/lsphp83/bin/lsphp',
                    '/usr/local/lsws/lsphp84/bin/lsphp',
                    '/opt/alt/php82/usr/bin/php',
                    '/opt/alt/php83/usr/bin/php',
                    '/opt/alt/php84/usr/bin/php',
                    'C:\php\php.exe',
                ] as $path
            ) {
                if (is_executable($path)) {
                    $php = $path;
                    break;
                }
            }
        }

        if ($php === false || !is_executable($php)) {
            throw new Exception('Could not find php executable.');
        }

        $script = JPATH_CLI . '/joomla.php';
        $baseUrl = (string)UriResolver::resolve(
            new Uri(SystemUri::homePageAbsolute($this->getPathsUtils())),
            new Uri((string)$params->get('recache_base_url'))
        );
        $log = JDEBUG && JCH_DEBUG ? JPATH_ADMINISTRATOR . '/logs/jchoptimize_recache.log' : '/dev/null';

        $cmd = sprintf(
            '%s %s jchoptimize:recache --live-site=%s > %s 2>&1 &',
            $php,
            '"${:SCRIPT}"',
            '"${:BASE_URL}"',
            '"${:LOG}"',
        );
        $env = ['SCRIPT' => $script, 'BASE_URL' => $baseUrl, 'LOG' => $log];
        $process = Process::fromShellCommandline($cmd);

        $process->setOptions(['create_new_console' => true, 'create_process_group' => true]);
        $process->start(null, $env);
    }

    public function getPathsUtils(): PathsInterface
    {
        if (null === $this->pathsUtils) {
            throw new LogicException('PathsUtils is not set');
        }

        return $this->pathsUtils;
    }

    public function setPathsUtils(PathsInterface $pathsUtils): void
    {
        $this->pathsUtils = $pathsUtils;
    }

    public function getCrawlQueue(): CacheCrawlQueue
    {
        if ($this->crawlQueue === null) {
            throw new LogicException('CrawlQueue is not set');
        }

        return $this->crawlQueue;
    }

    public function setCrawlQueue(?CacheCrawlQueue $crawlQueue): void
    {
        $this->crawlQueue = $crawlQueue;
    }

    public function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            throw new LogicException('Logger is not set');
        }

        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getParams(): Registry
    {
        if (null === $this->params) {
            throw new LogicException('Params not set');
        }

        return $this->params;
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    protected function populateState(): void
    {
        $this->state->set('params', $this->getParams());
    }
}
