<?php

namespace Rulatir\Tree;

use PHPUnit\Framework\TestCase;
use Rulatir\Tree\Contracts\FileHashingInterface;
use Rulatir\Tree\Contracts\GitRepositoryInterface;
use Rulatir\Tree\Contracts\PathFilterInterface;
use Rulatir\Tree\Testing\RepositoryTestingCapability;

class FingerprintStory extends TestCase
{
    use RepositoryTestingCapability;

    /**
     * @param string $changeSet
     * @param bool $expectFingerprintChange
     * @param string|null $firstChangeSet
     * @dataProvider changes
     */
    public function testGetFingerprint(string $changeSet, bool $expectFingerprintChange, ?string $firstChangeSet=null)
    {
        $treeStateFile = $this->tmpDir->getRoot()."/internal/tree.state";
        $repository = new GitRepository($this->tmpDir->getRoot());
        $hashing = new MD5FileHashing();
        $filter = new GlobFilter();
        $filter->addRule('foo/**/*.txt');
        $filter->addExclusionRule('**/*.hidden.txt');

        if ($firstChangeSet) $this->installRevision($firstChangeSet);

        $before = $this->fingerprint($treeStateFile, $repository, $hashing, $filter);

        $this->installRevision($changeSet);

        $after = $this->fingerprint($treeStateFile, $repository, $hashing, $filter);

        if($expectFingerprintChange) {
            self::assertNotEqualsCanonicalizing($before, $after);
        }
        else {
            self::assertEqualsCanonicalizing($before, $after);
        }
    }

    protected function fingerprint(
        string $stateFile,
        GitRepositoryInterface $repository,
        FileHashingInterface $hashing,
        PathFilterInterface $filter
    ) : array
    {
        $state = new State($repository, 'HEAD', $hashing);
        $snapshot = $state->getSnapshot();
        $snapshotJSON = json_encode($snapshot,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        file_put_contents($stateFile, $snapshotJSON);
        $fp = new Fingerprint($this->tmpDir->getRoot(), $stateFile, $filter);
        return $fp->getFingerprint();
    }

    public function changes() : array
    {
        $data = [
            ['delete-deep',             true    ],
            ['delete-deep-hidden',      false   ],
            ['add-deep',                true    ],
            ['add-deep-hidden',         false   ],
            ['modify-deep',             true    ],
            ['modify-deep-hidden',      false   ],
            ['add-in-new-dir',          true    ],
            ['more-in-new-dir',         true,   'add-in-new-dir'   ],
            ['more-in-new-dir-hidden',  false,  'add-in-new-dir'   ],
            ['more-in-new-dir-ignored', false,  'add-in-new-dir'   ],
            ['add-unmatched',           false,  'add-in-new-dir'   ],
            //reverting a modification that has already been fingerprinted should change the fingerprint
            ['unmodify-deep',           true,   'modify-deep'      ]
        ];
        return array_combine(array_column($data, 0),$data);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRepositoryTesting('initial');
    }

    protected function tearDown(): void
    {
        $this->tearDownRepositoryTesting();
        parent::tearDown();
    }

    protected function getStoryDirectory(): string
    {
        return __DIR__ . "/fingerprint";
    }
}
