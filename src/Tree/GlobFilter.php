<?php


namespace Rulatir\Tree;


use Rulatir\Tree\Contracts\PathFilterInterface;
use Webmozart\Glob\Glob;

class GlobFilter implements PathFilterInterface
{
    /** @var MatchRule[] */
    private array $rules = [];

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

    public function addExclusionRule(string $glob)
    {
        $this->addRule($glob, true);
    }

    public function filter(array $items, ?callable $obtainPath = null): array
    {
        $obtainPath = $obtainPath ?? fn($v) => $v;
        if (!count($this->getRules())) return $items;
        $filtered = [];
        $keyed = [];
        foreach($items as $item) $keyed['/'.ltrim($obtainPath($item),'/')] = $item;
        foreach($this->getRules() as $rule) {
            $matched = Glob::filter($keyed, '/'.ltrim($rule->glob,'/'), Glob::FILTER_KEY);
            $keyed = array_diff_key($keyed, $matched);
            if (!$rule->exclude) $filtered[] = $matched;
        }
        return array_values(array_merge(...$filtered));
    }

    public function reset(): void
    {
        $this->rules = [];
    }
}