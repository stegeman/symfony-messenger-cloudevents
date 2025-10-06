<?php

namespace Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents\Normalizer\V1;

use CloudEvents\Serializers\Normalizers\V1\NormalizerInterface;
use CloudEvents\V1\CloudEventInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Normalizer;

class NormalizerTest extends TestCase
{
    #[Test]
    public function cloudEventIsNormalizedToArray(): void
    {
        $normalizerInput = $this->createCloudEventWithData();

        $normalizerOutput =  [
            'type' => 'message-name',
            'data' => '{"id": 12345}'
        ];

        $normalizer = $this->getNormalizer(
            $this->createNormalizerWithNormalizeCall($normalizerInput, $normalizerOutput),
            $this->createSerializerWithSerializeCall($normalizerOutput)
        );

        $result = $normalizer->normalize($this->createCloudEventWithData());

        self::assertTrue(is_array($result));
        self::assertSame(
            [
                'body' => '{"type":"message-name","data":"{\"id\": 12345}"}',
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ],
            $result
        );
    }

    private function getNormalizer(NormalizerInterface $normalizer, SerializerInterface $serializer): Normalizer
    {
        return new Normalizer($normalizer, $serializer);
    }

    private function createCloudEventWithData(): CloudEventInterface
    {
        $cloudEvent = $this->createV1CloudEventInterface();

        $cloudEvent->expects($this->any())
            ->method('getId')
            ->willReturn('12345');

        $cloudEvent->expects($this->any())
            ->method('getDataContentType')
            ->willReturn('application/json');

        return $cloudEvent;
    }

    private function createV1CloudEventInterface(): CloudEventInterface&MockObject
    {
        return $this->createMock(CloudEventInterface::class);
    }

    private function createNormalizerWithNormalizeCall(CloudEventInterface $normalizerInput, array $response): NormalizerInterface
    {
        $normalizer = $this->createSdkNormalizerInterface();

        $normalizer->expects($this->once())
            ->method('normalize')
            ->with($normalizerInput)
            ->willReturn($response);

        return $normalizer;
    }

    private function createSdkNormalizerInterface(): NormalizerInterface&MockObject
    {
        return $this->createMock(NormalizerInterface::class);
    }

    public function createSerializerWithSerializeCall(array $serializerInput): SerializerInterface
    {
        $serializer = $this->createSerializerInterface();

        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                $serializerInput,
                'json',
                (new SerializationContext())->setSerializeNull(true)
            )
            ->willReturn(json_encode($serializerInput));

        return $serializer;
    }

    private function createSerializerInterface(): SerializerInterface&MockObject
    {
        return $this->createMock(SerializerInterface::class);
    }
}