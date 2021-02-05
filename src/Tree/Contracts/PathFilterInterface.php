<?php


namespace Rulatir\Tree\Contracts;


interface PathFilterInterface
{
    /**
     * @param array[string]mixed $items Items to filter
     * @param callable|null $obtainPath Method to obtain file path from item
     * @return array[string]mixed Filtered items
     */
    public function filter(array $items, ?callable $obtainPath = null) : array;
    public function reset() : void;
}