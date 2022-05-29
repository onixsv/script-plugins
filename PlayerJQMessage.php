<?php
/**
 * @name PlayerJQMessage
 * @author  alvin0319
 * @main    PlayerJQMessage\PlayerJQMessage
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace PlayerJQMessage;

use alvin0319\GuildAPI\Guild;
use alvin0319\GuildAPI\GuildAPI;
use alvin0319\LevelAPI\LevelAPI;
use ConnectTime\ConnectTime;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use function strtolower;

class PlayerJQMessage extends PluginBase implements Listener{

	/** @var PlayerJQMessage */
	public static PlayerJQMessage $i;

	protected function onLoad() : void{
		self::$i = $this;
	}

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->getServer()->getOnlinePlayers() as $player)
				$this->applyNameTag($player);
		}), 1200);
	}

	public function handlePlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();

		$event->setJoinMessage("");

		if(!$player->hasPlayedBefore()){
			OnixUtils::broadcast($player->getName() . "님, 환영합니다.");
		}

		if(($erank = $this->getEconomyRank($player)) <= 5 && $erank !== -1){
			OnixUtils::broadcast("돈 순위 §d{$erank}§f위 §d{$player->getName()}§f님이 접속했습니다.");
		}
		if(($crank = $this->getConnectTimeRank($player)) <= 5 && $crank !== -1){
			OnixUtils::broadcast("접속시간 순위 §d{$crank}§f위 §d{$player->getName()}§f님이 접속했습니다.");
		}
		if(($lrank = $this->getLevelRank($player)) <= 5 && $lrank !== -1){
			OnixUtils::message($player, "레벨 순위 §d{$lrank}§f위 §d{$player->getName()}§f님이 접속했습니다.");
		}

		$this->applyNameTag($player);
	}

	public function receivePacket(DataPacketReceiveEvent $event){
		$player = $event->getOrigin()->getPlayer();
		if(!$player instanceof Player){
			return;
		}
		$packet = $event->getPacket();

		if($packet instanceof PlayerActionPacket){
			switch($packet->action){
				case PlayerActionPacket::ACTION_START_SWIMMING:
					$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SWIMMING, true);
					break;
				case PlayerActionPacket::ACTION_STOP_SWIMMING:
					$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SWIMMING, false);
					break;
			}
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void{
		$packets = $event->getPackets();

		foreach($packets as $packet){
			if($packet instanceof StartGamePacket){
				$packet->gameRules["doimmedaterespawn"] = new BoolGameRule(true, false);
				$packet->gameRules["showcoordinates"] = new BoolGameRule(true, false);
			}
		}
	}

	public function handlePlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();

		$event->setQuitMessage("");

		$this->getServer()->broadcastTip("§a" . $player->getName() . "§f님이 §c퇴장§f하셨습니다.");
	}

	public function applyNameTag(Player $player){
		$guild = GuildAPI::getInstance()->getGuildByPlayer($player);
		$level = LevelAPI::getInstance()->getLevel($player);

		if($guild instanceof Guild){
			if($guild->getGuildStorage()->getAllowNameTag()){
				$player->setNameTag("§d§l[ §f" . $player->getName() . " §d]\n§r§7§l소속된 길드: " . $guild->getName() . "\n§r§7§l레벨: §d" . $level);
			}else{
				$player->setNameTag("§d§l[ §f" . $player->getName() . " §d]\n§r§7§l레벨: §d" . $level);
			}
		}else{
			$player->setNameTag("§d§l[ §f" . $player->getName() . " §d]\n§r§7§l레벨: §d" . $level);
		}
	}

	public function getConnectTimeRank(Player $player) : int{
		//foreach(ConnectTime::getInstance()->getRankPage(1) as $rank => $){}
		$c = 0;
		foreach(ConnectTime::getInstance()->getAll() as $name => $connectTime){
			++$c;
			if(strtolower($name) === strtolower($player->getName())){
				return $c;
			}
		}
		return -1;
	}

	public function getEconomyRank(Player $player) : int{
		$rank = EconomyAPI::getInstance()->getRank($player);
		if($rank === false){
			return -1;
		}
		return $rank;
	}

	public function getLevelRank(Player $player) : int{
		$c = 0;
		foreach(LevelAPI::getInstance()->getAll() as $name => $level){
			++$c;
			if(strtolower($name) === strtolower($player->getName())){
				return $c;
			}
		}
		return -1;
	}
}