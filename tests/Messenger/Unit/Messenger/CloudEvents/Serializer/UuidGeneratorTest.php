<?php

namespace Messenger\Unit\Messenger\CloudEvents\Serializer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Stegeman\Messenger\CloudEvents\Serializer\UuidGenerator;
use Symfony\Component\Messenger\Envelope;

class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function aUuidIsGenerated(): void
    {
        $UUIDGenerator = $this->getUUIDGenerator(
            $this->createUuidFactoryWithUuid4Call('69e1e5c8-78ff-4e2d-896b-5520754d5721')
        );

        $uuid = $UUIDGenerator->generate(new Envelope(new \stdClass()));

        self::assertSame('69e1e5c8-78ff-4e2d-896b-5520754d5721', $uuid);
    }

    private function getUUIDGenerator(UuidFactoryInterface $uuidFactory): UuidGenerator
    {
        return new UuidGenerator($uuidFactory);
    }

    private function createUuidFactoryWithUuid4Call(string $generatedUuid): UuidFactoryInterface
    {
        $uuidFactory = $this->createUuidFactoryInterface();

        $uuidFactory->expects($this->once())
            ->method('uuid4')
            ->willReturn(Uuid::fromString($generatedUuid));

        return $uuidFactory;
    }

    private function createUuidFactoryInterface(): UuidFactoryInterface&MockObject
    {
        return $this->createMock(UuidFactoryInterface::class);
    }
}