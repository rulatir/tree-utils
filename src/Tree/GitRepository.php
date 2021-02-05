<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Contracts\GitRepositoryInterface;
use Rulatir\Tree\Traits\HasGitRepository;

final class GitRepository implements GitRepositoryInterface
{
    use HasGitRepository {
        getRepositoryRoot as getRoot;
    }

    public function __construct(string $root)
    {
        $this->constructHasGitRepository($root);
    }

    public function getChanges(string $baseCommit): array
    {
        return $this->inRepositoryRoot([$this, 'getChangesInRoot'], $baseCommit);
    }

    /**
     * @param string $baseCommit
     * @return Change[]
     */
    private function getChangesInRoot(string $baseCommit): array
    {
        $this->assertRepositoryRoot(__METHOD__);
        $changes = trim(`git diff --name-status -M {$baseCommit}`);
        $additions = trim(`git ls-files --other --exclude-standard`);
        $lines = array_map('rtrim', [
            ...(strlen($changes) ? explode("\n", $changes) : []),
            ...array_map(fn($v) => "A     {$v}", strlen($additions) ? explode("\n", trim($additions)) : [])
        ]);

        $result = Change::fromChangeLines($lines);
        usort($result, [Change::class, 'compare']);
        return $result;
    }

    public function revParse(string $revisionRef): string
    {
        return $this->inRepositoryRoot([$this,'revParseInRoot'], $revisionRef);
    }

    private function revParseInRoot(string $revisionRef): string
    {
        return trim(`git rev-parse {$revisionRef}`);
    }
}