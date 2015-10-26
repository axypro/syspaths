<?php
/**
 * Specifying paths within the system
 *
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/syspaths/master/LICENSE MIT
 * @link https://github.com/axypro/syspaths repository
 * @link https://packagist.org/packages/axy/syspaths composer package
 * @uses PHP5.4+
 */

namespace axy\syspaths;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
