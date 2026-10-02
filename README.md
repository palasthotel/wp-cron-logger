# Cron Logger (WordPress-Plugin)

Cron Logger logs every run of `wp-cron.php` and every cron hook it executes, with its
duration. The logs are in **Tools → Cron Logs**, for users with `manage_options`.
It is available on [WordPress.org](https://wordpress.org/plugins/cron-logger/).

## What it logs

| Entry | When |
|---|---|
| a run of `wp-cron.php` | every request with `DOING_CRON`, from `plugins_loaded` to `shutdown` |
| each cron hook | start and finish, with the seconds it took |
| scheduled posts | the status change of every post `publish_future_post` publishes |
| Solr cron | `solr_cron_start` / `solr_cron_finish` of the Solr plugin, if present |

Logs older than 30 days are removed by a daily cron event and after every run; the
**Cleanup** button on the log page does the same on demand. Deleting the plugin
(`uninstall.php`) drops the log table and the plugin's option.

## Custom logs

If you have a cron run in your plugin that does not use the wp-cron.php, you can still use Cron Logger. Register your own Plugin with **cron_logger_init** action.

```php
/**
 * @param CronLogger/Plugin $logger
 */
function my_plugin_init_logger($logger){
	// start a log session (call only once per session)
	$logger->log->start('Log my Plugin');
	
	// now you can add logs to the session
	$logger->log->addInfo("Now my Plugin starts doing this...");
	
	// you can log passed time in seconds too
	$duration = 3;
	$logger->log->addInfo("Now my Plugin has done that...", $duration);
}
add_action("cron_logger_init", "my_plugin_init_logger");
```

## Custom log expiration time

```php
function my_plugin_cron_logger_expire(int $days){
	return 60; // logs will expire and cleaned up after 60 days
}
add_filter("cron_logger_expire", "my_plugin_cron_logger_expire");
```

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `cron_logger_init` | action | receives the `CronLogger\Plugin` instance to log your own runs |
| `cron_logger_expire` | filter | days after which logs are removed (default 30) |
| `cron_logger_wp_cron_start` | action | a `wp-cron.php` run starts being logged |
| `cron_logger_wp_cron_shutdown` | action | a `wp-cron.php` run is finished |

## Repository layout

| Path | Description |
|---|---|
| `public/` | the plugin as it is shipped to WordPress.org |
| `plugin.php` | development wrapper that loads `public/`; never deployed |
| `.github/workflows/` | CI/CD — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

- **WordPress.org:** https://wordpress.org/plugins/cron-logger/
- **User documentation:** [public/README.txt](public/README.txt) (the text shown on WordPress.org)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md) — release-please owns that file, do
  not add notes to it by hand. Entries before 1.3.4 are in the `== Changelog ==`
  section of [public/README.txt](public/README.txt).

## Development

There is nothing to build. See [CONTRIBUTING.md](CONTRIBUTING.md) for the local setup.

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. Nothing is bumped by hand —
commit with [conventional commits](https://www.conventionalcommits.org/) and
merge the release PR. See [CONTRIBUTING.md](CONTRIBUTING.md) and
[.github/WORKFLOWS.md](.github/WORKFLOWS.md).

## License

GPL-3.0-or-later, see [LICENSE](LICENSE).
