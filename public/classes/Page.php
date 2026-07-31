<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.17
 * Time: 15:43
 */

namespace CronLogger;


use CronLogger\Components\Component;

class Page extends Component {

	const ARG_ITEMS = "cron-logs-items";

	const ARG_PAGE = "cron-logs-page";

	const ARG_DURATION_MIN = "cron-logs-dm";

	public function onCreate(): void {
		add_action( 'admin_menu', array( $this, 'menu_pages' ) );
	}

	public function menu_pages(): void {
		add_submenu_page(
			'tools.php',
			__( 'Cron Logs', Plugin::DOMAIN ),
			__( 'Cron Logs', Plugin::DOMAIN ),
			'manage_options',
			'cron-logs',
			array(
				$this,
				"render",
			)
		);
	}

	function getArgs() {
		$args        = (object) array();
		$args->items = 10;
		if ( ! empty( $_GET[ self::ARG_ITEMS ] ) && intval( $_GET[ self::ARG_ITEMS ] ) > 0 ) {
			$args->items = intval( $_GET[ self::ARG_ITEMS ] );
		}
		$args->page = 1;
		if ( ! empty( $_GET[ self::ARG_PAGE ] ) && intval( $_GET[ self::ARG_PAGE ] ) > 0 ) {
			$args->page = intval( $_GET[ self::ARG_PAGE ] );
		}
		$args->duration_min = null;
		if ( ! empty( $_GET[ self::ARG_DURATION_MIN ] ) ) {
			$args->duration_min = intval( $_GET[ self::ARG_DURATION_MIN ] );
		}

		return $args;
	}

	function render() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Cron Logs', 'cron-logger' ); ?></h2>
			<?php
			$timezone = wp_timezone_string();
			try {
				$time = new \DateTime( "now", new \DateTimeZone( $timezone ) );
			} catch ( \Exception $e ) {
				echo "<p>" . esc_html__( "Missing »timezone_string« entry in options table. Please fix! Otherwise execution times could be wrong.", 'cron-logger' ) . "</p>";
				$time = new \DateTime( 'now' );
			}
			$args = $this->getArgs();
			?>

            <form method="GET" action="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">
                <input type="hidden" name="page" value="cron-logs"/>
                <label>
					<?php _e( 'Minimum duration of x seconds', Plugin::DOMAIN ); ?><br>
                    <input type="number"
                           name="<?php echo esc_attr( self::ARG_DURATION_MIN ); ?>"
                           placeholder="x"
                           value="<?php echo esc_attr( $args->duration_min ); ?>"/>
                </label><br>
                <label>
					<?php _e( "Page", Plugin::DOMAIN ); ?><br>
                    <input type="number" min="1"
                           name="<?php echo esc_attr( self::ARG_PAGE ); ?>" required
                           value="<?php echo esc_attr( $args->page ); ?>"/>
                </label><br>
                <label>
					<?php _e( 'Logs per Page', Plugin::DOMAIN ); ?><br>
                    <input type="number" min="1" max="50" maxlength="2"
                           name="<?php echo esc_attr( self::ARG_ITEMS ); ?>"
                           required
                           value="<?php echo esc_attr( $args->items ); ?>"/>
                </label>

				<?php
				submit_button( __( "Filter", Plugin::DOMAIN ) );
				?>
            </form>

            <div style="display: flex; gap: 25px;">
                <?php submit_button( __( 'Toggle open/close log details', Plugin::DOMAIN ), 'small', "toggle_logs" ); ?>
                <p class="submit"><button class="button button-small button-link-delete" id="cron-logger-cleanup"><?php esc_html_e( 'Cleanup', 'cron-logger' ); ?></button></p>
            </div>

            <table class="widefat striped">
                <thead>
                <tr>
                    <th style="width: 145px;" scope="col"
                        title="<?php echo esc_attr( $timezone ); ?>">
						<?php _e( 'Executed', Plugin::DOMAIN ); ?>
                    </th>
                    <th style="width: 90px;" scope="col"><?php _e( 'Duration', Plugin::DOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Info', Plugin::DOMAIN ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				$list = $this->plugin->log->getList( array(
					"count"       => $args->items,
					"page"        => $args->page,
					"min_seconds" => $args->duration_min,
				) );
				foreach ( $list as $log ) {
					?>
                    <tr style="cursor: pointer"
                        data-log-id="<?php echo esc_attr( $log->id ); ?>">
                        <td style="border-top: 3px solid #333;"><?php
							$time->setTimestamp( $log->executed );
							echo esc_html( $time->format( "Y-m-d H:i:s" ) );
							?></td>
                        <td style="border-top: 3px solid #333;"><?php echo esc_html( $this->getDurationString( $log->duration ) ); ?></td>
                        <td style="border-top: 3px solid #333;"><?php echo wp_kses_post( $log->info ); ?></td>
                    </tr>
					<?php
					$sublist = $this->plugin->log->getSublist( $log->id );
					foreach ( $sublist as $sub ) {
						?>
                        <tr data-parent-id="<?php echo esc_attr( $log->id ); ?>">
                            <td></td>
                            <td><?php echo esc_html( $this->getDurationString( $sub->duration ) ); ?></td>
                            <td><?php echo wp_kses_post( $sub->info ); ?></td>
                        </tr>
						<?php
					}
				}
				?>
                </tbody>
            </table>
        </div>
        <script>
            jQuery(function ($) {
                const $logs = $('[data-log-id]');
                $logs.on('click', function () {
                    const id = $(this).attr('data-log-id');
                    console.log('clicked', id);
                    $('[data-parent-id=' + id + ']').toggle();
                });
                let isVisible = true;
                $('[name=toggle_logs]').on('click', function () {
                    if (isVisible) {
                        $('[data-parent-id]').hide();
                    } else {
                        $('[data-log-id]').trigger('click');
                    }
                    isVisible = !isVisible;
                });
            });
            const cleanupButton = document.getElementById("cron-logger-cleanup");
            const cleanupNonce = <?php echo wp_json_encode( wp_create_nonce( Ajax::CLEANUP_NONCE_ACTION ) ); ?>;
            cleanupButton.addEventListener("click", function(e){
                e.preventDefault();
                cleanupButton.innerHTML = "<span class='spinner is-active'></span>";
                fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
                    body: new URLSearchParams({
                        action: "cron_logger_cleanup",
                        nonce: cleanupNonce
                    })
                })
                    .then(() => {
                        window.location.reload();
                    });
            }, {once: true})
        </script>
		<?php

	}

	private function getDurationString($duration ): string {
		if ( $duration == null ) {
			return "";
		}

		return $duration . "s";
	}

}

