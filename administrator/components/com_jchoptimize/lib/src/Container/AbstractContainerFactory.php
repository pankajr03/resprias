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

namespace JchOptimize\Core\Container;

use _JchOptimizeVendor\Joomla\DI\Container;
use JchOptimize\ContainerFactory;
use JchOptimize\Core\Debugger;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\Service\Provider\Admin;
use JchOptimize\Core\Service\Provider\LaminasCache;
use JchOptimize\Core\Service\Provider\Callbacks;
use JchOptimize\Core\Service\Provider\Core;
use JchOptimize\Core\Service\Provider\FeatureHelpers;
use JchOptimize\Core\Service\Provider\SharedEvents;
use JchOptimize\Core\Service\Provider\Spatie;

abstract class AbstractContainerFactory
{
    /**
     * Will return a new instance of the container every time
     *
     * @return Container
     */
    public static function getContainer(): Container
    {
        $ContainerFactory = new ContainerFactory();

        $container = new Container();

        $ContainerFactory->registerCoreProviders($container);
        $ContainerFactory->registerPlatformProviders($container);

        Debugger::setContainer($container);
        HtmlElementBuilder::setContainer($container);

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return void
     */
    protected function registerCoreProviders(Container $container): void
    {
        $container->registerServiceProvider(new SharedEvents())
            ->registerServiceProvider(new Core())
            ->registerServiceProvider(new Callbacks())
            ->registerServiceProvider(new LaminasCache())
            ->registerServiceProvider(new Admin());

        if (JCH_PRO) {
            $container->registerServiceProvider(new FeatureHelpers())
                ->registerServiceProvider(new Spatie());
        }
    }

    /**
     * To be implemented by JchOptimize/Container to attach service providers specific to the particular platform
     *
     * @param Container $container
     *
     * @return void
     */
    abstract protected function registerPlatformProviders(Container $container): void;
}
