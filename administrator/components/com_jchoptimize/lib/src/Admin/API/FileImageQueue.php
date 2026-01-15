<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\API;

use _JchOptimizeVendor\V91\Spatie\Crawler\Exceptions\InvalidUrl;
use JchOptimize\Core\Spatie\CrawlQueues\CacheCrawlQueue;
use JchOptimize\Core\Spatie\CrawlQueues\OptimizeImagesCrawlQueue;

class FileImageQueue extends OptimizeImagesCrawlQueue
{
    /** @var string */
    protected const URLS_NAMESPACE = 'file_images_queue';

    /** @var string */
    protected const PENDING_URLS_NAMESPACE = 'pending_file_images_queue';

    /**
     * Overrides parent destructor
     * @throws InvalidUrl
     */
    protected function getUrlId($crawlUrl): string
    {
        return CacheCrawlQueue::getUrlId($crawlUrl);
    }
}
