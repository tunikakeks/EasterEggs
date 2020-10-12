<?php
declare(strict_types=1);

namespace eastereggs\entity;

use eastereggs\EasterEggs;
use eastereggs\settings\SettingsManager;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use function array_map;
use function array_search;
use function in_array;

final class EasterEgg extends Human{

	/** @var int[] */
	public $foundBy = [];

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		/** @var IntTag $tag */
		foreach($this->namedtag->getTag("FoundBy", CompoundTag::class)->getValue() as $index => $tag)
			$this->foundBy[$tag->getName()] = $tag->getValue();
		$this->setGenericFlag(self::DATA_FLAG_IMMOBILE, true);

		EasterEggs::getInstance()->getLogger()->debug("spawn easteregg with id {$this->id}");
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$source instanceof EntityDamageByEntityEvent){
			return;
		}
		/** @var Player $player */
		if(!($player = $source->getDamager()) instanceof Player){
			return;
		}
		if(in_array($player, EasterEggs::$allowedEggAttackers)){
			unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
			$this->flagForDespawn();
			$player->sendMessage(EasterEggs::PREFIX."The egg was successfully removed.");
		}elseif(SettingsManager::getInstance()->isEggFindingEnabled()){
			if($this->foundBy[$player->getName()] ?? 0 >= SettingsManager::getInstance()->getEggFindings()){
				$player->sendMessage(EasterEggs::PREFIX."You've already found this egg.");
			}else{
				if(!isset($this->foundBy[$player->getName()]))
					$this->foundBy[$player->getName()] = 0;
				$this->foundBy[$player->getName()]++;
				foreach(SettingsManager::getInstance()->getActions() as $action)
					$action->execute($player);
			}
		}
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setTag(new CompoundTag("FoundBy", []), true);
		foreach($this->foundBy as $player => $count)
			$this->namedtag->getTag("FoundBy", CompoundTag::class)->setTag(new IntTag($player, $count));
	}

}