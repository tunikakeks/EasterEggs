<?php
declare(strict_types=1);

namespace eastereggs\settings;

use eastereggs\settings\action\Action;
use eastereggs\settings\action\AwardAction;
use eastereggs\settings\action\CommandAction;
use eastereggs\utils\SingletonTrait;
use pocketmine\utils\Config;
use function array_key_first;
use function array_shift;
use function strpos;
use function strstr;
use function strtolower;
use function substr;
use function var_dump;

final class SettingsManager{

	use SingletonTrait;

	/** @var Config */
	private $config;

	/**
	 * @param string $path
	 */
	public function initialize(string $path) : void{
		$this->config = new Config($path, Config::YAML);
	}

	/**
	 * @return bool
	 */
	public function isEggFindingEnabled() : bool{
		return $this->config->getNested("egg finding", true);
	}

	/**
	 * @return int
	 */
	public function getEggFindings() : int{
		return $this->config->getNested("egg findings", 1);
	}


	/**
	 * @return Action[]
	 * @throws \Exception
	 */
	public function getActions() : array{
		$raw = $this->config->getNested("action", []);
		$actions = [];
		foreach($raw as $index => $action){
			switch($type = $this->getActionType($action)){
				case "award":
				case "message":
				case "msg":
					$actions[] = new AwardAction($action[$type]["message"]);
					break;
				case "command":
				case "cmd":
					$actions[] = new CommandAction($action[$type]["command"], (bool) $action[$type]["execute as player"] ?? false);
					break;
				default:
					throw new \Exception("Unknown action type: {$type}");
			}
		}
		return $actions;
	}

	/**
	 * @param array $raw
	 * @return string
	 */
	private function getActionType(array $raw) : string{
		return strtolower(array_key_first($raw));
	}

}