<?php


namespace Rulatir\Tree;


final class MatchRule
{
    public string $glob;
    public bool $exclude;

    public function __construct(string $glob, bool $exclude = false)
    {
        $this->glob = $glob;
        $this->exclude = $exclude;
    }
}