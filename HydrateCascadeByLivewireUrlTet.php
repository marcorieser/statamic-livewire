<?php

use Illuminate\Http\Request;
use Livewire\Livewire;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Cascade;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Symfony\Component\HttpFoundation\Response;

class HydrateCascadeByLivewireUrlTet extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function cascade_is_hydrated_from_livewire_url_when_entry_exists()
    {
        $entry = Entry::findByUri('/about', Site::current());

        $this->runMiddleware('http://example.com/about');

        $this->assertSame($entry->id(), Cascade::content()->id());

        Cascade::hydrate();

        $this->assertSame('http://example.com/about', Cascade::get('current_url'));
        $this->assertSame($entry->id(), Cascade::get('page')->id());
    }

    #[Test]
    public function cascade_does_not_set_content_when_no_entry_matches()
    {
        $this->runMiddleware('http://example.com/missing');

        $this->assertNull(Cascade::content());

        Cascade::hydrate();

        $this->assertSame('http://example.com/missing', Cascade::get('current_url'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Site::setSiteValue('default', 'url', 'http://example.com');
        Site::setCurrent('default');

        Collection::make('pages')->routes('/{slug}')->save();

        Entry::make()
            ->collection('pages')
            ->slug('about')
            ->locale('default')
            ->data(['title' => 'About'])
            ->save();
    }

    protected function runMiddleware(string $originalUrl): void
    {
        Livewire::shouldReceive('originalUrl')->andReturn($originalUrl);
        Livewire::shouldReceive('originalMethod')->andReturn('GET');

        $request = Request::create('http://example.com/livewire/update', 'POST');
        $this->app->instance('request', $request);

        (new HydrateCascadeByLivewireUrl)->handle($request, fn () => new Response);
    }
}
