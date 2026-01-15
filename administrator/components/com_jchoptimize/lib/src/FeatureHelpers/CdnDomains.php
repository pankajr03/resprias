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

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use JchOptimize\Core\Cdn\Cdn;
use JchOptimize\Core\Cdn\CdnDomain;
use JchOptimize\Core\Registry;
use SplObjectStorage;

use function defined;
use function trim;

defined('_JCH_EXEC') or die('Restricted access');

class CdnDomains extends AbstractFeatureHelper
{
    public function __construct(Container $container, Registry $params, private Cdn $cdn)
    {
        parent::__construct($container, $params);
    }

    public function addCdnDomains(SplObjectStorage $domains): void
    {
        $domain2 = $this->params->get('pro_cookielessdomain_2', '');

        if (is_string($domain2) && trim($domain2) != '') {
            /** @var string[] $staticFiles2Array */
            $staticFiles2Array = $this->params->get('pro_staticfiles_2', Cdn::getStaticFiles());

            $domains->offsetSet(new CdnDomain($domain2, $staticFiles2Array, $this->cdn->getScheme()));
        }

        $domain3 = $this->params->get('pro_cookielessdomain_3', '');

        if (is_string($domain3) && trim($domain3) != '') {
            /** @var string[] $staticFiles3Array */
            $staticFiles3Array = $this->params->get('pro_staticfiles_3', Cdn::getStaticFiles());

            $domains->offsetSet(new CdnDomain($domain3, $staticFiles3Array, $this->cdn->getScheme()));
        }
    }
}
