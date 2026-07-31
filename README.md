# WordPress Cron Logger

With Cron Logger all wp-cron.php runs are logged. They are available in **Tools** -> **Cron Logs**.

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

## Repository layout

| Path | Description |
|---|---|
| `public/` | the plugin as it is shipped to WordPress.org |
| `plugin.php` | dev wrapper for local `wp-env` use |
| `bin/` | release helper scripts |
| `.github/workflows/` | CI/CD — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

- **WordPress.org:** https://wordpress.org/plugins/cron-logger/
- **User documentation:** [public/README.txt](public/README.txt) (the text shown on WordPress.org)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md) — release-please owns that file, do
  not add notes to it by hand. Entries before 1.3.4 are in the `== Changelog ==`
  section of [public/README.txt](public/README.txt).

## Development

```sh
npm install
npm run wp-env:start   # http://localhost:8080
npm run pack           # → cron-logger.zip
```

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. Nothing is bumped by hand —
commit with [conventional commits](https://www.conventionalcommits.org/) and
merge the release PR. See [CONTRIBUTING.md](CONTRIBUTING.md) and
[.github/WORKFLOWS.md](.github/WORKFLOWS.md).

## License

GNU General Public License v3.0 or later — see [public/LICENSE](public/LICENSE).
