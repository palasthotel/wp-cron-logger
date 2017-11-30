# Cron Logger

With Cron Logger the wp-cron.php runs are logged. They are available in **Tools** -> **Cron Logs**.

## Custom logs

If you have a cron run in your plugin that does not use the wp-cron.php, you can still use Cron Logger. Register you own Plugin with **cron_logger_init** action.

```php
/**
 * @param CronLogger/Log $log
 */
function my_plugin_init_logger($log){
	$log->start('Log my Plugin');
	
	// and now you can add logging steps after operations like
	$log->addInfo("Now my Plugin has done this...");
	
	// you can log passed time in seconds too
	$duration = 3;
	$log->addInfo("Now my Plugin has done that...", $duration);
}
add_action("cron_logger_init", "my_plugin_init_logger");
```