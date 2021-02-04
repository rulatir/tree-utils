<?php

namespace Rulatir\Tree;

use derhasi\tempdirectory\TempDirectory;
use Exception;
use GitWrapper\GitWorkingCopy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use GitWrapper\GitWrapper;

class GitRepositoryStory extends TestCase
{
    protected ?TempDirectory $tmpDir = null;
    protected ?string $storyDir = null;
    protected ?Filesystem $fs;
    protected ?GitWorkingCopy $gwc = null;

    public function testGit()
    {
        $this->assertDirectoryExists("{$this->tmpDir->getRoot()}/.git");
    }

    public function testGetChanges()
    {
        $root = $this->tmpDir->getRoot();
        $this->installRevision('modified');
        unlink("{$root}/will-be-renamed.txt");
        unlink("{$root}/will-be-renamed-and-a-little-modified.txt");
        unlink("{$root}/will-be-deleted.txt");
        $o = new GitRepository($root);
        $changes = $o->getChanges(trim($this->gwc->run('rev-parse', ['HEAD'])));
        $this->assertNotEmpty($changes);
    }

    /** @throws Exception */
    protected function setUp() : void
    {
        parent::setUp();
        $this->storyDir = __DIR__."/git-repository";
        $this->tmpDir = new TempDirectory('git-repository-story');
        chdir($this->tmpDir->getRoot());
        $this->fs = new Filesystem();
        $this->gwc = $this->gitInit();
        $this->installRevision('initial');
        $this->gitCommit("Initial revision");
    }

    protected function tearDown() : void
    {
        $this->tmpDir=null; //calls __destruct() quite eagerly, next name will be distinct anyway
        $this->storyDir = null;
        parent::tearDown();
    }

    protected function installRevision(string $revisionDir)
    {
        $this->fs->mirror(
            "{$this->storyDir}/{$revisionDir}",
            $this->tmpDir->getRoot(),
            null,
            ['overwrite' => true]
        );
    }

    protected function gitInit(): GitWorkingCopy
    {
        $wrapper = new GitWrapper();
        return $wrapper->init($this->tmpDir->getRoot(),[]);
    }

    protected function gitCommit(string $msg) : string
    {
        $this->gwc->add('.');
        $this->gwc->commit('Initial revision');
        return trim($this->gwc->run('rev-parse', ['HEAD']));
    }
}
