<?php

namespace Messenger\Unit\Messenger\CloudEvents\Serializer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stegeman\Messenger\CloudEvents\Serializer\MessageRegistry;
use Stegeman\Messenger\CloudEvents\Serializer\NoClassNameFoundForTypeException;
use Stegeman\Messenger\CloudEvents\Serializer\NoNameFoundForMessageException;

class MessageRegistryTest extends TestCase
{
    #[Test]
    public function theNameOfAMessageCanBeFoundByItsClassName(): void
    {
        $messageRegistry = new MessageRegistry();

        $messageRegistry->addMessage('name', stdClass::class);

        self::assertSame('name', $messageRegistry->getTypeForMessageClass(stdClass::class));
    }

    #[Test]
    public function anExceptionIsThrownIfNoNameCanBeFoundForGivenMessage(): void
    {
        self::expectException(NoNameFoundForMessageException::class);
        self::expectExceptionMessage('No name found for message of type stdClass');

        $messageRegistry = new MessageRegistry();

        $messageRegistry->getTypeForMessageClass(stdClass::class);
    }

    #[Test]
    public function aTypeForMessageClassIsRetrieved(): void
    {
        $messageRegistry = new MessageRegistry();

        $messageRegistry->addMessage('name', stdClass::class);

        self::assertSame(stdClass::class, $messageRegistry->getMessageClassNameForType('name'));
    }

    #[Test]
    public function anExceptionIsThrownIfNoClassNameCanBeFoundForGivenType(): void
    {
        self::expectException(NoClassNameFoundForTypeException::class);
        self::expectExceptionMessage('No classname found for message with name \'name\'');

        $messageRegistry = new MessageRegistry();

        $messageRegistry->getMessageClassNameForType('name');
    }
}