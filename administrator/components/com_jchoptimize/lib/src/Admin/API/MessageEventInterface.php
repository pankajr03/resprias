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

namespace JchOptimize\Core\Admin\API;

use _JchOptimizeVendor\V91\Joomla\Input\Input;

interface MessageEventInterface
{
    public function initialize(): void;

    public function receive(Input $input): object|null|bool;

    public function send(string $data, string $type = ''): void;

    public function disconnect(): void;
}
