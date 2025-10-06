<?php

namespace Stegeman\Tests\Messenger\Unit\CloudEvents\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\DependencyInjection\Configuration;
use Stegeman\Messenger\CloudEvents\Normalizer\V1\Normalizer;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    #[Test]
    public function nestedValuesCanBeGiven(): void
    {
        $this->assertConfigurationIsValid(
            [
                'cloud_events' => [
                    'registry' => [
                        [
                            'name' => 'message-name',
                            'className' => \stdClass::class
                        ],
                        [
                            'name' => 'message-name-2',
                            'className' => \DateTime::class
                        ],
                    ],
                    'normalizer_service_id' => Normalizer::class
                ]
            ]
        );
    }

    #[Test]
    public function configurationIsMarkedInvalidIfGivenClassNameDoesNotExist(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                'cloud_events' => [
                    'registry' => [
                        [
                            'name' => 'message-name',
                            'className' => \stdClass::class
                        ],
                        [
                            'name' => 'message-name-2',
                            'className' => 'Non\\Existing\\Class'
                        ],
                    ]
                ]
            ]
        );
    }

    #[Test]
    public function defaultRegistryValueIsSetToEmptyArray(): void
    {
         $this->assertProcessedConfigurationEquals(
             [
                 'cloud_events' => []
             ],
             [
                 'registry' => [],
                 'normalizer_service_id' => Normalizer::class
             ]
         );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
