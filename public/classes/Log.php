<?php

namespace CronLogger;

use CronLogger\Components\Database;

class Log  extends Database {

	private $log_id = null;
	public $errors = array();
	public string $table;

	public function init(): void {
		$this->table = $this->wpdb->prefix . Plugin::TABLE_LOGS;
	}

	function start( $info = "" ): void {
		if ( $this->log_id != null ) {
			error_log( "Only start logger once per session.", 4 );

			return;
		}
		$this->wpdb->insert(
			$this->table,
			array(
				'executed' => Plugin::instance()->timer->getStart(),
				'duration' => 0,
				'info'     => "Running ⏳ $info",
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);
		$this->log_id = $this->wpdb->insert_id;
	}

	function update( $duration, $info = null ): int {

		if ( $this->log_id == null ) {
			$this->start();
		}
		$data        = array( 'duration' => $duration );
		$data_format = array( '%d' );
		if ( $info != null ) {
			$data['info']  = $info;
			$data_format[] = '%s';
		}

		return $this->wpdb->update(
			$this->table,
			$data,
			array(
				'id' => $this->log_id,
			),
			$data_format,
			array(
				'%d',
			)
		);
	}

	function addInfo( $message, $duration = null ): void {
		$result = $this->wpdb->insert(
			$this->table,
			array(
				'parent_id' => $this->log_id,
				'info'      => $message,
				'executed'  => time(),
				'duration'  => $duration,
			),
			array(
				'%d',
				'%s',
				'%d',
				'%d',
			)
		);
		if ( $result == false ) {
			$error_message  = "🚨 " . $this->wpdb->last_query;
			$this->errors[] = $error_message;
			error_log( "Cron Logger: " . $error_message );
		} else {
			$this->update(
				Plugin::instance()->timer->getDuration()
			);
		}

	}

	function getList( $args = array() ): array {
		$args = (object) array_merge(
			array(
				"count"       => 15,
				"page"        => 1,
				"min_seconds" => null,
			),
			$args
		);
		$count  = absint( $args->count );
		$page   = max( 1, absint( $args->page ) );
		$offset = $count * ( $page - 1 );

		$wpdb  = $this->wpdb;
		$table = $this->table;

		// The table name is $wpdb->prefix plus a constant. It cannot be a placeholder:
		// %i needs WordPress 6.2 and the plugin supports 5.3.
		if ( $args->min_seconds != null ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, see above
					"SELECT * FROM {$table} WHERE parent_id IS NULL AND duration >= %d ORDER BY executed DESC LIMIT %d, %d",
					absint( $args->min_seconds ),
					$offset,
					$count
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, see getList()
				"SELECT * FROM {$table} WHERE parent_id IS NULL ORDER BY executed DESC LIMIT %d, %d",
				$offset,
				$count
			)
		);
	}

	function getSublist( $log_id, $count = 50, $page = 0 ): array {
		$count  = absint( $count );
		$offset = $count * absint( $page );
		$wpdb   = $this->wpdb;
		$table  = $this->table;

		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, see getList()
				"SELECT * FROM {$table} WHERE parent_id = %d ORDER BY id DESC LIMIT %d, %d",
				absint( $log_id ),
				$offset,
				$count
			)
		);
	}

	function clean(): void {
		$wpdb  = $this->wpdb;
		$table = $this->table;
		$days  = absint( apply_filters( Plugin::FILTER_EXPIRE, 30 ) );

		// Children first, then the expired runs themselves, then whatever child
		// lost its run some other way. The derived tables let MySQL delete from
		// the table it selects from. Table name: see getList().
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, see getList()
				"DELETE FROM {$table} WHERE parent_id IN ( SELECT id FROM ( SELECT id FROM {$table} WHERE parent_id IS NULL AND executed < UNIX_TIMESTAMP( DATE_SUB( NOW(), INTERVAL %d DAY ) ) ) AS expired_parents )",
				$days
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, see getList()
				"DELETE FROM {$table} WHERE id IN ( SELECT id FROM ( SELECT id FROM {$table} WHERE parent_id IS NULL AND executed < UNIX_TIMESTAMP( DATE_SUB( NOW(), INTERVAL %d DAY ) ) ) AS expired_parents )",
				$days
			)
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- no values, only the table name, see getList()
		$wpdb->query( "DELETE FROM {$table} WHERE id IN ( SELECT id FROM ( SELECT id FROM {$table} WHERE parent_id NOT IN ( SELECT id FROM {$table} WHERE parent_id IS NULL ) AND parent_id IS NOT NULL ) AS orphaned_children )" );
	}

	function createTables() {
		parent::createTables();
		dbDelta( "CREATE TABLE IF NOT EXISTS " . $this->table . " 
		(
		 id bigint(20) unsigned not null auto_increment,
		 parent_id bigint(20) unsigned default null,
		 executed bigint(20) unsigned default null ,
		 duration int(11) unsigned default null,
		 info text,
		 primary key (id),
		 key ( executed ),
		 key (duration),
		 key (parent_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}
}
