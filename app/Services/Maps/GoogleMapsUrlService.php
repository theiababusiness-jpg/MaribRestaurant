<?php

namespace App\Services\Maps;

use GuzzleHttp\Client;
use RuntimeException;
use Throwable;

class GoogleMapsUrlService
{
    public function resolve(string $url): array
    {
        $normalizedUrl = $this->normalizeUrl($url);
        $directCoordinates = $this->extractCoordinates($normalizedUrl);

        if ($directCoordinates) {
            return [
                'input_url' => $normalizedUrl,
                'resolved_url' => $normalizedUrl,
                'open_url' => $normalizedUrl,
                'embed_url' => $this->buildEmbedUrl($directCoordinates['lat'], $directCoordinates['lng']),
                'lat' => round($directCoordinates['lat'], 7),
                'lng' => round($directCoordinates['lng'], 7),
                'label' => $this->extractPlaceLabel($normalizedUrl),
            ];
        }

        $resolvedUrl = $normalizedUrl;
        $responseBody = '';

        try {
            $client = new Client([
                'allow_redirects' => [
                    'max' => 10,
                    'track_redirects' => true,
                ],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0',
                ],
                'http_errors' => false,
                'timeout' => 15,
            ]);

            $response = $client->request('GET', $normalizedUrl);
            $redirectHistory = $response->getHeader('X-Guzzle-Redirect-History');
            $resolvedUrl = end($redirectHistory) ?: $normalizedUrl;
            $responseBody = (string) $response->getBody();
        } catch (Throwable) {
            // Fall back to parsing the provided URL directly when redirects are unavailable.
        }

        $coordinates = $this->extractCoordinates($resolvedUrl)
            ?? $this->extractCoordinates($responseBody);

        if (! $coordinates) {
            throw new RuntimeException('Unable to extract coordinates from the provided Google Maps URL.');
        }

        return [
            'input_url' => $normalizedUrl,
            'resolved_url' => $resolvedUrl,
            'open_url' => $resolvedUrl,
            'embed_url' => $this->buildEmbedUrl($coordinates['lat'], $coordinates['lng']),
            'lat' => round($coordinates['lat'], 7),
            'lng' => round($coordinates['lng'], 7),
            'label' => $this->extractPlaceLabel($resolvedUrl),
        ];
    }

    public function buildEmbedUrl(float $lat, float $lng, int $zoom = 16): string
    {
        return "https://www.google.com/maps?q={$lat},{$lng}&z={$zoom}&output=embed";
    }

    public function buildOpenUrl(float $lat, float $lng): string
    {
        return "https://www.google.com/maps?q={$lat},{$lng}";
    }

    public function extractCoordinates(string $text): ?array
    {
        $patterns = [
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
            '/[?&](?:q|query|ll)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
            '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                return [
                    'lat' => (float) $matches[1],
                    'lng' => (float) $matches[2],
                ];
            }
        }

        return null;
    }

    protected function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new RuntimeException('Google Maps URL is required.');
        }

        if (! preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return $url;
    }

    protected function extractPlaceLabel(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($path === '' || ! preg_match('#/place/([^/]+)#', $path, $matches)) {
            return null;
        }

        $label = rawurldecode(str_replace('+', ' ', $matches[1]));
        $label = preg_replace('/\s+/', ' ', $label ?? '');
        $label = trim($label);

        return $label !== '' ? $label : null;
    }
}
