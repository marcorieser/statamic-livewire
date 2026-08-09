<?php

namespace MarcoRieser\Livewire\Tests\Hooks;

use Illuminate\Support\Collection;
use MarcoRieser\Livewire\Attributes\Cascade;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CascadeAttributeTest extends TestCase
{
    #[Test]
    public function cascade_path_finds_id_in_flat_array()
    {
        $cascade = new Cascade;

        $data = [
            'id' => 'target',
            'title' => 'Found',
        ];

        $result = $this->invokeCascadePath($cascade, $data, 'target');

        $this->assertSame('', $result);
    }

    #[Test]
    public function cascade_path_finds_id_in_nested_array()
    {
        $cascade = new Cascade;

        $data = [
            'id' => 'page-1',
            'sections' => [
                [
                    'id' => 'section-abc',
                    'type' => 'hero',
                ],
            ],
        ];

        $result = $this->invokeCascadePath($cascade, $data, 'section-abc');

        $this->assertSame('sections.0', $result);
    }

    #[Test]
    public function cascade_path_finds_id_in_deeply_nested_array()
    {
        $cascade = new Cascade;

        $data = [
            'id' => 'page-1',
            'sections' => [
                [
                    'id' => 'section-1',
                    'blocks' => [
                        [
                            'id' => 'block-deep',
                            'content' => 'Nested',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->invokeCascadePath($cascade, $data, 'block-deep');

        $this->assertSame('sections.0.blocks.0', $result);
    }

    #[Test]
    public function cascade_path_returns_empty_string_when_not_found()
    {
        $cascade = new Cascade;

        $data = [
            'id' => 'page-1',
            'sections' => [],
        ];

        $result = $this->invokeCascadePath($cascade, $data, 'nonexistent');

        $this->assertSame('', $result);
    }

    #[Test]
    public function cascade_path_handles_arrayable_data()
    {
        $cascade = new Cascade;

        $data = new Collection([
            'id' => 'page-1',
            'sections' => new Collection([
                new Collection([
                    'id' => 'section-abc',
                    'type' => 'hero',
                ]),
            ]),
        ]);

        $result = $this->invokeCascadePath($cascade, $data, 'section-abc');

        $this->assertSame('sections.0', $result);
    }

    #[Test]
    public function cascade_path_finds_first_match_among_siblings()
    {
        $cascade = new Cascade;

        $data = [
            'sections' => [
                ['id' => 'first', 'type' => 'a'],
                ['id' => 'second', 'type' => 'b'],
                ['id' => 'third', 'type' => 'c'],
            ],
        ];

        $result = $this->invokeCascadePath($cascade, $data, 'second');

        $this->assertSame('sections.1', $result);
    }

    protected function invokeCascadePath(Cascade $cascade, $data, string $value): string
    {
        $method = new \ReflectionMethod($cascade, 'cascadePath');

        return $method->invoke($cascade, $data, $value);
    }
}
