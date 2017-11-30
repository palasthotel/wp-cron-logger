<?php

namespace CronLogger;

class Log {

	private $plugin;
	private $log_id = null;
	private $errors = array();

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	function tableName(){
		global $wpdb;
		return $wpdb->prefix."cron_logs";
	}

	function start($info = ""){
		if($this->log_id != null){
			error_log("Only start logger once per session.",4);
			return;
		}
		global $wpdb;
		$wpdb->insert(
			$this->tableName(),
			array(
				'executed' => $this->plugin->timer->getStart(),
				'duration' => 0,
				'info' => "Running ⏳ $info",
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);
		$this->log_id = $wpdb->insert_id;
	}

	function update($duration, $info = null){

		if($this->log_id == null){
			$this->start();
		}
		$data = array('duration' => $duration);
		$data_format = array('%d');
		if($info != null){
			$data['info'] = $info;
			$data_format[] = '%s';
		}
		global $wpdb;
		return $wpdb->update(
			$this->tablename(),
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

	function addInfo($message, $duration = null){
		global $wpdb;
		$result = $wpdb->insert(
			$this->tableName(),
			array(
				'parent_id' => $this->log_id,
				'info' => $message,
				'executed' => time(),
				'duration' => $duration,
			),
			array(
				'%d',
				'%s',
				'%d',
				'%d',
			)
		);
		if($result == false){
			echo $wpdb->last_query;
			$error_message = "🚨 ".$wpdb->last_query;
			$this->errors[] = $error_message;
			error_log("Cron Logger: ".$error_message);
		} else {
			$this->update(
				$this->plugin->timer->getDuration()
			);
		}

	}

	function getList($args = array()){
		$args = (object) array_merge(
			array(
				"count" => 15,
				"page" => 1,
				"min_seconds" => null,
			),
			$args
		);
		global $wpdb;
		$count = $args->count;
		$page = $args->page;
		$offset = $count * ($page-1);

		$where_min_seconds = ($args->min_seconds != null)? "AND duration >= ".$args->min_seconds: "";

		return $wpdb->get_results(
			"SELECT * FROM ".$this->tableName()." WHERE parent_id IS NULL ".$where_min_seconds." ORDER BY executed DESC LIMIT $offset, $count"
		);
	}

	function getSublist($log_id , $count = 50, $page = 0){
		global $wpdb;
		$offset = $count * $page;
		return $wpdb->get_results(
			"SELECT * FROM ".$this->tableName()." WHERE parent_id = $log_id  ORDER BY id DESC LIMIT $offset, $count"
		);
	}

	function createTable(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta("CREATE TABLE IF NOT EXISTS ".$this->tableName() . " 
		(
		 id bigint(20) unsigned not null auto_increment,
		 parent_id bigint(20) unsigned default null,
		 executed bigint(20) unsigned default null ,
		 duration int(11) unsigned default null,
		 info text,
		 primary key (id),
		 key ( executed ),
		 key (duration)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	}
}