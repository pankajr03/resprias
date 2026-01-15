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

use _JchOptimizeVendor\V91\Composer\CaBundle\CaBundle;
use _JchOptimizeVendor\V91\Joomla\Input\Input;
use _JchOptimizeVendor\V91\Symfony\Component\Uid\Uuid;
use JchOptimize\Core\Admin\API\WebSocket\Client;

use function json_decode;
use function stream_context_create;

class WebSocket implements MessageEventInterface
{
    private ?Client $connection = null;

    private string $address = 'websocket.jch-optimize.net';

    private int $port = 443;
    private int $timeout = 30;

    private string $id;

    public function __construct(private string $browserId)
    {
        $this->id = (string) Uuid::v4();
    }

    public function initialize(): void
    {
        if ($this->isSSl()) {
            $options = [
                'ssl' => [
                    'cafile' => CaBundle::getBundledCaBundlePath(),
                    'disable_compression' => true,
                    'verify_peer' => true,
                    'allow_self_signed' => false
                ]
            ];
        } else {
            $options = null;
        }

        $context = stream_context_create($options);

        $this->connection = new Client(
            $this->address,
            $this->port,
            '',
            $errStr,
            $this->timeout,
            $this->isSSl(),
            false,
            '/',
            $context
        );

        $this->send('identify', 'identify');
    }

    public function receive(Input $input): object|null
    {
        if ($this->connection instanceof Client) {
            return @json_decode($this->connection->read(), false);
        }

        return null;
    }

    public function send(string $data, string $type = ''): void
    {
        $msg = [
           'payload' => [
                'data' => $data,
                'type' => $type
            ],
           'type' => $type,
           'id' => $this->id,
           'browserId' => $this->browserId,
           'role' => 'server'
        ];

        $this->connection?->write(json_encode($msg));
    }

    public function disconnect(): void
    {
        $this->connection?->close();
    }

    private function isSSl(): bool
    {
        return true;
    }
}
