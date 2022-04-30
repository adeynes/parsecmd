<?php
declare(strict_types=1);

namespace adeynes\parsecmd\form;

use pocketmine\player\Player;

class CommandForm extends Form
{

    /** @var string */
    protected string $command_name;

    public function getCommandName(): ?string
    {
        return $this->command_name;
    }

    public function setCommandName(string $command_name): self
    {
        $this->command_name = $command_name;
        return $this;
    }

    public function handleResponse(Player $player, $data): void
    {
        if (is_null($data)) return;
        assert(is_array($data)); // This is a custom_form, $data should always be array or null

        FormResponseHandler::handleCommandFormResponse($this, $player, $this->process($data));
    }

}