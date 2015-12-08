<?php

namespace axy\syspaths\tests;

use axy\syspaths\Paths;
use axy\syspaths\tests\tst\TestPaths;

/**
 * coversDefaultClass axy\syspaths\Path
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PathsTest extends \PHPUnit_Framework_TestCase
{
    public function testIsset()
    {
        $paths = new TestPaths('/var/htdocs');
        $this->assertTrue(isset($paths->root));
        $this->assertTrue(isset($paths->etc));
        $this->assertTrue(isset($paths->www));
        $this->assertTrue(isset($paths->templates));
        $this->assertTrue(isset($paths->dbs));
        $this->assertTrue(isset($paths->full));
        $this->assertFalse(isset($paths->unknown));
        $this->assertFalse(isset($paths->mails));
    }

    public function testGet()
    {
        $paths = new TestPaths('/var/htdocs');
        $this->assertSame('/var/htdocs', $paths->root);
        $this->assertSame('/var/htdocs/', $paths.'/');
        $this->assertSame('/var/htdocs/www', $paths->www);
        $this->assertSame('/var/htdocs/etc', $paths->etc);
        $this->assertSame('/var/htdocs/www/i', $paths->images);
        $this->assertSame('/this/is/full', $paths->full);
        $templates = $paths->templates;
        $this->assertInstanceOf('axy\syspaths\tests\tst\TemplatesPaths', $templates);
        $this->assertSame($templates, $paths->templates);
        $this->assertSame('/var/htdocs/templates/', $templates . '/');
        $this->assertSame('/var/htdocs/templates', $templates->root);
        $this->assertSame('/var/htdocs/templates/emails', $templates->mails);
        $this->assertSame('/var/htdocs/templates/layouts/default.twig', $templates->default);
        $this->assertSame('/var/htdocs/templates/layouts/default.twig', $paths->templates->default);
        $dbs = $paths->dbs;
        $this->assertInstanceOf('axy\syspaths\Paths', $dbs);
        $this->assertSame($dbs, $paths->dbs);
        $this->assertSame('/var/htdocs/dbs/', $dbs . '/');
        $this->assertSame('/var/htdocs/dbs', $dbs->root);
        $this->assertSame('/var/htdocs/dbs/db.sqlite', $dbs->sqlite);
        $this->assertSame('/var/htdocs/dbs/db.sqlite', $paths->dbs->sqlite);
        $message = 'Field "dbs.unknown" is not exist in "SysPaths"';
        $this->setExpectedException('axy\syspaths\errors\PatternNotFound', $message);
        return $paths->dbs->unknown;
    }

    public function testPatterns()
    {
        $patterns = [
            'www' => 'htdocs',
            'robots' => ':www:/robots.txt',
        ];
        $paths = new Paths('/var/htdocs', $patterns);
        $this->assertTrue(isset($paths->www));
        $this->assertTrue(isset($paths->robots));
        $this->assertFalse(isset($paths->templates));
        $this->assertFalse(isset($paths->unknown));
        $this->assertEquals('/var/htdocs/htdocs', $paths->www);
        $this->assertEquals('/var/htdocs/htdocs/robots.txt', $paths->robots);
        $this->assertSame(2, count($paths));
    }

    public function testPatternsMerge()
    {
        $patterns = [
            'www' => 'htdocs',
            'robots' => ':www:/robots.txt',
        ];
        $paths = new TestPaths('/var/htdocs', $patterns);
        $this->assertTrue(isset($paths->www));
        $this->assertTrue(isset($paths->robots));
        $this->assertTrue(isset($paths->templates));
        $this->assertFalse(isset($paths->unknown));
        $this->assertEquals('/var/htdocs/htdocs', $paths->www);
        $this->assertEquals('/var/htdocs/htdocs/robots.txt', $paths->robots);
        $this->assertEquals('/var/htdocs/templates', (string)$paths->templates);
        $this->assertSame(7, count($paths));
    }

    /**
     * @param string $pattern
     * @param bool $real
     * @param string $expected
     * @dataProvider providerCreate
     */
    public function testCreate($pattern, $real, $expected)
    {
        $patterns = [
            'tst' => [
                'root' => 'tst',
                'test' => 'TestPaths.php',
                'unk' => 'Unknown.php',
            ],
            'errors' => 'errors',
        ];
        $paths = new Paths(__DIR__, $patterns);
        $actual = $paths->create($pattern, $real);
        $invoke = $paths($pattern, $real);
        $this->assertSame($actual, $invoke);
        if ($real && $actual && substr(strtolower(PHP_OS), 0, 3) === 'win') {
            $actual = str_replace('\\', '/', $actual);
            $expected = str_replace('\\', '/', $expected);
        }
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $invoke);
    }

    /**
     * @return array
     */
    public function providerCreate()
    {
        return [
            [
                ':errors:/InvalidPatternTest.php',
                false,
                __DIR__.'/errors/InvalidPatternTest.php',
            ],
            [
                ':errors:/InvalidPatternTest.php',
                true,
                __DIR__.'/errors/InvalidPatternTest.php',
            ],
            [
                ':errors:/Unknown.php',
                false,
                __DIR__.'/errors/Unknown.php',
            ],
            [
                ':errors:/Unknown.php',
                true,
                null,
            ],
        ];
    }

    /**
     * @expectedException \axy\syspaths\errors\RequirePatterns
     */
    public function testPatternsRequire()
    {
        return new Paths('/var/htdocs');
    }

    public function testArrayAccess()
    {
        $paths = new TestPaths('/var/htdocs');
        $this->assertTrue(isset($paths['root']));
        $this->assertTrue(isset($paths['www']));
        $this->assertTrue(isset($paths['templates']));
        $this->assertFalse(isset($paths['unknown']));
        $this->assertSame('/var/htdocs/templates/layouts/default.twig', $paths['templates']['default']);
    }

    public function testCountable()
    {
        $paths = new TestPaths('/var/htdocs');
        $this->assertSame(6, count($paths));
    }

    public function testIterator()
    {
        $patterns = [
            'one' => 'one',
            'two' => 'two',
        ];
        $paths = new Paths('/dir', $patterns);
        $expected = [
            'one' => '/dir/one',
            'two' => '/dir/two',
        ];
        $this->assertSame($expected, iterator_to_array($paths));
    }

    /**
     * @param callable $callback
     * @dataProvider providerReadOnly
     * @expectedException \axy\errors\ContainerReadOnly
     */
    public function testReadOnly(callable $callback)
    {
        $paths = new TestPaths('/var/htdocs');
        $callback($paths);
    }

    /**
     * @return array
     */
    public function providerReadOnly()
    {
        return [
            '__set' => [function ($paths) {
                $paths->a = 1;
            }],
            '__unset' => [function ($paths) {
                unset($paths->b);
            }],
            'offsetSet' => [function ($paths) {
                $paths['c'] = 2;
            }],
            'offsetUnset' => [function ($paths) {
                unset($paths['d']);
            }],
        ];
    }

    /**
     * @dataProvider providerInvalidPattern
     * @param array $patterns
     * @expectedException \axy\syspaths\errors\InvalidPattern
     * @return mixed
     */
    public function testInvalidPattern($patterns)
    {
        $paths = new Paths('/var/htdocs', $patterns);
        return $paths->__get('key');
    }

    /**
     * @return array
     */
    public function providerInvalidPattern()
    {
        return [
            [
                [
                    'len' => 'len',
                    'key' => ':len',
                ],
            ],
            [
                [
                    'key' => [
                        'sub' => 'sub/key',
                    ],
                ],
            ],
            [
                [
                    'key' => [
                        'root' => 'x',
                        '__classname' => 'axy\syspaths\NotExists',
                        'sub' => 'sub/key',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerPatternsNotFound
     * @param array $patterns
     * @expectedException \axy\syspaths\errors\PatternNotFound
     * @return mixed
     */
    public function testPatternNotFound($patterns)
    {
        $paths = new Paths('/var/htdocs', $patterns);
        return $paths->__get('key');
    }

    /**
     * @return array
     */
    public function providerPatternsNotFound()
    {
        return [
            [
                [
                    'key' => ':len:/x',
                ],
            ],
            [
                [
                    'len' => 'len',
                    'key' => ':len.sub:/x',
                ],
            ],
        ];
    }

    public function testMultiKey()
    {
        $patterns = [
            'x' => ':templates.default:?x=1',
            'sub' => [
                'root' => ':templates.layouts:/admin',
                'one' => 'one/three',
                'two' => ':one:/two',
            ],
        ];
        $paths = new TestPaths('/var/htdocs', $patterns);
        $this->assertSame('/var/htdocs/templates/layouts/default.twig?x=1', $paths->x);
        $expected = '/var/htdocs/templates/layouts/admin/one/three/two/four/:five:/';
        $this->assertSame($expected, $paths->create(':sub.two:/four/:five:/'));
    }
}
