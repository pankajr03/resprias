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

namespace CodeAlfa\Component\JchOptimize\Site\Controller;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ReCacheModel;
use Exception;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Throwable;

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
class RecacheController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    public function run(): void
    {
        $app = $this->app;

        if (!$app instanceof SiteApplication) {
            return;
        }

        // 0) Validate HMAC signature
        $sig = (string)($this->input->get('sig', '', 'raw'));
        $when = (string)($this->input->get('ts', '', 'raw')); // e.g., unix ts
        if (!$this->isValidSig($sig, $when)) {
            $app->setHeader('Status', '403', true);
            echo 'Forbidden';
            $app->sendHeaders();
            $app->close();
        }

        // 1) Ensure no session is held
        $session = $app->getSession();
        if ($session->isStarted()) {
            $session->close();
        }

        // 2) Immediately respond 202 and end the HTTP request
        $app->setHeader('Status', '202 Accepted', true);
        $app->setHeader('Content-Type', 'text/plain; charset=utf-8', true);
        $app->setHeader('Cache-Control', 'no-store', true);
        $app->setHeader('Connection', 'close', true);
        $body = 'Recache accepted';
        $app->setHeader('Content-Length', (string)strlen($body), true);
        $app->sendHeaders();
        echo $body;

        // Try to detach the HTTP response cleanly
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @ob_end_flush();
            @flush();
        }

        // 3) Do the work (no output!)
        try {
            $this->runRecache();
        } catch (Throwable $e) {
            // log to Joomla logs; never echo
            $app->getLogger()->error('Recache failed: ' . $e->getMessage(), ['category' => 'com_jchoptimize']);
        }

        // 4) Hard stop to avoid any further output
        $app->close();
    }

    private function isValidSig(string $sig, string $ts): bool
    {
        // 5-minute freshness window
        if (!ctype_digit($ts) || abs(time() - (int)$ts) > 300) {
            return false;
        }
        $secret = $this->app->get('secret');
        $base = 'recache.run|' . $ts;
        $calc = hash_hmac('sha256', $base, $secret);

        return hash_equals($calc, $sig);
    }

    /**
     * @throws Exception
     */
    private function runRecache(): void
    {
        /** @var ReCacheModel $recacheModel */
        $recacheModel = $this->getModel('ReCache', 'Administrator');
        $recacheModel->reCache();
    }
}
