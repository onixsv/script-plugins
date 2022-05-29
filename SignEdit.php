<?php
/**
 * @name SignEdit
 * @author  alvin0319
 * @main    SignEdit\SignEdit
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace SignEdit;

use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class SignEdit extends PluginBase implements Listener{

	/** @var bool[]|BaseSign[] */
	protected array $queue = [];

	public static string $prefix = "§b§l[표지판] §r§7";

	public const FORM_MAIN = 19351;

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$command = new PluginCommand("표지판", $this, $this);
		$command->setDescription("표지판 명령어입니다.");
		$command->setAliases(["sign"]);
		$command->setPermission(DefaultPermissions::ROOT_OPERATOR);
		$this->getServer()->getCommandMap()->register("표지판", $command);
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 */
	public function handleInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();

		if(isset($this->queue[$player->getName()])){
			if($block instanceof BaseSign){
				$this->queue[$player->getName()] = $block;
				$encode = [
					"type" => "custom_form",
					"title" => "표지판 수정",
					"content" => [
						[
							"type" => "input",
							"text" => "Line 1",
							"default" => $block->getText()->getLine(0)
						],
						[
							"type" => "input",
							"text" => "Line 2",
							"default" => $block->getText()->getLine(1)
						],
						[
							"type" => "input",
							"text" => "Line 3",
							"default" => $block->getText()->getLine(2)
						],
						[
							"type" => "input",
							"text" => "Line 4",
							"default" => $block->getText()->getLine(3)
						]
					]
				];

				$pk = new ModalFormRequestPacket();
				$pk->formId = self::FORM_MAIN;
				$pk->formData = json_encode($encode);
				$player->getNetworkSession()->sendDataPacket($pk);
			}
		}
	}

	public function handlePacket(DataPacketReceiveEvent $event){
		$player = $event->getOrigin()->getPlayer();
		if(!$player instanceof Player)
			return;
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket){
			if($packet->formId === self::FORM_MAIN){
				$data = json_decode($packet->formData, true);
				if(!isset($this->queue[$player->getName()]) or is_bool($this->queue[$player->getName()])){
					//unset($this->queue[$player->getName()]);
					return;
				}
				$block = $this->queue[$player->getName()];
				$lines = [];
				for($i = 0; $i <= 3; $i++){
					if(isset($data[$i])){
						$lines[$i] = $data[$i];
					}
				}
				$block->setText(new SignText($lines));

				$ev = new SignChangeEvent($block->getPosition()->getWorld()->getBlock($block->getPosition()), $player, $block->getText());
				$ev->call();

				$block->getPosition()->getWorld()->setBlock($block->getPosition(), $block);

				$player->sendMessage(SignEdit::$prefix . "표지판을 수정하였습니다.");
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
			if(!isset($this->queue[$sender->getName()])){
				$this->queue[$sender->getName()] = true;
				$sender->sendMessage(SignEdit::$prefix . "수정을 원하는 표지판을 터치해주세요.");
			}else{
				unset($this->queue[$sender->getName()]);
				$sender->sendMessage(SignEdit::$prefix . "수정을 취소하였습니다.");
			}
		}
		return true;
	}
}