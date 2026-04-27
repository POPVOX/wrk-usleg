<?php

use App\Livewire\Media\MediaIndex;
use App\Models\Issue;
use App\Models\MediaMonitor;
use App\Models\PressClip;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('creates a media monitor from the media page', function () {
    $user = User::factory()->create([
        'access_level' => 'admin',
        'is_admin' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaIndex::class)
        ->call('openMonitorForm')
        ->set('monitorForm.name', 'Member Watch')
        ->set('monitorForm.query', '"Rep. Example" OR "Example Office"')
        ->set('monitorForm.monitor_type', 'member')
        ->set('monitorForm.cadence', 'hourly')
        ->call('saveMonitor');

    $monitor = MediaMonitor::first();

    expect($monitor)->not->toBeNull();
    expect($monitor->name)->toBe('Member Watch');
    expect($monitor->created_by)->toBe($user->id);
    expect($monitor->is_active)->toBeTrue();
});

it('ingests a press clip from a media monitor and avoids duplicates', function () {
    $user = User::factory()->create([
        'access_level' => 'admin',
        'is_admin' => true,
    ]);

    $issue = Issue::factory()->create([
        'name' => 'Farm Bill',
        'status' => 'active',
        'priority_level' => 'Top Priority',
    ]);

    $topic = Topic::create(['name' => 'Agriculture']);

    $monitor = MediaMonitor::create([
        'name' => 'Farm Bill Coverage',
        'query' => '"Farm Bill" AND Congress',
        'monitor_type' => 'issue',
        'cadence' => 'hourly',
        'issue_id' => $issue->id,
        'topic_id' => $topic->id,
        'created_by' => $user->id,
        'is_active' => true,
    ]);

    $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>News</title>
    <item>
      <title>Congress advances farm bill package</title>
      <link>https://example.com/news/farm-bill?utm_source=rss</link>
      <description><![CDATA[Coverage of the latest farm bill negotiations.]]></description>
      <pubDate>Tue, 14 Jan 2026 10:00:00 GMT</pubDate>
      <source url="https://example.com">Example News</source>
    </item>
  </channel>
</rss>
XML;

    $article = <<<'HTML'
<html>
    <head>
        <meta property="og:title" content="Congress advances farm bill package" />
        <meta property="og:description" content="Lawmakers moved a farm bill package forward after a late-night negotiation." />
        <meta property="og:site_name" content="Example News" />
        <meta property="og:image" content="https://example.com/farm-bill.jpg" />
        <meta name="author" content="Casey Reporter" />
        <meta property="article:published_time" content="2026-01-14T10:00:00Z" />
    </head>
    <body>Article body</body>
</html>
HTML;

    Http::fake([
        'https://news.google.com/*' => Http::response($rss, 200, ['Content-Type' => 'application/rss+xml']),
        'https://example.com/news/farm-bill*' => Http::response($article, 200),
    ]);

    $this->artisan('press:monitor', [
        '--monitor' => $monitor->id,
        '--inline' => true,
        '--force' => true,
    ])->assertSuccessful();

    $this->artisan('press:monitor', [
        '--monitor' => $monitor->id,
        '--inline' => true,
        '--force' => true,
    ])->assertSuccessful();

    $clip = PressClip::with(['issues', 'topics'])->sole();
    $monitor->refresh();

    expect($clip->title)->toBe('Congress advances farm bill package');
    expect($clip->outlet_name)->toBe('Example News');
    expect($clip->journalist_name)->toBe('Casey Reporter');
    expect($clip->status)->toBe('pending_review');
    expect($clip->source)->toBe('web_search');
    expect($clip->created_by)->toBe($user->id);
    expect($clip->issues->pluck('id')->all())->toContain($issue->id);
    expect($clip->topics->pluck('id')->all())->toContain($topic->id);
    expect(PressClip::count())->toBe(1);
    expect($monitor->clips_found)->toBe(1);
    expect($monitor->last_checked_at)->not->toBeNull();
});
