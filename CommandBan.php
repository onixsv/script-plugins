<?php
/**
 * @name    CommandBan
 * @author  alvin0319
 * @main    alvin0319\CommandBan\CommandBan
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace alvin0319\CommandBan;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use function count;
use function in_array;
use function trim;

/**
 * @package alvin0319\CommandBan
 * @Copyright (c) alvin0319(앨빈), all rights reserved.
 */
class CommandBan extends PluginBase{

	public static $prefix = "§d<§f시스템§d> §f";

	/** @var Config */
	protected Config $config;

	protected array $db = [];

	protected function onEnable() : void{
		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, ["commands" => []]);
		$this->db = $this->config->getAll();

		$c = new PluginCommand("명령어밴", $this, $this);
		$c->setDescription("명령어밴 명령어입니다.");
		$c->setPermission(DefaultPermissions::ROOT_OPERATOR);
		$this->getServer()->getCommandMap()->register("명령어밴", $c);

		$this->getScheduler()->scheduleTask(new ClosureTask(function() : void{
			$count = 0;
			foreach($this->db["commands"] as $c => $perm){
				if(($command = $this->getServer()->getCommandMap()->getCommand($c)) instanceof Command){
					$command->setPermission(DefaultPermissions::ROOT_OPERATOR);
					$count++;
				}
			}
			$this->getLogger()->info("{$count}개의 명령어를 차단했습니다.");
		}));
	}

	protected function onDisable() : void{
		$this->config->setAll($this->db);
		$this->config->save();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($args[0] ?? "x"){
			case "추가":
				if(trim($args[1] ?? "") !== ""){
					if(!in_array($args[1], $this->db["commands"])){
						$c = $this->getServer()->getCommandMap()->getCommand($args[1]);
						if($c instanceof Command){
							$this->db["commands"][$c->getName()] = $c->getPermission();
							$c->setPermission(DefaultPermissions::ROOT_OPERATOR);
							$sender->sendMessage(CommandBan::$prefix . "명령어 {$c->getName()}을(를) 차단했습니다.");
						}else{
							$sender->sendMessage(CommandBan::$prefix . "해당 명령어는 서버에 등록되어 있지 않습니다.");
						}
					}else{
						$sender->sendMessage(CommandBan::$prefix . "해당 명령어는 이미 차단되어 있습니다.");
					}
				}else{
					$sender->sendMessage(CommandBan::$prefix . "사용법: /명령어밴 추가 [명령어] - 명령어를 차단합니다.");
				}
				break;
			case "삭제":
				if(trim($args[1] ?? "") !== ""){
					if(isset($this->db["commands"][$args[1]])){
						$perm = $this->db["commands"][$args[1]];
						unset($this->db["commands"][$args[1]]);
						if(($c = $this->getServer()->getCommandMap()->getCommand($args[1])) instanceof Command){
							$c->setPermission($perm);
							//$this->getServer()->getCommandMap()->unregister($c);
							//$this->getServer()->getCommandMap()->register($c->getLabel(), $c);
							foreach($this->getServer()->getOnlinePlayers() as $player){
								$player->getNetworkSession()->syncAvailableCommands();
							}
						}
						$sender->sendMessage(CommandBan::$prefix . "명령어 {$args[1]}을(를) 차단 해제했습니다.");
					}else{

					}
				}else{
					$sender->sendMessage(CommandBan::$prefix . "사용법: /명령어밴 삭제 [명령어] - 명령어를 차단 해제합니다.");
				}
				break;
			case "목록":
				if(count($this->db["commands"]) > 0){
					$c = 0;
					foreach($this->db["commands"] as $banned => $perm){
						$sender->sendMessage(CommandBan::$prefix . "[{$c}] {$banned}");
						$c++;
					}
				}else{
					$sender->sendMessage(CommandBan::$prefix . "명령어밴 목록이 비어있습니다.");
				}
				break;
			default:
				foreach([
					["추가", "명령어를 차단합니다."],
					["삭제", "명령어를 차단 해제합니다."],
					["목록", "명령어 차단 목록을 확인합니다."]
				] as $usage){
					$sender->sendMessage(CommandBan::$prefix . "/명령어밴 " . $usage[0] . " - " . $usage[1]);
				}
		}
		return true;
	}
}