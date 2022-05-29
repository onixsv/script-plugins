<?php

/**
 * @name Reboot
 * @author  alvin0319
 * @main    Reboot\Reboot
 * @version 1.0.0
 * @api     4.0.0
 */

declare(strict_types=1);

namespace Reboot;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function date;

class Reboot extends PluginBase{

	protected function onEnable() : void{
		$handler = null;
		$handler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use (&$handler) : void{
			if((int) date("H") % 2 === 0 && (int) date("i") === 59 && (int) date("s") >= 50 && (int) date("s") <= 59){
				$this->start();
				$handler->cancel();
			}
		}), 20); // 2시간
		$c = new PluginCommand("재부팅", $this, $this);
		$c->setDescription("재부팅 명령어");
		$c->setPermission(DefaultPermissions::ROOT_OPERATOR);
		$this->getServer()->getCommandMap()->register("재부팅", $c);
	}

	public function start() : void{
		OnixUtils::broadcast("서버가 잠시후 재부팅 됩니다!");
		$this->getScheduler()->scheduleRepeatingTask(new class extends Task{
			protected $count = 10;

			public function onRun() : void{
				if($this->count !== 0){
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($this->count >= 3){
							$player->sendTitle("§b§l서버가 §a재부팅§f되기까지", "§d" . $this->count . "§f초 남았습니다!");
						}
					}
					--$this->count;
				}else{
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						$player->save();
						$player->disconnect("서버가 재부팅됐습니다!\n5초후 재접속 해주세요.");
					}

					foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
						$level->save(true);
					}

					Server::getInstance()->shutdown();
				}
			}
		}, 20);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
			$this->start();
		}
		return true;
	}
}