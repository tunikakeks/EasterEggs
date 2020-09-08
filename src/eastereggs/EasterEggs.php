<?php
declare(strict_types=1);
namespace eastereggs;

use eastereggs\commands\EasterCommand;
use eastereggs\entity\EasterEgg;
use Exception;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use function chr;
use function getimagesize;
use function imagecolorat;
use function imagecreatefrompng;
use function imagedestroy;

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
	}

	public function onEnable() : void{
		Entity::registerEntity(EasterEgg::class, true);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getCommandMap()->register($this->getName(), new EasterCommand());
	}

	public static function getPath() : string{
		return self::getInstance()->getFile();
	}

	/**
	 * @param string $file the path to the image, only png supported rn
	 * @return string the skin data
	 * @throws Exception if it doesn't exist or if its not a .png file
	 */
	public static function imageToSkinData(string $file) : string{
		$skinData = "";
		$img = @imagecreatefrompng($file);
		if($img === false){
			throw new Exception("Could not open provided image $file");
		}
		[$width, $height] = @getimagesize($file);
		for($y = 0; $y < $height; $y++){
			for($x = 0; $x < $width; $x++){
				$rgba = @imagecolorat($img, $x, $y);
				$a = ((~((int)($rgba >> 24))) << 1) & 0xff;
				$r = ($rgba >> 16) & 0xff;
				$g = ($rgba >> 8) & 0xff;
				$b = $rgba & 0xff;
				$skinData .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img); // no memory leak :-)
		return $skinData;
	}

}