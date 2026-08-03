# Contributing

## Code of Conduct

The project has a [Code of Conduct](./CODE_OF_CONDUCT.md) to which all contributors must adhere.

## Branching

`main` is the default branch and always reflects what is released, or about to
be. Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history by
[release-please](https://github.com/googleapis/release-please), so commit
messages follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (1.3.3 → 1.3.4) | yes, "Bug Fixes" |
| `feat:` | minor (1.3.3 → 1.4.0) | yes, "Features" |
| `feat!:` or a `BREAKING CHANGE:` footer | major (1.3.3 → 2.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `ci:`, `style:`, `test:` | none | no |

A pull request that should trigger a release needs at least one `fix:` or
`feat:` commit. When squash-merging, the squash commit message itself has to be
a conventional commit — that is the message release-please reads.

To force a specific version regardless of commit types, add a `Release-As: x.y.z`
footer to a commit.

## Versions

Never edit version numbers by hand. `package.json`, `CHANGELOG.md`,
`public/plugin.php` and the `Stable tag:` in `public/README.txt` are maintained
by the release pipeline, which is documented in
[.github/WORKFLOWS.md](./.github/WORKFLOWS.md).

Content changes to `public/README.txt` — description, FAQ, tested-up-to — are of
course made by hand; just leave `Stable tag:` and the `== Changelog ==` entries
alone.

## Repository layout

| Path | Description |
|---|---|
| `public/` | the plugin as it is shipped to WordPress.org |
| `plugin.php` | dev wrapper, loads `public/plugin.php` and registers the activation hooks for local `wp-env` use |
| `bin/` | release helper scripts |
| `.github/workflows/` | CI/CD |

Only `public/` is shipped. Everything outside it stays repository-only.

## Local environment

```sh
npm install
npm run wp-env:start   # http://localhost:8080
npm run pack           # → cron-logger.zip
```

## Checks

Every PR runs `php -l` against PHP 8.1, 8.2, 8.3 and 8.4, and packs the plugin
once. The plugin declares `Requires PHP: 8.1` in both the plugin header and
`public/composer.json`; raise both together if that changes.
