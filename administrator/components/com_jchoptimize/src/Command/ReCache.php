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

namespace CodeAlfa\Component\JchOptimize\Administrator\Command;

use CodeAlfa\Component\JchOptimize\Administrator\Container\ContainerFactory;
use CodeAlfa\Component\JchOptimize\Administrator\CrawlObservers\ReCacheCli;
use JchOptimize\Core\Model\CacheMaintainer;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Spatie\Crawler;
use JchOptimize\Core\Spatie\CrawlQueues\CacheCrawlQueue;
use JchOptimize\Core\Uri\Uri;
use JchOptimize\Core\Uri\Utils;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function defined;
use function version_compare;

use const JVERSION;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ReCache extends AbstractCommand
{
    /**
     * Default command name
     *
     * @var string|null
     */
    protected static $defaultName = 'jchoptimize:recache';

    /**
     * @inheritDoc
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $container = ContainerFactory::create();
        $params = $container->get(Registry::class);

        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_TITLE'));

            /** @var string $baseUrl */
        $baseUrl = $input->getOption('live-site')
                ?? $params->get('recache_base_url')
                ?? $this->getApplication()->get('live_site', '');
        $uri = Utils::uriFor($baseUrl);

        if (!$baseUrl || !Uri::isAbsolute($uri)) {
            $symfonyStyle->error(Text::_('COM_JCHOPTIMIZE_CLI_BASE_URL_NOT_SET'));

            return 255;
        }

        $_SERVER['HTTP_HOST'] = $uri->getHost();
        $_SERVER['REQUEST_URI'] = $uri->getPath();
        $_SERVER['HTTPS'] = $uri->getScheme() === 'https' ? 'on' : 'off';
        $_SERVER['SCRIPT_NAME'] = preg_replace('#/index.php$#', '', $uri->getPath()) . 'index.php';

        //First flush the cache
        if (!$input->getOption('no-delete-cache')) {
            /** @var CacheMaintainer $cache */
            $cache = $container->get(CacheMaintainer::class);
            $cache->cleanCache();

            $symfonyStyle->comment(Text::_('COM_JCHOPTIMIZE_CLI_CACHE_CLEANED'));
        }

        $symfonyStyle->section(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_START'));

        $cacheCrawlQueue = $container->get(CacheCrawlQueue::class);

        $crawlLimit = (int)($input->getOption('crawl-limit') ?? $params->get('recache_crawl_limit', 500));
        $concurrency = (int)($input->getOption('concurrency') ?? $params->get('recache_concurrency', 20));
        $maxDepth = (int)($input->getOption('max-depth') ?? $params->get('recache_max_depth', 5));

        $observer = new ReCacheCli($symfonyStyle);

        Crawler::create($baseUrl)
            ->setCrawlQueue($cacheCrawlQueue)
            ->setCrawlObserver($observer)
            ->setTotalCrawlLimit($crawlLimit)
            ->setConcurrency($concurrency)
            ->setMaximumDepth($maxDepth)
            ->startCrawling($baseUrl);

        $symfonyStyle->comment(
            Text::sprintf('COM_JCHOPTIMIZE_CLI_RECACHE_NUM_URLS_CRAWLED', $observer->getNumCrawled())
        );

        $symfonyStyle->success(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_SUCCESS'));

        return 0;
    }

    protected function configure(): void
    {
        $this->addOption(
            'delete-cache',
            null, // NO shortcut because value can be negated
            InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
            Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_DELETE_CACHE')
        );
        $this->addOption(
            'crawl-limit',
            'l',
            InputOption::VALUE_REQUIRED,
            Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_CRAWL_LIMIT')
        );
        $this->addOption(
            'concurrency',
            'c',
            InputOption::VALUE_REQUIRED,
            Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_CONCURRENCY')
        );
        $this->addOption(
            'max-depth',
            'm',
            InputOption::VALUE_REQUIRED,
            Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_MAX_DEPTH')
        );
        $this->setDescription(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_DESC'));
        $this->setHelp(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_HELP'));
    }
}
