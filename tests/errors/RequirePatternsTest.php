<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\tests\errors;

use axy\syspaths\errors\RequirePatterns;

/**
 * coversDefaultClass axy\syspaths\errors\RequirePatterns
 */
class RequirePatternsTest extends \PHPUnit_Framework_TestCase
{
    public function testError()
    {
        $e = new RequirePatterns();
        $m = 'SysPaths has an invalid format: "Require override $this->patterns or specify argument $patterns"';
        $this->assertSame($m, $e->getMessage());
        $this->assertSame('SysPaths', $e->getConfigName());
        $this->assertSame('Require override $this->patterns or specify argument $patterns', $e->getErrorMessage());
    }
}
