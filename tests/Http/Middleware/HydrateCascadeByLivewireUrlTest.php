<?php

namespace MarcoRieser\Livewire\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Cascade;

class HydrateCascadeByLivewireUrlTest extends TestCase
{
    #[Test]
    public function it_hydrates_the_cascade_from_the_snapshot_path_and_method()
    {
        $this->handle($this->livewireRequest(['path' => 'about', 'method' => 'POST']));

        $this->assertSame('http://localhost/about', Cascade::get('current_url'));
    }

    #[Test]
    public function it_hydrates_the_cascade_when_the_snapshot_path_and_method_are_null()
    {
        $this->handle($this->livewireRequest(['path' => null, 'method' => null]));

        $this->assertSame('http://localhost', Cascade::get('current_url'));
    }

    private function handle(Request $request): void
    {
        (new HydrateCascadeByLivewireUrl)->handle($request, fn () => new Response);

        Cascade::hydrate();
    }

    private function livewireRequest(array $memo): Request
    {
        $snapshot = json_encode([
            'data' => [],
            'memo' => ['id' => 'abc', 'name' => 'test', ...$memo],
            'checksum' => 'irrelevant',
        ]);

        $request = Request::create('/livewire/update', 'POST', [
            'components' => [['snapshot' => $snapshot, 'updates' => [], 'calls' => []]],
        ]);
        $request->headers->set('X-Livewire', 'true');

        $this->app->instance('request', $request);

        return $request;
    }
}
