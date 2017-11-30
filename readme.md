# Cron Logger

With Cron Logger the wp-cron.php runs are logged. They are available in **Tools** -> **Cron Logs**.

## Custom logs

If you have a cron run in your plugin that does not use the wp-cron.php, you can still use Cron Logger. Register your own Plugin with **cron_logger_init** action.

```php
/**
 * @param CronLogger/Plugin $logger
 */
function my_plugin_init_logger($logger){
	// start a log session (call only once per session)
	$logger->log->start('Log my Plugin');
	
	// and now you can add logging steps after operations like
	$logger->log->addInfo("Now my Plugin starts doing this...");
	
	// you can log passed time in seconds too
	$duration = 3;
	$logger->log->addInfo("Now my Plugin has done that...", $duration);
}
add_action("cron_logger_init", "my_plugin_init_logger");
```