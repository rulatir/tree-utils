<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Contracts\FileHashingInterface;
use Rulatir\Tree\Contracts\GitRepositoryInterface;

final class State
{
    private GitRepositoryInterface $repository;
    private FileHashingInterface $hashing;
    private string $baseCommitRef;
    private ?string $baseCommit;
    private ?array $snapshot = null;

    public function __construct(
        GitRepositoryInterface $repository,
        string $baseCommitRef,
        FileHashingInterface $hashing
    )
    {
        $this->repository = $repository;
        $this->baseCommitRef = $baseCommitRef;
        $this->baseCommit = null;
        $this->hashing = $hashing;
    }

    public function capture() : array
    {
        $changes = $this->repository->getChanges($this->getBaseCommit());
        $descriptors = $this->repository->inRepositoryRoot([$this, 'stampChanges'], $changes);
        return [
            'commit' => $this->getBaseCommit(),
            'files' => $descriptors
        ];
    }

    public function getBaseCommitRef(): string
    {
        return $this->baseCommitRef;
    }

    public function getBaseCommit(): ?string
    {
        return
            $this->baseCommit
                ?: ($this->baseCommit = $this->captureBaseCommit());
    }

    public function getSnapshot() : array
    {
        return
            $this->snapshot
            ?? ($this->snapshot = $this->repository->inRepositoryRoot([$this, 'capture']));
    }

    public function captureBaseCommit() : string
    {
        return $this->repository->revParse($this->getBaseCommitRef());
    }

    /** @return Change[] */
    public function captureChanges() : array
    {
        $this->repository->assertRepositoryRoot(__METHOD__);
        $changes = trim(`git diff {$this->getBaseCommit()} --name-status`);
        $additions = trim(`git ls-files --other --exclude-standard`);
        $lines = array_map('rtrim', [
            ...explode("\n", $changes),
            ...array_map(fn($v) => "A     {$v}", explode("\n", trim($additions)))
        ]);

        $result = Change::fromChangeLines($lines);
        usort($result, [Change::class, 'compare']);
        return $result;
    }

    /**
     * @param Change[] $changes
     * @return array[]
     */
    public function stampChanges(array $changes) : array
    {
        $descriptors = array_map([Change::class,'changeToArray'], $changes);
        foreach($descriptors as $i => $descriptor) {
            $descriptors[$i]['md5'] = $this->hashing->hashFile($descriptor['path']);
        }
        return $descriptors;
    }
}