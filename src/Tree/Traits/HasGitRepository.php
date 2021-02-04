<?php


namespace Rulatir\Tree\Traits;

trait HasGitRepository
{
    private string $repositoryRoot;

    public function constructHasGitRepository(string $repositoryRoot)
    {
        $this->repositoryRoot = $repositoryRoot;
    }

    public function getRepositoryRoot(): string
    {
        return $this->repositoryRoot;
    }

    public function inRepositoryRoot(callable $work, ...$arguments)
    {
        $oldcwd = getcwd();
        $repositoryRoot = $this->getRepositoryRoot();
        $wasNotInRepositoryRoot = $oldcwd !== $repositoryRoot;
        try {
            if ($wasNotInRepositoryRoot) {
                chdir($repositoryRoot);
            }
            return $work(...$arguments);
        }
        finally {
            if ($wasNotInRepositoryRoot) {
                chdir($oldcwd);
            }
        }
    }

    public function assertRepositoryRoot(string $method)
    {
        assert(getcwd() === $this->getRepositoryRoot(), "{$method} must be invoked with the repository root as CWD");
    }
}