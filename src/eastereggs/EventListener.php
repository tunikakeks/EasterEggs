<?php
declare(strict_types=1);
namespace eastereggs;

use eastereggs\entity\EasterEgg;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use function array_search;
use function in_array;
use function var_dump;

final class EventListener implements Listener{

	/**
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		if(!in_array($player, EasterEggs::$allowedEggAttackers)){
			return;
		}
		unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		if(!in_array($player, EasterEggs::$allowedEggAttackers)){
			return;
		}
		unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
		$player->sendMessage(EasterEggs::PREFIX . "The egg removal was aborted.");
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$player = $event->getPlayer();
		if(!in_array($player, EasterEggs::$allowedEggAttackers)){
			return;
		}
		if(!($packet = $event->getPacket()) instanceof InventoryTransactionPacket){
			return;
		}
		if(!($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY)){
			return;
		}
		$entity = $player->getLevel()->getEntity($packet->trData->entityRuntimeId);
		if(!$entity instanceof EasterEgg){
			$player->sendMessage(EasterEggs::PREFIX . "An error occurred while removing the entity.");
			return;
		}
		unset(EasterEggs::$allowedEggAttackers[array_search($player, EasterEggs::$allowedEggAttackers)]);
		$entity->close();
		$player->sendMessage(EasterEggs::PREFIX . "The egg was successfully removed.");
		$event->setCancelled(true);
	}

}