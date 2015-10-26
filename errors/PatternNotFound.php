<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\errors;

use axy\errors\FieldNotExist;

/**
 * A patterns not found
 *
 * $paths->unknown; // Field "unknown" is not exist in "SysPaths"'
 */
final class PatternNotFound extends FieldNotExist
{
    /**
     * The constructor
     *
     * @param string $key
     */
    public function __construct($key = null)
    {
        parent::__construct($key, 'SysPaths');
    }
}
