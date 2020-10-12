<?php
declare(strict_types=1);

namespace eastereggs;

use eastereggs\entity\EasterEgg;
use eastereggs\settings\SettingsManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use function array_search;
use function in_array;

final class EventListener implements Listener{

	/**
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();

		if(in_array($player, EasterEggs::$allowedEggAttackers))
			unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();

		if(in_array($player, EasterEggs::$allowedEggAttackers)){
			unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
			$player->sendMessage(EasterEggs::PREFIX."The egg removal was aborted.");
		}
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$player = $event->getPlayer();
		/** @var InventoryTransactionPacket $packet */
		if(!($packet = $event->getPacket()) instanceof InventoryTransactionPacket){
			return;
		}
		if(!($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY)){
			return;
		}
		$entity = $player->getLevel()->getEntity($packet->trData->entityRuntimeId);
		if(!$entity instanceof EasterEgg){
			$player->sendMessage(EasterEggs::PREFIX."An error occurred while removing the entity.");
			return;
		}
		if(in_array($player, EasterEggs::$allowedEggAttackers)){
			unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
			$entity->flagForDespawn();
			$player->sendMessage(EasterEggs::PREFIX."The egg was successfully removed.");
		}elseif(SettingsManager::getInstance()->isEggFindingEnabled()){
			if($entity->foundBy[$player->getName()] ?? 0 >= SettingsManager::getInstance()->getEggFindings()){
				$player->sendMessage(EasterEggs::PREFIX."You've already found this egg.");
			}else{
				if(!isset($entity->foundBy[$player->getName()]))
					$entity->foundBy[$player->getName()] = 0;
				$entity->foundBy[$player->getName()]++;
				foreach(SettingsManager::getInstance()->getActions() as $action)
					$action->execute($player);
			}
		}
		$event->setCancelled(true);
	}

}