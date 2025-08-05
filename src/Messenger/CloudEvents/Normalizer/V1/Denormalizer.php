<?php

namespace Stegeman\Messenger\CloudEvents\Normalizer\V1;

use CloudEvents\CloudEventInterface;
use CloudEvents\Serializers\Normalizers\V1\DenormalizerInterface as SdkDenormalizerInterface;
use JMS\Serializer\SerializerInterface;
use Stegeman\Messenger\CloudEvents\Normalizer\DenormalizerInterface;
use Stegeman\Messenger\CloudEvents\Serializer\MessageRegistryInterface;

readonly class Denormalizer implements DenormalizerInterface
{
    public function __construct(
        private MessageRegistryInterface $messageRegistry,
        private SerializerInterface $serializer,
        private SdkDenormalizerInterface $sdkDenormalizer
    ) {}

    public function denormalize(array $normalizedEvent): CloudEventInterface
    {
        $message = $this->serializer->deserialize(
            json_encode($normalizedEvent['data']),
            $this->messageRegistry->getMessageClassNameForType($normalizedEvent['type']),
            'json'
        );

        $normalizedEvent['data'] = $message;

        return $this->sdkDenormalizer->denormalize($normalizedEvent);
    }
}