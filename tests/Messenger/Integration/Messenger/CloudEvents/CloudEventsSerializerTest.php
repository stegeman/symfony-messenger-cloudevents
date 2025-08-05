<?php

namespace Stegeman\Tests\Messenger\Integration\Messenger\CloudEvents;

use CloudEvents\Serializers\Normalizers\V1\Denormalizer as SdkDenormalizer;
use CloudEvents\Serializers\Normalizers\V1\Normalizer as SdkNormalizer;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidFactory;
use Stegeman\Messenger\CloudEvents\Converter\V1\EnvelopeConverter;
use Stegeman\Messenger\CloudEvents\Factory\CloudEventToMessageConverter;
use Stegeman\Messenger\CloudEvents\Factory\V1\CloudEventFactory;
use Stegeman\Messenger\CloudEvents\Normalizer\DenormalizerInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Denormalizer;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Normalizer;
use Stegeman\Messenger\CloudEvents\Serializer\CloudEventsSerializer;
use Stegeman\Messenger\CloudEvents\Serializer\MessageRegistryInterface;
use Stegeman\Messenger\CloudEvents\Serializer\UuidGenerator;
use Symfony\Component\Messenger\Envelope;

class CloudEventsSerializerTest extends TestCase
{
    #[Test]
    public function aCloudEventIsEncoded(): void
    {
        $cloudEventsSerializer = new CloudEventsSerializer(
            new EnvelopeConverter(
                $this->createMessageRegistry(),
                new UuidGenerator(
                    new UuidFactory()
                )
            ),
            new Normalizer(
                new SdkNormalizer()
            ),
            new Denormalizer(
                $this->createMessageRegistry(),
                SerializerBuilder::create()->build(),
                new SdkDenormalizer()
            ),
            SerializerBuilder::create()->build(),
            'application/json'
        );

        $serializedEvent = $cloudEventsSerializer->encode(new Envelope(new DummyEvent('100', 'lalala')));

        self::assertTrue((bool) json_decode($serializedEvent['body']));
    }

    #[Test]
    public function aCloudEventIsDecoded(): void
    {
        $cloudEventSerializer = new CloudEventsSerializer(
            new EnvelopeConverter(
                $this->createMessageRegistry(),
                new UuidGenerator(
                    new UuidFactory()
                )
            ),
            new Normalizer(
                new SdkNormalizer()
            ),
            new Denormalizer(
                $this->createMessageRegistry(),
                SerializerBuilder::create()->build(),
                new SdkDenormalizer(),
            ),
            SerializerBuilder::create()->build(),
            'application/json'
        );

        $input = [
            'specversion' => '1.0',
            'id' => 'd4576019-8919-42c6-ba46-4f82fcbfc94d',
            'source' => 'nl.stegeman.dummy-message',
            'type' => 'nl.stegeman.dummy-message',
            'datacontenttype' => 'application/json',
            'time' => '2025-07-14T13:07:00Z',
            'data' => [
                'id' => '100',
                'name' => 'lalala',
            ],
        ];

        $envelope = $cloudEventSerializer->decode($input);

        self::assertInstanceOf(Envelope::class, $envelope);
        self::assertInstanceOf(DummyEvent::class, $envelope->getMessage());
        self::assertSame('100', $envelope->getMessage()->getId());
        self::assertSame('lalala', $envelope->getMessage()->getName());
    }

    private function createMessageRegistry(): MessageRegistryInterface
    {
        return new class () implements MessageRegistryInterface {

            public function getTypeForMessageClass(string $messageClass): string
            {
                return 'nl.stegeman.dummy-message';
            }

            public function getMessageClassNameForType(string $name): string
            {
                return DummyEvent::class;
            }
        };
    }
}