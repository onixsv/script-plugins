<?php
/**
 * @name DistanceChat
 * @author  alvin0319
 * @main    DistanceChat\DistanceChat
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace DistanceChat;

use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase;
use function count;

class DistanceChat extends PluginBase implements Listener{

	/** @var int[] */
	protected $chatTime = [];

	/** @var int[] */
	protected $commandTime = [];

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		OnixUtils::command("확성기", "/확성기 <할말> - 1000원을 지불하고 확성기를 사용합니다.", [], false, function(CommandSender $sender, string $commandLabel, array $args) : void{
			if(trim($args[0] ?? "") !== ""){
				if(EconomyAPI::getInstance()->reduceMoney($sender, 1000) === EconomyAPI::RET_SUCCESS){
					$this->getServer()->broadcastMessage("§d<§f확성기§d> §f" . $sender->getName() . " > §l" . implode(" ", $args));
				}else{
					OnixUtils::message($sender, "돈이 부족합니다.");
				}
			}else{
				OnixUtils::message($sender, "할 말을 적어주세요.");
			}
		});
	}

	public function handlePlayerChat(PlayerChatEvent $event){
		$player = $event->getPlayer();

		if(isset($this->chatTime[$player->getName()])){
			if(time() - $this->chatTime[$player->getName()] < 1 && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				$event->cancel();
				OnixUtils::message($player, "채팅 속도가 너무 빠릅니다!");
				return;
			}
		}
		$this->chatTime[$player->getName()] = time();
		if(count($this->getServer()->getOnlinePlayers()) > 34){
			if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				return;
			}
			$recipients = [new ConsoleCommandSender($this->getServer(), $this->getServer()->getLanguage())];

			foreach($this->getServer()->getOnlinePlayers() as $target){
				if($target->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
					$recipients[] = $target;
				}elseif($player->getWorld()->getFolderName() === $target->getWorld()->getFolderName() && $player->getPosition()->distance($target->getPosition()) <= 200){
					$recipients[] = $target;
				}
			}
			$event->setRecipients($recipients);
		}
	}

	public function handlePlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		if(substr($event->getMessage(), 0, -1) === "/"){
			if(isset($this->commandTime[$player->getName()])){
				if(time() - $this->commandTime[$player->getName()] < 1){
					$event->cancel();
					OnixUtils::message($player, "명령어 속도가 너무 빠릅니다!");
					return;
				}
			}
			$this->commandTime[$player->getName()] = time();
		}
	}
}