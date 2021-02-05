<?php


namespace Rulatir\Tree\Contracts;


use Rulatir\Tree\Change;

interface GitRepositoryInterface extends HasGitRepositoryInterface
{
    public function getRoot() : string;

    /**
     * @param string $baseCommit
     * @return Change[]
     */
    public function getChanges(string $baseCommit) : array;

    public function revParse(string $revisionRef) : string;
}