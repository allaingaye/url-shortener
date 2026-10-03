<?php

// tests/Feature/Api/QueuedAnalyticsTest.php

use App\Jobs\RecordClick;
use App\Models\Click;
use App\Models\Url;
use Illuminate\Support\Facades\Queue;

it('dispatches RecordClick when a short URL is visited', function () {
    Queue::fake();

    $url = Url::factory()->create(['short_code' => 'queued']);

    $this->get('/queued')->assertRedirect();

    Queue::assertPushed(RecordClick::class, function (RecordClick $job) use ($url) {
        return $job->urlId === $url->id
            && $job->userAgent !== '';
    });
});

it('records a click when the job is executed', function () {
    $url = Url::factory()->create(['short_code' => 'runs']);

    RecordClick::dispatchSync(
        $url->id,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
        'https://google.com/',
    );

    expect(Click::count())->toBe(1);

    $click = Click::first();
    expect($click->url_id)->toBe($url->id);
    expect($click->browser)->toBe('Chrome');
    expect($click->referer)->toBe('https://google.com/');
});

it('does not fail when the URL was deleted before the job runs', function () {
    $url = Url::factory()->create();
    $id = $url->id;
    $url->delete();

    RecordClick::dispatchSync($id, '127.0.0.1', 'test-agent');

    expect(Click::count())->toBe(0);
});
