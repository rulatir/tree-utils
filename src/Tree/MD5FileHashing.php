<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Contracts\FileHashingInterface;

final class MD5FileHashing implements FileHashingInterface
{

    public function hashFile(string $filePath): string
    {
        return is_file($filePath) ? md5_file($filePath) : "ABSENT";
    }

    public function hashFiles(array $filePaths): array
    {
        return array_combine(
            $filePaths,
            array_map([$this, 'hashFile'], $filePaths)
        );
    }
}