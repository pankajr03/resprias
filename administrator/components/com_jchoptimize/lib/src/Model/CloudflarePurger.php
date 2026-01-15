<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Model;

use _JchOptimizeVendor\V91\GuzzleHttp\Client;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\GuzzleException;
use _JchOptimizeVendor\V91\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\V91\Psr\Http\Client\ClientInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use JchOptimize\Core\Registry;

class CloudflarePurger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private Registry $params,
        /** @var ClientInterface&Client $httpClient */
        private ClientInterface $httpClient
    ) {
    }

    public function purge(?array $urls = null): void
    {
        if (!$this->params->get('cf_enable', '0')) {
            return;
        }

        $token = trim((string)$this->params->get('cf_api_token'));
        $zoneId = trim((string)$this->params->get('cf_zone_id'));

        if (!$token || !$zoneId) {
            return;
        }

        $endpoint = sprintf('https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $zoneId);

        $body = $urls && count($urls) ? ['files' => array_values($urls)] : ['purge_everything' => true];
        $options = [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => json_encode($body),
        ];

        try {
            $response = $this->httpClient->post($endpoint, $options);
            $data = json_decode((string)$response->getBody(), true);
            $code = $response->getStatusCode();
            if ($code >= 200 && $code < 300 && !empty($data['success'])) {
                $this->logger?->debug('Cloudflare purge OK (' . ($urls ? 'files' : 'everything') . ')');
                return;
            }

            $errors = isset($data['errors']) ? json_encode($data['errors']) : 'unknown';
            $this->logger?->debug(sprintf('Cloudflare purge failed: HTTP %s; errors: %s', (string)$code, $errors));
        } catch (GuzzleException $e) {
            $this->logger?->error($e->getMessage());
        }
    }
}
