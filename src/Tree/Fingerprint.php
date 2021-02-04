<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Traits\HasGitRepository;
use Webmozart\Glob\Glob;

final class Fingerprint
{
    use HasGitRepository;
    private string $stateFile;

    /** @var MatchRule[] */
    private array $rules = [];
    private ?array $fingerprint = null;

    public function __construct(string $repositoryRoot, string $stateFile)
    {
        $this->constructHasGitRepository($repositoryRoot);
        $this->stateFile = $stateFile;
    }

    public function getStateFile(): string
    {
        return $this->stateFile;
    }

    /**
     * @return MatchRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function addRule(string $glob, bool $exclude = false)
    {
        array_unshift($this->rules, new MatchRule($glob, $exclude));
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
        foreach($this->filter($files) as $fileState) {
            $result[$fileState['path']] = $fileState['md5'];
        }
        return [
            'commit'=>$state['commit'],
            'files'=>$result
        ];
    }

    public function filter(array $fileStates) : array
    {
        if (!count($this->getRules())) return $fileStates;
        $filtered = [];
        $keyed = []; foreach($fileStates as $fileState) $keyed[$fileState['path']] = $fileState;
        foreach($this->getRules() as $rule) {
            $matched = Glob::filter($keyed, $rule->glob, Glob::FILTER_KEY);
            $keyed = array_diff_key($keyed, $matched);
            if (!$rule->exclude) $filtered[] = $matched;
        }
        return array_values(array_merge(...$filtered));
    }
}