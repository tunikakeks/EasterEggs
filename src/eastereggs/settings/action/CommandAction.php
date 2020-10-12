<?php
declare(strict_types=1);

namespace eastereggs\settings\action;

use eastereggs\EasterEggs;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

final class CommandAction extends Action{

	/** @var string */
	private $command;
	/** @var bool */
	private $executeAsPlayer;

	/**
	 * CommandAction constructor.
	 * @param string $command
	 * @param bool $executeAsPlayer
	 */
	public function __construct(string $command, bool $executeAsPlayer){
		parent::__construct(self::TYPE_COMMAND);
		$this->command = $command;
		$this->executeAsPlayer = $executeAsPlayer;
	}

	public function execute(Player $player) : void{
		EasterEggs::getInstance()->getServer()->getCommandMap()->dispatch($this->executeAsPlayer ? $player : new ConsoleCommandSender(), $this->processPlaceholders($this->command, $player));
	}

}