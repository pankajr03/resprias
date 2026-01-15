<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Helper;

use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class OptimizeImage
{
    /**
     * @param WebAssetManager $wa
     * @param string $apiParams Json encoded string of params
     * @return void
     */
    public static function loadResources(WebAssetManager $wa, string $apiParams): void
    {
        $wa->useScript('com_jchoptimize.core.optimize-image');

        $message = addslashes(Text::_('COM_JCHOPTIMIZE_PLEASE_SELECT_FILES'));

        $sJs = <<<JS
window.jchOptimizeImageData = {
    message : '$message',   
    params : JSON.parse('{$apiParams}'),
};

JS;
        $wa->addInlineScript($sJs);
    }
}
