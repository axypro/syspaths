<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\errors;

use axy\errors\InvalidConfig;

/**
 * Patterns list is not defined
 */
final class RequirePatterns extends InvalidConfig
{
    /**
     * The constructor
     */
    public function __construct()
    {
        parent::__construct('SysPaths', 'Require override $this->patterns or specify argument $patterns');
    }
}
