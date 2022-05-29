<?php
/**
 * @name Repair
 * @author  alvin0319
 * @main    Repair\Repair
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace Repair;

use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\block\BlockLegacyIds as BlockIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\form\Form;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Repair extends PluginBase implements Listener{

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 */
	public function handleInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();

		if($block->getId() === BlockIds::COMMAND_BLOCK){
			$player->sendForm(new class implements Form{

				public function jsonSerialize() : array{
					return [
						"type" => "modal",
						"title" => "§lFixSystem - Master",
						"content" => "§l정말 손에 들고 있는 아이템을 수리하시겠습니까?\n§d1 퍼센트§f의 확률로 아이템이 §c소멸§f할 수 있습니다.\n비용 : §d1000§f원",
						"button1" => "§l네",
						"button2" => "§l아니요"
					];
				}

				public function handleResponse(Player $player, $data) : void{
					if($data !== null){
						if($data){
							$item = $player->getInventory()->getItemInHand();

							if(!$item instanceof Durable){
								OnixUtils::message($player, "수리할 아이템은 도구여야 합니다.");
								return;
							}

							if($item->getDamage() <= 0){
								OnixUtils::message($player, "아이템 내구도가 닳지 않았습니다.");
								return;
							}

							if(EconomyAPI::getInstance()->reduceMoney($player, 1000) !== EconomyAPI::RET_SUCCESS){
								OnixUtils::message($player, "돈이 부족합니다.");
								return;
							}

							if(mt_rand(1, 100) <= 1){
								$player->getInventory()->removeItem($item);
								OnixUtils::message($player, "어이쿠... 기계가 고장이 나서 아이템이 소멸해버렸네요!");
							}else{
								$item->setDamage(0);
								$player->getInventory()->setItemInHand($item);
								OnixUtils::message($player, "아이템이 수리되었습니다.");
							}
						}
					}
				}
			});
		}
	}
}