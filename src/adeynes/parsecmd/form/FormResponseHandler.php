<?php
declare(strict_types=1);

namespace adeynes\parsecmd\form;

use adeynes\parsecmd\parsecmd;
use pocketmine\Player;

class FormResponseHandler
{

    public static function handleCommandFormResponse(CommandForm $form, Player $player, array $data): void
    {
        $parsecmd = parsecmd::getInstance();
        $command_name = $form->getCommandName();

        if (!$command = $parsecmd->getCommand($command_name)) return;

        $blueprint = $command->getBlueprint();
        $usage = $blueprint->populateUsage($form, $data);

        $parsecmd->getPlugin()->getServer()->dispatchCommand(
            $player,
            "$command_name $usage"
        );
    }

}