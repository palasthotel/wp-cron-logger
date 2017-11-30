<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.17
 * Time: 15:43
 */

namespace CronLogger;


class Page {

	const ARG_ITEMS = "cron-logs-items";
	const ARG_PAGE = "cron-logs-page";
	const ARG_DURATION_MIN = "cron-logs-dm";

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('admin_menu', array($this, 'menu_pages'));
	}

	public function menu_pages() {

		add_submenu_page('tools.php', 'Cron Logs', 'Cron Logs', 'manage_options', 'cron-logs', array(
			$this,
			"render",
		));

	}

	function getArgs(){
		$args = (object) array();
		$args->items = getARGValue(self::ARG_ITEMS, 10, function($val){
			return intval($val) > 0;
		});
		$args->page = getARGValue(self::ARG_PAGE, 1, function($val){
			return intval($val) > 0;
		});
		$args->duration_min = getARGValue(self::ARG_DURATION_MIN, null, function($val){
			return intval($val) >= 0;
		});

		return $args;
	}

	function render() {
		?>
		<div class="wrap">
			<h2>Cron Logs</h2>
			<?php
			$timezone = get_option('timezone_string');
			try{
				$time = new \DateTime("now", new \DateTimeZone($timezone));
			} catch (\Exception $e){
				echo "<p>".__("Missing »timezone_string« entry in options table. Please fix! Otherwise execution times could be wrong.", Plugin::DOMAIN)."</p>";
				$time = new \DateTime('now');
			}
			$args = $this->getArgs();
			?>

			<form method="GET" action="<?php echo admin_url('tools.php'); ?>">
				<input type="hidden" name="page" value="cron-logs"/>
				<label>
					Minimum duration of x seconds<br>
					<input type="number"
					       name="<?php echo self::ARG_DURATION_MIN ?>"
					       placeholder="x"
					       value="<?php echo $args->duration_min; ?>"/>
				</label><br>
				<label>
					Page<br>
					<input type="number" min="1" name="<?php echo self::ARG_PAGE ?>" required
					       value="<?php echo $args->page; ?>"/>
				</label><br>
				<label>
					Logs per Page<br>
					<input type="number" min="1" max="50" maxlength="2" name="<?php echo self::ARG_ITEMS ?>"
					       required
					       value="<?php echo $args->items; ?>"/>
				</label>

				<?php
				submit_button("Filter");
				?>
			</form>

			<?php submit_button('Toggle open/close log details', 'small', "toggle_logs"); ?>

			<table class="widefat striped">
				<thead>
				<tr>
					<th style="width: 145px;" scope="col" title="<?php echo $timezone; ?>">
						Ausgeführt
					</th>
					<th style="width: 90px;" scope="col">Dauer</th>
					<th scope="col">Info</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$list = $this->plugin->log->getList(array(
					"count" => $args->items,
					"page" => $args->page,
					"min_seconds" => $args->duration_min,
				));
				foreach ($list as $log) {
					?>
					<tr style="cursor: pointer" data-log-id="<?php echo $log->id; ?>">
						<td style="border-top: 3px solid #333;"><?php
							$time->setTimestamp($log->executed);
							echo $time->format("Y-m-d H:i:s");
							?></td>
						<td style="border-top: 3px solid #333;"><?php echo getDurationString($log->duration); ?></td>
						<td style="border-top: 3px solid #333;"><?php echo $log->info; ?></td>
					</tr>
					<?php
					$sublist = $this->plugin->log->getSublist($log->id);
					foreach ($sublist as $sub) {
						?>
						<tr data-parent-id="<?php echo $log->id; ?>">
							<td></td>
							<td><?php echo getDurationString($sub->duration); ?></td>
							<td><?php echo $sub->info; ?></td>
						</tr>
						<?php
					}
				}
				?>
				</tbody>
			</table>
		</div>
		<script>
			jQuery(function($) {
				var $logs =  $('[data-log-id]');
				$logs.on('click', function() {
					var id = $(this).attr('data-log-id');
					console.log('clicked', id);
					$('[data-parent-id=' + id + ']').toggle();
				});
				var isVisible = true;
				$('[name=toggle_logs]').on('click', function(){
					if(isVisible){
						$('[data-parent-id]').hide();
					} else {
						$('[data-log-id]').trigger('click');
					}
					isVisible = !isVisible;
				});
			});
		</script>
		<?php

	}

}

function getDurationString($duration) {
	if ($duration == NULL) {
		return "";
	}
	return $duration . "s";
}

/**
 * @param $key
 * @param $default
 * @param null|callable $valid
 *
 * @return mixed
 */
function getARGValue($key, $default, $valid = null){
	return (!empty($_GET[$key]) && ($valid == null || $valid($_GET[$key]) ))? $_GET[$key]: $default;
}