<?php
declare(strict_types=1);

namespace eastereggs\utils;

trait SingletonTrait{

	private function __construct(){
	}

	/** @var self */
	private static $instance;

	/**
	 * @return self
	 */
	public static function getInstance() : self{
		return self::$instance ?? self::$instance = new static();
	}

}