<?php

namespace blugin\Parkour\Utils;

use pocketmine\lang\TextContainer;

class Color extends TextContainer {
    public const COLOR_CODE = "\xc2\xa7";

    // from https://github.com/pmmp/PocketMine-MP/blob/stable/src/pocketmine/utils/TextFormat.php#L53
    public const BLACK = "0";
    public const DARK_BLUE = "1";
    public const DARK_GREEN = "2";
    public const DARK_AQUA = "3";
    public const DARK_RED = "4";
    public const DARK_PURPLE = "5";
    public const GOLD = "6";
    public const GRAY = "7";
    public const DARK_GRAY = "8";
    public const BLUE = "9";
    public const GREEN = "a";
    public const AQUA = "b";
    public const RED = "c";
    public const LIGHT_PURPLE = "d";
    public const YELLOW = "e";
    public const WHITE = "f";

    public const OBFUSCATED = "k";
    public const BOLD = "l";
    public const STRIKETHROUGH = "m";
    public const UNDERLINE = "n";
    public const ITALIC = "o";
    public const RESET = "r";

    protected string $code;
    public static string $prefix, $explain, $button, $warning, $error;

    public function __construct(string $code) {
        parent::__construct($code);
        $this->code = $code;
    }

    public static function init(): void {
        self::$prefix = Text::getTranslatedString("prefix");
        self::$explain = Text::getTranslatedString("explain");
        self::$button = Text::getTranslatedString("button");
        self::$warning = Text::getTranslatedString("warning");
        self::$error = Text::getTranslatedString("error");
    }

    public function getText() : string{
        return self::COLOR_CODE.$this->code;
    }
}