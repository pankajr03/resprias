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

namespace JchOptimize\Core\PageCache;

use _JchOptimizeVendor\V91\Joomla\Filesystem\Folder;
use _JchOptimizeVendor\V91\Joomla\Filesystem\Path;
use _JchOptimizeVendor\V91\Joomla\Input\Input;
use _JchOptimizeVendor\V91\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Exception\RuntimeException;
use _JchOptimizeVendor\V91\Laminas\Cache\Pattern\CaptureCache as LaminasCaptureCache;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\IterableInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\TaggableInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use Exception;
use JchOptimize\Core\Admin\AdminTasks;
use JchOptimize\Core\Exception\InvalidArgumentException;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Htaccess;
use JchOptimize\Core\Model\CloudflarePurger;
use JchOptimize\Core\Platform\CacheInterface;
use JchOptimize\Core\Platform\HooksInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\UtilityInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Uri\Utils;
use Throwable;

use function defined;
use function file_exists;
use function gzencode;
use function preg_replace;
use function strtr;

defined('_JCH_EXEC') or die('Restricted access');

class CaptureCache extends PageCache
{
    protected bool $captureCacheEnabled = true;

    private string $captureCacheId = '';

    private string $startHtaccessLine = '## BEGIN CAPTURE CACHE - JCH OPTIMIZE ##';

    private string $endHtaccessLine = '## END CAPTURE CACHE - JCH OPTIMIZE ##';

    public function __construct(
        Registry $params,
        Input $input,
        StorageInterface $pageCacheStorage,
        /**
         * @var StorageInterface&TaggableInterface&IterableInterface $taggableCache
         */
        $taggableCache,
        private LaminasCaptureCache $captureCache,
        CacheInterface $cacheUtils,
        HooksInterface $hooks,
        UtilityInterface $utility,
        private PathsInterface $pathsUtils,
        CloudflarePurger $cloudflarePurger
    ) {
        parent::__construct(
            $params,
            $input,
            $pageCacheStorage,
            $taggableCache,
            $cacheUtils,
            $hooks,
            $utility,
            $cloudflarePurger
        );

        if (!$this->params->get('pro_capture_cache_enable', '0')) {
            $this->captureCacheEnabled = false;
        }

        if (
            $this->params->get('pro_cache_platform', '0')
            || $this->cacheUtils->isCaptureCacheIncompatible()
        ) {
            $this->captureCacheEnabled = false;
        }

        $uri = $this->getCurrentPage();

        //Don't use capture cache when there's query
        if (!$this->utility->isAdmin() && $uri->getQuery() !== '') {
            $this->captureCacheEnabled = false;
        }

        //Don't use capture cache when URL ends in index.php to avoid conflicts with CMS redirects
        if (
            !$this->utility->isAdmin()
            && trim($uri->getPath(), '/') == trim(SystemUri::currentBasePath() . 'index.php', '/')
            && empty($uri->getQuery())
        ) {
            $this->captureCacheEnabled = false;
        }
    }

    public function getItems(): array
    {
        $items = parent::getItems();
        $filteredItems = [];
        //set http-request tag if a cache file exists for this item
        foreach ($items as $item) {
            $uri = Utils::uriFor($item['url']);

            $captureCacheId = $this->getCaptureCacheIdFromPage($uri);
            $item['http-request'] = $this->captureCache->has($captureCacheId) ? 'yes' : 'no';

            //filter by HTTP Requests
            if (!empty($this->filters['filter_http-request'])) {
                if ($item['http-request'] != $this->filters['filter_http-request']) {
                    continue;
                }
            }

            $filteredItems[] = $item;
        }

        //If we're sorting by http-request we'll need to re-sort
        if (str_starts_with($this->lists['list_fullordering'], 'http-request')) {
            $this->sortItems($filteredItems, $this->lists['list_fullordering']);
        }

        return $filteredItems;
    }

    private function getCaptureCacheIdFromPage(?UriInterface $page = null): string
    {
        $uri = ((string)$page === '' || is_null($page)) ? $this->getCurrentPage() : $page;
        $id = $uri->getScheme() . '/'
              . $uri->getHost() . ($uri->getPort() ? ':' . $uri->getPort() : '')
              . '/' . $uri->getPath() . '/'
              . $uri->getQuery();
        $id .= '/index.html';

        return Path::clean($id);
    }

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        $this->captureCacheId = $this->getCaptureCacheIdFromPage();

