<?php


namespace Rulatir\Tree\Contracts;


interface FileHashingInterface
{
    /**
     * @param string $filePath File path
     * @return string Hash of the file contents
     */
    public function hashFile(string $filePath) : string;

    /**
     * @param string[] $filePaths Array of file paths
     * @return array[string]string Associative array with file paths as keys and their content hashes as values
     */
    public function hashFiles(array $filePaths) : array;
}