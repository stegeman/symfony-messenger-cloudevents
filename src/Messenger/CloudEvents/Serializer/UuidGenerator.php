<?php

namespace Stegeman\Messenger\CloudEvents\Serializer;

use Ramsey\Uuid\UuidFactoryInterface;
use Symfony\Component\Messenger\Envelope;

readonly class UuidGenerator implements IdGeneratorInterface
{
    public function __construct(private UuidFactoryInterface $uuidFactory) {}

    public function generate(Envelope $envelope): string
    {
        return $this->uuidFactory->uuid4()->toString();
    }
}