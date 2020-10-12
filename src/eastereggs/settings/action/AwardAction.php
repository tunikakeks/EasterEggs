<?php
declare(strict_types=1);

namespace eastereggs\settings\action;

use pocketmine\Player;

final class AwardAction extends Action{

	/** @var string */
	private $message;

	/**
	 * AwardAction constructor.
	 * @param string $message
	 */
	public function __construct(string $message){
		parent::__construct(self::TYPE_AWARD);
		$this->message = $message;
	}

	public function execute(Player $player) : void{
		$player->sendMessage($this->processPlaceholders($this->message, $player));
	}

}