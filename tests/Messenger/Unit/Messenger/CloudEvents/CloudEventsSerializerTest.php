<?php

namespace Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents;

use CloudEvents\CloudEventInterface;
use CloudEvents\V1\CloudEventTrait;
use DateTimeImmutable;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\Converter\EnvelopeConverterInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\DenormalizerInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\NormalizerInterface;
use Stegeman\Messenger\CloudEvents\Serializer\CloudEventsSerializer;
use Symfony\Component\Messenger\Envelope;

class CloudEventsSerializerTest extends TestCase
{
    #[Test]
    public function anEventIsSerializedInCloudEventsFormat(): void
    {
        $dummyEvent = new DummyEvent(
            'id',
            'name'
        );
        $envelope = new Envelope($dummyEvent);

        $serializer = new CloudEventsSerializer(
            $this->createEnvelopeConverterInterface(),
            normalizer: $this->createNormalizerWithNormalizeCall(),
            denormalizer: $this->createDenormalizerInterface(),
            serializer: $this->createSerializerWithSerializeCall(),
            format: 'application/json'
        );

        $encodedEnvelope = $serializer->encode($envelope);

        self::assertSame(
            [
                'body' => '{ "just-an-array": "not the responsibility of this class"}',
                'headers' => ['Content-Type' => 'application/json']
            ],
            $encodedEnvelope
        );
    }

    #[Test]
    public function aCloudEventsFormatIsDeserializedToEvent(): void
    {
        $serializer = new CloudEventsSerializer(
            $this->createEnvelopeConverterWithFromCloudEventCall(new Envelope(new DummyEvent('1', 'name'))),
            normalizer: $this->createNormalizerInterface(),
            denormalizer: $this->createDenormalizerWithDenormalizeCall(),
            serializer: $this->createSerializerInterface(),
            format: 'application/json'
        );

        $envelope = $serializer->decode(
            [
                'body' => '{ "just-an-array": "responsibility for decoding the body to an object is defered to dependency, so not relevant"}',
                'headers' => ['Content-Type' => 'application/json']
            ]
        );

        self::assertInstanceOf(Envelope::class, $envelope);
    }

    private function createCloudEventInterface(): CloudEventInterface
    {
        return new class(
            id: 'f1aef6eabe3b67bbb9eb00e287d66650',
            source: 'source',
            type: 'type',
            dataContentType: 'application/json',
            data: new DummyEvent(id: 'id', name: 'name'),
            time: new DateTimeImmutable(),
        ) implements CloudEventInterface {
            use CloudEventTrait;
        };
    }

    private function createNormalizerWithNormalizeCall(): NormalizerInterface
    {
        $normalizer = $this->createNormalizerInterface();

        $normalizer->expects($this->once())
            ->method('normalize')
            ->with($this->isInstanceOf(CloudEventInterface::class))
            ->willReturn(['just-an-array' => 'not the responsibility of this class']);

        return $normalizer;
    }

    private function createNormalizerInterface(): NormalizerInterface&MockObject
    {
        return $this->createMock(NormalizerInterface::class);
    }

    private function createDenormalizerWithDenormalizeCall(): DenormalizerInterface
    {
        $denormalizer = $this->createDenormalizerInterface();

        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                [
                    'body' => '{ "just-an-array": "responsibility for decoding the body to an object is defered to dependency, so not relevant"}',
                    'headers' => ['Content-Type' => 'application/json']
                ]
            )
            ->willReturn($this->createCloudEventInterface());

        return $denormalizer;
    }

    private function createDenormalizerInterface(): DenormalizerInterface&MockObject
    {
        return $this->createMock(DenormalizerInterface::class);
    }

    private function createSerializerWithSerializeCall(): SerializerInterface
    {
        $serializer = $this->createSerializerInterface();

        $serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{ "just-an-array": "not the responsibility of this class"}');

        return $serializer;
    }

    private function createSerializerInterface(): SerializerInterface&MockObject
    {
        return $this->createMock(SerializerInterface::class);
    }

    private function createEnvelopeConverterWithFromCloudEventCall(Envelope $fromCloudEventResult): EnvelopeConverterInterface
    {
        $envelopeConverter = $this->createEnvelopeConverterInterface();

        $envelopeConverter->expects($this->once())
            ->method('fromCloudEvent')
            ->willReturn($fromCloudEventResult);

        return $envelopeConverter;
    }

    private function createEnvelopeConverterInterface(): EnvelopeConverterInterface&MockObject
    {
        return $this->createMock(EnvelopeConverterInterface::class);
    }
}