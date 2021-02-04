<?php


namespace Rulatir\Tree;


use RuntimeException;

final class Change
{
    const STATUS_MODIFIED = "modified";
    const STATUS_DELETED = "deleted";
    const STATUS_RENAMED_FROM = "renamedFrom";
    const STATUS_RENAMED_TO = "renamedTo";
    const STATUS_ADDED = "added";

    private string $status;
    private string $path;
    private ?string $otherPath;

    public function __construct(string $status, string $path, ?string $otherPath = null)
    {
        $this->status = $status;
        $this->path = $path;
        $this->otherPath = $otherPath;
    }

    /**
     * @param string $changeLine
     * @return self[]
     */
    public static function fromChangeLine(string $changeLine) : array
    {
        if (!preg_match('/^(\S+)\s+(.*)$/',$changeLine,$matches)) return [];
        [$status, $path] = array_slice($matches, 1);
        switch($status[0]) {
            case "M": return [new self(self::STATUS_MODIFIED, $path)];
            case "D": return [new self(self::STATUS_DELETED, $path)];
            case "R":
                [$from, $to] = explode("\t", $path);
                return [
                    new self(self::STATUS_RENAMED_FROM, $from, $to),
                    new self(self::STATUS_RENAMED_TO, $to, $from)
                ];
            case "A":
                return [new self(self::STATUS_ADDED, $path)];
            default:
                throw new RuntimeException("Unknown change status \"{$status}\"");
        }
    }

    /**
     * @param string[] $lines
     * @return self[]
     */
    public static function fromChangeLines(array $lines) : array
    {
        return array_merge(
            ...array_map(
                [self::class, 'fromChangeLine'],
                $lines
            )
        );
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function hasStatus(string $status) : bool
    {
        return $this->getStatus() === $status;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function toArray() : array
    {
        $result = [
            'status' => $this->getStatus(),
            'path' => $this->getPath()
        ];
        if ($this->hasStatus(self::STATUS_RENAMED_FROM)) $result['to'] = $this->getOtherPath();
        elseif ($this->hasStatus(self::STATUS_RENAMED_TO)) $result['from'] = $this->getOtherPath();
        return $result;
    }

    public static function changeToArray(self $change) : array
    {
        return $change->toArray();
    }

    public static function compare(self $lhs, self $rhs) : int
    {
        return ($lhs->getPath() <=> $rhs->getPath()) ?: ($lhs->getStatus() <=> $rhs->getStatus());
    }

    /**
     * @return string|null
     */
    public function getOtherPath(): ?string
    {
        return $this->otherPath;
    }
}