<?php

namespace blugin\Parkour\UI;

use blugin\Parkour\Parkour;
use blugin\Parkour\UI\admin\AddParkour;
use blugin\Parkour\UI\admin\ContinueAdd;
use blugin\Parkour\UI\admin\DelParkour;
use blugin\Parkour\UI\admin\SelManage;
use blugin\Parkour\UI\admin\SelWork;
use blugin\Parkour\Utils\Text;
use blugin\Parkour\UI\all\Play;
use pocketmine\Player;

abstract class UIPage {
    public Parkour $plugin;

    public static array $pages, $form_ids = [];
    public static SelWork $selWork;
    public static SelManage $selManage;
    public static Play $play;
    public static AddParkour $addParkour;
    public static ContinueAdd $continueAdd;
    public static DelParkour $delParkour;

    /**
     * get (UI) Page with folder and page name
     *
     * @param string $folder A folder name to find in
     * @param string $name A page name to find with
     *
     * @return UIPage|null
     */
    public static function getPageByName(string $folder, string $name): ?UIPage {
        return self::$pages[$folder][$name] ?? null;
    }

    /**
     * get (UI) Page with form id
     *
     * @param int $formId A form id to find with
     *
     * @return UIPage|null
     */
    public static function getPageByFormId(int $formId): ?UIPage {
        return self::$form_ids[$formId] ?? null;
    }

    /**
     * Init
     *
     * @brief Make index for search and get
     *
     * @return void
     */
    public static function init(): void {
        self::$selWork = new SelWork();
        self::$selManage = new SelManage();
        self::$play = new Play();
        self::$addParkour = new AddParkour();
        self::$continueAdd = new ContinueAdd();
        self::$delParkour = new DelParkour();

        self::$pages = [];
        self::$form_ids = [];

        self::$pages[self::$selWork->getFolderName()][self::$selWork->getName()] = self::$selWork;
        self::$pages[self::$selManage->getFolderName()][self::$selManage->getName()] = self::$selManage;
        self::$pages[self::$play->getFolderName()][self::$play->getName()] = self::$play;
        self::$pages[self::$addParkour->getFolderName()][self::$addParkour->getName()] = self::$addParkour;
        self::$pages[self::$continueAdd->getFolderName()][self::$continueAdd->getName()] = self::$continueAdd;
        self::$pages[self::$delParkour->getFolderName()][self::$delParkour->getName()] = self::$delParkour;

        self::$form_ids[self::$selWork::FORM_ID] = self::$selWork;
        self::$form_ids[self::$selManage::FORM_ID] = self::$selManage;
        self::$form_ids[self::$play::FORM_ID] = self::$play;
        $formId = (int) self::$addParkour::FORM_ID; // addParkour Form id
        $formIdList = array();
        for($i=0; $i<=17; $i=$i+1) {
            array_push($formIdList, $formId+$i); // addParkour Form id array
        }
        foreach($formIdList as $value) {
            self::$form_ids[$value] = self::$addParkour;
        }
        self::$form_ids[self::$continueAdd::FORM_ID] = self::$continueAdd;
        self::$form_ids[self::$delParkour::FORM_ID] = self::$delParkour;
    }

    /**
     * @param array  $data old ui data
     * @param string $title text to be title
     *
     * @return array newer ui data
     */
    public function setTitle(array $data, string $title) : array {
        if($title instanceof Text) {
            $title = $title->getText();
        }
        $data["title"] = $title;
        return $data;
    }

    /**
     * @param array  $data old ui data
     * @param string $content text for content
     *
     * @return array newer ui data
     */
    public function addContent(array $data, string $content) : array {
        if($content instanceof Text) {
            $content = $content->getText();
        }
        if(isset($data["content"])) {
            $data["content"] .= "\n".$content;
        } else {
            $data["content"] = $content;
        }
        return $data;
    }

    /**
     * @param array  $data old ui data
     * @param string $text text to be written on the button
     * @param int    $imageType image path or url
     * @param string $imagePath image path
     *
     * @return array newer ui data
     */
    public function addButton(array $data, string $text, int $imageType = -1, string $imagePath = "") : array {
        if($text instanceof Text) {
            $text = $text->getText();
        }
        $content = ["text" => $text];
        if($imageType !== -1) {
            $content["image"]["type"] = $imageType === 0 ? "path" : "url";
            $content["image"]["data"] = $imagePath;
        }
        $data["buttons"][] = $content;
        return $data;
    }

    abstract public function handle(Player $player, $response, $id): void;
    abstract public function sendTo(Player $player, $id): void;

    /**
     * @brief get page's folder name
     *
     * @return string
     */
    abstract public function getFolderName(): string;

    /**
     * @brief get page name
     *
     * @return string
     */
    abstract public function getName(): string;
}