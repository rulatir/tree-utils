<?php

namespace Rulatir\Tree\Testing;
use derhasi\tempdirectory\TempDirectory;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Filesystem;

trait RepositoryTestingCapability
{

    protected ?TempDirectory $tmpDir = null;
    protected ?string $storyDir = null;
    protected ?Filesystem $fs;
    protected ?GitWorkingCopy $gwc = null;

    protected function installRevision(string $revisionDir)
    {
        $rootDir = $this->tmpDir->getRoot();
        $revisionDir = "{$this->storyDir}/{$revisionDir}";

        $gitignore_dist = "{$revisionDir}/.gitignore.dist";
        $have_gitignore_dist = is_file($gitignore_dist);

        $delete_lst = "{$revisionDir}/.delete.lst";
        $have_delete_lst = is_file($delete_lst);

        $gitignore = "{$rootDir}/.gitignore";
        $had_gitignore = is_file($gitignore);

        if ($have_gitignore_dist && $had_gitignore) {
            unlink("{$rootDir}/.gitignore");
        }

        if ($have_delete_lst) {
            foreach(
                array_filter(array_map('trim',explode("\n", trim(file_get_contents("{$revisionDir}/.delete.lst")))))
                as $toDelete
            )
                unlink("{$rootDir}/{$toDelete}");
        }

        $this->fs->mirror($revisionDir, $rootDir, null, ['override' => true]);

        if ($have_gitignore_dist) {
            rename("{$rootDir}/.gitignore.dist", ".gitignore");
        }
        if ($have_delete_lst) {
            unlink("{$rootDir}/.delete.lst");
        }
    }

    protected function gitInit(): GitWorkingCopy
    {
        $wrapper = new GitWrapper();
        return $wrapper->init($this->tmpDir->getRoot(), []);
    }

    protected function gitCommit(string $msg): string
    {
        $this->gwc->add('.');
        $this->gwc->commit($msg);
        return trim($this->gwc->run('rev-parse', ['HEAD']));
    }

    protected function setUpRepositoryTesting($initialStateDir): void
    {
        $this->storyDir = $this->getStoryDirectory();
        $this->tmpDir = new TempDirectory('git-repository-story');
        chdir($this->tmpDir->getRoot());
        $this->fs = new Filesystem();
        $this->gwc = $this->gitInit();
        $this->installRevision('initial');
        $this->gitCommit("Initial revision");
    }

    protected function tearDownRepositoryTesting(): void
    {
        $this->tmpDir = null; //calls __destruct() quite eagerly, next name will be distinct anyway
        $this->storyDir = null;
    }

    protected abstract function getStoryDirectory() : string;
}