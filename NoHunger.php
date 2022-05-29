<?php
/**
 * @name NoHunger
 * @author  alvin0319
 * @main    alvin0319\NoHunger\NoHunger
 * @version 1.0.0
 * @api     4.0.0
 */

namespace alvin0319\NoHunger;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\plugin\PluginBase;

class NoHunger extends PluginBase implements Listener{

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerExhause(PlayerExhaustEvent $event) : void{
		$event->cancel();
	}
}
