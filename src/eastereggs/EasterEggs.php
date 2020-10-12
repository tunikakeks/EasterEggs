<?php
declare(strict_types=1);

namespace eastereggs;

use eastereggs\commands\EasterCommand;
use eastereggs\entity\EasterEgg;
use eastereggs\settings\SettingsManager;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\level\Location;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use function basename;
use function chr;
use function file_get_contents;
use function getimagesize;
use function imagecolorat;
use function imagecreatefrompng;
use function imagedestroy;
use function in_array;
use function scandir;
use function strlen;
use function substr;
use function var_dump;

final class EasterEggs extends PluginBase{

	public const PREFIX = "§aEaster§2Eggs §8» §7";

	/**
	 * @var Player[]
	 */
	public static $allowedEggAttackers = [];

	/** @var EasterEggs|null */
	private static $instance;

	/**
	 * @return EasterEggs|null
	 */
	public static function getInstance() : ?EasterEggs{
		return self::$instance;
	}

	public function onLoad() : void{
		self::$instance = $this;

		foreach($this->getResources() as $resource){
			$this->saveResource($resource->getFilename());
		}

		$this->reloadConfig();
		SettingsManager::getInstance()->initialize($this->getConfig()->getPath());
	}

	public function onEnable() : void{
		Entity::registerEntity(EasterEgg::class, true);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getCommandMap()->register($this->getName(), new EasterCommand());
	}

	/**
	 * @param string $file the path to the image, only png supported rn
	 * @return string the skin data
	 * @throws \Exception if it doesn't exist or if its not a .png file
	 */
	public static function imageToSkinData(string $file) : string{
		$skinData = "";
		$img = @imagecreatefrompng($file);
		if($img === false){
			throw new \Exception("Could not open provided image $file");
		}
		[$width, $height] = @getimagesize($file);
		for($y = 0; $y < $height; $y++){
			for($x = 0; $x < $width; $x++){
				$rgba = @imagecolorat($img, $x, $y);
				$a = ((~((int) ($rgba >> 24))) << 1) & 0xff;
				$r = ($rgba >> 16) & 0xff;
				$g = ($rgba >> 8) & 0xff;
				$b = $rgba & 0xff;
				$skinData .= chr($r).chr($g).chr($b).chr($a);
			}
		}
		@imagedestroy($img); // no memory leak :-)
		return $skinData;
	}

	/**
	 * @return string[]
	 */
	public static function getEggTypes() : array{
		$ext = ".png";
		$available = [];
		foreach(scandir(self::getInstance()->getDataFolder()) as $file){
			if(substr(basename($file), -strlen($ext)) !== $ext){
				continue;
			}
			$name = basename($file, $ext);
			if(substr($name, 0, 4) !== "egg."){
				continue;
			}
			$available[] = substr($name, 4);
		}
		return $available;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function isValidEggType(string $type) : bool{
		return in_array($type, self::getEggTypes());
	}

	/**
	 * @param Location $location
	 * @param string $type
	 * @return bool
	 */
	public static function spawnEasterEgg(Location $location, string $type) : bool{
		if(!self::isValidEggType($type))
			return false;
		$nbt = Entity::createBaseNBT($location->asVector3(), null, $location->getYaw() + 45.0, 0.0);
		$nbt->setTag(new CompoundTag("FoundBy", []));
		$skin = new Skin("EasterEgg", self::imageToSkinData(self::getInstance()->getDataFolder()."egg.$type.png"), "", "geometry.egg", file_get_contents(self::getInstance()->getDataFolder()."egg.geo.json"));
		$nbt->setTag(new CompoundTag("Skin", [
			new StringTag("Name", $skin->getSkinId()),
			new ByteArrayTag("Data", $skin->getSkinData()),
			new ByteArrayTag("CapeData", $skin->getCapeData()),
			new StringTag("GeometryName", $skin->getGeometryName()),
			new ByteArrayTag("GeometryData", $skin->getGeometryData())
		]));
		$entity = Entity::createEntity("EasterEgg", $location->getLevel(), $nbt);
		if(!$entity instanceof EasterEgg)
			return false;
		$entity->spawnToAll();
		$entity->sendSkin();
		return true;
	}

}