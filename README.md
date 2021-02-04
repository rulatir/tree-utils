# tree-utils

This directory contains helper scripts used in build system commands. They provide the ability to
create surrogate build goals that change whenever any file in a large set of files changes. They
utilize `git` to speed up change detection.

#### `tree-state`

```bash
tree-state path/to/repository/root
```

The `tree-state` script uses `git` to generate a compressed identity of the tree state. Abstractly,
the state of the tracked subset of the tree is fully (if somewhat redundantly) identified by
the combination of the following bits of information:
- current HEAD commit hash (output of `git rev-parse HEAD`)
- changes and deletions (output of `git diff HEAD --name-status`), together with hashes of the files
listed
- "new" files, i.e. untracked files that are not ignored (output of `git ls-files --other --exclude-standard`)
together with hashes of the files listed

This information is output as JSON. A build system can use it to generate a tree state
file that is guaranteed to change when HEAD is moved or when there are tracked changes in
the tree compared to the time the file was last generated. The state file MUST be ignored
to avoid a vicious cycle. It should always be rebuilt in any build scenario that
might use it.

#### `tree-fp`

```bash
tree-fp path/to/repository/root path/to/statefile 'glob' [[-n] 'glob'] ...
```

The `tree-fp` script is used to generate a fingerprint hash of changes in a subset of the tree.
It reads the state file generated with `tree-state`, filters the list of changes according to
glob rules, and outputs a hash of the filtered state (including the HEAD commit).
The filter is specified using glob expressions relative to the repository root. Precede an
expression with `-n` to make it an exclusion term.
