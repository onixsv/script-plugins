<?php
/**
 * @name NoFall
 * @author  alvin0319
 * @main    NoFall\NoFall
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace NoFall;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class NoFall extends PluginBase{

	protected function onEnable() : void{
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->getServer()->getOnlinePlayers() as $player){
				if($player->getLocation()->getY() < -3){
					$ev = new PlayerDeathEvent($player, $player->getDrops(), $player->getXpDropAmount(), "");
					$ev->call();

					$player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
				}
			}
		}), 20);
	}
}