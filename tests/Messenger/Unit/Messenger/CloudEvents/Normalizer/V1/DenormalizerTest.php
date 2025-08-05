<?php

namespace Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents\Normalizer\V1;

use CloudEvents\V1\CloudEventInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Denormalizer;
use CloudEvents\Serializers\Normalizers\V1\DenormalizerInterface;
use Stegeman\Messenger\CloudEvents\Serializer\MessageRegistryInterface;
use Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents\DummyEvent;

class DenormalizerTest extends TestCase
{
    #[Test]
    public function  anArrayIsDenormalizedToCloudEvent(): void
    {
        $data = [
            'type' => 'dummy-event',
            'data' => [
                'id' => '100',
                'name' => 'foobar'
            ]
        ];
        $targetClass = DummyEvent::class;
        $message = new DummyEvent('100', 'foobar');
        $denormalizerInput = $data;
        $denormalizerInput['data'] = $message;

        $denormalizer = new Denormalizer(
            $this->createMessageRegistry('dummy-event', $targetClass),
            $this->createSerializerWithDeserializeCall(json_encode($data['data']), $targetClass, $message),
            $this->createDenormalizerWithDenormalizeCall($denormalizerInput, $message)
        );

        $cloudEvent = $denormalizer->denormalize($data);

        self::assertInstanceOf(CloudEventInterface::class, $cloudEvent);
        self::assertInstanceOf(DummyEvent::class, $cloudEvent->getData());
    }

    private function createDenormalizerWithDenormalizeCall(array $denormalizeInput, object $denormalizeResult): DenormalizerInterface
    {
        $denormalizer = $this->createSdkDenormalizerInterface();

        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($denormalizeInput)
            ->willReturn($this->createCloudEventWithData($denormalizeResult));

        return $denormalizer;
    }

    private function createSdkDenormalizerInterface(): DenormalizerInterface&MockObject
    {
        return $this->createMock(DenormalizerInterface::class);
    }

    private function createCloudEventWithData(object $data): CloudEventInterface
    {
        $cloudEvent = $this->createMock(CloudEventInterface::class);

        $cloudEvent->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        return $cloudEvent;
    }

    private function createSerializerWithDeserializeCall(string $data, string $type, object $result): SerializerInterface
    {
        $serializer = $this->createSerializerInterface();

        $serializer->expects($this->once())
            ->method('deserialize')
            ->with($data, $type, 'json')
            ->willReturn($result);

        return $serializer;
    }

    private function createSerializerInterface(): SerializerInterface&MockObject
    {
        return $this->createMock(SerializerInterface::class);
    }

    private function createMessageRegistry(string $type, string $messageClassName): MessageRegistryInterface
    {
        $messageRegistry = $this->createMock(MessageRegistryInterface::class);

        $messageRegistry->expects($this->any())
            ->method('getTypeForMessageClass')
            ->with($messageClassName)
            ->willReturn($type);

        $messageRegistry->expects($this->any())
            ->method('getMessageClassNameForType')
            ->with($type)
            ->willReturn($messageClassName);

        return $messageRegistry;
    }
}