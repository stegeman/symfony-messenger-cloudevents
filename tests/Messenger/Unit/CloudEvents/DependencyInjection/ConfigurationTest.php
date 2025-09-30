<?php

namespace Stegeman\Tests\Messenger\Unit\CloudEvents\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stegeman\Messenger\CloudEvents\DependencyInjection\Configuration;

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
                    ]
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
                     'registry' => []
             ]
         );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
