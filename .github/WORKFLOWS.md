# CI/CD Workflows

The plugin is versioned by [release-please](https://github.com/googleapis/release-please)
based on [conventional commits](https://www.conventionalcommits.org/):
`fix:` → patch, `feat:` → minor, `feat!:` / `BREAKING CHANGE:` → major.

Tag format: `v*` (e.g. `v1.3.4`).

---

## Overview

```
Push to main
    │
    ├──▶ [release-please.yml]
    │        Creates / updates the release PR
    │
    │    On release PR (opened / synchronize)
    ├──▶ [update-plugin-version.yml]
    │        Syncs public/plugin.php Version + public/README.txt
    │
    │    On PR to main
    └──▶ [pr.yml]
             php -l on 8.1 / 8.2 / 8.3 / 8.4  +  npm run pack


Merge release PR  →  release-please pushes tag v1.3.4 + creates GitHub Release
    │
    └── v*  ──▶ [wordpress-svn-release.yml]
                    Version check → pack → upload zip to the Release
                    → deploy public/ to WordPress.org SVN
```

---

## Workflows

### `pr.yml` — PR checks

**Trigger:** Any pull request against `main`

`php -l` over every PHP file outside `public/vendor`, on the PHP versions the
plugin supports (`Requires PHP: 8.1`), plus one `npm run pack` so a broken
packaging script is caught before a release depends on it.

---

### `release-please.yml` — Release PR management

**Trigger:** Push to `main`
**Token:** installation token of the org-owned *Palasthotel Release Bot* GitHub
App, minted per run by `actions/create-github-app-token`. Required because
`GITHUB_TOKEN` pushes do not trigger downstream workflows — the tag would never
start `wordpress-svn-release.yml`.

```
Push to main
      │
      ▼
  release-please
      │
      └──▶ opens / updates PR  "chore(main): release 1.3.4"
                bumps package.json + package-lock.json
                bumps .release-please-manifest.json
                updates CHANGELOG.md

  PR merged
      └──▶ pushes tag v1.3.4
           creates GitHub Release
```

> release-please owns `CHANGELOG.md`. Do not add notes to that file by hand: it
> prepends its own title and demotes an existing one, so hand-written content
> ends up below the release entries.

---

### `update-plugin-version.yml` — Plugin version files

**Trigger:** `pull_request` on `main` — types: `opened`, `synchronize`
**Condition:** Only runs for release-please PRs (`release-please--*`) whose head
branch lives in this repository
**Token:** app installation token — pushing with it re-runs the PR checks on the
new head commit, and `bin/update-plugin-version.sh` is idempotent, so the
resulting `synchronize` event is a no-op instead of a loop

```
Release PR opened / updated
              │
              ▼
    bash bin/update-plugin-version.sh
              │
              ├── reads version from package.json
              ├── updates "Version:" header in public/plugin.php
              ├── updates "Stable tag:" in public/README.txt
              └── prepends new "= x.y.z =" section to the readme changelog
              │
              ▼
    git commit + push → back onto the release PR branch
```

---

### `wordpress-svn-release.yml` — Deploy to WordPress.org

**Trigger:** Push of a `v*` tag

```
Tag: v1.3.4
      │
      ├── strip prefix → VERSION=1.3.4
      │
      ├── bin/version-checker.sh
      │       package.json == README.txt Stable tag == plugin.php Version == tag
      │       mismatch → job fails before anything is published
      │
      ├── npm run pack   (bin/pack.sh)
      │       rsync public/ → build/cron-logger/
      │       composer install + dump-autoload, drop composer files
      │       zip → cron-logger.zip
      │
      ├──▶ Upload cron-logger.zip to the GitHub Release
      │
      ├── svn checkout  $SVN_REPO_URL  →  ./svn/
      │
      └── SVN commit
              rm trunk/*  +  rm tags/$VERSION
              cp public/* → trunk/  +  tags/$VERSION/
              svn add --force .
              svn rm deleted files
              svn commit "Release version $VERSION"
```

---

## Required secrets / variables

| Name | Type | Level | Value |
|---|---|---|---|
| `RELEASE_BOT_APP_ID` | variable | org | App ID of the *Palasthotel Release Bot* GitHub App |
| `RELEASE_BOT_PRIVATE_KEY` | secret | org | that app's private key (full `.pem`, incl. BEGIN/END lines) |
| `SVN_USERNAME` | secret | org | WordPress.org committer |
| `SVN_PASSWORD` | secret | org | WordPress.org password |
| `SVN_REPO_URL` | variable | repo | `https://plugins.svn.wordpress.org/cron-logger` |

The GitHub App needs to be installed on this repository with
`Contents: read & write` and `Pull requests: read & write`. `SVN_REPO_URL` is
repo-level because the slug differs per plugin; everything else is shared across
all plugin repos.

release-please never pushes to `main` — it opens a pull request — so a branch
ruleset on `main` needs no exception for the app. Add the app as a bypass actor
only if a **tag** ruleset restricts creating `v*` tags, if a ruleset also covers
the `release-please--*` branches and forbids direct pushes, or if signed commits
are required.

---

## Files the release touches

| File | Updated by | Purpose |
|---|---|---|
| `package.json`, `package-lock.json` | release-please | version source |
| `.release-please-manifest.json` | release-please | last released version |
| `CHANGELOG.md` | release-please | GitHub-facing changelog |
| `public/plugin.php` | `bin/update-plugin-version.sh` | `Version:` plugin header |
| `public/README.txt` | `bin/update-plugin-version.sh` | `Stable tag:` + `== Changelog ==` |

---

## Version history note

Versions 1.3.1, 1.3.2 and 1.3.3 exist as WordPress.org SVN tags but were never
made stable: the `Stable tag` in `README.txt` stayed at 1.3.0, so every user kept
receiving 1.3.0. They also had no changelog entries and no git tags.

The entries were reconstructed from the SVN tags and added to `README.txt`, the
git tag `v1.3.3` was created as the boundary release-please measures from, and
1.3.4 is the first release produced by this pipeline — it is what finally carries
those fixes to users.
