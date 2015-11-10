<?php

namespace axy\syspaths\tests\tst;

use axy\syspaths\Paths;

/**
 * @property-read string $pages
 * @property-read string $layouts
 * @property-read string $default
 * @property-read string $mails
 */
class TemplatesPaths extends Paths
{
    protected $patterns = [
        'pages' => 'pages',
        'layouts' => 'layouts',
        'default' => ':layouts:/default.twig',
    ];
}
