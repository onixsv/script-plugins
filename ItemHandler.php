<?php
/**
 * @name ItemHandler
 * @author  alvin0319
 * @main    ItemHandler\ItemHandler
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace ItemHandler;

use OnixUtils\OnixUtils;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemBlock;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class ItemHandler extends PluginBase{

	protected function onEnable() : void{
		OnixUtils::command("아이템이름", "아이템 이름을 설정합니다.", [], true, function(CommandSender $sender, string $commandLabel, array $args) : void{
			if(isset($args[0])){
				if($sender instanceof Player){
					$item = $sender->getInventory()->getItemInHand();
					if(!$item->isNull()){
						$item->setCustomName(implode(" ", $args));
						$sender->getInventory()->setItemInHand($item);
					}else{
						OnixUtils::message($sender, "아이템은 공기가 아니어야 합니다.");
					}
				}else{
				}
			}else{
				OnixUtils::message($sender, "/아이템이름 <하고싶은이름(띄어쓰기 가능)>");
			}
		});

		OnixUtils::command("아이템로어", "아이템의 로어를 설정합니다.", [], true, function(CommandSender $sender, string $commandLabel, array $args) : void{
			if(isset($args[0])){
				if($sender instanceof Player){
					$item = $sender->getInventory()->getItemInHand();
					if(!$item->isNull()){
						$item->setLore($args);
						$sender->getInventory()->setItemInHand($item);
					}else{
						OnixUtils::message($sender, "아이템은 공기가 아니어야 합니다.");
					}
				}else{
				}
			}else{
				OnixUtils::message($sender, "/아이템로어 <하고싶은로아(띄어쓰기시 줄바꿈)>");
			}
		});

		OnixUtils::command("iv", "아이템코드를 확인합니다", [], true, function(CommandSender $sender, string $commandLabel, array $args) : void{
			if($sender instanceof Player){
				$item = $sender->getInventory()->getItemInHand();
				OnixUtils::message($sender, "아이템 코드: " . $item->getId() . ":" . $item->getDamage() . ($item instanceof ItemBlock ? "블럭코드: " . $item->getBlock()->getId() . ":" . $item->getBlock()->getDamage() : ""));
			}
		});
	}
}