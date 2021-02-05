<?php

namespace Rulatir\Tree;

use PHPUnit\Framework\TestCase;

class MD5FileHashingStory extends TestCase
{

    const PREFIX = __DIR__."/md5-file-hashing";
    
    public function testHashFiles()
    {
        $data = $this->fileHashes();
        $expected = array_combine(array_column($data,0),array_column($data,1));
        $hashing = new MD5FileHashing();
        chdir(__DIR__);
        $actual = $hashing->hashFiles(array_keys($expected));
        self::assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @dataProvider fileHashes
     * @param string $path
     * @param string $expectedHash
     */
    public function testHashFile(string $path, string $expectedHash)
    {
        $hashing = new MD5FileHashing();
        chdir(__DIR__);
        self::assertEquals($expectedHash, $hashing->hashFile($path));
    }

    public function fileHashes() : array
    {
        $result = [];
        foreach(json_decode(file_get_contents(self::PREFIX."/expected.json")) as $path=>$hash) {
            $result[] = [$path, $hash];
        }
        return $result;
    }
}