        //If user is logged in we'll need to set a cookie, so they won't see pages cached by another user
        if (
            !$this->utility->isGuest() && !$this->input->cookie->get(
                'jch_optimize_no_cache_user_state'
            ) == 'user_logged_in'
        ) {
            $options = [
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            $this->input->cookie->set('jch_optimize_no_cache_user_state', 'user_logged_in', $options);
            //if they're logged out we can delete the cookie, so they can now see cached pages
        } elseif (
            $this->utility->isGuest() && $this->input->cookie->get(
                'jch_optimize_no_cache_user_state'
            ) == 'user_logged_in'
        ) {
            $options = [
                'expires'  => 1,
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            $this->input->cookie->set('jch_optimize_no_cache_user_state', '', $options);
        }

        parent::initialize();
    }

    public function store(string $html): string
    {
        //Tag should be set in parent::store()
        $html = parent::store($html);

        //This function will check for a valid tag before saving capture cache
        $this->setCaptureCache($html);

        return $html;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function setCaptureCache(string $html): void
    {
        if (
            $this->getCachingEnabled()
            && $this->isCaptureCacheEnabled()
            && !empty($this->taggableCache->getTags($this->cacheId))
            && !$this->captureCache->has($this->captureCacheId)
        ) {
            try {
                $html = $this->tagCaptureCacheHtml($html);
                $this->captureCache->set($html, $this->captureCacheId);

                //Gzip
                $html = preg_replace('#and served using HTTP Request#', '\0 (Gzipped)', $html);
                $htmlGz = gzencode($html, 9);
                $this->captureCache->set($htmlGz, $this->getGzippedCaptureCacheId($this->captureCacheId));
            } catch (Exception $e) {
            }
        }
    }

    private function tagCaptureCacheHtml(string $content): ?string
    {
        return preg_replace('#Cached by JCH Optimize on .*? GMT#', '\0 and served using HTTP Request', $content);
    }

    private function getGzippedCaptureCacheId(string $id): string
    {
        return $id . '.gz';
    }

    public function deleteItemById(string $id): bool
    {
        $result = 1;

        try {
            $captureCacheId = $this->getCaptureCacheIdFromPageCacheId($id);
            $gzCaptureCacheId = $this->getGzippedCaptureCacheId($captureCacheId);

            $this->captureCache->remove($captureCacheId);
            $this->captureCache->remove($gzCaptureCacheId);

            $result &= (int)!$this->captureCache->has($captureCacheId);
            $result &= (int)!$this->captureCache->has($gzCaptureCacheId);
        } catch (RuntimeException) {
            //Failed to delete cache
            $result = false;
        } catch (Throwable) {
            //Cache didn't exist
        }

        if ($result) {
            //Delete parent cache only if successful because tag will be deleted here
            $result &= (int)parent::deleteItemById($id);
        }

        return (bool)$result;
    }

    public function getCaptureCacheIdFromPageCacheId(string $id): string
    {
        $tags = $this->taggableCache->getTags($id);

        if (!empty($tags[1])) {
            return $this->getCaptureCacheIdFromPage(Utils::uriFor($tags[1]));
        }

        throw new InvalidArgumentException('No tags found for cache id');
    }

    public function deleteItemsByIds(array $ids): bool
    {
        $result = 1;

        $urls = [];

        foreach ($ids as $id) {
            $urls[] = $this->getUrlFromId($id);
            $result &= (int)$this->deleteItemById($id);
        }

        $this->cloudflarePurger?->purge($urls);

        return (bool)$result;
    }

    public function deleteAllItems(): bool
    {
        $result = 1;
        $result &= (int)$this->deleteCaptureCacheDir();

        //Only delete parent if successful, tags will be deleted here
        if ($result) {
            $result &= (int)parent::deleteAllItems();
        }

        return (bool)$result;
    }

    private function deleteCaptureCacheDir(): bool
    {
        try {
            if (file_exists($this->pathsUtils->captureCacheDir())) {
                return Folder::delete($this->pathsUtils->captureCacheDir());
            }
        } catch (Exception $e) {
            //Let's try another way
            try {
                if (!Helper::deleteFolder($this->pathsUtils->captureCacheDir())) {
                    $this->logger->error('Error trying to delete Capture Cache dir: ' . $e->getMessage());
                }
            } catch (Exception $e) {
            }
        }

        return !file_exists($this->pathsUtils->captureCacheDir());
    }

    /**
     * @return void
     */
    public function updateHtaccess(): void
    {
        $pluginState = $this->cacheUtils->isPageCacheEnabled($this->params, true);

        //If Capture Cache not enabled just clean htaccess and leave
        if (
            !$pluginState || !$this->params->get('pro_capture_cache_enable', '1')
            || $this->params->get('pro_cache_platform', '0')
            || !$this->captureCacheEnabled
        ) {
            $this->cleanHtaccess();

            return;
        }

        $captureCacheDir = strtr($this->pathsUtils->captureCacheDir(), '\\', '/');
        $relCaptureCacheDir = strtr($this->pathsUtils->captureCacheDir(true), '\\', '/');
        $jchVersion = JCH_VERSION;

        $htaccessContents = <<<APACHECONFIG
<IfModule mod_headers.c>
	Header set X-Cached-By: "JCH Optimize v$jchVersion"
</IfModule>

<IfModule mod_rewrite.c>
	RewriteEngine On
	
	RewriteRule "\.html\.gz$" "-" [T=text/html,E=no-gzip:1,E=no-brotli:1,L]
	
	<IfModule mod_headers.c>
		<FilesMatch "\.html\.gz$" >
			Header set Content-Encoding gzip
			Header set Vary Accept-Encoding
		</FilesMatch>
		
		RewriteRule .* - [E=JCH_GZIP_ENABLED:yes]
	</IfModule>
	
	<IfModule !mod_headers.c>
		<IfModule mod_mime.c>
		 	AddEncoding gzip .gz
		</IfModule>
		
		RewriteRule .* - [E=JCH_GZIP_ENABLED:yes]
	</IfModule>

	RewriteCond %{ENV:JCH_GZIP_ENABLED} ^yes$
	RewriteCond %{HTTP:Accept-Encoding} gzip
	RewriteRule .* - [E=JCH_GZIP:.gz]
	#--- Build vars --
	# scheme	
	RewriteRule .* - [E=JCH_SCHEME:http]
	RewriteCond %{HTTPS} on [OR]
	RewriteCond %{SERVER_PORT} ^443$
	RewriteRule .* - [E=JCH_SCHEME:https]

    # query segment: "/{QUERY}" only when non-empty, else ""
    RewriteCond %{QUERY_STRING} !^$
    RewriteRule .* - [E=JCH_QS:/%{QUERY_STRING}]
    RewriteCond %{QUERY_STRING} ^$
    RewriteRule .* - [E=JCH_QS:]
    
	RewriteCond %{REQUEST_METHOD} ^GET 
	RewriteCond %{HTTP_COOKIE} !jch_optimize_no_cache
	RewriteCond %{REQUEST_URI} !^{$relCaptureCacheDir}/ [NC]
	RewriteCond "{$captureCacheDir}/%{ENV:JCH_SCHEME}/%{HTTP_HOST}%{REQUEST_URI}%{ENV:JCH_QS}/index\.html%{ENV:JCH_GZIP}" -f
	RewriteRule .* "{$relCaptureCacheDir}/%{ENV:JCH_SCHEME}/%{HTTP_HOST}%{REQUEST_URI}%{ENV:JCH_QS}/index.html%{ENV:JCH_GZIP}" [L]
</IfModule>
APACHECONFIG;

        try {
            Htaccess::updateHtaccess(
                $this->pathsUtils,
                $htaccessContents,
                [$this->startHtaccessLine, $this->endHtaccessLine],
                AdminTasks::$endHtaccessLine
            );
        } catch (Exception $e) {
        }
    }

    public function cleanHtaccess(): void
    {
        Htaccess::cleanHtaccess($this->pathsUtils, [$this->startHtaccessLine, $this->endHtaccessLine]);
    }

    public function hasCaptureCache(UriInterface $uri): bool
    {
        $captureCacheId = $this->getCaptureCacheIdFromPage($uri);

        return $this->captureCache->has($captureCacheId);
    }
}
