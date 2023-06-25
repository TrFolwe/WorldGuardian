<?php

namespace TrFolwe\forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use pocketmine\player\Player;

class WGuardPermissionForm extends CustomForm
{

    public function __construct(string $formTitle, array $formElements, \Closure $onSubmit)
    {
        parent::__construct(
            $formTitle,
            $formElements,
            function (Player $player, CustomFormResponse $response) use($onSubmit) :void {$onSubmit($player, $response->getAll());});
    }
}