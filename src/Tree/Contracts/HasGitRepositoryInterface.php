<?php


namespace Rulatir\Tree\Contracts;

interface HasGitRepositoryInterface
{
    public function inRepositoryRoot(callable $work, ...$arguments);
    public function assertRepositoryRoot(string $method);
}