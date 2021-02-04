<?php

namespace Rulatir\Tree;

use PHPUnit\Framework\TestCase;

class GitRepositoryTest extends TestCase
{
    public function testGetRoot()
    {
        $repository = new GitRepository(__DIR__);
        self::assertEquals(__DIR__, $repository->getRoot());
    }

}
