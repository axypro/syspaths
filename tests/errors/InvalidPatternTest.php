<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\tests\errors;

use axy\syspaths\errors\InvalidPattern;

/**
 * coversDefaultClass axy\syspaths\errors\InvalidPattern
 */
class InvalidPatternTest extends \PHPUnit_Framework_TestCase
{
    public function testError()
    {
        $e = new InvalidPattern(':key', '?:');
        $this->assertSame('Path pattern ":key" is invalid: ?:', $e->getMessage());
        $this->assertSame(':key', $e->getVarName());
        $this->assertSame('?:', $e->getErrorMessage());
    }
}
