<?php

namespace Rulatir\Tree;

use Exception;
use PHPUnit\Framework\TestCase;
use Rulatir\Tree\Testing\RepositoryTestingCapability;

class GitRepositoryStory extends TestCase
{
    use RepositoryTestingCapability;

    public function testGit()
    {
        $this->assertDirectoryExists("{$this->tmpDir->getRoot()}/.git");
    }

    public function testGetChanges()
    {
        $root = $this->tmpDir->getRoot();
        $this->installRevision('modified');
        $o = new GitRepository($root);
        $changes = $o->getChanges(trim($this->gwc->run('rev-parse', ['HEAD'])));
        $this->assertNotEmpty($changes);
    }

    /** @throws Exception */
    protected function setUp() : void
    {
        parent::setUp();
        $this->setUpRepositoryTesting('initial');
    }

    protected function tearDown() : void
    {
        $this->tearDownRepositoryTesting();
        parent::tearDown();
    }

    protected function getStoryDirectory(): string
    {
        return __DIR__ . "/git-repository";
    }
}
