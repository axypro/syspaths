<?php

namespace axy\syspaths\tests\tst;

use axy\syspaths\Paths;

/**
 * @property-read string $www
 * @property-read string $etc
 * @property-read string $images
 * @property-read \axy\syspaths\tests\tst\TemplatesPaths $templates
 * @property-read \axy\syspaths\Paths $dbs
 * @property-read string $full
 */
class TestPaths extends Paths
{
    protected $patterns = [
        'www' => 'www',
        'etc' => ':root:/etc',
        'images' => ':www:/i',
        'templates' => [
            'root' => 'templates',
            '__classname' => 'axy\syspaths\tests\tst\TemplatesPaths',
            'mails' => 'emails',
        ],
        'dbs' => [
            'root' => 'dbs',
            'sqlite' => 'db.sqlite',
        ],
        'full' => '/this/is/full',
    ];
}
