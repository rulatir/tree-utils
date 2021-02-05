<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Contracts\PathFilterInterface;
use Rulatir\Tree\Traits\HasGitRepository;

final class Fingerprint
{
    use HasGitRepository;
    private string $stateFile;

    /** @var MatchRule[] */
    private array $rules = [];
    private ?array $fingerprint = null;
    private string $repositoryRoot;
    /**
     * @var PathFilterInterface
     */
    private PathFilterInterface $filter;

    public function __construct(string $repositoryRoot, string $stateFile, PathFilterInterface $filter)
    {
        $this->constructHasGitRepository($repositoryRoot);
        $this->stateFile = $stateFile;
        $this->repositoryRoot = $repositoryRoot;
        $this->filter = $filter;
    }

    public function getStateFile(): string
    {
        return $this->stateFile;
    }

    public function getFingerprint() : array
    {
        return
            $this->fingerprint
            ?? ($this->fingerprint = $this->inRepositoryRoot([$this,'capture']));
    }

    public function capture() : array
    {
        $this->assertRepositoryRoot(__METHOD__);
        $state = json_decode($what=file_get_contents($this->getStateFile()),true);
        if (null===$state) {

            fwrite(STDERR, "Invalid JSON in file {$this->getStateFile()}\n");
            exit(1);
        }
        $files = $state['files'];

        $result = [];
        foreach($this->filter->filter($files, fn($fileState) => $fileState['path']) as $fileState) {
            $result[$fileState['path']] = $fileState['md5'];
        }
        return [
            'commit'=>$state['commit'],
            'files'=>$result
        ];
    }
}