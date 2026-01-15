<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator;

use _JchOptimizeVendor\Joomla\DI\Container;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use Joomla\CMS\Factory;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

/**
 * A class to easily fetch a Joomla\DI\Container with all dependencies registered
 */
class ContainerFactory
{
    public static function getContainer(): Container
    {
        /** @var JchOptimizeComponent $component */
        $component = Factory::getApplication()->bootComponent('com_jchoptimize');

        return $component->getContainer();
    }
}
