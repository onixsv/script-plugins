<?php
/**
 * @name GuildAPIManager
 * @author  alvin0319
 * @main    GuildAPIManager\GuildAPIManager
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace GuildAPIManager;

use alvin0319\GuildAPI\event\EconomyEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class GuildAPIManager extends PluginBase implements Listener{

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onEconomyEvent(EconomyEvent $event) : void{
		$player = $event->getPlayer();
		$money = $event->getMoney();
		if(!$event->isAddMoney()){
			if(EconomyAPI::getInstance()->reduceMoney($player, $money) !== EconomyAPI::RET_SUCCESS){
				$event->cancel();
			}
		}
	}
}