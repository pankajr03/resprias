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

namespace CodeAlfa\Plugin\User\JchUserState\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

use function defined;
use function time;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JchUserState extends CMSPlugin implements SubscriberInterface
{
    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        parent::__construct($dispatcher, $config);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAfterLogin' => 'onUserAfterLogin',
            'onUserAfterLogout' => 'onUserAfterLogout',
            'onUserPostForm' => 'onUserPostForm',
            'onUserPostFormDeleteCookie' => 'onUserPostFormDeleteCookie',
        ];
    }

    public function onUserAfterLogin(Event $event): void
    {
        /** @var CMSApplication $app */
        $app = $this->getApplication();

        if ($app->isClient('site')) {
            $options = [
                'expires' => 0,
                'path' => $app->get('cookie_path', '/'),
                'domain' => $app->get('cookie_domain', ''),
                'secure' => $app->isHttpsForced(),
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            $app->getInput()->cookie->set(
                'jch_optimize_no_cache_user_state',
                'user_logged_in',
                $options
            );
        }
    }

    public function onUserAfterLogout(Event $event): void
    {
        /** @var CMSApplication $app */
        $app = $this->getApplication();

        if ($app->isClient('site')) {
            $options = [
                'expires' => 1,
                'path' => $app->get('cookie_path', '/'),
                'domain' => $app->get('cookie_domain', ''),
                'secure' => $app->isHttpsForced(),
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            $app->getInput()->cookie->set(
                'jch_optimize_no_cache_user_state',
                '',
                $options
            );
        }
    }

    public function onUserPostForm(Event $event): void
    {
        /** @var CMSApplication $app */
        $app = $this->getApplication();

        $options = [
            'expires' => time() + (int)$this->params->get('page_cache_lifetime', '900'),
            'path' => $app->get('cookie_path', '/'),
            'domain' => $app->get('cookie_domain', ''),
            'secure' => $app->isHttpsForced(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        $app->getInput()->cookie->set(
            'jch_optimize_no_cache_user_activity',
            'user_posted_form',
            $options
        );
    }

    public function onUserPostFormDeleteCookie(Event $event): void
    {
        /** @var CMSApplication $app */
        $app = $this->getApplication();

        $options = [
            'expires' => 1,
            'path' => $app->get('cookie_path', '/'),
            'domain' => $app->get('cookie_domain', ''),
            'secure' => $app->isHttpsForced(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        $app->getInput()->cookie->set(
            'jch_optimize_no_cache_user_activity',
            '',
            $options
        );
    }
}
