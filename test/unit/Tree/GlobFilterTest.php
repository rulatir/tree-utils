<?php

namespace Rulatir\Tree;

use PHPUnit\Framework\TestCase;

class GlobFilterTest extends TestCase
{
   public function testFilter()
    {
        $o = new GlobFilter();
        foreach([
            ['foo/bar/*.txt'],
            ['foo/bar/*.hidden.txt', true],
            ['foo/deep/**/*.php'],
            ['**/*.hidden.php', true]
                ] as $ruleDef) $o->addRule(...$ruleDef);

        $files = [
            'foo/bar/something.txt'                         => true,
            'foo/bar/hidden.txt'                            => true,
            'foo/bar/something.hidden.txt'                  => false,
            'foo/something.txt'                             => false,
            'foo/deep/very/deep/indeed/index.php'           => true,
            'foo/deep/very/deep/indeed/index.hidden.php'    => false,
            'foo/deep/very/deep/indeed/hidden.php'          => true,
            'foo/deep/index.php'                            => true,
            'foo/deep/index.hidden.php'                     => false,
            'foo/deep/hidden.php'                           => true
        ];

        $expected = array_keys(array_filter($files));
        $input = array_keys($files);
        self::assertEqualsCanonicalizing(
            $expected,
            $o->filter($input)
        );
    }
}
