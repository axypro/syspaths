<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\tests\errors;

use axy\syspaths\errors\PatternNotFound;

/**
 * coversDefaultClass axy\syspaths\errors\PatternNotFound
 */
class PatternNotFoundTest extends \PHPUnit_Framework_TestCase
{
    public function testError()
    {
        $e = new PatternNotFound('unknown');
        $this->assertSame('Field "unknown" is not exist in "SysPaths"', $e->getMessage());
        $this->assertSame('unknown', $e->getKey());
        $this->assertSame('SysPaths', $e->getContainer());
    }
}
