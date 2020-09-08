<?php
declare(strict_types=1);
namespace eastereggs\commands;

use eastereggs\EasterEggs;
use eastereggs\entity\EasterEgg;
use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use function array_filter;
use function array_search;
use function array_shift;
use function file_get_contents;
use function in_array;
use function strtolower;
use const DIRECTORY_SEPARATOR as DS;

final class EasterCommand extends Command implements PluginIdentifiableCommand{

	/**
	 * EasterCommand constructor.
	 */
	public function __construct(){
		parent::__construct("eastereggs", "Manage your easter eggs!", "egg <spawn|remove> [color: string|all]", ["egg"]);
		$this->setPermission("eastereggs.command");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(EasterEggs::PREFIX . "Use this command in-game.");
			return;
		}
		if(!$this->testPermissionSilent($sender)){
			$sender->sendMessage(EasterEggs::PREFIX . "You don't have the permission to execute this command.");
			return;
		}
		if(!isset($args[0])){
			$sender->sendMessage(EasterEggs::PREFIX . "Usage: {$this->getUsage()}");
			return;
		}
		switch(strtolower(array_shift($args))){
			case "spawn":
			case "add":
				$color = strtolower($args[0] ?? "red");
				if(!in_array($color, ["red", "green", "blue"])){
					$sender->sendMessage(EasterEggs::PREFIX . "Invalid color: $color.");
					break;
				}
				$nbt = Entity::createBaseNBT($sender, null, $sender->getYaw(), $sender->getPitch());
				$nbt->setTag($sender->namedtag->getTag("Skin"));
				$egg = Entity::createEntity("EasterEgg", $sender->getLevel(), $nbt);
				if(!$egg instanceof EasterEgg){
					$sender->sendMessage(EasterEggs::PREFIX . "An error occurred while spawning the entity.");
					break;
				}
				try{
					$egg->setSkin(new Skin("EasterEgg", EasterEggs::imageToSkinData(EasterEggs::getPath() . "resources" . DS . "easteregg.$color.png"), "", "geometry.easteregg", file_get_contents(EasterEggs::getPath() . "resources" . DS ."egg.geo.json")));
					$egg->sendSkin();
					$egg->spawnToAll();
					$sender->sendMessage(EasterEggs::PREFIX . "Spawned the egg successfully.");
				}catch(Exception $e){
					$sender->sendMessage(EasterEggs::PREFIX . "An error occurred while spawning the entity.");
					$egg->close();
				}
				break;
			case "remove":
			case "delete":
				if(strtolower($args[0] ?? "") === "all"){
					if(in_array($sender, EasterEggs::$allowedEggAttackers)){
						unset(EasterEggs::$allowedEggAttackers[array_search($sender, EasterEggs::$allowedEggAttackers)]);
					}
					$sum = 0;
					foreach(array_filter($sender->getLevel()->getEntities(), function(Entity $entity) : bool{
						return $entity instanceof EasterEgg;
					}) as $egg){
						$egg->close();
						$sum++;
					}
					if($sum === 0){
						$sender->sendMessage(EasterEggs::PREFIX . "There aren't any eggs to remove.");
						break;
					}
					$tmp = $sum === 1 ? "egg was" : "eggs were";
					$sender->sendMessage(EasterEggs::PREFIX . "$sum $tmp removed.");
					break;
				}
				if(in_array($sender, EasterEggs::$allowedEggAttackers)){
					$sender->sendMessage(EasterEggs::PREFIX . "Click an egg before removing another one.");
				}
				EasterEggs::$allowedEggAttackers[] = $sender;
				$sender->sendMessage(EasterEggs::PREFIX . "Click an egg for removing it. To cancel, hit anything else.");
				break;
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin() : Plugin{
		return EasterEggs::getInstance();
	}

}