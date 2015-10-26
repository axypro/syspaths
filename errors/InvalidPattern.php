<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths\errors;

use axy\errors\NotValid;

/**
 * A pattern is invalid
 *
 * $paths->create(':key); // require the ending ":"
 */
final class InvalidPattern extends NotValid
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Path pattern "{{ varName }}" is invalid: {{ errorMessage }}';

    /**
     * The constructor
     *
     * @param string $pattern [optional]
     * @param string $errorMessage [optional]
     */
    public function __construct($pattern = null, $errorMessage = null)
    {
        parent::__construct($pattern, $errorMessage);
    }
}
