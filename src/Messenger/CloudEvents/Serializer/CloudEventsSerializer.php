<?php

namespace Stegeman\Messenger\CloudEvents\Serializer;

use Stegeman\Messenger\CloudEvents\Converter\EnvelopeConverterInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\DenormalizerInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\NormalizerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use JMS\Serializer;

readonly class CloudEventsSerializer implements SerializerInterface
{
    public function __construct(
        private EnvelopeConverterInterface $envelopeConverter,
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer,
        private Serializer\SerializerInterface $serializer,
        private string $format
    ) {}

    public function decode(array $encodedCloudEvent): Envelope
    {
        $cloudEvent = $this->denormalizer->denormalize($encodedCloudEvent);

        return $this->envelopeConverter->fromCloudEvent($cloudEvent);
    }

    public function encode(Envelope $envelope): array
    {
        $cloudEvent = $this->envelopeConverter->toCloudEvent($envelope);

        $normalizedEvent = $this->normalizer->normalize($cloudEvent);

        return [
            'body' => $this->serializer->serialize(
                $normalizedEvent,
                $this->format === 'application/json' ? 'json' : $this->format
            ),
            'headers' => ['Content-Type' => $this->format]
        ];
    }
}
