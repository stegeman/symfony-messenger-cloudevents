<?php

namespace Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents;

class DummyEvent
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name
    ) {}
}