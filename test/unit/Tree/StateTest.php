<?php

namespace Rulatir\Tree;

use DG\BypassFinals;
use PHPUnit\Framework\TestCase;
use Rulatir\Tree\Contracts\FileHashingInterface;
use Rulatir\Tree\Contracts\GitRepositoryInterface;
use function PHPUnit\Framework\assertEqualsCanonicalizing;

class StateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
        parent::setUpBeforeClass();
    }

    /**
     * @dataProvider stampChangesData
     * @param string[] $changeLines
     * @param array[string]string $files
     */
    public function testStampChanges(array $changeLines, array $files)
    {
        $mockHashing =
            $this->getMockBuilder(FileHashingInterface::class)
                ->onlyMethods(['hashFile'])
                ->getMockForAbstractClass();

        $mockHashing->method('hashFile')
            ->willReturnCallback(fn($path) => $files[$path] ?: 'ABSENT');

        $mockRepo =
            $this->getMockBuilder(GitRepositoryInterface::class)
                ->getMock();

        $changeSet = Change::fromChangeLines($changeLines);
        $state = new State($mockRepo, 'HEAD', $mockHashing);
        $stamped = $state->stampChanges($changeSet);
        assertEqualsCanonicalizing(
            $files,
            array_combine(
                array_column($stamped, 'path'),
                array_column($stamped, 'md5')
            )
        );

    }

    public function stampChangesData() : array
    {
        $data = [
            [
                ["A", "foo/bar.txt", "Text of the foo bar file"],
                ["D", "gen/lou.txt", null],
                ["M", "gen/pedro.txt", "Text of the gen pedro file"]
            ]
        ];
        return array_map(
            fn($dataSet) => [
                array_map(fn($f) => "{$f[0]} {$f[1]}", $dataSet),
                array_combine(
                    array_column($dataSet,1),
                    array_map(fn($v) => null===$v ? 'ABSENT' : md5($v), array_column($dataSet, 2))
                )
            ],
            $data
        );
    }
}
