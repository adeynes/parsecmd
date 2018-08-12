<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\command\Command as PMCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;

abstract class Command extends PMCommand implements PluginIdentifiableCommand
{

    /** @var Plugin */
    protected $plugin;

    /** @var CommandBlueprint */
    protected $blueprint;

    protected function __construct(Plugin $plugin, CommandBlueprint $blueprint, string $name, string $permission = null,
                                   string $description = '', string $usage = null) {
        $this->plugin = $plugin;
        $this->blueprint = $blueprint;
        $this->setPermission($permission);
        parent::__construct($name, $description, $usage ?? $blueprint->getUsage());
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function getBlueprint(): CommandBlueprint
    {
        return $this->blueprint;
    }

    /**
     * This contains boilerplate code e.g. permission checking, and runs _execute()
     * @param CommandSender $sender
     * @param string $label
     * @param array $arguments
     * @return bool
     */
    public function execute(CommandSender $sender, string $label, array $arguments): bool
    {
        if (!$this->testPermission($sender)) return false;

        $command = CommandParser::parse($this, $arguments);
        if ($command->getArgumentCount() < $this->getBlueprint()->getMinimumArgumentCount()) {
            throw new InvalidCommandSyntaxException;
        }

        return $this->_execute($sender, $command);
    }

    abstract public function _execute(CommandSender $sender, ParsedCommand $command): bool;

}