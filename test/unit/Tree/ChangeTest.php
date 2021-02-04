<?php

namespace Rulatir\Tree;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ChangeTest extends TestCase
{
    public function lines() : array
    {
        return [
            [
                "A foo/bar.txt",
                [Change::STATUS_ADDED], ["foo/bar.txt"], [null]
            ],
            [
                "AA foo/bar.txt",
                [Change::STATUS_ADDED], ["foo/bar.txt"], [null]
            ],
            [
                "AD foo/bar.txt",
                [Change::STATUS_ADDED], ["foo/bar.txt"], [null]
            ],
            [
                "DA foo/bar.txt",
                [Change::STATUS_DELETED], ["foo/bar.txt"], [null]
            ],
            [
                "M foo/bar.txt",
                [Change::STATUS_MODIFIED], ["foo/bar.txt"], [null]
            ],
            [
                "RD foo/bar.txt\tfoo/gen/lou.txt",
                [Change::STATUS_RENAMED_FROM, Change::STATUS_RENAMED_TO],
                ["foo/bar.txt", "foo/gen/lou.txt"], ["foo/gen/lou.txt", "foo/bar.txt"]
            ]
        ];
    }

    /**
     * @param string $changeLine
     * @param array $expectedStatuses
     * @param array $expectedPaths
     * @param array $expectedOtherPaths
     * @dataProvider lines
     * @covers Change::fromChangeLine()
     */
    public function testFromChangeLine(
        string $changeLine,
        array $expectedStatuses,
        array $expectedPaths,
        array $expectedOtherPaths
    )
    {
        $changes = Change::fromChangeLine($changeLine);
        $this->assertCount(count($expectedStatuses), $changes);
        foreach($changes as $i => $change) {
            $this->assertEquals($change->getStatus(), $expectedStatuses[$i], "Wrong status");
            $this->assertEquals($change->getPath(), $expectedPaths[$i], "Wrong path");
            $this->assertEquals($change->getOtherPath(), $expectedOtherPaths[$i], "Wrong other path");
        }
    }

    public function invalidLines() : array
    {
        return [
            [""],
            ["A"],
            ["A "],
            ["wrong"],
            ["wrong wrong"],
            ["R foo.txt"],
            ["R foo.txt bar.txt"],
            ["R foo.txt\tbar.txt\tbaz.txt"]
        ];
    }

    /**
     * @param string $invalidLine
     * @dataProvider invalidLines
     * @covers Change::fromChangeLine()
     */
    public function testFromInvalidChangeLine(string $invalidLine)
    {
        $this->expectException(InvalidArgumentException::class);
        $changes = Change::fromChangeLine($invalidLine);
    }

    /**
     * @covers Change::fromChangeLines()
     */
    public function testFromChangeLines()
    {
        $lines = [
            "A foo/bar.baz",
            "M foo/desmi.baz",
            "R foo/gen.txt\tfoo/lou.txt",
            "D foo/dead.txt"
        ];
        $changes = Change::fromChangeLines($lines);
        $this->assertNotEmpty(
            array_filter($changes, fn($change) =>
                $change->getStatus()===Change::STATUS_ADDED && $change->getPath()==="foo/bar.baz"
            )
        );
        $this->assertNotEmpty(
            array_filter($changes, fn($change) =>
                $change->getStatus()===Change::STATUS_MODIFIED && $change->getPath()==="foo/desmi.baz"
            )
        );
        $this->assertNotEmpty(
            array_filter($changes, fn($change) =>
                $change->getStatus()===Change::STATUS_RENAMED_FROM
                && $change->getPath()==="foo/gen.txt"
                && $change->getOtherPath()==="foo/lou.txt"
            )
        );
        $this->assertNotEmpty(
            array_filter($changes, fn($change) =>
                $change->getStatus()===Change::STATUS_RENAMED_TO
                && $change->getPath()==="foo/lou.txt"
                && $change->getOtherPath()==="foo/gen.txt"
            )
        );
        $this->assertNotEmpty(
            array_filter($changes, fn($change) =>
                $change->getStatus()===Change::STATUS_DELETED && $change->getPath()==="foo/dead.txt"
            )
        );
    }

    public function testChangeToArray()
    {
        [$from, $to] = Change::fromChangeLine("R foo/bar.txt\tbaz/mek.txt");
        $arrayFrom = Change::changeToArray($from);
        $this->assertEquals(Change::STATUS_RENAMED_FROM, $arrayFrom['status']);
        $this->assertEquals('foo/bar.txt', $arrayFrom['path']);
        $this->assertEquals('baz/mek.txt', $arrayFrom['to']);
        $arrayTo = Change::changeToArray($to);
        $this->assertEquals(Change::STATUS_RENAMED_TO, $arrayTo['status']);
        $this->assertEquals('baz/mek.txt', $arrayTo['path']);
        $this->assertEquals('foo/bar.txt', $arrayTo['from']);
    }

    public function compare() : array
    {
        return [
            [0, 'A foo/bar.txt', 'A foo/bar.txt'],
            [0, 'A foo/bar.txt', 'AD foo/bar.txt'],
            [0, 'AD foo/bar.txt', 'A foo/bar.txt'],
            [-1, 'A foo/annie.txt', 'A foo/bar.txt'],
            [-1, 'A foo/annie.txt', 'D foo/bar.txt'],
            [-1, 'D foo/annie.txt', 'A foo/bar.txt'],
            [1, 'A foo/bar.txt', 'A foo/annie.txt'],
            [1, 'A foo/bar.txt', 'D foo/annie.txt'],
            [1, 'D foo/bar.txt', 'A foo/annie.txt'],
            [-1, 'A foo/bar.txt', 'D foo/bar.txt'],
            [1, 'D foo/bar.txt', 'A foo/bar.txt'],
        ];
    }

    /**
     * @dataProvider compare
     * @param int $expected
     * @param string $lhsLine
     * @param string $rhsLine
     */
    public function testCompare(int $expected, string $lhsLine, string $rhsLine)
    {
        $lhs = Change::fromChangeLine($lhsLine)[0];
        $rhs = Change::fromChangeLine($rhsLine)[0];
        self::assertEquals($expected, Change::compare($lhs, $rhs));
    }

    public function testToArray()
    {
        [$from, $to] = Change::fromChangeLine("R foo/bar.txt\tbaz/mek.txt");
        $arrayFrom = $from->toArray();
        $this->assertEquals(Change::STATUS_RENAMED_FROM, $arrayFrom['status']);
        $this->assertEquals('foo/bar.txt', $arrayFrom['path']);
        $this->assertEquals('baz/mek.txt', $arrayFrom['to']);
        $arrayTo = $to->toArray();
        $this->assertEquals(Change::STATUS_RENAMED_TO, $arrayTo['status']);
        $this->assertEquals('baz/mek.txt', $arrayTo['path']);
        $this->assertEquals('foo/bar.txt', $arrayTo['from']);
    }
}
