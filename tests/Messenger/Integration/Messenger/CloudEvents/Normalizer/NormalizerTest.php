<?php

namespace Stegeman\Tests\Messenger\Integration\Messenger\CloudEvents\Normalizer;

use CloudEvents\Serializers\Normalizers\V1\Normalizer as SdkNormalizer;
use CloudEvents\V1\CloudEvent;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Normalizer;
use Stegeman\Tests\Messenger\Unit\Messenger\CloudEvents\DummyEvent;

class NormalizerTest extends TestCase
{
    #[Test]
    public function aCloudEventIsNormalized(): void
    {
        $normalizer = $this->getNormalizer();

        $result = $normalizer->normalize(
            new CloudEvent(
                '100',
                'name-of-producer',
                'nl.stegeman.dummy-event-name',
                new DummyEvent('dummy-event-id', 'dummy-event-name'),
                'application/json',
                'v1.0',
                "subject",
                $time = new \DateTimeImmutable(),
                []
            )
        );

        $result = json_decode($result['body'], true);
        foreach(['specversion', 'id', 'source', 'type', 'datacontenttype', 'dataschema', 'subject', 'time', 'data'] as $key)
        {
            self::assertArrayHasKey($key, $result);
        }
    }

    #[Test]
    public function nullValuesAreAlsoSerialized(): void
    {
        $normalizer = $this->getNormalizer();

        $result = $normalizer->normalize(
            new CloudEvent(
                '100',
                'name-of-producer',
                'nl.stegeman.dummy-event-name',
                new DummyEvent('dummy-event-id', null),
                'application/json',
                'v1.0',
                "subject",
                $time = new \DateTimeImmutable(),
            )
        );

        self::assertMatchesRegularExpression('/"name":null/', $result['body']);
    }

    private function getNormalizer(): Normalizer
    {
        return new Normalizer(
            new SdkNormalizer(),
            SerializerBuilder::create()->build()
        );
    }
}