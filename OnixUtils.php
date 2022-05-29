<?php
/**
 * @name OnixUtils
 * @author  alvin0319
 * @main    OnixUtils\OnixUtils
 * @version 1.0.0
 * @api     4.0.0
 */
declare(strict_types=1);

namespace OnixUtils;

use Closure;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\world\Position;
use ReflectionClass;
use ReflectionException;
use function date_default_timezone_set;
use function explode;
use function file_exists;
use function implode;
use function strtolower;
use function trim;
use const JSON_BIGINT_AS_STRING;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class OnixUtils extends PluginBase{

	protected function onLoad() : void{
		DefaultPermissions::registerCorePermissions();
		$operatorPermission = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
		PermissionManager::getInstance()->addPermission(new Permission("op", '', [$operatorPermission]));

		date_default_timezone_set("Asia/Seoul");
	}

	protected function onEnable() : void{
		$this->getScheduler()->scheduleTask(new ClosureTask(function() : void{
			foreach($this->getServer()->getPluginManager()->getPlugins() as $plugin){
				try{
					$reflection = new ReflectionClass($plugin);
					if($reflection->hasProperty("prefix")){
						$property = $reflection->getProperty("prefix");
						if(!$property->isPublic()){
							$property->setAccessible(true);
						}
						$property->setValue($plugin, "§d<§f시스템§d> §f");
					}elseif($reflection->getStaticPropertyValue("prefix", "") !== ""){
						$reflection->setStaticPropertyValue("prefix", "§d<§f시스템§d> §f");
					}
				}catch(ReflectionException $e){
					continue;
				}
			}
		}));
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $text
	 *
	 * @return void
	 */
	public static function message(CommandSender $sender, string $text){
		$sender->sendMessage("§d<§f시스템§d> §f" . $text);
	}

	/**
	 * @param string $text
	 *
	 * @return void
	 */
	public static function broadcast(string $text){
		Server::getInstance()->broadcastMessage("§d<§f시스템§d> §f" . $text);
	}

	public static function posToStr(?Position $pos) : ?string{
		if($pos === null)
			return null;
		return implode(":", [$pos->x, $pos->y, $pos->z, $pos->getWorld()->getFolderName()]);
	}

	public static function strToPos(?string $str) : ?Position{
		if($str === null)
			return null;
		if(trim($str) === "")
			return null;
		[$x, $y, $z, $world] = explode(":", $str);

		return new Position((float) $x, (float) $y, (float) $z, Server::getInstance()->getWorldManager()->getWorldByName($world));
	}

	public static function convertTimeToString(?int $value) : string{
		if($value === null)
			return "0분";
		$h = (int) ($value / 60 / 60);
		$m = ((int) ($value / 60)) - ($h * 60);
		$s = (int) $value - (($h * 60 * 60) + ($m * 60));

		$str = "";

		if($h > 0)
			$str .= $h . "시간 ";
		if($m > 0)
			$str .= $m . "분 ";
		$str .= $s . "초";

		return $str;
	}

	public static function command(string $command, string $description = "", array $alias = [], bool $isOp = false, Closure $c = null){
		Utils::validateCallableSignature(function(CommandSender $sender, string $commandLabel, array $args) : void{
		}, $c);

		Server::getInstance()->getCommandMap()->register($command, new class($command, $description, $alias, $isOp, $c) extends Command{

			public $c;

			public $isOp;

			public function __construct(string $command, string $description, array $alias, bool $isOp, Closure $c){
				parent::__construct($command, $description, "", $alias);
				if($isOp){
					$this->setPermission(DefaultPermissions::ROOT_OPERATOR);
				}else{
					$this->setPermission(DefaultPermissions::ROOT_USER);
				}
				$this->c = $c;
			}

			public function execute(CommandSender $sender, string $commandLabel, array $args){
				if(!$this->testPermission($sender)){
					return;
				}
				($this->c)($sender, $commandLabel, $args);
			}
		});
	}

	public static function existsData(string $str) : bool{
		return file_exists(Server::getInstance()->getDataPath() . "players/" . strtolower($str) . ".dat");
	}

	public static function getJsonEncodingOption() : int{
		return JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_BIGINT_AS_STRING;
	}
}