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

use function apache_setenv;
use function connection_aborted;
use function date_default_timezone_set;
use function function_exists;
use function header;
use function ini_set;
use function json_decode;
use function ob_end_flush;
use function ob_flush;
use function ob_get_level;
use function ob_implicit_flush;
use function session_write_close;
use function stripslashes;

class EventSource implements MessageEventInterface
{
    public function initialize(): void
    {
        date_default_timezone_set("UTC");
        @header("X-Accel-Buffering: no");
        @header("Cache-Control: no-cache");
        @header("Content-Type: text/event-stream");

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');

        ob_implicit_flush();
    }

    private function stringify(string $data, string $type): string
    {
        $stream = '';

        if ($type) {
            $stream .= "event: {$type}\n";
        }

        $stream .= str_pad("data: {$data}", 4096, "\n");

        return $stream;
    }

    public function send(string $data, string $type = ''): void
    {
        $stream = $this->stringify($data, $type);

        echo $stream;

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        @ob_flush();

        if (connection_aborted()) {
            exit();
        }
    }

    public function receive(Input $input): object|null
    {
        $data = stripslashes($input->cookie->getString('jch_optimize_images_api'));

        //Destroy the cookie
        $input->cookie->set('jch_optimize_images_api', '', ['expires' => 1]);
        session_write_close();

        return json_decode($data);
    }

    public function disconnect(): void
    {
    }
}
