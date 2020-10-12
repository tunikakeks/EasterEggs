<?php
declare(strict_types=1);

namespace eastereggs\settings\action;

use pocketmine\Player;
use function sprintf;
use function str_ireplace;

abstract class Action{

	public const TYPE_AWARD = "award";
	public const TYPE_COMMAND = "command";

	/** @var string */
	protected $type;

	/**
	 * Action constructor.
	 * @param string $type
	 */
	public function __construct(string $type){
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	final public function getType() : string{
		return $this->type;
	}

	/**
	 * @param string $input
	 * @param Player $player
	 * @return string
	 */
	final protected function processPlaceholders(string $input, Player $player) : string{
		return str_ireplace([
			"{player}"], [
				sprintf("%1\$s{$player->getName()}%1\$s", "\"")
		], $input);
	}

	abstract public function execute(Player $player) : void;

}