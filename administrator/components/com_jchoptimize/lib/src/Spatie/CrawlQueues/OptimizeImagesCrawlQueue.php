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

namespace JchOptimize\Core\Spatie\CrawlQueues;

class OptimizeImagesCrawlQueue extends NonOptimizedCacheCrawlQueue
{
    /** @var string */
    protected const URLS_NAMESPACE = 'optimize_images_urls';

    /** @var string */
    protected const PENDING_URLS_NAMESPACE = 'optimize_images_pending_urls';

    /**
     * Overrides parent destructor
     */
    public function __destruct()
    {
    }

    public function setStorageNamespace(): void
    {
        $this->storage->getOptions()->setNamespace(static::URLS_NAMESPACE);
        $this->pendingStorage->getOptions()->setNamespace(static::PENDING_URLS_NAMESPACE);
    }

    public function empty(): void
    {
        $this->emptyStorage($this->storage, static::URLS_NAMESPACE);
        $this->emptyStorage($this->pendingStorage, static::PENDING_URLS_NAMESPACE);
    }
}
