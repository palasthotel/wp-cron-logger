# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `cron-logger` |
| main file | `public/plugin.php` - keep the name, see [CONTRIBUTING.md](../CONTRIBUTING.md) |
| readme | `public/README.txt` (upper case, as in the SVN since the first release) |
| version file | `package.json` (`release-type: node`) - keep it, release-please and the scripts read the version there |
| build step | none |
| composer | `public/composer.json` only provides the PSR-4 autoloader; the pack step runs `composer install --no-dev`, writes an optimized autoloader and drops `composer.json`/`composer.lock` from the payload |
| PHP | `php -l` on 8.1 to 8.4, matching `Requires PHP: 8.1` |

## Version history note

Versions 1.3.1, 1.3.2 and 1.3.3 exist as WordPress.org SVN tags but were never
made stable: the `Stable tag` in `README.txt` stayed at 1.3.0, so every user kept
receiving 1.3.0. They also had no changelog entries and no git tags.

The entries were reconstructed from the SVN tags and added to `README.txt`, the
git tag `v1.3.3` was created as the boundary release-please measures from, and
1.3.4 is the first release produced by this pipeline — it is what finally carries
those fixes to users.
