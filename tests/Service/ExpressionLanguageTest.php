<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 */
class ExpressionLanguageTest extends TestCase
{
    public function testRegisterFunctions(): void
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'The function "constant" does not exist around position 1 for expression `constant("PHP_VERSION ")`.'
        );
        $expressionLanguage->evaluate('constant("PHP_VERSION ")');
    }
}
