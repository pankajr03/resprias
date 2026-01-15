<?php

namespace CodeAlfa\Component\JchOptimize\Administrator\Controller;

use _JchOptimizeVendor\V91\GuzzleHttp\Client;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\GuzzleException;
use _JchOptimizeVendor\V91\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\V91\Psr\Http\Client\ClientInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Throwable;

class CfVerifyController extends BaseController
{
    use MVCFactoryDecoratorAwareTrait;

    /** @var ClientInterface&Client */
    private ClientInterface $http;

    public function verify(): void
    {
        // CSRF
        if (!Session::checkToken()) {
            echo new JsonResponse(null, 'Invalid token (CSRF).', true);
            $this->app->close();
        }

        $cfToken = trim($this->input->post->get('token', '', 'raw'));
        $zoneId = trim($this->input->post->get('zone_id', '', 'string'));

        if ($cfToken === '' || $zoneId === '') {
            echo new JsonResponse(null, 'Cloudflare API token and Zone ID are required.', true);
            $this->app->close();
        }

        // HTTP client
        if (!isset($this->http)) {
            $this->http = new Client([
                'timeout' => 8.0,
                'http_errors' => false,
            ]);
        }

        $headers = [
            'Authorization' => 'Bearer ' . $cfToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            // 1) ZONE READ (works for User or Account tokens if scoped properly)
            $zResp = $this->http->get(
                "https://api.cloudflare.com/client/v4/zones/{$zoneId}",
                [RequestOptions::HEADERS => $headers]
            );

            $zCode = $zResp->getStatusCode();
            $zBody = json_decode((string)$zResp->getBody(), true) ?: [];

            if (!($zCode >= 200 && $zCode < 300 && !empty($zBody['success']))) {
                $friendly = $this->normalizeVerifyError($zCode, $zBody);
                echo new JsonResponse(
                    ['success' => false, 'zone_read' => false, 'purge_ok' => false],
                    $friendly,
                    true
                );
                $this->app->close();
            }

            $zoneName = (string)($zBody['result']['name'] ?? '');
            $host = strtolower(rtrim((string)Uri::getInstance()->getHost(), '.'));

            // 2) ZONE ↔ HOST MATCH
            if (!$this->hostMatchesZone($host, $zoneName)) {
                $msg = sprintf(
                    'Incorrect zone: This site is running on "%s" but the provided Zone ID belongs to "%s".',
                    $host,
                    $zoneName ?: '(unknown)'
                );
                echo new JsonResponse(
                    [
                        'success' => false,
                        'zone_read' => true,
                        'purge_ok' => false,
                        'zone_name' => $zoneName,
                        'site_host' => $host,
                    ],
                    $msg,
                    true
                );
                $this->app->close();
            }

            // 3) PURGE PERMISSION CHECK (safe purge-by-URL; object need not exist)
            $testUrl = "https://{$host}/__jch_verify__.txt";

            $pResp = $this->http->post(
                "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache",
                [
                    RequestOptions::HEADERS => $headers,
                    RequestOptions::BODY => json_encode(['files' => [$testUrl]], JSON_UNESCAPED_SLASHES),
                ]
            );

            $pCode = $pResp->getStatusCode();
            $pBody = json_decode((string)$pResp->getBody(), true) ?: [];

            if (!($pCode >= 200 && $pCode < 300 && !empty($pBody['success']))) {
                echo new JsonResponse(
                    [
                        'success' => false,
                        'zone_read' => true,
                        'purge_ok' => false,
                        'zone_name' => $zoneName,
                        'site_host' => $host,
                    ],
                    'Purge permission check failed: Invalid API or insufficient permissions.',
                    true
                );
                $this->app->close();
            }

            // SUCCESS
            echo new JsonResponse(
                [
                    'success' => true,
                    'zone_read' => true,
                    'purge_ok' => true,
                    'zone_name' => $zoneName,
                    'site_host' => $host,
                    'verified_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ],
                'Zone readable, domain matches, and purge permission confirmed.'
            );
        } catch (GuzzleException $e) {
            echo new JsonResponse(null, 'HTTP error: ' . $e->getMessage(), true);
        } catch (Throwable $e) {
            echo new JsonResponse(null, 'Exception: ' . $e->getMessage(), true);
        }

        $this->app->close();
    }

    public function setHttp(ClientInterface $http): void
    {
        $this->http = $http;
    }

    /**
     * Host must equal the zone OR be a subdomain of the zone.
     * Examples:
     *   host=example.com, zone=example.com        ✅
     *   host=www.example.com, zone=example.com    ✅
     *   host=admin.example.com, zone=example.com  ✅
     *   host=example.net, zone=example.com        ❌
     *   host=foo.bar.com, zone=bar.com            ✅
     */
    private function hostMatchesZone(string $host, string $zone): bool
    {
        $host = strtolower(rtrim($host, '.'));
        $zone = strtolower(rtrim((string)$zone, '.'));

        if ($zone === '' || $host === '') {
            return false;
        }

        if ($host === $zone) {
            return true;
        }

        return str_ends_with($host, '.' . $zone);
    }

    /**
     * Map Cloudflare verify errors to helpful, user-facing messages.
     */
    private function normalizeVerifyError(int $statusCode, array $body): string
    {
        $errMsg = $body['errors'][0]['message'] ?? '';
        $errCode = $body['errors'][0]['code'] ?? null;

        // Common cases we’ll translate nicely:
        if (in_array($statusCode, [400, 401, 403])) {
            return 'Invalid API token. API is incorrect or not authorized on this zone.';
        }

        if ($statusCode === 404) {
            return 'Invalid zone ID.';
        }

        // Fallback
        return $errMsg !== '' ? $errMsg : ('Token verification failed (HTTP ' . $statusCode . ').');
    }
}
