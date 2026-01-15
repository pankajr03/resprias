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

namespace CodeAlfa\Plugin\Console\JchOptimize\Extension;

use CodeAlfa\Component\JchOptimize\Administrator\Command\ReCache;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationEvent;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Console\Loader\WritableLoaderInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JchOptimize extends CMSPlugin implements SubscriberInterface
{
    private array $registeredCommands = [
        ReCache::class => 'jchoptimize:recache'
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            /** @see self::registerCommands() */
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    public function registerCommands(ApplicationEvent $event): void
    {
        /** @var ConsoleApplication $cliApp */
        $cliApp = $event->getApplication();
        $lang = $cliApp->getLanguage();

        // Disable if the component is not installed or disabled
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            $lang->load('plg_console_jchoptimize', JPATH_ADMINISTRATOR);
            $ioStyle = new SymfonyStyle($cliApp->getConsoleInput(), $cliApp->getConsoleOutput());
            $ioStyle->error(Text::_('PLG_CONSOLE_JCHOPTIMIZE_COMPONENT_NOT_ENABLED'));

            $cliApp->close(1);
        }

        //load language
        $lang->load('com_jchoptimize', JPATH_ADMINISTRATOR);

        $container = Factory::getContainer();

        foreach ($this->registeredCommands as $id => $command) {
            $container->share(
                $id,
                function () use ($id) {
                    return new $id();
                },
                true
            );

            $container->get(WritableLoaderInterface::class)->add($command, $id);
        }
    }
}
