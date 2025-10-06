<?php

namespace Stegeman\Messenger\CloudEvents\Normalizer\V1;

use CloudEvents\CloudEventInterface;
use CloudEvents\Serializers\Normalizers\V1\NormalizerInterface as SdkNormalizerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\NormalizerInterface;

readonly class Normalizer implements NormalizerInterface
{
    public function __construct(
        private SdkNormalizerInterface $normalizer,
        private SerializerInterface $serializer
    ) {}

    public function normalize(CloudEventInterface $cloudEvent): array
    {
        $normalizedEvent = $this->normalizer->normalize($cloudEvent, true);

        $serializedNormalizedEvent = $this->serializer->serialize(
            $normalizedEvent,
            'json',
            (new SerializationContext())->setSerializeNull(true)
        );

        return  [
            'body' => $serializedNormalizedEvent,
            'headers' => ['Content-Type' => $cloudEvent->getDataContentType()]
        ];
    }
}