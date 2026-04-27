<?php

namespace App\Services;

use App\Models\MediaMonitor;
use App\Models\PressClip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PressClipMonitorService
{
    public function __construct(
        protected UrlMetadataService $metadataService,
    ) {
    }

    public function run(MediaMonitor $monitor): array
    {
        $created = 0;
        $updated = 0;

        try {
            $entries = $this->fetchEntries($monitor);

            foreach ($entries as $entry) {
                [$clip, $wasCreated] = $this->storeEntry($monitor, $entry);
                if ($wasCreated) {
                    $created++;
                } elseif ($clip !== null) {
                    $updated++;
                }
            }

            $monitor->update([
                'last_checked_at' => now(),
                'last_clip_at' => $created > 0 ? now() : $monitor->last_clip_at,
                'clips_found' => $monitor->clips_found + $created,
                'last_error' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Press monitor failed', [
                'monitor_id' => $monitor->id,
                'message' => $e->getMessage(),
            ]);

            $monitor->update([
                'last_checked_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    protected function fetchEntries(MediaMonitor $monitor): Collection
    {
        $feedUrl = $this->buildFeedUrl($monitor);

        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; wrk-usleg-monitor/1.0)',
                'Accept' => 'application/rss+xml, application/xml, text/xml',
            ])
            ->get($feedUrl);

        if (!$response->successful()) {
            throw new \RuntimeException("Feed request failed with status {$response->status()}");
        }

        $xml = @simplexml_load_string($response->body(), \SimpleXMLElement::class, LIBXML_NOCDATA);

        if (!$xml || !isset($xml->channel->item)) {
            throw new \RuntimeException('Could not parse RSS feed response.');
        }

        return collect($xml->channel->item)
            ->map(fn($item) => $this->mapFeedItem($item))
            ->filter(fn($entry) => !empty($entry['url']))
            ->unique('url')
            ->take(10)
            ->values();
    }

    protected function mapFeedItem(\SimpleXMLElement $item): array
    {
        $url = $this->normalizeUrl((string) $item->link);
        $summary = trim(strip_tags((string) $item->description));
        $source = trim((string) $item->source);

        $publishedAt = null;
        if (!empty((string) $item->pubDate)) {
            try {
                $publishedAt = Carbon::parse((string) $item->pubDate);
            } catch (\Throwable) {
                $publishedAt = null;
            }
        }

        return [
            'title' => trim(html_entity_decode((string) $item->title)),
            'url' => $url,
            'summary' => $summary ?: null,
            'outlet_name' => $source ?: $this->hostLabel($url),
            'published_at' => $publishedAt,
        ];
    }

    protected function storeEntry(MediaMonitor $monitor, array $entry): array
    {
        $clip = PressClip::where('url', $entry['url'])->first();
        $metadata = $this->metadataService->extractMetadata($entry['url']);

        $payload = [
            'title' => $metadata['title'] ?: $entry['title'] ?: 'News coverage',
            'url' => $entry['url'],
            'outlet_name' => $metadata['site_name'] ?: $entry['outlet_name'] ?: $this->hostLabel($entry['url']),
            'journalist_name' => $metadata['author'] ?: null,
            'published_at' => $this->resolvePublishedDate($metadata['published_date'], $entry['published_at']),
            'clip_type' => 'article',
            'sentiment' => 'neutral',
            'status' => $monitor->auto_approve ? 'approved' : 'pending_review',
            'summary' => $metadata['description'] ?: $entry['summary'],
            'image_url' => $metadata['image'] ?: null,
            'source' => 'web_search',
            'created_by' => $this->resolveCreatedBy($monitor),
        ];

        $wasCreated = false;

        if ($clip) {
            $updates = collect($payload)
                ->filter(function ($value, $key) use ($clip) {
                    if (in_array($key, ['status', 'created_by'], true)) {
                        return false;
                    }

                    return blank($clip->{$key}) && filled($value);
                })
                ->all();

            if (!empty($updates)) {
                $clip->update($updates);
            }
        } else {
            $clip = PressClip::create($payload);
            $wasCreated = true;
        }

        if ($monitor->issue_id) {
            $clip->issues()->syncWithoutDetaching([$monitor->issue_id]);
        }

        if ($monitor->topic_id) {
            $clip->topics()->syncWithoutDetaching([$monitor->topic_id]);
        }

        return [$clip, $wasCreated];
    }

    protected function buildFeedUrl(MediaMonitor $monitor): string
    {
        $query = urlencode($monitor->query);

        return "https://news.google.com/rss/search?q={$query}&hl=en-US&gl=US&ceid=US:en";
    }

    protected function resolvePublishedDate(?string $metadataDate, mixed $entryDate): string
    {
        if (!empty($metadataDate)) {
            try {
                return Carbon::parse($metadataDate)->toDateString();
            } catch (\Throwable) {
                // Fall back to entry date.
            }
        }

        if ($entryDate instanceof Carbon) {
            return $entryDate->toDateString();
        }

        return now()->toDateString();
    }

    protected function resolveCreatedBy(MediaMonitor $monitor): int
    {
        if ($monitor->created_by) {
            return $monitor->created_by;
        }

        $configuredEmail = config('automation.user_email');
        if ($configuredEmail) {
            $userId = User::where('email', $configuredEmail)->value('id');
            if ($userId) {
                return $userId;
            }
        }

        $userId = User::query()
            ->orderByDesc('is_super_admin')
            ->orderByDesc('is_admin')
            ->orderBy('id')
            ->value('id');

        if (!$userId) {
            throw new \RuntimeException('No user exists to own automated press clips.');
        }

        return $userId;
    }

    protected function normalizeUrl(?string $url): ?string
    {
        if (blank($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $queryParams = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            unset(
                $queryParams['utm_source'],
                $queryParams['utm_medium'],
                $queryParams['utm_campaign'],
                $queryParams['utm_term'],
                $queryParams['utm_content'],
                $queryParams['gclid'],
                $queryParams['fbclid'],
                $queryParams['mc_cid'],
                $queryParams['mc_eid']
            );
        }

        $normalized = "{$parts['scheme']}://{$parts['host']}" . ($parts['path'] ?? '');
        if (!empty($queryParams)) {
            $normalized .= '?' . http_build_query($queryParams);
        }

        return rtrim($normalized, '/');
    }

    protected function hostLabel(?string $url): string
    {
        $host = parse_url($url ?? '', PHP_URL_HOST);
        if (!$host) {
            return 'Unknown outlet';
        }

        $host = preg_replace('/^www\./', '', $host);
        $label = explode('.', $host)[0] ?? $host;

        return ucwords(str_replace(['-', '_'], ' ', $label));
    }
}
