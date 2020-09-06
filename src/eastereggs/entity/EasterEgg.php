<?php
declare(strict_types=1);
namespace eastereggs\entity;

use eastereggs\EasterEggs;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use function array_search;
use function in_array;

final class EasterEgg extends Human{

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		EasterEggs::getInstance()->getLogger()->debug("spawn easteregg with id {$this->id}");

	}

	public function canBeCollidedWith() : bool{
		return true;
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$source instanceof EntityDamageByEntityEvent){
			return;
		}
		if(!($player = $source->getDamager()) instanceof Player){
			return;
		}
		if(!in_array($player, EasterEggs::$allowedEggAttackers)){
			return;
		}
		unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
		$this->close();
		$player->sendMessage(EasterEggs::PREFIX . "The egg was successfully removed.");
	}

	protected function updateFallState(float $distanceThisTick, bool $onGround) : void{}

}