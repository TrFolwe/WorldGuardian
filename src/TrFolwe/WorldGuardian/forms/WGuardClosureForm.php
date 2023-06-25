<?php

namespace TrFolwe\WorldGuardian\forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class WGuardClosureForm extends MenuForm
{

    public function __construct(array $formOptions, array $menuButtons, \Closure $submitClosure)
    {
        parent::__construct(
            $formOptions["title"],
            $formOptions["content"] ?? "",
            array_map(fn($c) => new MenuOption($c), $menuButtons),
            function (Player $player, int $selected) use(&$submitClosure): void {$submitClosure($player, $this->getOption($selected)->getText());});
    }
}