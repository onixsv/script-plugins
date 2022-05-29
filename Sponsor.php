<?php
/**
 * @name Sponsor
 * @author  alvin0319
 * @main    Sponsor\Sponsor
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace Sponsor;

use Cash\Cash;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function strtolower;

class Sponsor extends PluginBase implements Listener{

	/** @var Config */
	protected Config $config;

	protected array $db = [];

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$command = new PluginCommand("후원", $this, $this);
		$command->setDescription("후원 명령어입니다.");
		$this->getServer()->getCommandMap()->register("후원", $command);
		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, []);
		$this->db = $this->config->getAll();
	}

	public function onDisable() : void{
		$this->config->setAll($this->db);
		$this->config->save();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($args[0] ?? "x"){
			case "정보":
				if(trim($args[1] ?? "") !== ""){
					if(isset($this->db[strtolower($args[1])])){
						$name = strtolower($args[1]);
					}else{
						$name = strtolower($sender->getName());
					}
				}else{
					$name = strtolower($sender->getName());
				}
				OnixUtils::message($sender, $name . "님의 후원 총액은 " . EconomyAPI::getInstance()->koreanWonFormat($this->db[$name]) . " 입니다.");
				break;
			case "지급":
				if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
					if(trim($args[1] ?? "") !== ""){
						if(trim($args[2] ?? "") !== "" and is_numeric($args[2]) and intval($args[2]) > 0){
							if(trim($args[3] ?? "") !== ""){
								if(in_array($args[3], ["true", "false"])){
									$bool = boolval($args[3]);
								}else{
									$bool = false;
								}
							}else{
								$bool = false;
							}
							if(!isset($this->db[strtolower($args[1])])){
								$this->db[strtolower($args[1])] = 0;
							}
							$cash = intval($args[2]);
							$cash = $bool ? $cash * 1.1 : $cash;
							$this->db[strtolower($args[1])] += intval($args[2]);
							OnixUtils::message($sender, "{$args[1]}님께 {$args[2]}원의 후원을 추가했습니다.");
							Cash::getInstance()->addCash(strtolower($args[1]), intval($cash));
						}else{
							OnixUtils::message($sender, "/후원 지급 [닉네임] [금액] [계좌인지여부(true/false)] - 후원을 지급합니다.");
						}
					}else{
						OnixUtils::message($sender, "/후원 지급 [닉네임] [금액] [계좌인지여부(true/false)] - 후원을 지급합니다.");
					}
				}
				break;
			default:
				OnixUtils::message($sender, "/후원 정보 [닉네임] - 후원 정보는 봅니다.");
				if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR))
					OnixUtils::message($sender, "/후원 지급 [닉네임] [금액] [계좌인지여부(true/false)] - 후원을 지급합니다.");
		}
		return true;
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		if(!isset($this->db[strtolower($player->getName())])){
			$this->db[strtolower($player->getName())] = 0;
		}
		if($this->db[strtolower($player->getName())] > 0){
			OnixUtils::broadcast("후원자 {$player->getName()}님이 접속했습니다.");
		}
	}
}