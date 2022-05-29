<?php
/**
 * @name Gamble
 * @author  alvin0319
 * @main    alvin0319\Gamble
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace alvin0319;

use onebone\economyapi\EconomyAPI;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;

class Gamble extends PluginBase implements Listener{

	protected $prefix = '§d<§f시스템§d> §r';

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 */
	public function onInteract(PlayerInteractEvent $event){
		$item = $event->getItem();
		$block = $event->getBlock();
		$player = $event->getPlayer();
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			if($block->getId() === BlockLegacyIds::NOTE_BLOCK){
				if($item->getId() !== ItemIds::GUNPOWDER){
					$player->sendMessage($this->prefix . '코인으로 터치해주세요');
					return;
				}
				if($item->getMeta() !== 5){
					$player->sendMessage($this->prefix . '정품 코인 만을 사용해주세요');
					return;
				}
				$rand = mt_rand(0, 100000);
				$player->getInventory()->removeItem(ItemFactory::getInstance()->get(ItemIds::GUNPOWDER, 5, 1));
				EconomyAPI::getInstance()->addMoney($player, $rand);
				$player->sendMessage($this->prefix . $rand . '원 당첨입니다!');
			}
		}
	}
}