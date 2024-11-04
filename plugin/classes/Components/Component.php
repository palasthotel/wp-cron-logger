<?php


namespace CronLogger\Components;
abstract class Component {


	public function __construct(
		public \CronLogger\Plugin $plugin
	) {
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	public function onCreate(): void {
		// init your hooks and stuff
	}
}
