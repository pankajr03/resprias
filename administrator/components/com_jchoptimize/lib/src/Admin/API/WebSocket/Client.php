<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\API\WebSocket;

class Client extends \_JchOptimizeVendor\V91\Paragi\PhpWebsocket\Client
{
    public function close(): void
    {
        if ($this->connection) {
            fclose($this->connection);
        }
    }
}
