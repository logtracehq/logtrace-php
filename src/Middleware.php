
<?php

declare(strict_types=1);

namespace Logtrace;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 middleware that injects a {@see RequestClient} as a request
 * attribute, making it available to any downstream handler.
 *
 * **Setup (e.g. Slim, Mezzio, Laravel PSR-15 bridge):**
 * ```php
 * $app->add(new \Logtrace\Middleware($client));
 * ```
 *
 * **Inside a handler:**
 * ```php
 * $rc = $request->getAttribute(\Logtrace\Middleware::ATTRIBUTE);
 * $rc->createEvent(new CreateEventRequest(...));
 * ```
 */
final class Middleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'logtrace.request_client';

    public function __construct(private readonly Client $client) {}

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $userAgent = $request->getHeaderLine('User-Agent');

        $statusRef = new \stdClass();
        $statusRef->code = 200;

        $rc = new RequestClient(
            client:          $this->client,
            method:          $request->getMethod(),
            endpoint:        (string) $request->getUri()->getPath(),
            clientIp:        self::realIP($request),
            userAgent:       $userAgent,
            operatingSystem: self::operatingSystem($userAgent),
            getStatus:       static fn () => $statusRef->code,
        );

        $response = $handler->handle(
            $request->withAttribute(self::ATTRIBUTE, $rc)
        );

        $statusRef->code = $response->getStatusCode();

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = end($values) ?: '';
        }
        $rc->setHeaders($headers);

        return $response;
    }


    public static function realIP(ServerRequestInterface $request): string
    {
        foreach (['CF-Connecting-IP', 'X-Real-IP'] as $header) {
            if ($ip = $request->getHeaderLine($header)) {
                return $ip;
            }
        }

        if ($xff = $request->getHeaderLine('X-Forwarded-For')) {
            return trim(explode(',', $xff)[0]);
        }

        $params = $request->getServerParams();
        return isset($params['REMOTE_ADDR']) && is_string($params['REMOTE_ADDR'])
            ? $params['REMOTE_ADDR']
            : '';
    }

    public static function operatingSystem(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'curl')                                                          => 'Unknown (curl)',
            str_contains($ua, 'windows')                                                       => 'Windows',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') || str_contains($ua, 'darwin') => 'macOS',
            str_contains($ua, 'android')                                                       => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios') => 'iOS',
            str_contains($ua, 'linux')                                                         => 'Linux',
            str_contains($ua, 'cros')                                                          => 'Chrome OS',
            default                                                                            => 'Unknown',
        };
    }
}
